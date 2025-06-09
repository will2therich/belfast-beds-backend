<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class CustomPropertiesOptions extends Model
{

    protected $guarded = [];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->string('slug')->nullable();
        $table->string('icon')->nullable();
        $table->longText('description')->nullable();
        $table->foreignId('custom_property_id');
        $table->timestamps();
    }

    public function customProperty()
    {
        return $this->belongsTo(CustomProperties::class, 'custom_property_id', 'id');
    }

    /**
     * The categories that a product belongs to.
     */
    public function products()
    {
        return $this->belongsToMany(
            CustomPropertiesOptions::class,
            'product_custom_properties',
            'custom_property_option_id',
            'product_id',
            'id',
            'id'
        );
    }
}
