<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rencana_pembelajarans', function (Blueprint $table) {
            if (!Schema::hasColumn('rencana_pembelajarans', 'original_docx_path')) {
                $table->string('original_docx_path')->nullable()->after('status');
            }
            if (!Schema::hasColumn('rencana_pembelajarans', 'html_content')) {
                $table->longText('html_content')->nullable()->after('original_docx_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rencana_pembelajarans', function (Blueprint $table) {
            if (Schema::hasColumn('rencana_pembelajarans', 'html_content')) {
                $table->dropColumn('html_content');
            }
            if (Schema::hasColumn('rencana_pembelajarans', 'original_docx_path')) {
                $table->dropColumn('original_docx_path');
            }
        });
    }
};
