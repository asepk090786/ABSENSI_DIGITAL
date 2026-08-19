<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservationItem extends Model
{
    use HasFactory;

    protected $table = 'observation_items';

    protected $fillable = [
        'supervisi_id',
        'indicator_id',
        'skor',
        'catatan',
    ];

    public function supervisi()
    {
        return $this->belongsTo(Supervisi::class);
    }

    public function indicator()
    {
        return $this->belongsTo(SupervisionIndicator::class, 'indicator_id');
    }
}
