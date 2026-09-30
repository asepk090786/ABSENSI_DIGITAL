<?php

namespace Tests\Feature;

use App\Support\NilaiInputNormalizer;
use Tests\TestCase;

class NilaiInputNormalizationTest extends TestCase
{
    public function test_decimal_separator_is_normalized_for_indonesian_input(): void
    {
        $this->assertSame(80.5, NilaiInputNormalizer::normalize('80,5'));
        $this->assertSame(1234.5, NilaiInputNormalizer::normalize('1.234,5'));
        $this->assertSame(100.0, NilaiInputNormalizer::normalize('100'));
        $this->assertNull(NilaiInputNormalizer::normalize(''));
    }
}
