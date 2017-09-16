<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class MenuGroup extends Model
{
    public $table = "menu_group";

    public $timestamps = false;

    public $primaryKey = "ID";

    public function menu_subgroups(){
        return $this->hasMany(MenuSubGroup::class, 'ID_menu_group');
    }

    public function menu_type(){
        return $this->belongsTo(MenuType::class, 'ID_menu_type');
    }
}
