<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PagoResource\Pages;
use App\Models\Pago;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PagoResource extends Resource
{
    protected static ?string $model = Pago::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Pagos';
    protected static ?string $pluralModelLabel = 'Pagos';
    protected static ?string $modelLabel = 'Pago';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('cita_id')
                ->label('Cita')
                ->relationship('cita', 'id')
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\TextInput::make('monto')
                ->numeric()
                ->prefix('COP')
                ->required(),
            Forms\Components\Select::make('metodo')
                ->options([
                    'efectivo' => 'Efectivo',
                    'nequi' => 'Nequi',
                    'daviplata' => 'Daviplata',
                    'transferencia' => 'Transferencia',
                ])->searchable(),
            Forms\Components\Select::make('estado')
                ->options([
                    'pagado' => 'Pagado',
                    'pendiente' => 'Pendiente',
                    'anulado' => 'Anulado',
                ])->required(),
            Forms\Components\DatePicker::make('pagado_at')
                ->label('Fecha de pago')
                ->native(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pagado_at')
                    ->label('Fecha pago')
                    ->date('Y-m-d')
                    ->sortable(),
                Tables\Columns\TextColumn::make('cita.cliente.nombre')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('cita.barbero.nombre')
                    ->label('Barbero')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('cita.servicio.nombre')
                    ->label('Servicio')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('metodo')
                    ->label('Método')
                    ->badge()
                    ->colors([
                        'primary',
                        'success' => 'efectivo',
                        'warning' => 'transferencia',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('monto')
                    ->label('Monto')
                    ->money('COP', locale: 'es-CO')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'success' => 'pagado',
                        'warning' => 'pendiente',
                        'danger' => 'anulado',
                    ])
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'pagado' => 'Pagado',
                        'pendiente' => 'Pendiente',
                        'anulado' => 'Anulado',
                    ]),
                Tables\Filters\Filter::make('fecha')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Desde'),
                        Forms\Components\DatePicker::make('until')->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('pagado_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('pagado_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('pagado_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagos::route('/'),
            'create' => Pages\CreatePago::route('/create'),
            'edit' => Pages\EditPago::route('/{record}/edit'),
        ];
    }
}
