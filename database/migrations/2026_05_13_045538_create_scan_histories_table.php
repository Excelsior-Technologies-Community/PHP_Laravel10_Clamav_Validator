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
                'Infected'
            ]);

            $table->text('scan_output')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_histories');
    }
};