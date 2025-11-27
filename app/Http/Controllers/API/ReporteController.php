<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReporteResumenExport;

class ReporteController extends Controller
{
    // ... tu método resumen() puede quedarse igual

    public function resumen(Request $request)
{
    $monthStr = $request->get('month');
    $start = $monthStr
        ? Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth()
        : now()->startOfMonth();
    $end   = (clone $start)->endOfMonth();

    $summary = $this->buildSummary($start, $end);

    return response()->json([
        'rango' => $summary['rango'],

        'ingresos' => [
            'totalVentas'      => $summary['ingresos']['total'],
            'numTransacciones' => $summary['ingresos']['transacciones'],
            'promedioPorVenta' => $summary['ingresos']['promedio'],
        ],

        'clientes' => [
            'activosMes'  => $summary['clientes']['activos'],
            'citasDelMes' => $summary['clientes']['citas'],
            'retencion'   => $summary['clientes']['retencion'],
        ],

        'rendimientoBarberos' => $summary['barberos'],
        'metodosPago'         => $summary['metodos'],
    ]);
}

    
    /** Construye el mismo resumen que devuelve resumen(), para reuso en PDF/Excel */
    private function buildSummary(Carbon $start, Carbon $end): array
    {
        // -------- INGRESOS --------
        $fechaPagoSql = "COALESCE(pagado_at, created_at)";
        $ing = DB::table('pagos')
            ->whereRaw('LOWER(TRIM(estado)) = ?', ['pagado'])
            ->whereRaw("$fechaPagoSql BETWEEN ? AND ?", [$start, $end])
            ->selectRaw('COUNT(*) as transacciones, COALESCE(SUM(monto),0) as total')
            ->first();

        $totalVentas      = (float) ($ing->total ?? 0);
        $transacciones    = (int)   ($ing->transacciones ?? 0);
        $promedioPorVenta = $transacciones > 0 ? $totalVentas / $transacciones : 0.0;

        // -------- CLIENTES --------
        $activosMes = DB::table('citas')
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->distinct()
            ->count('cliente_id');

        $citasDelMes = DB::table('citas')
            ->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])
            ->count();

        // Retención 6 meses
        $retStart = now()->subMonths(6)->startOfDay();
        $retRaw = DB::table('citas')
            ->select('cliente_id', DB::raw('COUNT(*) as cnt'))
            ->where('fecha', '>=', $retStart->toDateString())
            ->groupBy('cliente_id')->get();
        $c1 = $retRaw->count();
        $c2 = $retRaw->where('cnt', '>=', 2)->count();
        $retencion = $c1 > 0 ? round(($c2 / $c1) * 100) : 0;

        // -------- MÉTODOS DE PAGO --------
        $metodos = DB::table('pagos')
            ->whereRaw('LOWER(TRIM(estado)) = ?', ['pagado'])
            ->whereRaw("$fechaPagoSql BETWEEN ? AND ?", [$start, $end])
            ->groupBy('metodo')
            ->selectRaw('metodo, COUNT(*) as pagos, COALESCE(SUM(monto),0) as total')
            ->orderBy('metodo')
            ->get()
            ->map(fn($m) => [
                'metodo' => $m->metodo ?: 'Desconocido',
                'pagos'  => (int) $m->pagos,
                'total'  => (float) $m->total,
            ])->values();

        // -------- RENDIMIENTO BARBEROS --------
        $rendimiento = DB::table('citas as c')
            ->join('barberos as b', 'b.id', '=', 'c.barbero_id')
            ->whereBetween('c.fecha', [$start->toDateString(), $end->toDateString()])
            ->whereIn(DB::raw('LOWER(TRIM(c.estado))'), ['completada','completado'])
            ->groupBy('b.id','b.nombre')
            ->selectRaw('b.id, b.nombre, COUNT(*) as citas')
            ->orderBy('b.nombre')
            ->get()
            ->map(fn($r) => ['barbero' => $r->nombre, 'citas' => (int) $r->citas])
            ->values();

        return [
            'rango' => ['inicio' => $start->toDateString(), 'fin' => $end->toDateString()],
            'ingresos' => [
                'total'          => $totalVentas,
                'transacciones'  => $transacciones,
                'promedio'       => $promedioPorVenta,
            ],
            'clientes' => [
                'activos'  => (int) $activosMes,
                'citas'    => (int) $citasDelMes,
                'retencion'=> (int) $retencion,
            ],
            'metodos' => $metodos,        // [{metodo,pagos,total}]
            'barberos'=> $rendimiento,    // [{barbero,citas}]
        ];
    }

    public function exportPdf(Request $request)
    {
        $monthStr = $request->get('month');
        $start = $monthStr ? Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth() : now()->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $summary = $this->buildSummary($start, $end);

        $pdf = Pdf::loadView('reportes.resumen', [
            'summary' => $summary,
        ])->setPaper('a4', 'portrait');

        $filename = 'reporte_resumen_' . $start->format('Y_m') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $monthStr = $request->get('month');
        $start = $monthStr ? Carbon::createFromFormat('Y-m', $monthStr)->startOfMonth() : now()->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $summary = $this->buildSummary($start, $end);

        $filename = 'reporte_resumen_' . $start->format('Y_m') . '.xlsx';
        return Excel::download(new ReporteResumenExport($summary), $filename);
    }
}
