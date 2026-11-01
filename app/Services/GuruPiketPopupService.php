<?php

namespace App\Services;

use App\Models\User;

class GuruPiketPopupService
{
    private const CONTENT_PURIFIER_CONFIG = [
        'HTML.Allowed' => 'p[style],br,strong,b,em,i,u,s,ul,ol,li,blockquote,h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],div[style],span[style],a[href|title],table[style],thead[style],tbody[style],tfoot[style],tr[style],td[colspan|rowspan|style],th[colspan|rowspan|style],img[src|alt|width|height]',
        'CSS.AllowedProperties' => 'font,font-family,font-size,font-weight,font-style,text-decoration,text-align,color,background-color,margin-left,margin-right',
        'AutoFormat.AutoParagraph' => true,
        'AutoFormat.RemoveEmpty' => true,
    ];

    public function __construct(private SettingsManager $settings)
    {
    }

    public function sanitizeContent(string $content): string
    {
        $runtimeUser = function_exists('posix_geteuid') ? (string) posix_geteuid() : 'shared';
        $cachePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'absensi-digital-purifier-' . $runtimeUser;

        if (! is_dir($cachePath) && ! mkdir($cachePath, 0700, true) && ! is_dir($cachePath)) {
            throw new \RuntimeException("Tidak dapat membuat direktori cache HTMLPurifier: {$cachePath}");
        }

        if (! is_writable($cachePath)) {
            throw new \RuntimeException("Direktori cache HTMLPurifier tidak dapat ditulis: {$cachePath}");
        }

        config(['purifier.cachePath' => $cachePath]);

        return app('purifier')->clean($content, self::CONTENT_PURIFIER_CONFIG);
    }

    public function forLogin(User $user): ?array
    {
        $hariPiketHariIni = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ][now()->dayOfWeekIso];
        $hariPiketGuru = (array) ($user->guru?->hari_piket ?? []);

        if (! in_array($hariPiketHariIni, $hariPiketGuru, true)) {
            return null;
        }

        return [
            'title' => $this->settings->get('guru_piket_popup.title', 'Tugas Piket Hari Ini'),
            'content' => $this->sanitizeContent(
                (string) $this->settings->get(
                    'guru_piket_popup.content',
                    'Anda mendapatkan tugas piket hari ini. Silakan melaksanakan tugas sesuai jadwal dan ketentuan sekolah.'
                )
            ),
        ];
    }
}
