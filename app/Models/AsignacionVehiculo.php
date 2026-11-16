<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionVehiculo extends Model
{
    protected $table = 'asignacion_vehiculos';

    // El UML usa id_asignacion en lugar de id
    protected $primaryKey = 'id_asignacion';

    protected $fillable = [
        'fecha_salida',
        'fecha_regreso',
        'motivo_uso',
        'destino_vehiculo',
        'kilometraje_inicial',
        'kilometraje_final',
        'usuario_id',
        'vehiculo_id',
    ];

    protected $casts = [
        'fecha_salida'        => 'datetime',
        'fecha_regreso'       => 'datetime',
        'kilometraje_inicial' => 'integer',
        'kilometraje_final'   => 'integer',
    ];

    /**
     * RN-04: una asignación está activa mientras no tenga fecha de regreso.
     */
    public function getActivaAttribute(): bool
    {
        return $this->fecha_regreso === null;
    }

    /**
     * Texto del estado de la asignación: Activa o Finalizada.
     */
    public function getEstadoAsignacionAttribute(): string
    {
        return $this->activa ? 'Activa' : 'Finalizada';
    }

    /**
     * Kilómetros recorridos (solo cuando ya hubo devolución).
     */
    public function getKilometrosRecorridosAttribute(): ?int
    {
        if ($this->kilometraje_final === null) {
            return null;
        }

        return $this->kilometraje_final - $this->kilometraje_inicial;
    }

    /**
     * Solo asignaciones activas (sin devolución registrada).
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereNull('fecha_regreso');
    }

    /**
     * El solicitante: usuario al que se le asigna el vehículo.
     */
    public function solicitante(): BelongsTo
    {
        // withTrashed: si el usuario se elimina (soft delete), su historial se conserva
        return $this->belongsTo(User::class, 'usuario_id')->withTrashed();
    }

    /**
     * El vehículo asignado.
     */
    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id', 'id_vehiculo');
    }
}