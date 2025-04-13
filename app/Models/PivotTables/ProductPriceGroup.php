<?php

namespace App\Models\PivotTables;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class ProductPriceGroup extends Model
{


    public function migration(Blueprint $table)
    {
        $table->integer('rs_product_id')->index();
        $table->integer('rs_price_group_option_id')->index();
        $table->integer('price_group_id')->index();
        $table->float('price')->nullable();
        $table->string('name')->nullable();
        $table->timestamps();
    }
}
