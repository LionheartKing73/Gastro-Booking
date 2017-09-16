<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    public $table = "meal";

    public $primaryKey = "ID";

    protected $fillable = [
        "name", "description"
    ];

    public function photos(){
        return $this->hasMany(Photo::class, 'item_id');
    }
}
