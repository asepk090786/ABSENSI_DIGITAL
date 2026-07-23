<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AgendaGuruCreateJamBelajarFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->foreignId('guru_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('guru')) {
            Schema::create('guru', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('nip')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('tahun_ajaran')) {
            Schema::create('tahun_ajaran', function (Blueprint $table) {
                $table->id();
                $table->string('nama_tahun');
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('semester')) {
            Schema::create('semester', function (Blueprint $table) {
                $table->id();
                $table->string('nama_semester');
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('jam_belajar')) {
            Schema::create('jam_belajar', function (Blueprint $table) {
                $table->id();
                $table->string('hari');
                $table->integer('urutan')->default(1);
                $table->string('jam_mulai');
                $table->string('jam_selesai');
                $table->string('jenis')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('jadwal_kbm')) {
            Schema::create('jadwal_kbm', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kelas_id');
                $table->foreignId('guru_id');
                $table->foreignId('mata_pelajaran_id');
                $table->foreignId('jam_belajar_id');
                $table->string('hari');
                $table->integer('jam_ke');
                $table->foreignId('tahun_ajaran_id');
                $table->foreignId('semester_id');
                $table->timestamps();
            });
        }

        DB::table('tahun_ajaran')->truncate();
        DB::table('semester')->truncate();
        DB::table('jadwal_kbm')->truncate();
        DB::table('jam_belajar')->truncate();
        DB::table('guru')->truncate();
        DB::table('users')->truncate();

        DB::table('tahun_ajaran')->insert([
            'id' => 1,
            'nama_tahun' => '2026/2027',
            'is_active' => true,
        ]);

        DB::table('semester')->insert([
            'id' => 1,
            'nama_semester' => 'Ganjil',
            'is_active' => true,
        ]);

        DB::table('guru')->insert([
            'id' => 10,
            'nama' => 'Guru Uji',
            'nip' => '1234567890',
        ]);

        $user = User::create([
            'name' => 'Guru Uji',
            'username' => 'guruuji',
            'email' => 'guruuji@example.com',
            'password' => bcrypt('password'),
            'guru_id' => 10,
        ]);

        $this->actingAs($user);

        DB::table('jam_belajar')->insert([
            ['id' => 1, 'hari' => 'Senin', 'urutan' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:45', 'jenis' => 'KBM'],
            ['id' => 2, 'hari' => 'Senin', 'urutan' => 2, 'jam_mulai' => '07:45', 'jam_selesai' => '08:30', 'jenis' => 'KBM'],
            ['id' => 5, 'hari' => 'Kamis', 'urutan' => 5, 'jam_mulai' => '10:00', 'jam_selesai' => '10:45', 'jenis' => 'KBM'],
            ['id' => 6, 'hari' => 'Kamis', 'urutan' => 6, 'jam_mulai' => '10:45', 'jam_selesai' => '11:30', 'jenis' => 'KBM'],
        ]);

        DB::table('jadwal_kbm')->insert([
            ['id' => 100, 'kelas_id' => 1, 'guru_id' => 10, 'mata_pelajaran_id' => 1, 'jam_belajar_id' => 5, 'hari' => 'Kamis', 'jam_ke' => 5, 'tahun_ajaran_id' => 1, 'semester_id' => 1],
            ['id' => 101, 'kelas_id' => 1, 'guru_id' => 10, 'mata_pelajaran_id' => 1, 'jam_belajar_id' => 6, 'hari' => 'Kamis', 'jam_ke' => 6, 'tahun_ajaran_id' => 1, 'semester_id' => 1],
        ]);
    }

    public function test_create_view_only_shows_jam_that_exist_in_teacher_schedule_for_selected_day(): void
    {
        $response = $this->get('/agenda_guru/create?tanggal=2026-07-23');

        $response->assertOk();
        $response->assertSee('10:00 - 10:45');
        $response->assertSee('10:45 - 11:30');
        $response->assertDontSee('07:00 - 07:45');
        $response->assertDontSee('07:45 - 08:30');
    }
}
