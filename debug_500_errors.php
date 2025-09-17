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
    
    echo "6. Checking calculations.show view...\n";
    try {
        // We need a user for the view's "Created by" line
        $user = new \App\Models\User(['name' => 'Test User']);

        // Create a mock calculation object with the properties the view expects
        $calculation = new \App\Models\Calculation([
            'name' => 'Test Calculation',
            'created_at' => new \Illuminate\Support\Carbon(),
            'calculation_year' => 2024,
            'proration_method' => 'weight',
            'description' => 'Test Description',
            'use_tlc_china' => false,
            'insurance_rate' => 1.5,
            'total_fob_value' => 0, // Set to 0 for empty calculation
        ]);

        // Manually set the relationships that the view will try to access
        $calculation->setRelation('user', $user);
        $calculation->setRelation('items', new \Illuminate\Database\Eloquent\Collection());

        // Attempt to render the view
        $viewFactory = $app->make(\Illuminate\Contracts\View\Factory::class);
        $html = $viewFactory->make('calculations.show', ['calculation' => $calculation])->render();

        echo "   ✓ calculations.show view compiled successfully\n";

    } catch (Exception $e) {
        echo "   ✗ calculations.show view failed: " . $e->getMessage() . "\n";
        // Print a simplified trace
        $trace = array_slice($e->getTrace(), 0, 5);
        foreach ($trace as $t) {
            echo "     at " . ($t['file'] ?? 'unknown file') . ":" . ($t['line'] ?? 'unknown line') . "\n";
        }
    }

    echo "\n=== Debug Complete ===\n";
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
