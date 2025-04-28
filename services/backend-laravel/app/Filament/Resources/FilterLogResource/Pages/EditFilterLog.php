<?php

namespace App\Filament\Resources\FilterLogResource\Pages;

use App\Filament\Resources\FilterLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFilterLog extends EditRecord
{
    protected static string $resource = FilterLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
