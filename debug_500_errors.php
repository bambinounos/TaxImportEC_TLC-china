<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

try {
    echo "=== Debugging 500 Errors ===\n";
    
    echo "1. Checking Laravel bootstrap...\n";
    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "   ✓ Laravel bootstrap successful\n";
    
    echo "2. Checking database connection...\n";
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    try {
        DB::connection()->getPdo();
        echo "   ✓ Database connection successful\n";
    } catch (Exception $e) {
        echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
    }
    
    echo "3. Checking service resolution...\n";
    try {
        $taxService = $app->make(\App\Services\TaxCalculationService::class);
        echo "   ✓ TaxCalculationService resolved\n";
    } catch (Exception $e) {
        echo "   ✗ TaxCalculationService failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $csvImportService = $app->make(\App\Services\CsvImportService::class);
        echo "   ✓ CsvImportService resolved\n";
    } catch (Exception $e) {
        echo "   ✗ CsvImportService failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $csvExportService = $app->make(\App\Services\CsvExportService::class);
        echo "   ✓ CsvExportService resolved\n";
    } catch (Exception $e) {
        echo "   ✗ CsvExportService failed: " . $e->getMessage() . "\n";
    }
    
    echo "4. Checking model instantiation...\n";
    try {
        $user = new \App\Models\User();
        echo "   ✓ User model instantiated\n";
    } catch (Exception $e) {
        echo "   ✗ User model failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $calculation = new \App\Models\Calculation();
        echo "   ✓ Calculation model instantiated\n";
    } catch (Exception $e) {
        echo "   ✗ Calculation model failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $tariffCode = new \App\Models\TariffCode();
        echo "   ✓ TariffCode model instantiated\n";
    } catch (Exception $e) {
        echo "   ✗ TariffCode model failed: " . $e->getMessage() . "\n";
    }
    
    echo "5. Checking view compilation...\n";
    try {
        $view = view('calculations.index', ['calculations' => collect()]);
        echo "   ✓ calculations.index view compiled\n";
    } catch (Exception $e) {
        echo "   ✗ calculations.index view failed: " . $e->getMessage() . "\n";
    }
    
    try {
        $view = view('calculations.create');
        echo "   ✓ calculations.create view compiled\n";
    } catch (Exception $e) {
        echo "   ✗ calculations.create view failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Debug Complete ===\n";
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
