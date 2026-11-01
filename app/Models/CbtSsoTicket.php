<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CbtSsoTicket extends Model
{
    protected $fillable = [
        'user_id',
        'ticket_hash',
        'state',
        'expires_at',
        'consumed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'consumed_at' => 'datetime',
    ];
}
