<?php

namespace App\Filament\Resources\Core\PagesResource\Pages;

use App\Filament\Resources\Core\PagesResource;
use App\Helper\StringHelper;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePages extends CreateRecord
{
    protected static string $resource = PagesResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['slug'] = StringHelper::generateSlug($data['name']);
        return parent::mutateFormDataBeforeCreate($data);
    }
}
