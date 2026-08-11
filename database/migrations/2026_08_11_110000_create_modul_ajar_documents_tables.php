<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('modul_ajar_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_ajar_id')->unique()->constrained('rencana_pembelajarans')->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('filename');
            $table->string('filepath');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();
        });

        Schema::create('modul_ajar_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_ajar_id')->constrained('rencana_pembelajarans')->cascadeOnDelete();
            $table->string('filename');
            $table->string('filepath');
            $table->unsignedInteger('version');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['modul_ajar_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modul_ajar_document_versions');
        Schema::dropIfExists('modul_ajar_documents');
    }
};
