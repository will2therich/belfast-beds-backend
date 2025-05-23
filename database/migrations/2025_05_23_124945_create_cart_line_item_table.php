<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create the pivot table for the many-to-many relationship
        // between 'carts' and 'line_items'.
        Schema::create('cart_line_item', function (Blueprint $table) {
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('line_item_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->primary(['cart_id', 'line_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the pivot table if the migration is rolled back.
        Schema::dropIfExists('cart_line_item');
    }
};
