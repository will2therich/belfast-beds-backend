<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class Product extends Model
{


    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->string('rs_id')->index();
        $table->boolean('enabled')->default(true);
        $table->timestamps();
    }

    /**
     * The categories that a product belongs to.
     */
    public function categories()
    {
        return $this->belongsToMany(
            ProductCategory::class,
            'category_products',
            'rs_product_id',
            'rs_category_id' ,
        'rs_id',
        'rs_id'
        );
    }
}
