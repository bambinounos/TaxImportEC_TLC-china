<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculation_items', function (Blueprint $table) {
            $table->decimal('profit_margin_percent', 8, 4)->nullable()->after('unit_sale_price');
        });
    }

    public function down(): void
    {
        Schema::table('calculation_items', function (Blueprint $table) {
            $table->dropColumn('profit_margin_percent');
        });
    }
};
