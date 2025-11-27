<?php

namespace App\Filament\Admin\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Cita;
use Carbon\Carbon;

class CitasHoyTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = 2;
    
    protected static ?string $heading = '📋 Agenda del Día';

    public function table(Table $table): Table
    {
        $today = Carbon::today()->toDateString();

        return $table
            ->query(
                Cita::query()
                    ->whereDate('fecha', $today)
                    ->with(['cliente', 'barbero', 'servicio'])
                    ->orderBy('hora')
            )
            ->columns([
                Tables\Columns\TextColumn::make('hora')
                    ->label('⏰ Hora')
                    ->time('H:i')
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('👤 Cliente')
                    ->searchable()
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('barbero.nombre')
                    ->label('✂️ Barbero')
                    ->default('—')
                    ->icon('heroicon-m-scissors'),
                Tables\Columns\TextColumn::make('servicio.nombre')
                    ->label('💈 Servicio')
                    ->icon('heroicon-m-sparkles'),
                Tables\Columns\TextColumn::make('servicio.precio')
                    ->label('💰 Precio')
                    ->money('COP', locale: 'es-CO')
                    ->weight('bold'),
                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'info' => 'confirmada',
                        'success' => 'completada',
                        'danger' => 'cancelada',
                    ])
                    ->icons([
                        'heroicon-m-clock' => 'pendiente',
                        'heroicon-m-check-circle' => 'confirmada',
                        'heroicon-m-check-badge' => 'completada',
                        'heroicon-m-x-circle' => 'cancelada',
                    ]),
            ])
            ->paginated([10, 25, 50])
            ->defaultSort('hora', 'asc')
            ->striped();
    }
}
