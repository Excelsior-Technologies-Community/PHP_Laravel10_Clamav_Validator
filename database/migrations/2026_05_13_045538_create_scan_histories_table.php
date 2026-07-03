<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_histories', function (Blueprint $table) {
            $table->id();

            $table->string('file_name');
            $table->string('original_name');
            $table->string('file_type');
            $table->string('file_size');

            $table->enum('scan_status', [
                'Clean',
                'Infected',
                'Pending',
                'Failed',
                'Error'
            ])->default('Pending');

            $table->string('virus_name')->nullable();
            $table->text('scan_output')->nullable();

            // New columns for advanced features
            $table->boolean('is_quarantined')->default(false);
            $table->string('quarantine_path')->nullable();
            $table->float('scan_duration')->nullable();

            $table->timestamps();

            // Add indexes for better performance
            $table->index('scan_status');
            $table->index('is_quarantined');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_histories');
    }
};