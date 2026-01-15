<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->unsignedBigInteger('jenis_kegiatan_id')->nullable()->after('id');
            $table->foreign('jenis_kegiatan_id')->references('id')->on('jenis_kegiatan')->nullOnDelete();
        });
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->unsignedBigInteger('jenis_kegiatan_id')->nullable()->after('id');
            $table->foreign('jenis_kegiatan_id')->references('id')->on('jenis_kegiatan')->nullOnDelete();
        });
    }
    public function down()
    {
        Schema::table('mata_pelajaran', function (Blueprint $table) {
            $table->dropForeign(['jenis_kegiatan_id']);
            $table->dropColumn('jenis_kegiatan_id');
        });
        Schema::table('kegiatan', function (Blueprint $table) {
            $table->dropForeign(['jenis_kegiatan_id']);
            $table->dropColumn('jenis_kegiatan_id');
        });
    }
};
