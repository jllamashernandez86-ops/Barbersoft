<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today()->toDateString();
        
        // Citas de hoy
        $citasHoy = Cita::whereDate('fecha', $today)->count();
        $citasCompletadas = Cita::whereDate('fecha', $today)
            ->where('estado', 'completada')
            ->count();
        $citasPendientes = Cita::whereDate('fecha', $today)
            ->where('estado', 'pendiente')
            ->count();
        
        // Clientes activos (total)
        $clientesActivos = Cliente::count();
        $clientesNuevos = Cliente::whereDate('created_at', '>=', Carbon::now()->subDays(7))->count();
        
        // Ingresos de hoy (pagos con estado 'pagado')
        $ingresosHoy = Pago::whereRaw('LOWER(TRIM(estado)) = ?', ['pagado'])
            ->whereDate(DB::raw('COALESCE(pagado_at, created_at)'), $today)
            ->sum('monto');
        
        $ingresosAyer = Pago::whereRaw('LOWER(TRIM(estado)) = ?', ['pagado'])
            ->whereDate(DB::raw('COALESCE(pagado_at, created_at)'), Carbon::yesterday())
            ->sum('monto');
        
        $cambioIngresos = $ingresosAyer > 0 
            ? round((($ingresosHoy - $ingresosAyer) / $ingresosAyer) * 100, 1)
            : 0;
        
        // Servicios completados hoy
        $serviciosCompletados = Cita::whereDate('fecha', $today)
            ->where('estado', 'completada')
            ->count();

        return [
            Stat::make('📅 Citas de Hoy', $citasHoy)
                ->description($citasCompletadas . ' completadas · ' . $citasPendientes . ' pendientes')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning')
                ->chart([5, 8, 12, 10, $citasHoy])
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-lg transition',
                ]),
            
            Stat::make('👥 Clientes', $clientesActivos)
                ->description('+' . $clientesNuevos . ' esta semana')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([10, 15, 12, 18, $clientesActivos]),
            
            Stat::make('💰 Ingresos Hoy', '$' . number_format($ingresosHoy, 0, ',', '.'))
                ->description(($cambioIngresos >= 0 ? '+' : '') . $cambioIngresos . '% vs ayer')
                ->descriptionIcon($cambioIngresos >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($cambioIngresos >= 0 ? 'success' : 'danger')
                ->chart([50000, 75000, 60000, 90000, $ingresosHoy]),
            
            Stat::make('✂️ Servicios', $serviciosCompletados)
                ->description('Completados hoy')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('info')
                ->chart([3, 5, 7, 6, $serviciosCompletados]),
        ];
    }
}
