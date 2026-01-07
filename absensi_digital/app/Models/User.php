<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Role;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name','email','password','role_id','guru_id','siswa_id'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
