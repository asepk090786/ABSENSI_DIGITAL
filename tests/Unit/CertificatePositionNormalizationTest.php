<?php

namespace Tests\Unit;

use App\Services\CertificateService;
use PHPUnit\Framework\TestCase;

class CertificatePositionNormalizationTest extends TestCase
{
    public function test_ratio_position_uses_image_dimensions_not_editor_scale(): void
    {
        $service = new CertificateService();

        $position = [
            'x_ratio' => 0.5,
            'y_ratio' => 0.48,
            'font_size' => 18,
            'color' => '#000000',
            'align' => 'center',
        ];

        $normalized = $service->normalizeEditorPlaceholderPosition($position, 1200, 848, 900, 600);

        $this->assertSame(600, $normalized['x']);
        $this->assertSame(407, $normalized['y']);
        $this->assertSame(18, $normalized['font_size']);
    }
}
