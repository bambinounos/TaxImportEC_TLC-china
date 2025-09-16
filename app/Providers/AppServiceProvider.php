<?php

namespace App\Providers;

use App\Services\TaxCalculationService;
use App\Services\CsvImportService;
use App\Services\CsvExportService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TaxCalculationService::class, function ($app) {
            return new TaxCalculationService();
        });

        $this->app->singleton(CsvImportService::class, function ($app) {
            return new CsvImportService();
        });

        $this->app->singleton(CsvExportService::class, function ($app) {
            return new CsvExportService();
        });
    }

    public function boot(): void
    {
        //
    }
}
