<?php

namespace App\Models\Ecom;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class AdditionalService extends Model
{

    protected $guarded = [];

    protected $casts = [
        'category_ids' => 'array'
    ];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->longText('category_ids');
        $table->string('name');
        $table->float('price');
        $table->longText('description')->nullable();
        $table->timestamps();
    }
}
