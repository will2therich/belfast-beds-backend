<?php

namespace App\Models\Product;

use App\Models\PivotTables\ProductPriceGroup;
use App\Models\PropertyOption;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class Product extends Model
{


    protected $casts = [
        'pictures' => 'array'
    ];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->string('rs_id')->index();
        $table->boolean('enabled')->default(true);
        $table->longText('photos')->nullable();
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


    /**
     * The categories that a product belongs to.
     */
    public function options()
    {
        return $this->belongsToMany(
            PropertyOption::class,
            'product_properties',
            'rs_product_id',
            'rs_property_option_id',
            'rs_id',
            'rs_id'
        );
    }

    public function priceOptions()
    {
        return $this->hasMany(ProductPriceGroup::class, 'rs_product_id', 'rs_id');
    }
}
