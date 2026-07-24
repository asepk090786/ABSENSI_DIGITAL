<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pengembangan_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengembangan_id')->constrained('pengembangan_diri')->onDelete('cascade');
            $table->string('peserta_type'); // 'guru' or 'siswa'
            $table->unsignedBigInteger('peserta_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengembangan_peserta');
    }
};
