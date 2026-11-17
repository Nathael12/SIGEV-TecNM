<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->date('proximo_mantenimiento_fecha')->nullable()->change();
            $table->unsignedInteger('proximo_mantenimiento_km')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mantenimientos', function (Blueprint $table) {
            $table->date('proximo_mantenimiento_fecha')->nullable(false)->change();
            $table->unsignedInteger('proximo_mantenimiento_km')->nullable(false)->change();
        });
    }
};
