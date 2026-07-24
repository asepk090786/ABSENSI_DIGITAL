<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengembanganSertifikat extends Model
{
    protected $table = 'pengembangan_sertifikats';
    protected $fillable = ['pengembangan_id','peserta_type','peserta_id','file_path','barcode','verified_at'];
}
