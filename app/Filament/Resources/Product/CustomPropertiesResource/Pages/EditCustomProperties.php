<?php

namespace App\Filament\Resources\Product\CustomPropertiesResource\Pages;

use App\Filament\Resources\Product\CustomPropertiesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomProperties extends EditRecord
{
    protected static string $resource = CustomPropertiesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
