<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;


class RestaurantType extends Model
{
    
    public $table = "restaurant_type";
    //
    public function restaurants(){
        return $this->hasMany(Restaurant::class, 'ID_restaurant_type');
    }
}
