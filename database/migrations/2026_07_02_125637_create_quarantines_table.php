<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quarantines', function (Blueprint $table) {
            $table->id();

            $table->string('original_path');
            $table->string('quarantine_path');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('virus_name')->nullable();

            $table->unsignedBigInteger('scan_history_id')->nullable();

            $table->timestamp('scanned_at')->nullable();
            $table->timestamp('restored_at')->nullable();

            $table->timestamps();

            // Foreign key constraint
            $table->foreign('scan_history_id')
                ->references('id')
                ->on('scan_histories')
                ->onDelete('set null');

            // Add indexes
            $table->index('file_name');
            $table->index('virus_name');
            $table->index('created_at');
            $table->index('restored_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quarantines');
    }
};