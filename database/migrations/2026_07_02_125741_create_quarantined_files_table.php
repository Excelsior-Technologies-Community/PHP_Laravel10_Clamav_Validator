<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quarantined_files', function (Blueprint $table) {
            $table->id();

            $table->string('original_name');
            $table->string('file_path');
            $table->string('virus_name')->nullable();
            $table->text('scan_output')->nullable();

            $table->unsignedBigInteger('scan_history_id')->nullable();

            $table->boolean('is_restored')->default(false);
            $table->timestamp('restored_at')->nullable();

            $table->timestamps();

            $table->foreign('scan_history_id')
                ->references('id')
                ->on('scan_histories')
                ->onDelete('set null');

            $table->index('is_restored');
            $table->index('created_at');
            $table->index('virus_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quarantined_files');
    }
};