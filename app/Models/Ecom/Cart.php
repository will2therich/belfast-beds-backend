<?php

namespace App\Models\Ecom;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Schema\Blueprint;

class Cart extends Model
{

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('uuid')->index();
        $table->float('value');
        $table->boolean('ordered')->default(false);
        $table->timestamps();
    }

    /**
     * Get the line items associated with the cart.
     *
     * @return BelongsToMany
     */
    public function lineItems(): BelongsToMany
    {
        return $this->belongsToMany(LineItem::class)->withTimestamps();
    }

}
