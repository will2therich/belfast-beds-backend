<?php

namespace App\Models\Ecom;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Customer extends Authenticatable implements JWTSubject
{

    protected $guarded = [];

    public function migration(Blueprint $table)
    {
        $table->id();
        $table->string('title')->nullable();
        $table->string('full_name');
        $table->string('password')->nullable();
        $table->string('telephone')->nullable();
        $table->string('email');
        $table->boolean('active')->default(true);
        $table->timestamps();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

}
