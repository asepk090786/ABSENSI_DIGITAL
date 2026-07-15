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
        // Header text lines (HTML from Summernote)
        'header_line1',
        'header_line1_spacing',
        'header_line2',
        'header_line2_spacing',
        'header_line3',
        'header_line3_spacing',
        'header_line4',
        'header_line4_spacing',
        'tampilkan_jadwal',
        'jadwal_maintenance_message',
    ];

    protected $casts = [
        'tampilkan_jadwal' => 'boolean',
    ];
}
