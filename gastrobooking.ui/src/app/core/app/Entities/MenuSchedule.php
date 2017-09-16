<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class MenuSchedule extends Model
{
    public $table = "menu_schedule";

    public $timestamps = false;

    public $primaryKey = "ID";

    public function menu_list(){
        return $this->belongsTo(MenuList::class, "ID_menu_list");
    }
}
