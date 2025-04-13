<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class PriceGroup extends Model
{


    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->string('rs_id')->index();
        $table->timestamps();
    }
}
