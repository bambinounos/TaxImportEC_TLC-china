<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tlc_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('hs_code', 10);
            $table->string('country_code', 3)->default('CHN');
            $table->decimal('base_rate', 8, 4);
            $table->integer('elimination_years');
            $table->date('start_date');
            $table->enum('reduction_type', ['immediate', 'linear', 'staged'])->default('linear');
            $table->string('tlc_category', 10)->nullable();
            $table->json('yearly_rates')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('hs_code')->references('hs_code')->on('tariff_codes');
            $table->index(['hs_code', 'country_code']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tlc_schedules');
    }
};
