<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cita extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id', 'barbero_id', 'servicio_id',
        'fecha', 'hora', 'estado'
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'hora'  => 'datetime:H:i', // guarda H:i:s; castea a H:i
    ];

    public function cliente(){ return $this->belongsTo(Cliente::class); }
    public function barbero(){ return $this->belongsTo(Barbero::class); }
    public function servicio(){ return $this->belongsTo(Servicio::class); }
}

