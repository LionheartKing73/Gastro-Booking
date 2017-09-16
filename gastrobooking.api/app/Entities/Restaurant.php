<?php

namespace App\Entities;

use App\User;
use Carbon\Carbon;
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

    public function invoices(){
        return $this->hasMany(Invoice::class, 'ID_restaurant');
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

    public function restaurantType(){
        return $this->belongsTo(RestaurantType::class, 'ID_restaurant_type');
    }

    public function district(){
        return $this->belongsTo(District::class, 'ID_district', 'ID');
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

    public function scopeFilterByDelivery($query, Request $request)
    {
        if ($request->delivery){
            $query->whereHas("menu_lists", function($query){
               return $query->where("delivered", 1);
            });
        }
    }

    public function scopeFilterByIsDayMenu($query, Request $request)
    {
        if ($request->isDayMenu){
            $query->whereHas("menu_lists", function($query){
                return $query->where("is_day_menu", '<>', 0);
            });
        }
    }

    public function scopeFilterByRestaurantType($query, Request $request)
    {
        if ($request->restaurantType){
            $type = $request->restaurantType;
            return $query->whereHas("restaurantType", function($query) use ($type) {
                    return $query->where("name", $type);
                });
        }
    }

    public function scopeFilterByKitchenType($query, Request $request)
    {
        if ($request->cuisineType){
            $type = $request->cuisineType;
            return $query->whereHas("menu_lists", function($query) use ($type){
                return $query->whereHas("kitchenType", function($query) use ($type){
                    $query->where("name", $type);
                });;
            });
        }
    }

    public function scopeFilterByStatus($query, Request $request){
        return $query->where("status", 'A');
    }

    public function scopeFilterByUserId($query, Request $request){
        return $query->where([["status", 'A'],["ID_user_dealer", $request->user_id]]);
    }
}
