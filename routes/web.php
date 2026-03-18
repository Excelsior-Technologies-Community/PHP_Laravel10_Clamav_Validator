<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileScanController;

Route::get('/', [FileScanController::class, 'index']);
Route::post('/upload', [FileScanController::class, 'scan'])->name('file.upload');