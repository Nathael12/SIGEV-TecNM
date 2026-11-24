<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Vehiculo extends Model
{
    protected $table = 'vehiculos';

    protected $primaryKey = 'id_vehiculo';

    protected $fillable = [
        'numero_economico',
        'placas',
        'marca',
        'modelo',
        'anio',
        'tipo vehiculo',
        'color',
        'tipo combustible',
        'kilometraje actual',
        'fecha_adquisicion',
        'estado',
        'fotografia',
    ];

    protected $casts = [
        'anio' => 'integer',
        'kilometraje actual' => 'decimal:2',
        'fecha_adquisicion' => 'date',
    ];

    public function getFotografiaUrlAttribute(): ?string
    {
        if (! $this->fotografia || ! Storage::disk('public')->exists($this->fotografia)) {
            return null;
        }

        return asset('storage/' . ltrim($this->fotografia, '/'));
    }

    public function mantenimientos(): HasMany
    {
        return $this->hasMany(Mantenimiento::class, 'vehiculo_id', 'id_vehiculo');
    }

    public function sincronizarEstadoMantenimiento(): bool
    {
        $tieneMantenimientoAbierto = $this->mantenimientos()
            ->whereIn('estado_mantenimiento', ['pendiente', 'en_proceso'])
            ->exists();

        if ($this->asignaciones()->whereNull('fecha_regreso')->exists()) {
            return false;
        }

        $estadoActual = $this->getAttribute('estado');
        if ($tieneMantenimientoAbierto) {
            if (! in_array($estadoActual, ['Disponible', 'En mantenimiento'], true)) {
                return false;
            }
            $estadoNuevo = 'En mantenimiento';
        } else {
            if ($estadoActual !== 'En mantenimiento') {
                return false;
            }
            $estadoNuevo = 'Disponible';
        }

        if ($estadoNuevo === $estadoActual) {
            return false;
        }

        DB::table($this->getTable())
            ->where($this->getKeyName(), $this->getKey())
            ->update(['estado' => $estadoNuevo]);

        $this->setAttribute('estado', $estadoNuevo);

        return true;
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionVehiculo::class, 'vehiculo_id', 'id_vehiculo');
    }

    /** Obtiene la asignación más reciente según la fecha de salida registrada. */
    public function ultimaAsignacion(): HasOne
    {
        return $this->hasOne(AsignacionVehiculo::class, 'vehiculo_id', 'id_vehiculo')
            ->ofMany(['fecha_salida' => 'max', 'id_asignacion' => 'max']);
    }

    public function getKilometrajeVigenteAttribute(): ?int
    {
        $asignacion = $this->ultimaAsignacion;
        if ($asignacion) {
            // La lectura inicial rige mientras la asignación sigue abierta; al cerrar, rige la final.
            $kilometraje = $asignacion->kilometraje_final ?? $asignacion->kilometraje_inicial;

            return $kilometraje !== null ? (int) $kilometraje : null;
        }

        $kilometraje = $this->getAttribute('kilometraje actual');

        return $kilometraje !== null ? (int) round((float) $kilometraje) : null;
    }

    public function ultimoMantenimiento(): HasOne
    {
        return $this->hasOne(Mantenimiento::class, 'vehiculo_id', 'id_vehiculo')->latestOfMany('id_mantenimiento');
    }
}
