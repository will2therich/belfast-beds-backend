<?php

namespace App\Models\Ecom;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class Address extends Model
{

    protected $guarded = [];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('cart_id')->index();
        $table->string('order_id')->nullable()->index();
        $table->string('address_line_one')->index();
        $table->string('address_line_two')->nullable();
        $table->string('town_city');
        $table->string('county');
        $table->string('postcode');
        $table->string('country');
        $table->timestamps();
    }
}
