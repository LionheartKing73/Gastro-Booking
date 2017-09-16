<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class MenuType extends Model
{
    public $table = "menu_type";

    public $timestamps = false;

    public $primaryKey = "ID";

    public function menu_groups(){
        return $this->hasMany(MenuGroup::class, "ID_menu_type");
    }
}
