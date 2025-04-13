<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class ProductProperties extends Model
{


    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('rs_id')->index();
        $table->integer('rs_product_id')->index();
        $table->integer('rs_property_id')->index();
        $table->integer('rs_property_option_id')->index();
        $table->timestamps();
    }
}
