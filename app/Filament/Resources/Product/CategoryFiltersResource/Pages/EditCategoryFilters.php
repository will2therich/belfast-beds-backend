<?php

namespace App\Filament\Resources\Product\CategoryFiltersResource\Pages;

use App\Filament\Resources\Product\CategoryFiltersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoryFilters extends EditRecord
{
    protected static string $resource = CategoryFiltersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
