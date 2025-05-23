<?php

namespace App\Filament\Resources\Product\CategoryFiltersResource\Pages;

use App\Filament\Resources\Product\CategoryFiltersResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryFilters extends ListRecords
{
    protected static string $resource = CategoryFiltersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
