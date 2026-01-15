<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            // Drop old foreign key
            $table->dropForeign(['jenis_kegiatan_id']);
            // Add new foreign key to kegiatan
            $table->foreign('jenis_kegiatan_id')->references('id')->on('kegiatan')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropForeign(['jenis_kegiatan_id']);
            $table->foreign('jenis_kegiatan_id')->references('id')->on('jenis_kegiatan')->onDelete('set null');
        });
    }
};
