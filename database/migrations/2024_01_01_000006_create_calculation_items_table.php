<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculation_id')->constrained()->onDelete('cascade');
            $table->string('part_number')->nullable();
            $table->text('description_en');
            $table->text('description_es')->nullable();
            $table->string('hs_code', 10)->nullable();
            $table->decimal('unit_weight', 10, 4)->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price_fob', 10, 4);
            $table->decimal('total_fob_value', 12, 2);
            $table->decimal('prorated_freight', 10, 4)->default(0);
            $table->decimal('prorated_insurance', 10, 4)->default(0);
            $table->decimal('prorated_additional_pre_tax', 10, 4)->default(0);
            $table->decimal('cif_value', 12, 2);
            $table->decimal('tariff_rate', 8, 4)->default(0);
            $table->decimal('tariff_amount', 10, 4)->default(0);
            $table->decimal('ice_rate', 8, 4)->default(0);
            $table->decimal('ice_amount', 10, 4)->default(0);
            $table->decimal('iva_rate', 8, 4)->default(15.00);
            $table->decimal('iva_amount', 10, 4)->default(0);
            $table->decimal('total_taxes', 10, 4)->default(0);
            $table->decimal('prorated_additional_post_tax', 10, 4)->default(0);
            $table->decimal('total_cost', 12, 2);
            $table->decimal('unit_cost', 10, 4);
            $table->decimal('sale_price', 12, 2);
            $table->decimal('unit_sale_price', 10, 4);
            $table->timestamps();
            
            $table->foreign('hs_code')->references('hs_code')->on('tariff_codes')->nullOnDelete();
            $table->index(['calculation_id', 'hs_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculation_items');
    }
};
