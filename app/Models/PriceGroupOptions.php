<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class PriceGroupOptions extends Model
{


    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name')->index();
        $table->integer('price_group_id')->index();
        $table->string('rs_id')->index();
        $table->timestamps();
    }

    public function priceGroup()
    {
        return $this->belongsTo(PriceGroup::class);
    }
}
