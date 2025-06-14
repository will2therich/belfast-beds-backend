<?php

namespace App\Filament\Resources\Ecom\AdditionalServiceResource\Pages;

use App\Filament\Resources\Ecom\AdditionalServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAdditionalService extends EditRecord
{
    protected static string $resource = AdditionalServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
