<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileScanController;

Route::get('/', [FileScanController::class, 'index'])->name('scan.index');
Route::post('/upload', [FileScanController::class, 'scan'])->name('file.upload');
Route::delete('/delete/{id}', [FileScanController::class, 'deleteHistory'])->name('delete.history');
Route::get('/export', [FileScanController::class, 'export'])->name('export.scans');
Route::get('/quarantine', [FileScanController::class, 'quarantineList'])->name('quarantine.list');
Route::post('/quarantine/restore/{id}', [FileScanController::class, 'restoreQuarantine'])->name('quarantine.restore');
Route::delete('/quarantine/delete/{id}', [FileScanController::class, 'deleteQuarantine'])->name('quarantine.delete');
Route::get('/scan/status/{id}', [FileScanController::class, 'scanStatus'])->name('scan.status');