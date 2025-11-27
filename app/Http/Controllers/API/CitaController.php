<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\Cita;
use App\Models\Servicio;
use Carbon\Carbon;
use App\Models\Pago;

class CitaController extends Controller
{
    /**
     * Listar citas por fecha (default: hoy)
     */
    public function index(Request $request)
    {
        $fecha = $request->get('fecha', Carbon::now()->toDateString());

        return Cita::whereDate('fecha', $fecha)
            ->with(['cliente', 'barbero', 'servicio'])
            ->orderBy('hora')
            ->get();
    }

    /**
     * Crear cita (con validación de choque e índice único)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'cliente_id'  => 'required|exists:clientes,id',
            'barbero_id'  => 'required|exists:barberos,id',
            'servicio_id' => 'required|exists:servicios,id',
            'fecha'       => 'required|date_format:Y-m-d',
            'hora'        => 'required|date_format:H:i',
        ]);

        // Estado por defecto en minúsculas
        $data['estado'] = strtolower($request->input('estado', 'pendiente'));

        // Pre-chequeo: mismo barbero + misma fecha + misma hora
        $yaExiste = Cita::where('barbero_id', $data['barbero_id'])
            ->whereDate('fecha', $data['fecha'])
            ->whereTime('hora', $data['hora'])
            ->exists();

        if ($yaExiste) {
            return response()->json([
                'message' => 'Ese barbero ya tiene una cita a esa hora.',
            ], 422);
        }

        try {
            $cita = Cita::create($data);
            return response()->json($cita->load(['cliente','barbero','servicio']), 201);
        } catch (QueryException $e) {
            // Si (además) el índice único en la BD dispara 1062
            if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                return response()->json([
                    'message' => 'Conflicto de horario: barbero ocupado en ese horario.',
                ], 409);
            }
            throw $e;
        }
    }

    /**
     * Actualizar cita (datos generales)
     */
    public function update(Request $request, Cita $cita)
    {
        $data = $request->all();
        if (isset($data['fecha'])) {
            $request->validate(['fecha' => 'date_format:Y-m-d']);
        }
        if (isset($data['hora'])) {
            $request->validate(['hora' => 'date_format:H:i']);
        }
        if (isset($data['estado'])) {
            $data['estado'] = strtolower($data['estado']);
        }

        $cita->update($data);

        return response()->json($cita->load(['cliente','barbero','servicio']));
    }

    /**
     * Eliminar cita
     */
    public function destroy(Cita $cita)
    {
        $cita->delete();
        return response()->json(['message' => 'Cita eliminada']);
    }

    /**
     * Disponibilidad de barbero para una fecha y servicio
     * GET /api/barberos/{barbero_id}/disponibilidad?fecha=YYYY-MM-DD&servicio_id=ID
     */
    public function disponibilidad(Request $request, $barbero_id)
    {
        $params = $request->validate([
            'fecha'       => 'required|date_format:Y-m-d',
            'servicio_id' => 'required|exists:servicios,id',
        ], [
            'servicio_id.required' => 'Debes seleccionar un servicio para calcular la duración.',
        ]);

        $fechaStr  = $params['fecha'];
        $servicio  = Servicio::findOrFail($params['servicio_id']);
        $duracion  = (int) $servicio->duracion; // minutos

        // Jornada (ajusta a tu horario real)
        $inicioJornada = Carbon::createFromFormat('Y-m-d H:i', "{$fechaStr} 08:00");
        $finJornada    = Carbon::createFromFormat('Y-m-d H:i', "{$fechaStr} 18:00");

        // Generar slots cada 15 min (o el paso que prefieras)
        $slots = [];
        for ($t = $inicioJornada->copy(); $t->lte($finJornada->copy()->subMinutes($duracion)); $t->addMinutes(15)) {
            $inicio = $t->copy();
            $fin    = $t->copy()->addMinutes($duracion);
            if ($fin->gt($finJornada)) break;
            $slots[] = [$inicio, $fin]; // guarda fecha+hora
        }

        // Traer citas existentes del barbero ese día
        $citas = Cita::where('barbero_id', $barbero_id)
            ->whereDate('fecha', $fechaStr)
            ->get(['hora', 'servicio_id']);

        // Construir intervalos ocupados con parseo TOLERANTE (HH:MM o HH:MM:SS)
        $ocupados = [];
        foreach ($citas as $c) {
            $dur = (int) (Servicio::find($c->servicio_id)->duracion ?? $duracion);
            $hStr = trim((string) $c->hora);

            // normaliza a HH:MM
            if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $hStr)) {
                $hStr = substr($hStr, 0, 5);
            } elseif (!preg_match('/^\d{2}:\d{2}$/', $hStr)) {
                // fallback por si está guardado con otro formato
                $hStr = Carbon::parse($hStr)->format('H:i');
            }

