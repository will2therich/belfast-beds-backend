<?php

namespace App\Models\PivotTables;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class ProductCustomProperties extends Model
{

    protected $guarded = [];

    public function migration(Blueprint $table)
    {
        $table->integer('product_id')->index();
        $table->integer('custom_property_option_id')->index();
    }
}
