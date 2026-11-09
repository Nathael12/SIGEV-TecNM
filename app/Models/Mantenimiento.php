<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mantenimiento extends Model
{
    protected $table = 'mantenimientos';
    protected $primaryKey = 'id_mantenimiento';

    protected $fillable = [
        'fecha_mantenimiento',
        'kilometraje_mantenimiento',
        'categoria_mantenimiento',
        'descripcion_mantenimiento',
        'monto_mantenimiento',
        'proximo_mantenimiento_fecha',
        'proximo_mantenimiento_km',
        'vehiculo_id',
    ];

    protected $casts = [
        'fecha_mantenimiento' => 'date',
        'kilometraje_mantenimiento' => 'integer',
        'monto_mantenimiento' => 'decimal:2',
        'proximo_mantenimiento_fecha' => 'date',
        'proximo_mantenimiento_km' => 'integer',
    ];

    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id', 'id_vehiculo');
    }
}
