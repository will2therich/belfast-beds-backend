<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class StockItem extends Model
{

    protected $guarded = [];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('item_id');
        $table->string('item_hash');
        $table->string('qty');
        $table->timestamps();
    }

}
