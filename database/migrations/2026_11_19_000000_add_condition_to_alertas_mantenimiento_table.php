<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('alertas_mantenimiento', 'condicion')) {
            Schema::table('alertas_mantenimiento', function (Blueprint $table) {
                $table->string('condicion', 20)->default('fecha')->after('mantenimiento_id');
            });
        }

        if (! $this->indexExists('alertas_mantenimiento_mantenimiento_id_index')) {
            // MySQL needs a standalone replacement index before dropping the
            // unique index currently supporting the mantenimiento_id foreign key.
            Schema::table('alertas_mantenimiento', function (Blueprint $table) {
                $table->index('mantenimiento_id', 'alertas_mantenimiento_mantenimiento_id_index');
            });
        }

        if (! $this->indexExists('alertas_mantenimiento_condicion_unique')) {
            Schema::table('alertas_mantenimiento', function (Blueprint $table) {
                if ($this->indexExists('alertas_mantenimiento_mantenimiento_id_dias_anticipacion_unique')) {
                    $table->dropUnique('alertas_mantenimiento_mantenimiento_id_dias_anticipacion_unique');
                }
                $table->unique(['mantenimiento_id', 'dias_anticipacion', 'condicion'], 'alertas_mantenimiento_condicion_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('alertas_mantenimiento', function (Blueprint $table) {
            $table->dropUnique('alertas_mantenimiento_condicion_unique');
            $table->unique(['mantenimiento_id', 'dias_anticipacion']);
        });

        Schema::table('alertas_mantenimiento', function (Blueprint $table) {
            $table->dropIndex('alertas_mantenimiento_mantenimiento_id_index');
            $table->dropColumn('condicion');
        });
    }

    private function indexExists(string $name): bool
    {
        return collect(Schema::getIndexes('alertas_mantenimiento'))->contains('name', $name);
    }
};
