<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\QrLoginToken;
use App\Models\Role;
use App\Models\Sekolah;
use App\Models\User;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class KartuLoginController extends Controller
{
    public function index()
    {
        $personalOnly = false;
        $roles = Role::orderBy('role_name')->get();
        $kelas = Kelas::withCount('siswa')->orderBy('nama_kelas')->get();
        $sekolah = Sekolah::first();
        $users = User::with(['role', 'siswa.kelas', 'guru', 'kepalaSekolah', 'tenagaPendidikan'])
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->orderBy('name')
            ->get();
        
        // Filter berdasarkan role parameter dari URL (untuk redirect dari page generate)
        $roleFilter = request('role');
        if ($roleFilter) {
            $users = $users->filter(fn ($user) => $user->role->role_name === $roleFilter)->values();
        }
        
        $qrWriter = new PngWriter();
        $users->each(function (User $user) use ($qrWriter) {
            $token = QrLoginToken::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->latest()
                ->first();
            if ($token && filled($token->encrypted_token)) {
                $rawToken = Crypt::decryptString($token->encrypted_token);
            } else {
                $rawToken = $this->issueToken($user)[1];
            }
            $qrTarget = route('qr_login.consume', $rawToken);
            $user->setAttribute('login_qr', $qrWriter->write(QrCode::create($qrTarget)->setSize(180)->setMargin(8))->getDataUri());
        });

        return view('kartu_login.index', compact('roles', 'kelas', 'users', 'sekolah', 'personalOnly'));
    }

    public function personal()
    {
        abort_unless(auth()->user()->hasAnyRole(['Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas', 'Guru BK', 'Guru Piket', 'Siswa']), 403);

        $user = auth()->user()->load(['role', 'siswa.kelas', 'guru']);
        $sekolah = Sekolah::first();
        $token = QrLoginToken::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$token || !filled($token->encrypted_token)) {
            $rawToken = $this->issueToken($user)[1];
        } else {
            $rawToken = Crypt::decryptString($token->encrypted_token);
        }

        $qrUrl = route('qr_login.consume', $rawToken);
        $user->setAttribute('login_qr', (new PngWriter())
            ->write(QrCode::create($qrUrl)->setSize(180)->setMargin(8))
            ->getDataUri());
        $users = collect([$user]);
        $roles = collect();
        $kelas = collect();
        $personalOnly = true;

        return view('kartu_login.index', compact('roles', 'kelas', 'users', 'sekolah', 'personalOnly'));
    }

    public function generatePage()
    {
        abort_unless(auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']), 403);

        $roles = Role::orderBy('role_name')->get();
        $kelas = Kelas::withCount('siswa')->orderBy('nama_kelas')->get();
        $generatedUsers = null;
        $validated = session('kartu_login_generate');

        if ($validated) {
            $generatedUsers = $this->loadUsersWithQr($validated);
        }

        return view('kartu_login.generate', compact('roles', 'kelas', 'generatedUsers', 'validated'));
    }

    public function generate(Request $request)
    {
        abort_unless($request->user()->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']), 403);

        $validated = $request->validate([
            'role' => ['required', 'exists:roles,role_name'],
            'kelas_id' => ['nullable', 'required_if:role,Siswa', 'exists:kelas,id'],
        ]);

        $query = User::with(['role', 'siswa.kelas'])
            ->whereHas('role', fn ($roleQuery) => $roleQuery->where('role_name', $validated['role']))
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->orderBy('name');
        if ($validated['role'] === 'Siswa' && ! empty($validated['kelas_id'])) {
            $query->whereHas('siswa', fn ($siswaQuery) => $siswaQuery->where('kelas_id', $validated['kelas_id']));
        }

        $users = $query->get();
        
        // Hitung berapa banyak token baru vs yang di-reuse
        $newTokenCount = 0;
        $reusedTokenCount = 0;
        foreach ($users as $user) {
            $existingToken = QrLoginToken::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->exists();
            if ($existingToken) {
                $reusedTokenCount++;
            } else {
                $newTokenCount++;
            }
        }
        
        $generatedUsers = $this->loadUsersWithQr($validated, $query, true);
        $generatedCount = $generatedUsers->count();
        session(['kartu_login_generate' => $validated]);

        $roles = Role::orderBy('role_name')->get();
        $kelas = Kelas::withCount('siswa')->orderBy('nama_kelas')->get();

        // Buat pesan yang lebih informatif
        if ($reusedTokenCount > 0 && $newTokenCount > 0) {
            $successMessage = sprintf(
                'QR login ditampilkan untuk %d akun (%d akun baru dengan token baru, %d akun dengan token yang sudah ada). Berlaku selama 1 tahun ajaran.',
                $generatedCount,
                $newTokenCount,
                $reusedTokenCount
            );
        } elseif ($reusedTokenCount > 0) {
            $successMessage = sprintf(
                'QR login ditampilkan untuk %d akun. Semua menggunakan token yang sudah ada (belum expired). Berlaku selama 1 tahun ajaran.',
                $generatedCount
            );
        } else {
            $successMessage = sprintf(
                'QR login berhasil dibuat untuk %d akun baru. Berlaku selama 1 tahun ajaran. Token tersimpan di database dan dapat dilihat di halaman Kartu Login.',
                $generatedCount
            );
        }

        return view('kartu_login.generate', compact('roles', 'kelas', 'generatedUsers', 'validated'))
            ->with('success', $successMessage)
            ->with('generatedCount', $generatedCount)
            ->with('newTokenCount', $newTokenCount)
            ->with('reusedTokenCount', $reusedTokenCount);
    }

    private function loadUsersWithQr(array $validated, $query = null, bool $replaceTokens = false)
    {
        $query ??= User::with(['role', 'siswa.kelas'])
            ->whereHas('role', fn ($roleQuery) => $roleQuery->where('role_name', $validated['role']))
            ->whereNotNull('username')
            ->where('username', '!=', '')
            ->orderBy('name');

        if ($validated['role'] === 'Siswa' && ! empty($validated['kelas_id'])) {
            $query->whereHas('siswa', fn ($siswaQuery) => $siswaQuery->where('kelas_id', $validated['kelas_id']));
        }

        $users = $query->get();
        
        $qrWriter = new PngWriter();
        $users->each(function (User $user) use ($qrWriter, $replaceTokens) {
            // Cek token yang ada dan masih berlaku (tidak perlu check used_at)
            $existingToken = QrLoginToken::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->latest()
                ->first();

            // Jika ada token yang masih berlaku, gunakan yang ada
            // Jika tidak ada, buat token baru
            $token = $existingToken ?? ($replaceTokens ? $this->issueToken($user)[0] : null);

            if (!$token) {
                return;
            }

            $rawToken = Crypt::decryptString($token->encrypted_token);
            $qrUrl = route('qr_login.consume', $rawToken);
            $user->setAttribute('login_qr', $qrWriter->write(QrCode::create($qrUrl)->setSize(180)->setMargin(8))->getDataUri());
            $user->setAttribute('login_qr_url', $qrUrl);
        });

        return $users->filter(fn (User $user) => $user->login_qr)->values();
    }

    public function consume(Request $request, string $token)
    {
        try {
            $record = QrLoginToken::with('user')
                ->where('token_hash', hash('sha256', $token))
                ->where('expires_at', '>', now())
                ->firstOrFail();

            abort_unless($record->user->is_active, 403, 'Akun pengguna tidak aktif.');

            // Track last scanned untuk audit trail (optional)
            // $record->update(['last_scanned_at' => now()]);
            
            Auth::login($record->user);
            $request->session()->regenerate();

            return redirect()->intended(route('home'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Cek alasan token tidak ditemukan
            $tokenHash = hash('sha256', $token);
            $expiredToken = QrLoginToken::where('token_hash', $tokenHash)->first();
            
            if ($expiredToken) {
                abort(410, 'QR Code sudah expired. Silahkan minta QR Code baru kepada admin.');
            } else {
                abort(404, 'QR Code tidak valid. Pastikan kode QR lengkap dan belum pernah dimodifikasi.');
            }
        }
    }

    private function issueToken(User $user): array
    {
        $rawToken = Str::random(64);
        $token = QrLoginToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $rawToken),
            'encrypted_token' => Crypt::encryptString($rawToken),
            'expires_at' => now()->addYear(),
            'created_by' => auth()->id(),
        ]);
        $token->plain_token = $rawToken;

        return [$token, $rawToken];
    }
}
