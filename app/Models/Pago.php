<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'cita_id',
        'monto',
        'metodo',     // efectivo, nequi, daviplata, transferencia, etc.
        'estado',     // pagado, pendiente, anulado
        'pagado_at',
    ];

    protected $casts = [
        'pagado_at' => 'date',
    ];

    public function cita()
    {
        return $this->belongsTo(Cita::class);
    }

    public function cliente()
    {
        return $this->hasOneThrough(
            Cliente::class,
            Cita::class,
            'id',         // local key on Cita
            'id',         // local key on Cliente
            'cita_id',    // foreign key on Pago -> Cita
            'cliente_id'  // foreign key on Cita -> Cliente
        );
    }

    public function servicio()
    {
        return $this->hasOneThrough(
            Servicio::class,
            Cita::class,
            'id',
            'id',
            'cita_id',
            'servicio_id'
        );
    }
}
