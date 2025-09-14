<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ice_taxes', function (Blueprint $table) {
            $table->id();
            $table->string('product_category');
            $table->text('description');
            $table->string('taxable_subjects')->nullable();
            $table->text('taxable_event')->nullable();
            $table->enum('base_type', ['specific', 'advalorem', 'both'])->default('advalorem');
            $table->string('specific_base_description')->nullable();
            $table->decimal('specific_rate_usd', 10, 4)->nullable();
            $table->decimal('advalorem_rate_percent', 8, 4)->nullable();
            $table->text('exemptions')->nullable();
            $table->text('reductions')->nullable();
            $table->text('benefits')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('product_category');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ice_taxes');
    }
};
