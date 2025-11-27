<?php

namespace App\Filament\Admin\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Cita;
use Carbon\Carbon;

class ProximasCitasWidget extends BaseWidget
{
    protected int | string | array $columnSpan = [
        'md' => 2,
        'xl' => 1,
    ];
    
    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        $now = Carbon::now();

        return $table
            ->heading('🔔 Próximas Citas')
            ->query(
                Cita::query()
                    ->where(function($q) use ($now) {
                        $q->where('fecha', '>', $now->toDateString())
                          ->orWhere(function($q2) use ($now) {
                              $q2->where('fecha', $now->toDateString())
                                 ->where('hora', '>=', $now->format('H:i:s'));
                          });
                    })
                    ->whereIn('estado', ['pendiente', 'confirmada'])
                    ->with(['cliente', 'barbero', 'servicio'])
                    ->orderBy('fecha')
                    ->orderBy('hora')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('fecha')
                    ->label('📅 Fecha')
                    ->date('D, d M')
                    ->sortable(),
                Tables\Columns\TextColumn::make('hora')
                    ->label('⏰ Hora')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Cliente')
                    ->limit(20)
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('estado')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'confirmada',
                    ]),
            ])
            ->paginated(false);
    }
}
