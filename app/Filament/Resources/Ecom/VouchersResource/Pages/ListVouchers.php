<?php

namespace App\Filament\Resources\Ecom\VouchersResource\Pages;

use App\Filament\Resources\Ecom\VouchersResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVouchers extends ListRecords
{
    protected static string $resource = VouchersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
