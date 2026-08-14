<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tenaga_pendidikan')) {
            Schema::create('tenaga_pendidikan', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('nip')->nullable()->unique();
                $table->string('jabatan')->nullable();
                $table->string('telepon')->nullable();
                $table->text('alamat')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
                $table->string('email')->nullable()->unique();
                $table->string('foto')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tenaga_pendidikan');
    }
};
