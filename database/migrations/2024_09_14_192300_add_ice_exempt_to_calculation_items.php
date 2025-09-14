<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculation_items', function (Blueprint $table) {
            $table->boolean('ice_exempt')->default(false)->after('hs_code');
            $table->text('ice_exempt_reason')->nullable()->after('ice_exempt');
        });
    }

    public function down(): void
    {
        Schema::table('calculation_items', function (Blueprint $table) {
            $table->dropColumn(['ice_exempt', 'ice_exempt_reason']);
        });
    }
};
