<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Exports\ReportePagosExport;
use App\Exports\ReporteResumenExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function exportPagos(Request $request)
    {
        [$start, $end] = $this->dateRange($request);
        return Excel::download(new ReportePagosExport($start, $end), 'reporte_pagos_'.$start->format('Ymd').'-'.$end->format('Ymd').'.xlsx');
    }

    public function exportResumen(Request $request)
    {
        [$start, $end] = $this->dateRange($request);

        // Construir datos de resumen desde la BD
        $ingresos = DB::table('pagos as p')
            ->whereRaw('LOWER(TRIM(p.estado)) = ?', ['pagado'])
            ->whereBetween(DB::raw('COALESCE(p.pagado_at, p.created_at)'), [$start, $end])
            ->selectRaw('SUM(monto) as total, COUNT(*) as transacciones, AVG(monto) as promedio')
            ->first();

        $clientesActivos = DB::table('clientes')->count();
        $citasPeriodo = DB::table('citas')->whereBetween('fecha', [$start->toDateString(), $end->toDateString()])->count();

        $metodos = DB::table('pagos')
            ->select('metodo', DB::raw('COUNT(*) as pagos'), DB::raw('SUM(monto) as total'))
            ->whereRaw('LOWER(TRIM(estado)) = ?', ['pagado'])
            ->whereBetween(DB::raw('COALESCE(pagado_at, created_at)'), [$start, $end])
            ->groupBy('metodo')->get()->map(fn($r)=>['metodo'=>$r->metodo,'pagos'=>(int)$r->pagos,'total'=>(float)$r->total])->toArray();

        $barberos = DB::table('citas as c')
            ->leftJoin('barberos as b','b.id','=','c.barbero_id')
            ->whereBetween('c.fecha', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('IFNULL(b.nombre, "—") as barbero, COUNT(*) as citas')
            ->groupBy('barbero')->get()->map(fn($r)=>['barbero'=>$r->barbero,'citas'=>(int)$r->citas])->toArray();

        $summary = [
            'rango' => ['inicio'=>$start->toDateString(),'fin'=>$end->toDateString()],
            'ingresos' => [
                'total' => (float) ($ingresos->total ?? 0),
                'transacciones' => (int) ($ingresos->transacciones ?? 0),
                'promedio' => (float) ($ingresos->promedio ?? 0),
            ],
            'clientes' => [
                'activos' => $clientesActivos,
                'citas' => $citasPeriodo,
                'retencion' => 0,
            ],
            'metodos' => $metodos,
            'barberos' => $barberos,
        ];

        return Excel::download(new ReporteResumenExport($summary), 'reporte_resumen_'.$start->format('Ymd').'-'.$end->format('Ymd').'.xlsx');
    }

    private function dateRange(Request $request): array
    {
        $from = $request->input('from');
        $until = $request->input('until');
        $start = $from ? Carbon::parse($from)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $until ? Carbon::parse($until)->endOfDay() : Carbon::now()->endOfMonth();
        return [$start, $end];
    }
}
