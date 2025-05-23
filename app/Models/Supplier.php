<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class Supplier extends Model
{


    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->string('slug')->nullable();
        $table->boolean('show_in_menu')->default(true);
        $table->string('rs_id')->index();
        $table->timestamps();
    }
}
