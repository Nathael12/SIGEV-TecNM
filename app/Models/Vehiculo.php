<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'tipo',
        'color',
        'combustible',
        'kilometraje',
        'fecha_adquisicion',
        'estado',
        'fotografia',
    ];

    protected $casts = [
        'anio' => 'integer',
        'kilometraje' => 'decimal:2',
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
}
