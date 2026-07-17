<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkTugas extends Model
{
    use HasFactory;

    protected $table = 'sk_tugas';

    protected $fillable = [
        'guru_id',
        'judul',
        'file',
        'is_visible_to_guru',
    ];

    protected $casts = [
        'is_visible_to_guru' => 'boolean',
    ];

    public function scopeVisibleToGuru($query)
    {
        return $query->where('is_visible_to_guru', true);
    }
}
