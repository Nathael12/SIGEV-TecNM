<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaMantenimiento extends Model
{
    protected $table = 'alertas_mantenimiento';

    protected $fillable = ['mantenimiento_id', 'dias_anticipacion', 'tipo', 'estado', 'fecha_mantenimiento', 'vista_en'];

    protected $casts = ['fecha_mantenimiento' => 'date', 'vista_en' => 'datetime'];

    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(Mantenimiento::class, 'mantenimiento_id', 'id_mantenimiento');
    }
}
