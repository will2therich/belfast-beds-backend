<?php

namespace App\Models\Ecom;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Schema\Blueprint;

class Transactions extends Model
{

    protected $guarded = [];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->foreignId('order_id')->nullable()->index();
        $table->string('transaction_id')->nullable();
        $table->float('value')->nullable();
        $table->timestamps();
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
