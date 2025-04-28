<?php

namespace App\Filament\Resources\FilterLogResource\Pages;

use App\Filament\Resources\FilterLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFilterLogs extends ListRecords
{
    protected static string $resource = FilterLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
