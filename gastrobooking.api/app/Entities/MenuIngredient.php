<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class MenuIngredient extends Model
{
    public $table = "menu_ingredient";

    public $timestamps = false;

    public $primaryKey = "ID";
}
