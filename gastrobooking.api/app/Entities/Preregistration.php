<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Preregistration extends Model
{
    protected $fillable = [
        "user_id",
        "password"
    ];

    public $timestamps = false;
}
