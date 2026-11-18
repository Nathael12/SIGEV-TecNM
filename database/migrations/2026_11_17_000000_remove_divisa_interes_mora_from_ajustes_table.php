<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ajustes', function (Blueprint $table) {
            $table->dropColumn(['divisa', 'interes', 'mora']);
        });
    }

    public function down(): void
    {
        Schema::table('ajustes', function (Blueprint $table) {
            $table->string('divisa');
            $table->decimal('interes', 5, 2)->default(10);
            $table->decimal('mora', 5, 2)->default(2);
        });
    }
};
