<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolah';

    protected $fillable = [
        'nama_sekolah',
        'nama_kepala_sekolah',
        'npsn',
        'alamat',
        'alamat_jalan',
        'kelurahan',
        'kecamatan',
        'kota',
        'provinsi',
        'kode_pos',
        'telepon',
        'email',
        'website',
        'jenjang',
        'status',
        'akreditasi',
        'logo',
        'logo_kanan',
        'logo_header_kiri',
        'header_html',
        'tampilkan_jadwal',
        'tampilkan_jadwal_guru',
        'tampilkan_jadwal_siswa',
        'tampilkan_nama_wali_kelas',
        'tampilkan_nama_wali_kelas_guru',
        'tampilkan_nama_wali_kelas_siswa',
        'jadwal_maintenance_message',
        'wali_kelas_hidden_message',
        // Header text lines (HTML from Summernote)
        'header_line1',
        'header_line1_spacing',
        'header_line2',
        'header_line2_spacing',
        'header_line3',
        'header_line3_spacing',
        'header_line4',
        'header_line4_spacing',
    ];

    protected $casts = [
        'tampilkan_jadwal' => 'boolean',
        'tampilkan_jadwal_guru' => 'boolean',
        'tampilkan_jadwal_siswa' => 'boolean',
        'tampilkan_nama_wali_kelas' => 'boolean',
        'tampilkan_nama_wali_kelas_guru' => 'boolean',
        'tampilkan_nama_wali_kelas_siswa' => 'boolean',
    ];

    public function shouldShowJadwalForUser($user)
    {
        if (! $user) {
            return true;
        }

        if ($user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah'])) {
            return true;
        }

        if ($user->hasAnyRole(['Siswa'])) {
            return $this->tampilkan_jadwal_siswa ?? $this->tampilkan_jadwal ?? true;
        }

        return $this->tampilkan_jadwal_guru ?? $this->tampilkan_jadwal ?? true;
    }

    public function shouldShowNamaWaliKelasForUser($user)
    {
        if (! $user) {
            return true;
        }

        if ($user->hasAnyRole(['Siswa'])) {
            return $this->tampilkan_nama_wali_kelas_siswa ?? $this->tampilkan_nama_wali_kelas ?? true;
        }

        return $this->tampilkan_nama_wali_kelas_guru ?? $this->tampilkan_nama_wali_kelas ?? true;
    }
}

