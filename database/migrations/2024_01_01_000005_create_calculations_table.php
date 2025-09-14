<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('use_tlc_china')->default(false);
            $table->integer('calculation_year')->default(2024);
            $table->enum('proration_method', ['weight', 'price'])->default('weight');
            $table->decimal('freight_cost', 12, 2)->default(0);
            $table->decimal('insurance_rate', 8, 4)->default(1.00);
            $table->json('additional_costs_pre_tax')->nullable();
            $table->json('additional_costs_post_tax')->nullable();
            $table->integer('container_count')->default(1);
            $table->decimal('profit_margin_percent', 8, 4)->default(60.00);
            $table->decimal('total_fob_value', 12, 2)->default(0);
            $table->decimal('total_cif_value', 12, 2)->default(0);
            $table->decimal('total_taxes', 12, 2)->default(0);
            $table->decimal('total_final_cost', 12, 2)->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculations');
    }
};
