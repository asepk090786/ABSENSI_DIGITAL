<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JamBelajar extends Model
{
    use HasFactory;

    protected $table = 'jam_belajar';
    protected $fillable = ['hari', 'urutan', 'jam_mulai', 'jam_selesai', 'jenis'];

    /**
     * Cast time fields to string for consistent formatting.
     */
    protected $casts = [
        'jam_mulai' => 'string',
        'jam_selesai' => 'string',
    ];

    public function scopeOrderByDay($query)
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $dayOrder = implode(',', array_map(fn($day) => "'{$day}'", $days));
        return $query->orderByRaw("FIELD(hari, {$dayOrder})")->orderBy('urutan');
    }

}
