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
            $table->string('hs_code', 20)->unique();
            $table->text('description_en');
            $table->text('description_es');
            $table->decimal('base_tariff_rate', 8, 4)->nullable();
            $table->decimal('iva_rate', 8, 4)->default(15.0);
            $table->string('unit', 10)->nullable();
            $table->boolean('has_ice')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('hierarchy_level')->default(10);
            $table->string('parent_code', 20)->nullable();
            $table->integer('order_number')->nullable();
            $table->timestamps();
            
            $table->index('hs_code');
            $table->index('is_active');
            $table->index('has_ice');
            $table->index(['hierarchy_level', 'parent_code']);
            $table->index('order_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariff_codes');
    }
};
