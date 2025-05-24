<?php

namespace App\Filament\Resources\Product\ProductCollectionsResource\Pages;

use App\Filament\Resources\Product\ProductCollectionsResource;use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductCollections extends EditRecord
{
    protected static string $resource = ProductCollectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
