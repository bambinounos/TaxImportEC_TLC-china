<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariff_codes', function (Blueprint $table) {
            $table->id();
            $table->string('hs_code', 10)->unique();
            $table->string('description_en');
            $table->string('description_es');
            $table->decimal('base_tariff_rate', 8, 4)->default(0);
            $table->decimal('iva_rate', 8, 4)->default(15.00);
            $table->boolean('has_ice')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('hs_code');
            $table->index(['is_active', 'has_ice']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariff_codes');
    }
};
