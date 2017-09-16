<?php

namespace App\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Sofa\Eloquence\Eloquence;

class MenuList extends Model
{
    use Eloquence;
    public $table = "menu_list";
    public $primaryKey = "ID";
    public $days = array(
        "-1"=> "Scheduled",
        "0" =>"Cooked Every day",
        "1"=> "Monday",
        "2"=> "Tuesday",
        "3"=> "Wednesday",
        "4"=> "Thursday",
        "5"=> "Friday",
        "6"=> "Saturday",
        "7"=> "Sunday"
    );

    public $timestamps = false;

    public function menu_subgroup(){
        return $this->belongsTo(MenuSubGroup::class, "ID_menu_subgroup");
    }

    public function menu_schedule(){
        return $this->hasOne(MenuSchedule::class, "ID_menu_list");
    }

    public function restaurant(){
        return $this->belongsTo(Restaurant::class, "ID_restaurant");
    }

    public function kitchenType(){
        return $this->belongsTo(KitchenType::class, "ID_kitchen_type");
    }

    public function orders_detail(){
        return $this->hasMany(OrderDetail::class, "ID_menu_list");
    }

    public function recommended_side_dishes(){
        return $this->belongsToMany(MenuList::class, "menu_side_dish", "ID_menu_list", "ID_menu_list_side_dish")
            ->withPivot('sd_order')->orderBy('pivot_sd_order');
    }
    public function scopeFilterByPrice($query, Request $request){
        if($request->has('price')){
            $price = $request->price;
            if(!empty($price["min"])){
                $query->where('price','>=',$price["min"]);
            }
            if(!empty($price["max"])){
                $query->where('price','<=',$price["max"]);
            }
        }
    }

    public function scopeSearchMeal( $query, Request $request){
        if ($request->has('search')){
            $search_key = $request->search;
            return $query->search($search_key, ["name", "menu_subgroup.name", "menu_subgroup.menu_group.name"]);

        }
    }

    public function scopeFilterByActive($query, Request $request){
        return $query->where("isActive", 1);
    }

    public function scopeFilterByIsDayMenu($query, Request $request)
    {
        if ($request->isDayMenu){
            return $query->where("is_day_menu", "<>", 0);
        }

    }

    public function scopeFilterByGarden($query, Request $request)
    {
        if ($request->hasGarden){
            $query->whereHas("restaurant", function($query){
                $query->whereHas("photos", function($query){
                   return $query->where("item_type", "garden");
                });
            });
        }
    }

    public function scopeFilterByDelivery($query, Request $request)
    {
        if ($request->delivery){
            return $query->where("delivered", 1);
        }
    }

    public function scopeFilterByRestaurantType($query, Request $request)
    {
        if ($request->restaurantType){
            $type = $request->restaurantType;
            return $query->whereHas("restaurant", function($query) use ($type){
                $query->whereHas("restaurantType", function($query) use ($type) {
                    return $query->where("name", $type);
                });
            });
        }
    }

    public function scopeFilterByKitchenType($query, Request $request)
    {
        if ($request->cuisineType){
            $type = $request->cuisineType;
            return $query->whereHas("kitchenType", function($query) use ($type){
                $query->where("name", $type);
            });
        }
    }


}