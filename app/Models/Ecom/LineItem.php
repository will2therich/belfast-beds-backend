<?php

namespace App\Models\Ecom;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Schema\Blueprint;

class LineItem extends Model
{

    protected $casts = [
        'options' => 'array',
        'selections' => 'array'
    ];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('item_name')->index();
        $table->integer('product_id')->index();
        $table->string('slug')->nullable();
        $table->longText('options');
        $table->float('price');
        $table->longText('selections')->nullable();
        $table->integer('quantity')->default(1);
        $table->timestamps();
    }

    /**
     * Get the carts that contain this line item.
     *
     * @return BelongsToMany
     */
    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class)->withTimestamps();
    }

}
