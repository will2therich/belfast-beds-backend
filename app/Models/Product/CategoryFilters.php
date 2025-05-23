<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class CategoryFilters extends Model
{

    protected $casts = [
        'options' => 'array',
        'categories' => 'array'
    ];

    protected $guarded = [];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->json('categories')->nullable();
        $table->string('option_name')->nullable();
        $table->longText('options')->nullable();
        $table->timestamps();
    }

}
