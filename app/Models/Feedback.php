<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'post_conference_id',
        'kekuatan',
        'area_pengembangan',
        'umpan_balik',
    ];

    public function postConference()
    {
        return $this->belongsTo(PostConference::class, 'post_conference_id');
    }
}
