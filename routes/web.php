<?php

use App\Http\Controllers\AdminController;
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

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/tariff-codes', [AdminController::class, 'tariffCodes'])->name('tariff-codes');
        Route::get('/tariff-codes/create', [AdminController::class, 'createTariffCode'])->name('tariff-codes.create');
        Route::post('/tariff-codes', [AdminController::class, 'storeTariffCode'])->name('tariff-codes.store');
        Route::get('/tariff-codes/{hsCode}', [AdminController::class, 'showTariffCode'])->name('tariff-codes.show');
        Route::get('/tariff-codes/{hsCode}/edit', [AdminController::class, 'editTariffCode'])->name('tariff-codes.edit');
        Route::put('/tariff-codes/{hsCode}', [AdminController::class, 'updateTariffCode'])->name('tariff-codes.update');
        Route::delete('/tariff-codes/{hsCode}', [AdminController::class, 'destroyTariffCode'])->name('tariff-codes.destroy');
        Route::get('/tlc-schedules', [AdminController::class, 'tlcSchedules'])->name('tlc-schedules');
    });
});

require __DIR__.'/auth.php';
