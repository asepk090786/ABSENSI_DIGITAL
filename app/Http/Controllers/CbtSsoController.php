<?php

namespace App\Http\Controllers;

use App\Models\CbtSsoTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\SettingsManager;

class CbtSsoController extends Controller
{
    public function login(Request $request, SettingsManager $settings)
    {
        $user = $request->user();
        abort_unless($this->canUseCbtSso($user, $settings), 403, 'Login Garuda CBT tidak tersedia untuk akun ini.');

        $startUrl = (string) config('services.cbt_sso.start_url');
        $start = parse_url($startUrl);
        abort_unless(
            $start
            && ($start['scheme'] ?? '') === 'https'
            && ! empty($start['host'])
            && empty($start['query'])
            && empty($start['fragment']),
            503,
            'Alamat login Garuda CBT belum dikonfigurasi dengan benar.'
        );

        return redirect()->away($startUrl)
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function launch(Request $request, SettingsManager $settings)
    {
        $user = $request->user();
        abort_unless($this->canUseCbtSso($user, $settings), 403, 'Login Garuda CBT tidak tersedia untuk akun ini.');

        $callbackUrl = (string) config('services.cbt_sso.callback_url');
        $callback = parse_url($callbackUrl);
        abort_unless(
            $callback
            && ($callback['scheme'] ?? '') === 'https'
            && ! empty($callback['host'])
            && empty($callback['query'])
            && empty($callback['fragment']),
            503,
            'Alamat callback Garuda CBT belum dikonfigurasi dengan benar.'
        );

        $state = (string) $request->query('state', '');
        abort_unless(preg_match('/\A[a-f0-9]{64}\z/', $state) === 1, 400, 'Permintaan SSO tidak valid.');

        CbtSsoTicket::where('expires_at', '<', now()->subDay())->delete();

        $plainTicket = Str::random(64);
        CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', $plainTicket),
            'state' => $state,
            'expires_at' => now()->addSeconds(90),
        ]);
        Log::info('Garuda CBT SSO ticket issued.');

        $separator = str_contains($callbackUrl, '?') ? '&' : '?';
        $destination = $callbackUrl . $separator . http_build_query([
            'ticket' => $plainTicket,
            'state' => $state,
        ]);

        return redirect()->away($destination)
            ->header('Cache-Control', 'no-store, private')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function exchange(Request $request)
    {
        $data = $request->validate([
            'ticket' => ['required', 'string', 'size:64', 'regex:/\A[A-Za-z0-9]+\z/'],
        ]);

        $ticket = DB::transaction(function () use ($data) {
            $ticket = CbtSsoTicket::query()
                ->where('ticket_hash', hash('sha256', $data['ticket']))
                ->whereNull('consumed_at')
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->first();

            if (! $ticket) {
                return null;
            }

            $ticket->forceFill(['consumed_at' => now()])->save();

            return $ticket;
        });

        if (! $ticket) {
            Log::warning('Garuda CBT SSO ticket exchange rejected.');
            throw ValidationException::withMessages([
                'ticket' => 'Tiket SSO tidak valid, sudah digunakan, atau kedaluwarsa.',
            ]);
        }

        $user = User::with(['guru', 'siswa.kelas'])->find($ticket->user_id);
        if (! $user || ! $user->is_active) {
            Log::warning('Garuda CBT SSO exchange rejected an inactive or missing SIMADIS account.');
            abort(403, 'Akun SIMADIS tidak aktif.');
        }

        $roles = $user->roleNames();
        $role = $this->cbtRole($roles);
        $activeYears = DB::table('tahun_ajaran')->where('is_active', true)->get();
        $activeSemesters = $activeYears->count() === 1
            ? DB::table('semester')
                ->where('tahun_ajaran_id', $activeYears->first()->id)
                ->where('is_active', true)
                ->get()
            : collect();
        Log::info('Garuda CBT SSO identity exchange completed.', [
            'cbt_role' => $role ?? 'unmapped',
        ]);

        return response()->json([
            'state' => $ticket->state,
            'user' => [
                'simadis_user_id' => (string) $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => (bool) $user->is_active,
                'roles' => $roles,
                'cbt_role' => $role,
                'nip' => $user->nip ?: $user->guru?->nip,
                'nis' => $user->siswa?->nis,
                'nisn' => $user->siswa?->nisn,
                'current_class_name' => $user->siswa?->kelas?->nama_kelas,
                'active_year_label' => $activeYears->count() === 1 ? $activeYears->first()->nama_tahun : null,
                'active_semester_label' => $activeSemesters->count() === 1 ? $activeSemesters->first()->nama_semester : null,
            ],
        ])->header('Cache-Control', 'no-store, private');
    }

    private function cbtRole(array $roles): ?string
    {
        $normalized = array_map(
            static fn (string $role): string => mb_strtolower(trim($role)),
            $roles
        );

        if (in_array('admin', $normalized, true)) {
            return 'admin';
        }

        if (in_array('siswa', $normalized, true)) {
            return 'siswa';
        }

        foreach ($normalized as $role) {
            if (
                $role === 'guru' ||
                $role === 'wali kelas' ||
                str_starts_with($role, 'guru ')
            ) {
                return 'guru';
            }
        }

        return null;
    }

    private function canUseCbtSso(?User $user, SettingsManager $settings): bool
    {
        if (! $user || ! $user->is_active || ! (bool) $settings->get('integrations.cbt_sso.enabled', false)) {
            return false;
        }

        if ($user->hasRole('Siswa')) {
            $menuGroup = 'siswa';
        } elseif ($user->hasRole('Admin')) {
            return true;
        } elseif ($user->hasAnyRole(['Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas', 'Guru BK', 'Guru Piket', 'Pembina'])) {
            $menuGroup = 'guru';
        } else {
            return false;
        }

        $visibleMenus = $settings->get('menu_visibility.' . $menuGroup, []);

        return empty($visibleMenus) || in_array('integrations_cbt_sso', $visibleMenus, true);
    }
}
