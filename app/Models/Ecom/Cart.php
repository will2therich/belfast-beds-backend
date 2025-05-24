<?php

namespace App\Models\Ecom;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Schema\Blueprint;

class Cart extends Model
{

    protected $guarded = [];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('uuid')->index();
        $table->foreignId('customer_id')->nullable()->index();
        $table->boolean('ordered')->default(false);
        $table->string('email')->nullable();
        $table->string('full_name')->nullable();
        $table->string('telephone')->nullable();
        $table->foreignId('shipping_address_id')->nullable();
        $table->foreignId('billing_address_id')->nullable();
        $table->float('value');
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

    public function shippingAddress(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'shipping_address_id');
    }

    public function billingAddress(): HasOne
    {
        return $this->hasOne(Address::class, 'id', 'billing_address_id');
    }

}
