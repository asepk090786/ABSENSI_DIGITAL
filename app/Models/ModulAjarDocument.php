<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulAjarDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'modul_ajar_id',
        'original_filename',
        'filename',
        'filepath',
        'mime_type',
        'file_size',
        'version',
        'uploaded_by',
    ];

    public function modulAjar()
    {
        return $this->belongsTo(RencanaPembelajaran::class, 'modul_ajar_id');
    }

    public function versions()
    {
        return $this->hasMany(ModulAjarDocumentVersion::class, 'modul_ajar_id', 'modul_ajar_id')->orderByDesc('version');
    }
}
