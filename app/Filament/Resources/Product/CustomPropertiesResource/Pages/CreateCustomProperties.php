<?php

namespace App\Filament\Resources\Product\CustomPropertiesResource\Pages;

use App\Filament\Resources\Product\CustomPropertiesResource;
use App\Helper\StringHelper;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomProperties extends CreateRecord
{
    protected static string $resource = CustomPropertiesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = 'prop_' . StringHelper::generateSlug($data['name']) . '_' . time();
        return parent::mutateFormDataBeforeCreate($data);
    }
}
