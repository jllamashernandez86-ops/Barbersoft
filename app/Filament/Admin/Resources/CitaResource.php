<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CitaResource\Pages;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Barbero;
use App\Models\Servicio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;

class CitaResource extends Resource
{
    protected static ?string $model = Cita::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Citas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('fecha')->required(),
                Forms\Components\TimePicker::make('hora')->required(),
                Forms\Components\Select::make('cliente_id')
                    ->label('Cliente')
                    ->relationship('cliente','nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('barbero_id')
                    ->label('Barbero')
                    ->relationship('barbero','nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('servicio_id')
                    ->label('Servicio')
                    ->relationship('servicio','nombre')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'confirmada' => 'Confirmada',
                        'completada' => 'Completada',
                        'cancelada' => 'Cancelada',
                    ])
                    ->default('pendiente'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fecha')->date(),
                Tables\Columns\TextColumn::make('hora'),
                Tables\Columns\TextColumn::make('cliente.nombre')->label('Cliente'),
                Tables\Columns\TextColumn::make('barbero.nombre')->label('Barbero'),
                Tables\Columns\TextColumn::make('servicio.nombre')->label('Servicio'),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'warning' => 'pendiente',
                        'success' => 'confirmada',
                        'info' => 'completada',
                        'danger' => 'cancelada',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCitas::route('/'),
            'create' => Pages\CreateCita::route('/create'),
            'edit' => Pages\EditCita::route('/{record}/edit'),
        ];
    }
}

