<?php

namespace App\Filament\Resources\Product\CustomPropertiesResource\Pages;

use App\Filament\Resources\Product\CustomPropertiesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomProperties extends ListRecords
{
    protected static string $resource = CustomPropertiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
