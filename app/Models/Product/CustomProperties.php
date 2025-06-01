<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class CustomProperties extends Model
{

    protected $guarded = [];
    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->boolean('display_in_filters')->default(false);
        $table->boolean('display_on_product_page')->default(true);
        $table->boolean('display_in_nav_menu')->default(false);
        $table->longText('nav_menu_categories')->nullable();
        $table->timestamps();
    }

    public function options()
    {
        return $this->hasMany(CustomPropertiesOptions::class, 'custom_property_id', 'id');
    }
}
