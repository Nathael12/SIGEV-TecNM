
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id('id_vehiculo');

            $table->string('numero_economico')->unique();
            $table->string('placas')->unique();
            $table->string('marca');
            $table->string('modelo');
            $table->year('anio');
            $table->string('tipo');
            $table->string('color');
            $table->string('combustible');
            $table->decimal('kilometraje', 10, 2)->default(0);
            $table->date('fecha_adquisicion');
            $table->string('estado')->default('Activo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
