<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use App\Models\Pengembangan;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateService
{
    protected ImageManager $image;

    public function __construct()
    {
        $this->image = new ImageManager(new Driver());
    }

    public function generate($template, Pengembangan $item, string $name, string $barcode, ?string $nomorSurat = null): string
    {
        $bgPath = $template->background_image ?? null;
        if (!$bgPath || !Storage::disk('public')->exists($bgPath)) {
            return $this->generateFallbackPdf($item, $name, $barcode);
        }

        $fullPath = Storage::disk('public')->path($bgPath);
        $img = $this->image->read($fullPath);

        $width = $img->width();
        $height = $img->height();

        // Canvas editor Fabric.js menampilkan background dengan scale = min(900/w, 600/h)
        // Maka untuk konversi koordinat canvas 900x600 ke gambar asli: scale = max(w/900, h/600)
        $scale = max($width / 900, $height / 600);

        $positions = [];
        if (!empty($template->placeholder_positions)) {
            $positions = json_decode($template->placeholder_positions, true) ?? [];
        }

        $placeholders = [
            'name' => $name,
            'kegiatan->nama_kegiatan' => $item->nama_kegiatan ?? '',
            'kegiatan->tema_kegiatan' => $item->tema_kegiatan ?? '',
            'barcode' => $barcode,
            'nomor_surat' => $nomorSurat ?? $item->nomor_surat ?? '',
        ];

        foreach ($placeholders as $key => $text) {
            $pos = $positions[$key] ?? null;
            if (!$pos) continue;

            $x = ($pos['x'] ?? 450) * $scale;
            $y = ($pos['y'] ?? 300) * $scale;
            $fontSize = ($pos['font_size'] ?? 24) * $scale;
            // Minimum font size agar selalu proporsional besar
            if ($key === 'name' && $fontSize < 120) $fontSize = 120;
            elseif ($key !== 'barcode' && $fontSize < 48) $fontSize = 48;
            $color = $pos['color'] ?? '#000000';
            $fontFile = null;

            // Try per-placeholder font file, then template-level font file
            if (!empty($pos['font_file']) && Storage::disk('public')->exists($pos['font_file'])) {
                $fontFile = Storage::disk('public')->path($pos['font_file']);
            } elseif (!empty($template->font_file) && Storage::disk('public')->exists($template->font_file)) {
                $fontFile = Storage::disk('public')->path($template->font_file);
            }

            $alignment = $pos['align'] ?? $pos['alignment'] ?? 'center';

            // Add text to image
            $img->text($text, (int) $x, (int) $y, function ($font) use ($fontSize, $color, $fontFile, $alignment) {
                if ($fontFile) {
                    $font->file($fontFile);
                }
                $font->size($fontSize);
                $font->color($color);
                $font->align($alignment === 'left' ? 'left' : ($alignment === 'right' ? 'right' : 'center'));
                $font->valign('middle');
            });
        }

        $outputPath = 'certificates/cert_' . uniqid() . '.jpg';
        $img->toJpeg(90)->save(Storage::disk('public')->path($outputPath));

        return $outputPath;
    }

    public function generatePdf($template, Pengembangan $item, string $name, string $barcode, ?string $nomorSurat = null): string
    {
        $imagePath = $this->generate($template, $item, $name, $barcode, $nomorSurat);

        if (str_starts_with($imagePath, 'certificates/')) {
            $fullImgPath = Storage::disk('public')->path($imagePath);
            if (file_exists($fullImgPath)) {
                $imgData = base64_encode(file_get_contents($fullImgPath));
                $src = 'data:image/jpeg;base64,' . $imgData;
                $html = '<!DOCTYPE html><html><body style="margin:0;padding:0;"><img src="' . $src . '" style="width:100%;height:auto;"/></body></html>';
                $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
                $pdfPath = 'certificates/pdf_' . uniqid() . '.pdf';
                Storage::disk('public')->put($pdfPath, $pdf->output());
                return $pdfPath;
            }
        }

        // Fallback
        $htmlView = view('pengembangan.certificate_template', [
            'name' => $name,
            'kegiatan' => $item,
            'barcode' => $barcode,
        ])->render();
        $pdf = Pdf::loadHTML($htmlView)->setPaper('a4', 'landscape');
        $pdfPath = 'certificates/pdf_' . uniqid() . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());
        return $pdfPath;
    }

    public function preview($template, Pengembangan $item, string $name, string $barcode): string
    {
        $imagePath = $this->generate($template, $item, $name, $barcode);
        $fullImgPath = Storage::disk('public')->path($imagePath);
        $imgData = base64_encode(file_get_contents($fullImgPath));
        return 'data:image/jpeg;base64,' . $imgData;
    }

    protected function generateFallbackPdf(Pengembangan $item, string $name, string $barcode): string
    {
        $htmlView = view('pengembangan.certificate_template', [
            'name' => $name,
            'kegiatan' => $item,
            'barcode' => $barcode,
        ])->render();
        $pdf = Pdf::loadHTML($htmlView)->setPaper('a4', 'landscape');
        $pdfPath = 'certificates/pdf_' . uniqid() . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->output());
        return $pdfPath;
    }

    public function previewFromFile($uploadedFile, $positionsJson): string
    {
        $img = $this->image->read($uploadedFile->getRealPath());
        $positions = json_decode($positionsJson, true) ?? [];

        // Canvas editor 900x600, scale ke ukuran gambar asli: max(w/900, h/600)
        $scale = max($img->width() / 900, $img->height() / 600);

        $placeholders = [
            'name' => 'Nama Peserta',
            'kegiatan->nama_kegiatan' => 'Nama Kegiatan',
            'kegiatan->tema_kegiatan' => 'Tema Kegiatan',
            'barcode' => 'ABC123',
            'nomor_surat' => 'No. Sertifikat',
        ];

        foreach ($placeholders as $key => $text) {
            $pos = $positions[$key] ?? null;
            if (!$pos) continue;
            $x = ($pos['x'] ?? 450) * $scale;
            $y = ($pos['y'] ?? 300) * $scale;
            $fontSize = ($pos['font_size'] ?? 24) * $scale;
            // Minimum font size
            if ($key === 'name' && $fontSize < 120) $fontSize = 120;
            elseif ($key !== 'barcode' && $fontSize < 48) $fontSize = 48;
            $color = $pos['color'] ?? '#000000';
            $alignment = $pos['align'] ?? $pos['alignment'] ?? 'center';

            $img->text($text, (int) $x, (int) $y, function ($font) use ($fontSize, $color, $alignment) {
                $font->size($fontSize);
                $font->color($color);
                $font->align($alignment === 'left' ? 'left' : ($alignment === 'right' ? 'right' : 'center'));
                $font->valign('middle');
            });
        }

        $outPath = 'certificates/preview_' . uniqid() . '.jpg';
        $img->toJpeg(90)->save(Storage::disk('public')->path($outPath));
        return url('storage/' . $outPath);
    }

    public function streamPreview($template, Pengembangan $item, string $name, string $barcode)
    {
        $bgPath = $template->background_image ?? null;
        if (!$bgPath || !Storage::disk('public')->exists($bgPath)) {
            $htmlView = view('pengembangan.certificate_template', [
                'name' => $name,
                'kegiatan' => $item,
                'barcode' => $barcode,
            ])->render();
            $pdf = Pdf::loadHTML($htmlView)->setPaper('a4', 'landscape');
            return response($pdf->output(), 200)->header('Content-Type', 'application/pdf');
        }

        $fullPath = Storage::disk('public')->path($bgPath);
        $img = $this->image->read($fullPath);
        $width = $img->width();
        $height = $img->height();

        // Canvas editor 900x600, scale ke ukuran gambar asli: max(w/900, h/600)
        $scale = max($width / 900, $height / 600);

        $positions = [];
        if (!empty($template->placeholder_positions)) {
            $positions = json_decode($template->placeholder_positions, true) ?? [];
        }

        $placeholders = [
            'name' => $name,
            'kegiatan->nama_kegiatan' => $item->nama_kegiatan ?? '',
            'kegiatan->tema_kegiatan' => $item->tema_kegiatan ?? '',
            'barcode' => $barcode,
            'nomor_surat' => request()->input('nomor_surat', $item->nomor_surat ?? ''),
        ];

        foreach ($placeholders as $key => $text) {
            $pos = $positions[$key] ?? null;
            if (!$pos) continue;

            $x = ($pos['x'] ?? 450) * $scale;
            $y = ($pos['y'] ?? 300) * $scale;
            $fontSize = ($pos['font_size'] ?? 24) * $scale;
            // Minimum font size agar selalu proporsional besar
            if ($key === 'name' && $fontSize < 120) $fontSize = 120;
            elseif ($key !== 'barcode' && $fontSize < 48) $fontSize = 48;
            $color = $pos['color'] ?? '#000000';
            $fontFamily = $pos['font_family'] ?? 'Arial, sans-serif';
            $fontFile = null;
            if (!empty($pos['font_file']) && Storage::disk('public')->exists($pos['font_file'])) {
                $fontFile = Storage::disk('public')->path($pos['font_file']);
            } elseif (!empty($template->font_file) && Storage::disk('public')->exists($template->font_file)) {
                $fontFile = Storage::disk('public')->path($template->font_file);
            }
            $alignment = $pos['align'] ?? $pos['alignment'] ?? 'center';

            $img->text($text, (int) $x, (int) $y, function ($font) use ($fontSize, $color, $fontFile, $alignment, $fontFamily) {
                if ($fontFile) $font->file($fontFile);
                $font->size($fontSize);
                $font->color($color);
                $font->align($alignment === 'left' ? 'left' : ($alignment === 'right' ? 'right' : 'center'));
                $font->valign('middle');
            });
        }

        $tempPath = storage_path('app/temp_preview_' . uniqid() . '.jpg');
        $img->toJpeg(90)->save($tempPath);

        $imgData = base64_encode(file_get_contents($tempPath));
        @unlink($tempPath);

        $html = '<!DOCTYPE html><html><body style="margin:0;padding:0;display:flex;justify-content:center;align-items:center;min-height:100vh;background:#333;">';
        $html .= '<img src="data:image/jpeg;base64,' . $imgData . '" style="max-width:100%;max-height:100vh;object-fit:contain;"/>';
        $html .= '</body></html>';

        // Convert to PDF for download/preview
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
        return response($pdf->output(), 200)->header('Content-Type', 'application/pdf');
    }

    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }
}
