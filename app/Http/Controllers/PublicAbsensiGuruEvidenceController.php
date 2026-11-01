<?php

namespace App\Http\Controllers;

use App\Models\AbsensiGuru;
use Illuminate\Support\Facades\Storage;

class PublicAbsensiGuruEvidenceController extends Controller
{
    public function show(string $token)
    {
        $absensi = AbsensiGuru::where('bukti_token', $token)->firstOrFail();
        abort_unless($absensi->bukti_path, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($absensi->bukti_path), 404);

        $path = $disk->path($absensi->bukti_path);
        $mimeType = mime_content_type($path);
        abort_unless(in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png'], true), 404);

        return response()->file($path, [
            'Content-Type' => $mimeType,
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => 'sandbox',
        ]);
    }
}
