<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class Pages extends Model
{

    protected $guarded = [];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->string('slug')->nullable()->index();
        $table->longText('content')->nullable();
        $table->boolean('enabled')->default(true);
        $table->boolean('show_in_footer')->default(false);
        $table->timestamps();
    }
}
