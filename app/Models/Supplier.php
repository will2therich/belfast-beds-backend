<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class Supplier extends Model
{

    protected $guarded = [];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->string('slug')->nullable()->index();
        $table->boolean('show_in_menu')->default(true);
        $table->longText('description')->nullable();
        $table->string('image')->nullable();
        $table->string('banner_image')->nullable();
        $table->integer('lead_time')->nullable();
        $table->string('rs_id')->index();
        $table->timestamps();
    }
}
