<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionPlan extends Model
{
    use HasFactory;

    protected $table = 'action_plans';

    protected $fillable = [
        'post_conference_id',
        'tujuan',
        'aktivitas',
        'rekomendasi',
        'penanggung_jawab_id',
        'target_selesai',
        'status',
    ];

    protected $casts = [
        'target_selesai' => 'date',
    ];

    public function postConference()
    {
        return $this->belongsTo(PostConference::class, 'post_conference_id');
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(Guru::class, 'penanggung_jawab_id');
    }

    public function monitorings()
    {
        return $this->hasMany(ActionPlanMonitoring::class, 'action_plan_id');
    }
}
