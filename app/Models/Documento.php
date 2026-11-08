<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use SoftDeletes;

    // El UML usa id_documento en lugar de id
    protected $primaryKey = 'id_documento';

    protected $fillable = [
        'descripcion_documento',
        'vigencia_inicio',
        'vigencia_fin',
        'importe',
        'ruta_archivo',
        'nombre_original',
        'tipo_archivo',
        'tamano_archivo',
        'vehiculo_id',
    ];

    protected $casts = [
        'vigencia_inicio' => 'date',
        'vigencia_fin'    => 'date',
        'importe'         => 'decimal:2',
    ];

    // Días de anticipación para marcar un documento como "Por vencer" (RF-14)
    public const DIAS_ALERTA = 30;

    /**
     * Estado de vigencia calculado a partir de vigencia_fin:
     * Vigente, Por vencer o Vencido. No se guarda en la base de datos.
     */
    public function getEstadoVigenciaAttribute(): string
    {
        $hoy = now()->startOfDay();

        if ($this->vigencia_fin->lt($hoy)) {
            return 'Vencido';
        }

        if ($this->vigencia_fin->lte($hoy->copy()->addDays(self::DIAS_ALERTA))) {
            return 'Por vencer';
        }

        return 'Vigente';
    }

    /**
     * Un documento pertenece a un vehículo (documentos.vehiculo_id -> vehiculos.id_vehiculo).
     */
    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id', 'id_vehiculo');
    }
}