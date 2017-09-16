<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class MenuSubGroup extends Model
{
    public $table = "menu_subgroup";

    public $timestamps = false;

    public $primaryKey = "ID";

    public function menu_lists(){
        return $this->hasMany(MenuList::class, 'ID_menu_subgroup');
    }

    public function menu_group(){
        return $this->belongsTo(MenuGroup::class, 'ID_menu_group');
    }
}
