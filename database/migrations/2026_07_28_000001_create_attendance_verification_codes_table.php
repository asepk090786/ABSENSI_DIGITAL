<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('attendance_verification_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('guru_id')->nullable()->index();
            $table->unsignedBigInteger('kelas_id')->index();
            $table->unsignedBigInteger('jam_belajar_id')->nullable()->index();
            $table->date('tanggal')->index();
            $table->string('kode', 64)->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['guru_id', 'kelas_id', 'jam_belajar_id', 'tanggal'], 'uniq_verif_scope');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_verification_codes');
    }
};
