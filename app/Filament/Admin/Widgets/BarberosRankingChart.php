<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Cita;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BarberosRankingChart extends ChartWidget
{
    protected static ?string $heading = '🏆 Ranking de Barberos (Este Mes)';
    
    protected static ?int $sort = 6;
    
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $ranking = Cita::whereBetween('fecha', [$startOfMonth, $endOfMonth])
            ->whereNotNull('barbero_id')
            ->select('barbero_id', DB::raw('COUNT(*) as total'))
            ->groupBy('barbero_id')
            ->orderByDesc('total')
            ->limit(5)
            ->with('barbero')
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            'rgba(255, 193, 7, 0.7)',   // Oro
            'rgba(158, 158, 158, 0.7)', // Plata
            'rgba(205, 127, 50, 0.7)',  // Bronce
            'rgba(59, 130, 246, 0.7)',  // Azul
            'rgba(16, 185, 129, 0.7)',  // Verde
        ];

        foreach ($ranking as $index => $item) {
            $nombre = $item->barbero->nombre ?? 'Sin barbero';
            $labels[] = $nombre;
            $data[] = $item->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Servicios realizados',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
