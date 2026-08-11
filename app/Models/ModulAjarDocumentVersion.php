<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulAjarDocumentVersion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'modul_ajar_id',
        'filename',
        'filepath',
        'version',
        'file_size',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function modulAjar()
    {
        return $this->belongsTo(RencanaPembelajaran::class, 'modul_ajar_id');
    }
}
