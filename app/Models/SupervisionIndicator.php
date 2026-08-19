<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupervisionIndicator extends Model
{
    use HasFactory;

    protected $table = 'supervision_indicators';

    protected $fillable = [
        'instrument_id',
        'kategori',
        'indikator',
        'deskripsi',
        'bobot',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function instrument()
    {
        return $this->belongsTo(SupervisionInstrument::class, 'instrument_id');
    }

    public function observationItems()
    {
        return $this->hasMany(ObservationItem::class, 'indicator_id');
    }
}
