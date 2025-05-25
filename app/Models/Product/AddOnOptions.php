<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class AddOnOptions extends Model
{


    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name')->index();
        $table->integer('add_on_id')->index();
        $table->string('rs_id')->index();
        $table->float('price')->nullable();
        $table->string('image')->nullable();
        $table->timestamps();
    }

    public function addOn()
    {
        return $this->belongsTo(AddOn::class);
    }
}
