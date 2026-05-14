<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileScanController;

Route::get('/', [FileScanController::class, 'index']);
Route::post('/upload', [FileScanController::class, 'scan'])->name('file.upload');
Route::delete('/delete/{id}', [FileScanController::class, 'deleteHistory'])->name('delete.history');
Route::get('/export', [FileScanController::class, 'export'])->name('export.scans');