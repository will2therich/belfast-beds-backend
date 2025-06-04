<?php

namespace App\Models\Product;

use App\Models\PivotTables\ProductPriceGroup;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class Product extends Model
{


    protected $casts = [
        'photos' => 'array',
        'sections' => 'array'
    ];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->string('rs_id')->index();
        $table->boolean('enabled')->default(true);
        $table->string('slug')->nullable()->unique();
        $table->longText('photos')->nullable();
        $table->string('brand')->nullable();
        $table->float('starting_price')->nullable();
        $table->longText('sections')->nullable();
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

    /**
     * The categories that a product belongs to.
     */
    public function addons()
    {
        return $this->belongsToMany(
            AddOnOptions::class,
            'product_add_ons',
            'rs_product_id',
            'rs_addon_option_id',
            'rs_id',
            'rs_id'
        );
    }

    /**
     * The categories that a product belongs to.
     */
    public function customProperties()
    {
        return $this->belongsToMany(
            CustomPropertiesOptions::class,
            'product_custom_properties',
            'product_id',
            'custom_property_option_id',
            'id',
            'id'
        );
    }

    public function priceOptions()
    {
        return $this->hasMany(ProductPriceGroup::class, 'rs_product_id', 'rs_id');
    }

    public function brand()
    {
        return $this->belongsTo(Supplier::class, 'brand', 'id');
    }
}
