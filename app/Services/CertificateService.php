<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use App\Models\Pengembangan;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

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

        $positions = [];
        if (!empty($template->placeholder_positions)) {
            $positions = json_decode($template->placeholder_positions, true) ?? [];
        }

        if (($template->include_barcode ?? false) && !isset($positions['barcode'])) {
            $positions['barcode'] = [
                'x_ratio' => 0.5,
                'y_ratio' => 0.8,
                'font_size' => 16,
                'color' => '#000000',
                'align' => 'center',
                'is_qr' => $template->barcode_is_qr ?? true,
                'qr_size' => $template->barcode_qr_size ?? 180,
            ];
        }

        $placeholders = [
            'name' => $name,
            'kegiatan->nama_kegiatan' => $item->nama_kegiatan ?? '',
            'kegiatan->tema_kegiatan' => $item->tema_kegiatan ?? '',
            'barcode' => $barcode,
            'nomor_surat' => $nomorSurat ?? '',
        ];

        foreach ($placeholders as $key => $text) {
            $pos = $positions[$key] ?? null;
            if (!$pos) continue;

            $normalizedPos = $this->normalizeEditorPlaceholderPosition($pos, $width, $height);
            $x = $normalizedPos['x'] ?? ($width / 2);
            $y = $normalizedPos['y'] ?? ($height / 2);
            $fontSize = $normalizedPos['font_size'] ?? 24;
            $color = $normalizedPos['color'] ?? '#000000';
            $fontFile = $this->resolveFontFile($pos['font_file'] ?? null, $template->font_file ?? null);

            $alignment = $pos['align'] ?? $pos['alignment'] ?? 'center';
            // Special-case: render barcode as QR image or as text based on placeholder options
            if ($key === 'barcode') {
                $isQr = $template->barcode_is_qr ?? true;
                if (isset($pos['is_qr'])) {
                    $isQr = (bool) $pos['is_qr'];
                }
                $qrSize = isset($pos['qr_size']) ? (int) $pos['qr_size'] : ($template->barcode_qr_size ? (int) $template->barcode_qr_size : max(80, min(300, (int) ($fontSize * 4))));
                if ($isQr) {
                    try {
                        $verifyUrl = route('pengembangan.verify', ['code' => $text]);
                        $qr = Builder::create()
                            ->writer(new PngWriter())
                            ->data($verifyUrl)
                            ->size(max(40, min(800, $qrSize)))
                            ->margin(0)
                            ->build();

                        $tmpQr = sys_get_temp_dir() . '/qr_' . uniqid() . '.png';
                        file_put_contents($tmpQr, $qr->getString());

                        // center the QR at x,y
                        $left = (int) round($x - ($qrSize / 2));
                        $top = (int) round($y - ($qrSize / 2));
                        $img->place($tmpQr, 'top-left', $left, $top);

                        // Optionally add textual barcode under the QR
                        $img->text($text, (int) $x, (int) ($y + ($qrSize / 2) + 10), function ($font) use ($fontSize, $color, $fontFile) {
                            if ($fontFile) $font->file($fontFile);
                            $font->size(max(10, (int) ($fontSize / 1.2)));
                            $font->color($color);
                            $font->align('center');
                            $font->valign('top');
                        });

                        @unlink($tmpQr);
                    } catch (\Throwable $e) {
                        // fallback to text
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
                } else {
                    // Render barcode as plain text
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

                continue;
            }

            // Parse color
            $colorRgb = $this->hexToRgb($color);

            // Add text to image - use hex color string, NOT array (Intervention v3 doesn't support array format)
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
                $html = '<!DOCTYPE html><html style="margin:0;padding:0;width:100%;height:100%;"><body style="margin:0;padding:0;width:100%;height:100%;"><img src="' . $src . '" style="width:100%;height:100%;"/></body></html>';
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

    public function preview($template, Pengembangan $item, string $name, string $barcode, ?string $nomorSurat = null): string
    {
        $imagePath = $this->generate($template, $item, $name, $barcode, $nomorSurat);
        $fullImgPath = Storage::disk('public')->path($imagePath);
        $imgData = base64_encode(file_get_contents($fullImgPath));
        return 'data:image/jpeg;base64,' . $imgData;
    }

    protected function generateFallbackPdf(Pengembangan $item, string $name, string $barcode, ?string $nomorSurat = null): string
    {
        $htmlView = view('pengembangan.certificate_template', [
            'name' => $name,
            'kegiatan' => $item,
            'barcode' => $barcode,
            'nomor_surat' => $nomorSurat,
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
            $normalizedPos = $this->normalizeEditorPlaceholderPosition($pos, $img->width(), $img->height());
            $x = $normalizedPos['x'] ?? ($img->width() / 2);
            $y = $normalizedPos['y'] ?? ($img->height() / 2);
            $fontSize = $normalizedPos['font_size'] ?? 24;
            $color = $normalizedPos['color'] ?? '#000000';
            $alignment = $normalizedPos['align'] ?? $normalizedPos['alignment'] ?? 'center';
            $fontFile = $this->resolveFontFile($pos['font_file'] ?? null, null);
            if ($key === 'barcode') {
                $isQr = true;
                if (isset($pos['is_qr'])) {
                    $isQr = (bool) $pos['is_qr'];
                }
                $qrSize = isset($pos['qr_size']) ? (int) $pos['qr_size'] : max(80, min(300, (int) ($fontSize * 4)));
                if ($isQr) {
                    try {
                        $verifyUrl = route('pengembangan.verify', ['code' => $text]);
                        $qr = Builder::create()
                            ->writer(new PngWriter())
                            ->data($verifyUrl)
                            ->size(max(40, min(800, $qrSize)))
                            ->margin(0)
                            ->build();

                        $tmpQr = sys_get_temp_dir() . '/qr_' . uniqid() . '.png';
                        file_put_contents($tmpQr, $qr->getString());
                        $left = (int) round($x - ($qrSize / 2));
                        $top = (int) round($y - ($qrSize / 2));
                        $img->place($tmpQr, 'top-left', $left, $top);
                        $img->text($text, (int) $x, (int) ($y + ($qrSize / 2) + 10), function ($font) use ($fontSize, $color, $fontFile, $alignment) {
                            if ($fontFile) $font->file($fontFile);
                            $font->size(max(10, (int) ($fontSize / 1.2)));
                            $font->color($color);
                            $font->align('center');
                            $font->valign('top');
                        });
                        @unlink($tmpQr);
                    } catch (\Throwable $e) {
                        $img->text($text, (int) $x, (int) $y, function ($font) use ($fontSize, $color, $alignment, $fontFile) {
                            if ($fontFile) {
                                $font->file($fontFile);
                            }
                            $font->size($fontSize);
                            $font->color($color);
                            $font->align($alignment === 'left' ? 'left' : ($alignment === 'right' ? 'right' : 'center'));
                            $font->valign('middle');
                        });
                    }
                } else {
                    $img->text($text, (int) $x, (int) $y, function ($font) use ($fontSize, $color, $alignment, $fontFile) {
                        if ($fontFile) {
                            $font->file($fontFile);
                        }
                        $font->size($fontSize);
                        $font->color($color);
                        $font->align($alignment === 'left' ? 'left' : ($alignment === 'right' ? 'right' : 'center'));
                        $font->valign('middle');
                    });
                }
                continue;
            }

            $img->text($text, (int) $x, (int) $y, function ($font) use ($fontSize, $color, $alignment, $fontFile) {
                if ($fontFile) {
                    $font->file($fontFile);
                }
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

    public function streamPreview($template, Pengembangan $item, string $name, string $barcode, ?string $nomorSurat = null)
    {
        $bgPath = $template->background_image ?? null;
        if (!$bgPath || !Storage::disk('public')->exists($bgPath)) {
            $htmlView = view('pengembangan.certificate_template', [
                'name' => $name,
                'kegiatan' => $item,
                'barcode' => $barcode,
                'nomor_surat' => $nomorSurat,
            ])->render();
            $pdf = Pdf::loadHTML($htmlView)->setPaper('a4', 'landscape');
            return response($pdf->output(), 200)->header('Content-Type', 'application/pdf');
        }

        $fullPath = Storage::disk('public')->path($bgPath);
        $img = $this->image->read($fullPath);
        $width = $img->width();
        $height = $img->height();

        $positions = [];
        if (!empty($template->placeholder_positions)) {
            $positions = json_decode($template->placeholder_positions, true) ?? [];
        }

        if (($template->include_barcode ?? false) && !isset($positions['barcode'])) {
            $positions['barcode'] = [
                'x_ratio' => 0.5,
                'y_ratio' => 0.8,
                'font_size' => 16,
                'color' => '#000000',
                'align' => 'center',
                'is_qr' => $template->barcode_is_qr ?? true,
                'qr_size' => $template->barcode_qr_size ?? 180,
            ];
        }

        $placeholders = [
            'name' => $name,
            'kegiatan->nama_kegiatan' => $item->nama_kegiatan ?? '',
            'kegiatan->tema_kegiatan' => $item->tema_kegiatan ?? '',
            'barcode' => $barcode,
            'nomor_surat' => $nomorSurat ?? '',
        ];

        foreach ($placeholders as $key => $text) {
            $pos = $positions[$key] ?? null;
            if (!$pos) continue;

            $normalizedPos = $this->normalizeEditorPlaceholderPosition($pos, $width, $height);
            $x = $normalizedPos['x'] ?? ($width / 2);
            $y = $normalizedPos['y'] ?? ($height / 2);
            $fontSize = $normalizedPos['font_size'] ?? 24;
            $color = $normalizedPos['color'] ?? '#000000';
            $fontFile = $this->resolveFontFile($pos['font_file'] ?? null, $template->font_file ?? null);
            $alignment = $pos['align'] ?? $pos['alignment'] ?? 'center';

            if ($key === 'barcode') {
                $isQr = $template->barcode_is_qr ?? true;
                if (isset($pos['is_qr'])) {
                    $isQr = (bool) $pos['is_qr'];
                }
                $qrSize = isset($pos['qr_size']) ? (int) $pos['qr_size'] : ($template->barcode_qr_size ? (int) $template->barcode_qr_size : max(80, min(300, (int) ($fontSize * 4))));

                if ($isQr) {
                    try {
                        $verifyUrl = route('pengembangan.verify', ['code' => $text]);
                        $qr = Builder::create()
                            ->writer(new PngWriter())
                            ->data($verifyUrl)
                            ->size(max(40, min(800, $qrSize)))
                            ->margin(0)
                            ->build();

                        $tmpQr = sys_get_temp_dir() . '/qr_' . uniqid() . '.png';
                        file_put_contents($tmpQr, $qr->getString());

                        $left = (int) round($x - ($qrSize / 2));
                        $top = (int) round($y - ($qrSize / 2));
                        $img->place($tmpQr, 'top-left', $left, $top);

                        $img->text($text, (int) $x, (int) ($y + ($qrSize / 2) + 10), function ($font) use ($fontSize, $color, $fontFile) {
                            if ($fontFile) $font->file($fontFile);
                            $font->size(max(10, (int) ($fontSize / 1.2)));
                            $font->color($color);
                            $font->align('center');
                            $font->valign('top');
                        });

                        @unlink($tmpQr);
                        continue;
                    } catch (\Throwable $e) {
                        // Fall back to text below if QR generation fails.
                    }
                }
            }

            $img->text($text, (int) $x, (int) $y, function ($font) use ($fontSize, $color, $fontFile, $alignment) {
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

        $html = '<!DOCTYPE html><html style="margin:0;padding:0;width:100%;height:100%;"><body style="margin:0;padding:0;width:100%;height:100%;">';
        $html .= '<img src="data:image/jpeg;base64,' . $imgData . '" style="width:100%;height:100%;"/>';
        $html .= '</body></html>';

        // Convert to PDF for download/preview
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');
        return response($pdf->output(), 200)->header('Content-Type', 'application/pdf');
    }

    public function normalizeEditorPlaceholderPosition(array $position, int $imageWidth, int $imageHeight, int $canvasWidth = 900, int $canvasHeight = 600): array
    {
        $imageWidth = max(1, $imageWidth);
        $imageHeight = max(1, $imageHeight);
        $canvasWidth = max(1, $canvasWidth);
        $canvasHeight = max(1, $canvasHeight);

        $scale = min($canvasWidth / $imageWidth, $canvasHeight / $imageHeight);
        $scale = max($scale, 0.0001);

        $normalized = $position;

        if (array_key_exists('x_ratio', $position) || array_key_exists('x_percent', $position)) {
            $xValue = $position['x_ratio'] ?? $position['x_percent'] ?? null;
            $normalized['x'] = (int) round(($this->resolveRatioValue($xValue, $canvasWidth) / $scale));
        } elseif (array_key_exists('x', $position) || array_key_exists('left', $position)) {
            $normalized['x'] = (int) round((($position['x'] ?? $position['left'] ?? 0) / $scale));
        }

        if (array_key_exists('y_ratio', $position) || array_key_exists('y_percent', $position)) {
            $yValue = $position['y_ratio'] ?? $position['y_percent'] ?? null;
            $normalized['y'] = (int) round(($this->resolveRatioValue($yValue, $canvasHeight) / $scale));
        } elseif (array_key_exists('y', $position) || array_key_exists('top', $position)) {
            $normalized['y'] = (int) round((($position['y'] ?? $position['top'] ?? 0) / $scale));
        }

        if (!empty($position['font_size'])) {
            $normalized['font_size'] = (int) $position['font_size'];
        }

        return $normalized;
    }

    /**
     * Resolve a usable TTF/OTF font path for text rendering.
     *
     * Without a real font file, the GD driver falls back to its built-in
     * bitmap fonts, which completely ignore the configured font size. To
     * make sure `font_size` from the editor is always honored, we fall back
     * to a bundled DejaVu Sans font when no custom font is uploaded.
     */
    protected function resolveFontFile(?string $placeholderFontFile, ?string $templateFontFile): ?string
    {
        if (!empty($placeholderFontFile) && Storage::disk('public')->exists($placeholderFontFile)) {
            return Storage::disk('public')->path($placeholderFontFile);
        }

        if (!empty($templateFontFile) && Storage::disk('public')->exists($templateFontFile)) {
            return Storage::disk('public')->path($templateFontFile);
        }

        $defaultFont = public_path('fonts/DejaVuSans-Bold.ttf');
        if (file_exists($defaultFont)) {
            return $defaultFont;
        }

        return null;
    }

    protected function resolveRatioValue($value, int $size): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        $numeric = (float) $value;
        if ($numeric > 1) {
            return (int) round(($numeric / 100) * $size);
        }

        return (int) round($numeric * $size);
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
