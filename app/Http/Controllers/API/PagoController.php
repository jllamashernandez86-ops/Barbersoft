<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\Pago;
use App\Models\Cita;
use App\Models\Servicio;
use Carbon\Carbon;

class PagoController extends Controller
{
    /**
     * Listado con filtros por fecha (de la cita) y estado del pago.
     * GET /api/pagos?fecha=YYYY-MM-DD&estado=pagado|pendiente|anulado
     */
    public function index(Request $request)
    {
        $fecha  = $request->get('fecha');   // opcional
        $estado = $request->get('estado');  // opcional

        $q = Pago::query()
            ->with([
                'cita.cliente:id,nombre',
                'cita.servicio:id,nombre,precio',
            ])
            ->orderByDesc('id');

        if ($estado) {
            $q->whereRaw('LOWER(estado)=?', [strtolower($estado)]);
        }

        if ($fecha) {
            // filtra por fecha de la CITA (como en tu pantalla)
            $q->whereHas('cita', fn($qq) => $qq->whereDate('fecha', $fecha));
        }

        return $q->get();
    }

    public function show(Pago $pago)
    {
        return $pago->load('cita.cliente','cita.servicio');
    }

    /**
     * Crea un pago. Si no envían monto, toma el precio del servicio de la cita.
     * Body: cita_id, metodo, (monto opcional), (estado opcional), (pagado_at opcional)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'cita_id'   => 'required|exists:citas,id',
            'metodo'    => 'required|string|max:30',
            'monto'     => 'nullable|numeric|min:0',
            'estado'    => 'nullable|in:pagado,pendiente,anulado',
            'pagado_at' => 'nullable|date',
        ]);

        $cita = Cita::with('servicio:id,precio')->findOrFail($data['cita_id']);

        // Si no viene monto, usar el precio del servicio
        if (!isset($data['monto'])) {
            $data['monto'] = (float) $cita->servicio->precio;
        }

        $data['estado']    = strtolower($data['estado'] ?? 'pagado');
        $data['pagado_at'] = $data['pagado_at'] ?? Carbon::now()->toDateString();

        // Evitar duplicado (si tu tabla tiene unique cita_id)
        $existe = Pago::where('cita_id', $data['cita_id'])->exists();
        if ($existe) {
            return response()->json(['message' => 'La cita ya tiene pago registrado.'], 422);
        }

        $pago = Pago::create($data);

        return $pago->load('cita.cliente','cita.servicio');
    }

    /**
     * Actualiza método/estado/monto/pagado_at
     */
    public function update(Request $request, Pago $pago)
    {
        $data = $request->validate([
            'metodo'    => 'sometimes|required|string|max:30',
            'monto'     => 'sometimes|required|numeric|min:0',
            'estado'    => 'sometimes|required|in:pagado,pendiente,anulado',
            'pagado_at' => 'sometimes|nullable|date',
        ]);

        if (isset($data['estado'])) {
            $data['estado'] = strtolower($data['estado']);
        }

        $pago->update($data);

        return $pago->load('cita.cliente','cita.servicio');
    }

    public function destroy(Pago $pago)
    {
        $pago->delete();
        return response()->json(['message' => 'Pago eliminado']);
    }
}
