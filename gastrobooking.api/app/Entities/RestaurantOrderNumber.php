<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class RestaurantOrderNumber extends Model
{
    public $table = "restaurant_order_number";

    public $primaryKey = "ID";

    public $timestamps = false;


}
