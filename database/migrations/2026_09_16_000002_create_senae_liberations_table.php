<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('senae_liberations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // e.g. '0672'
            $table->string('type', 50)->default('TPNG'); // e.g. 'TPNG', 'LEY', 'CONVENIO'
            $table->string('description', 255); // e.g. 'Maquinaria, implementos y partes agrícolas'
            $table->text('legal_basis')->nullable(); // e.g. 'LORTI Art. 55 Num. 5 / Decreto Ejecutivo 1232'
            $table->boolean('exempts_iva')->default(true);
            $table->decimal('iva_reduction_percent', 5, 2)->default(100.00); // 100% reduction = 0% IVA
            $table->boolean('exempts_tariff')->default(false);
            $table->decimal('tariff_reduction_percent', 5, 2)->default(0.00);
            $table->boolean('exempts_ice')->default(false);
            $table->boolean('exempts_fodinfa')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('senae_liberations');
    }
};
