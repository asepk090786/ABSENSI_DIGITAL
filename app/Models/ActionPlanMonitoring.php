<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActionPlanMonitoring extends Model
{
    use HasFactory;

    protected $table = 'action_plan_monitorings';

    protected $fillable = [
        'action_plan_id',
        'tanggal_monitoring',
        'progress_persen',
        'catatan',
        'bukti',
    ];

    protected $casts = [
        'tanggal_monitoring' => 'date',
    ];

    public function actionPlan()
    {
        return $this->belongsTo(ActionPlan::class, 'action_plan_id');
    }
}
