<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    public $table = "setting";

    public $primaryKey = "ID";

    // public function menu_groups(){
    //     return $this->hasMany(MenuGroup::class, "ID_menu_type");
    // }
}
