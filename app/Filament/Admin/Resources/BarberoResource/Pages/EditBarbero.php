<?php

namespace App\Filament\Admin\Resources\BarberoResource\Pages;

use App\Filament\Admin\Resources\BarberoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBarbero extends EditRecord
{
    protected static string $resource = BarberoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
