<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostConference extends Model
{
    use HasFactory;

    protected $table = 'post_conferences';

    protected $fillable = [
        'supervisi_id',
        'refleksi_guru',
        'refleksi_supervisor',
        'tanggal_pelaksanaan',
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'datetime',
    ];

    public function supervisi()
    {
        return $this->belongsTo(Supervisi::class);
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class, 'post_conference_id');
    }

    public function actionPlans()
    {
        return $this->hasMany(ActionPlan::class, 'post_conference_id');
    }
}
