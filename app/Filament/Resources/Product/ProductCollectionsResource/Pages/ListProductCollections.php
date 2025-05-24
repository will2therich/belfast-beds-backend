<?php

namespace App\Filament\Resources\Product\ProductCollectionsResource\Pages;

use App\Filament\Resources\Product\ProductCollectionsResource;use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductCollections extends ListRecords
{
    protected static string $resource = ProductCollectionsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
