<?php

namespace App\Models\Ecom;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Schema\Blueprint;

class ProductCollections extends Model
{

    protected $guarded = [];

    protected $casts = [
        'suppliers' => 'array',
        'products' => 'array'
    ];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->string('image')->nullable();
        $table->longText('description')->nullable();
        $table->string('slug')->index();
        $table->json('suppliers');
        $table->json('products');
        $table->timestamps();
    }


}
