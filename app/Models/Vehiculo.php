<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'anio' => 'integer',
        'kilometraje' => 'decimal:2',
        'fecha_adquisicion' => 'date',
    ];
}