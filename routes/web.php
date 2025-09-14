<?php

use App\Http\Controllers\CalculationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::resource('calculations', CalculationController::class);
    Route::post('/calculations/{calculation}/import-csv', [CalculationController::class, 'importCsv'])
        ->name('calculations.import-csv');
    Route::post('/calculations/{calculation}/calculate', [CalculationController::class, 'calculate'])
        ->name('calculations.calculate');
    Route::get('/calculations/{calculation}/export-csv', [CalculationController::class, 'exportCsv'])
        ->name('calculations.export-csv');
    Route::get('/calculations/{calculation}/export-excel', [CalculationController::class, 'exportExcel'])
        ->name('calculations.export-excel');
});

require __DIR__.'/auth.php';
