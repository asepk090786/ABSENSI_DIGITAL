<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pengembangan_sertifikat_templates', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->nullable();
            $table->text('template_html')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pengembangan_sertifikat_templates');
    }
};
