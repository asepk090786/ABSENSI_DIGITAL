<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasTambahan extends Model
{
    use HasFactory;

    protected $table = 'tugas_tambahan';

    protected $fillable = [
        'tenaga_pendidikan_id',
        'tugas',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relationship ke TenagaPendidikan
     */
    public function tenagaPendidikan()
    {
        return $this->belongsTo(TenagaPendidikan::class, 'tenaga_pendidikan_id');
    }
}
