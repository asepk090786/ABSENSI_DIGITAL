<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NilaiHarianMigrationTest extends TestCase
{
    public function test_nilai_harian_has_new_penilaian_fields()
    {
        if (config('database.default') !== 'mysql') {
            $this->markTestSkipped('Validasi kolom ini dilakukan pada database MySQL aplikasi.');
        }

        $this->assertTrue(Schema::hasColumn('nilai_harian', 'materi_pelajaran'));
        $this->assertTrue(Schema::hasColumn('nilai_harian', 'jenis_penilaian'));
    }
}
