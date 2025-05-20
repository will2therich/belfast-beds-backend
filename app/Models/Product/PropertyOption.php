<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class PropertyOption extends Model
{

    protected $casts = [
        'photos' => 'array',
    ];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->integer('property_id');
        $table->string('rs_id')->index();
        $table->longText('photos')->nullable();
        $table->timestamps();
    }

    /**
     * The categories that a product belongs to.
     */
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'product_properties',
            'rs_property_option_id',
            'product_id' ,
            'rs_id',
            'rs_id'
        );
    }

    public function property()
    {
        return $this->belongsTo(Properties::class, 'property_id', 'id');
    }
}
