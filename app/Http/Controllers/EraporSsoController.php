<?php

namespace App\Http\Controllers;

use App\Services\SettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class EraporSsoController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->hasAnyRole(['Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas', 'Guru BK', 'Guru Piket', 'Siswa']), 403);

        $settings = new SettingsManager();
        $menuVisibility = $settings->get('menu_visibility', ['guru' => [], 'siswa' => []]);

        $guruEnabledMenus = $menuVisibility['guru'] ?? [];
        $siswaEnabledMenus = $menuVisibility['siswa'] ?? [];

        if ($user->hasRole('Siswa')) {
            abort_unless(empty($siswaEnabledMenus) || in_array('integrations_erapor_sso', $siswaEnabledMenus, true), 403, 'Menu e-Rapor dinonaktifkan oleh admin.');
        } else {
            abort_unless(empty($guruEnabledMenus) || in_array('integrations_erapor_sso', $guruEnabledMenus, true), 403, 'Menu e-Rapor dinonaktifkan oleh admin.');
        }

        $identity = trim((string) optional($user->guru)->nip);
        if ($identity === '' && $user->siswa) {
            $identity = trim((string) ($user->siswa->nisn ?? $user->siswa->nis ?? ''));
        }

        $secret = (string) config('services.erapor.sso_secret');
        $target = trim((string) config('services.erapor.sso_url'));
        abort_unless($identity !== '' && $secret !== '' && filter_var($target, FILTER_VALIDATE_URL), 503, 'Akses cepat e-Rapor belum dikonfigurasi.');

        $payload = [
            'aud' => 'erapor',
            'nip' => $identity,
            'jti' => (string) Str::uuid(),
            'iat' => now()->timestamp,
            'exp' => now()->addSeconds(60)->timestamp,
        ];
        $encoded = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
        $token = $encoded . '.' . hash_hmac('sha256', $encoded, $secret);
        return redirect()->away(rtrim($target, '/') . '?token=' . urlencode($token));
    }
}