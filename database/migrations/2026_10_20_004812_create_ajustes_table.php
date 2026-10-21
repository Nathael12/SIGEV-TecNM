<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ajustes', function (Blueprint $table) {
            $table->id();
        
            //información de la empresa
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('direccion');
            $table->string('telefono');
            $table->string('email');
            $table->string('divisa');
            $table->string('logo')->nullable();
            $table->string('web')->nullable();

            //informacion financiera
            $table->decimal('interes', 5, 2)->default(10);
            $table->decimal('mora', 5, 2)->default(2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajustes');
    }
};
