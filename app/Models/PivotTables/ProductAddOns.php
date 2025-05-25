<?php

namespace App\Models\PivotTables;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class ProductAddOns extends Model
{


    public function migration(Blueprint $table)
    {
        $table->integer('rs_product_id')->index();
        $table->integer('rs_addon_option_id')->index();
    }
}
