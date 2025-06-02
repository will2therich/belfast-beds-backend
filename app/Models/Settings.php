<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

class Settings extends Model {

    protected $guarded = [];

    protected $casts = [];

    public function migration(Blueprint $table) {
        $table->id();
        $table->string('key')->index();
        $table->longText('value');
        $table->timestamp('created_at')->nullable();
        $table->timestamp('updated_at')->nullable();
    }

}
