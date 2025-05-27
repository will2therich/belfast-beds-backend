<?php

namespace App\Filament\Resources\Ecom\OrderResource\Pages;

use App\Filament\Resources\Ecom\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
