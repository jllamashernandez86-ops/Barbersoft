<?php

namespace App\Filament\Admin\Resources\ServicioResource\Pages;

use App\Filament\Admin\Resources\ServicioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServicios extends ListRecords
{
    protected static string $resource = ServicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