            $oIni = Carbon::createFromFormat('Y-m-d H:i', "{$fechaStr} {$hStr}");
            $oFin = $oIni->copy()->addMinutes($dur);
            $ocupados[] = [$oIni, $oFin];
        }

        // Filtrar slots que NO se solapan
        $horasDisponibles = [];
        foreach ($slots as [$ini, $fin]) {
            $solapa = false;
            foreach ($ocupados as [$oIni, $oFin]) {
                // Solapa si: ini < oFin y fin > oIni
                if ($ini->lt($oFin) && $fin->gt($oIni)) { $solapa = true; break; }
            }
            if (!$solapa) $horasDisponibles[] = $ini->format('H:i');
        }

        return response()->json([
            'fecha'       => $fechaStr,
            'barbero_id'  => (int) $barbero_id,
            'servicio_id' => (int) $servicio->id,
            'duracion'    => $duracion,
            'horas'       => $horasDisponibles,
            'disponibles' => $horasDisponibles, // compatibilidad con front antiguo
        ]);
    }

    /**
     * Cambiar solo el estado de una cita
     * PATCH /api/citas/{cita}/estado { estado: pendiente|completada|cancelada }
     */
    public function cambiarEstado(Request $request, Cita $cita)
    {
       
{
    // Normaliza
    $request->merge(['estado' => strtolower($request->estado ?? '')]);

    $request->validate([
        'estado' => 'required|in:pendiente,completada,cancelada',
        // Si quieres permitir elegir método cuando completes:
        'metodo' => 'sometimes|string|max:30',
    ]);

    // Cambiar estado
    $cita->estado = $request->estado;
    $cita->save();

    /**
     * ✅ AUTO-CREAR PAGO SI LA CITA SE COMPLETÓ
     */
    if ($cita->estado === 'completada') {

        // Cargar servicio si aún no está cargado
        $cita->loadMissing('servicio');

        // Evitar pagos duplicados
        $yaHayPago = Pago::where('cita_id', $cita->id)->exists();

        if (!$yaHayPago) {
            $precio = (float) optional($cita->servicio)->precio ?? 0;

            Pago::create([
                'cita_id'   => $cita->id,
                'monto'     => $precio,
                'metodo'    => $request->input('metodo', 'efectivo'), // por defecto efectivo
                'estado'    => 'pagado', // Cambia a 'pendiente' si prefieres cobrar luego
                'pagado_at' => now()->toDateString(),
            ]);
        }
    }

    return response()->json([
        'message' => 'Estado actualizado',
        'cita'    => $cita->only(['id','fecha','hora','estado','cliente_id','barbero_id','servicio_id']),
    ]);
}

    }

    /**
     * Estadísticas para dashboard
     */
    public function stats(Request $request)
{
    $today = now()->timezone(config('app.timezone'))->toDateString();
    $fecha = $request->get('fecha', $today);

    // ---- totales de citas (igual que antes) ----
    $estadoExpr = DB::raw("LOWER(TRIM(citas.estado))");

    $total = \App\Models\Cita::whereDate('fecha', $fecha)->count();

    $pendientes = \App\Models\Cita::whereDate('fecha', $fecha)
        ->where($estadoExpr, 'pendiente')->count();

    $completadas = \App\Models\Cita::whereDate('fecha', $fecha)
        ->whereIn($estadoExpr, ['completada', 'completado'])->count();

    $canceladas = \App\Models\Cita::whereDate('fecha', $fecha)
        ->where($estadoExpr, 'cancelada')->count();

    $clientesActivos = DB::table('citas')->distinct()->count('cliente_id');

    // ---- ⚡ INGRESOS DESDE PAGOS (pagado) ----
    // usa TRIM para evitar espacios, y whereDate con pagado_at/created_at
    $ingresosPagadosHoy = DB::table('pagos')
        ->whereRaw('LOWER(TRIM(estado)) = ?', ['pagado'])
        ->whereDate(DB::raw('COALESCE(pagado_at, created_at)'), $fecha)
        ->sum('monto'); // DECIMAL en tu tabla, no hace falta CAST

    return response()->json([
        'total'            => (int) $total,
        'pendientes'       => (int) $pendientes,
        'completadas'      => (int) $completadas,
        'canceladas'       => (int) $canceladas,

        // 👉 entrega en snake y camel por compatibilidad
        'ingresos_hoy'     => (float) $ingresosPagadosHoy,
        'ingresosHoy'      => (float) $ingresosPagadosHoy,

        'clientes_activos' => (int) $clientesActivos,
        'total_citas_hoy'  => (int) $total,
        'completadas_hoy'  => (int) $completadas,
    ]);
}



}
