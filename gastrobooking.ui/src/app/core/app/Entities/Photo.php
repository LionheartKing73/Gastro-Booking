<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    public $table = "photo";

    protected $fillable = [
        "item_name"
    ];

    public function restaurant(){
        return $this->belongsTo(Restaurant::class, 'item_id');
    }

    public function meal(){
        return $this->belongsTo(Meal::class, 'item_id');
    }
}
