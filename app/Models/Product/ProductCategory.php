<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class ProductCategory extends Model
{

    protected $guarded = [];

    protected $casts = [
        'featured_sections' => 'array',
        'upsell_categories' => 'array'
    ];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('name');
        $table->string('image')->nullable();
        $table->string('slug')->nullable();
        $table->string('rs_id')->index();
        $table->integer('parent_category_id')->nullable();
        $table->string('link')->nullable();
        $table->boolean('enabled')->default(true);
        $table->integer('order')->default(1);
        $table->longText('featured_sections')->nullable();
        $table->longText('upsell_categories')->nullable();
        $table->timestamps();
    }

    /**
     * The products that belong to this category.
     */
    public function products()
    {
        return $this->belongsToMany(
            Product::class,
            'category_products',      // Pivot table name
            'rs_category_id',        // Foreign key on the pivot table for Category
            'rs_product_id',          // Foreign key on the pivot table for Product,
            'rs_id',
            'rs_id'
        );
    }

    /**
     * Get the parent category of the current category.
     */
    public function parentCategory()
    {
        return $this->belongsTo(self::class, 'parent_category_id');
    }

    /**
     * Get the child categories of the current category.
     */
    public function childCategories()
    {
        return $this->hasMany(self::class, 'parent_category_id');
    }

    public function filters()
    {
        return CategoryFilters::query()->whereJsonContains('categories', '' . $this->id);
    }
}
