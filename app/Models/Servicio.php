<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    protected $fillable = ['nombre','precio','duracion'];

    protected $casts = [
        'precio'   => 'decimal:2',
        'duracion' => 'integer',
    ];
}
