<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TopServiciosChart extends ChartWidget
{
    protected static ?string $heading = '🔥 Top 5 Servicios Más Solicitados';
    
    protected static ?int $sort = 3;
    
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $topServicios = Cita::whereBetween('fecha', [$startOfMonth, $endOfMonth])
            ->select('servicio_id', DB::raw('COUNT(*) as total'))
            ->groupBy('servicio_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('servicio')
            ->get();

        $labels = [];
        $data = [];

        foreach ($topServicios as $item) {
            $labels[] = $item->servicio->nombre ?? 'Sin servicio';
            $data[] = $item->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad de citas',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
