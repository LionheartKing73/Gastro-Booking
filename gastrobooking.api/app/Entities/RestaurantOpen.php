<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class RestaurantOpen extends Model
{
    public $table = "restaurant_open";

    public function restaurant(){
        return $this->belongsTo(Restaurant::class, 'ID_restaurant');
    }
}
