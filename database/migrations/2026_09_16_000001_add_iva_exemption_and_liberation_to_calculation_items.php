<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculation_items', function (Blueprint $table) {
            $table->boolean('iva_exempt')->default(false)->after('ice_exempt_reason');
            $table->string('iva_exempt_reason', 255)->nullable()->after('iva_exempt');
            $table->string('liberation_code', 20)->nullable()->after('iva_exempt_reason');
            $table->boolean('tariff_exempt')->default(false)->after('liberation_code');
            $table->string('tariff_exempt_reason', 255)->nullable()->after('tariff_exempt');

            $table->index('liberation_code');
        });
    }

    public function down(): void
    {
        Schema::table('calculation_items', function (Blueprint $table) {
            $table->dropIndex(['liberation_code']);
            $table->dropColumn([
                'iva_exempt',
                'iva_exempt_reason',
                'liberation_code',
                'tariff_exempt',
                'tariff_exempt_reason',
            ]);
        });
    }
};
