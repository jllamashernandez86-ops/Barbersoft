<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IngresosSemanalesChart extends ChartWidget
{
    protected static ?string $heading = '💵 Ingresos de la Semana';
    
    protected static ?int $sort = 4;
    
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $ingresosPorDia = [];
        $labels = [];

        for ($i = 0; $i < 7; $i++) {
            $dia = $startOfWeek->copy()->addDays($i);
            $labels[] = $dia->locale('es')->isoFormat('ddd'); // Lun, Mar, Mié...
            
            $ingreso = Pago::whereRaw('LOWER(TRIM(estado)) = ?', ['pagado'])
                ->whereDate(DB::raw('COALESCE(pagado_at, created_at)'), $dia->toDateString())
                ->sum('monto');
            
            $ingresosPorDia[] = $ingreso;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos (COP)',
                    'data' => $ingresosPorDia,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return "$" + value.toLocaleString(); }',
                    ],
                ],
            ],
        ];
    }
}
