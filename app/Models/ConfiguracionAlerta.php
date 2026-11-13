<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionAlerta extends Model
{
    protected $table = 'configuracion_alertas';

    protected $fillable = ['entidad', 'dias_anticipacion', 'activo'];

    protected $casts = ['activo' => 'boolean', 'dias_anticipacion' => 'integer'];
}
