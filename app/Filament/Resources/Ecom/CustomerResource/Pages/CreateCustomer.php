<?php

namespace App\Filament\Resources\Ecom\CustomerResource\Pages;

use App\Filament\Resources\Ecom\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
