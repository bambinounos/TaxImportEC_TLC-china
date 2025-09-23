<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculation_items', function (Blueprint $table) {
            if (!Schema::hasColumn('calculation_items', 'fodinfa_rate')) {
                $table->decimal('fodinfa_rate', 8, 4)->default(0.5)->after('tariff_amount');
            }
            if (!Schema::hasColumn('calculation_items', 'fodinfa_amount')) {
                $table->decimal('fodinfa_amount', 10, 4)->default(0)->after('fodinfa_rate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('calculation_items', function (Blueprint $table) {
            $table->dropColumn(['fodinfa_rate', 'fodinfa_amount']);
        });
    }
};
