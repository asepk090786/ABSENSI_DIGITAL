<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                // Drop old foreign key and add new one on non-sqlite drivers
                $table->dropForeign(['jenis_kegiatan_id']);
                $table->foreign('jenis_kegiatan_id')->references('id')->on('kegiatan')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['jenis_kegiatan_id']);
                $table->foreign('jenis_kegiatan_id')->references('id')->on('jenis_kegiatan')->onDelete('set null');
            }
        });
    }
};
