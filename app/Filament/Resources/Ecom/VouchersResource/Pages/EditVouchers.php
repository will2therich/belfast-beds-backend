<?php

namespace App\Filament\Resources\Ecom\VouchersResource\Pages;

use App\Filament\Resources\Ecom\VouchersResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVouchers extends EditRecord
{
    protected static string $resource = VouchersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
