<?php

namespace App\Entities;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Sofa\Eloquence\Eloquence;

class Restaurant extends Model
{
    use Eloquence;

    public $table = "restaurant";

    protected $fillable = [
        "name", "description", "location"
    ];

    public function photos(){
        return $this->hasMany(Photo::class, 'item_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'ID_user');
    }

    public function openingHours(){
        return $this->hasMany(RestaurantOpen::class, 'ID_restaurant');
    }
    public function type(){
        return $this->belongsTo(RestaurantType::class, 'ID_restaurant_type');
    }

    public function menu_lists(){
        return $this->hasMany(MenuList::class, 'ID_restaurant');
    }

    public function scopeSearchRestaurant($query, Request $request){
        if ($request->has('search')){
            $search_key = $request->get('search');
            return $query->search($search_key, ["name"]);
        }
    }

    public function scopeFilterByDistance( $query, Request $request){
        if ($request->has("currentPosition")){
            $current_position = $request->currentPosition;
            $lower_lat = $current_position["latitude"] + .09;
            $upper_lat = $current_position["latitude"] - .09;
            $lower_long = $current_position["longitude"] + .09;
            $upper_long = $current_position["longitude"] - .09;
            return $query->whereBetween("latitude", [$lower_lat, $upper_lat])
                ->whereBetween("longitude", [$lower_long, $upper_long]);
        }
    }

    public function scopeFilterByGarden($query, Request $request)
    {
        if ($request->hasGarden){
            $query->whereHas("photos", function($query){
                return $query->where("item_type", "garden");
            });
        }
    }

    public function scopeFilterByDelivery()
    {

    }
}
