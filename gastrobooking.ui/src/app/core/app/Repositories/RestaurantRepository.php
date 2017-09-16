<?php
/**
 * Created by PhpStorm.
 * Restaurant: tOm_HydRa
 * Date: 9/10/16
 * Time: 12:06 PM
 */

namespace App\Repositories;

use App\Entities\MenuGroup;
use App\Entities\MenuList;
use App\Entities\MenuSubGroup;
use App\Entities\MenuType;
use App\Entities\MenuVisualOrder;
use App\Entities\Restaurant;
use App\Entities\RestaurantOpen;
use App\Entities\RestaurantType;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Geotools;
use Webpatser\Uuid\Uuid;


class RestaurantRepository
{
    protected $MAX_DISTANCE = 10;
    public $days = array(
        "-1"=> "Scheduled",
        "0" =>"Cooked Every day",
        "1"=> "monday",
        "2"=> "tuesday",
        "3"=> "wednesday",
        "4"=> "thursday",
        "5"=> "friday",
        "6"=> "saturday",
        "7"=> "sunday"
    );

    public $MENU_TYPE_LEVEL = 0;
    public $MENU_GROUP_LEVEL = 1;
    public $MENU_SUB_GROUP_LEVEL = 2;
    public $MENU_LIST_LEVEL = 3;

    public $DEFAULT_LEVEL = 1000;

    public function store($request, $user_id){
        if ($request->has("restaurant")){
            $this->save($request->restaurant, $user_id);
        }
    }




    public function find($restaurant_id){
        $restaurant = Restaurant::find($restaurant_id);
        return $restaurant;
    }

    public function all(Request $request, $n){
        if ($request->currentPosition){
            $currentPosition = $request->currentPosition;
            if ($request->has("distance"))
                $this->MAX_DISTANCE = $request->distance;

            $restaurants = Restaurant::searchRestaurant($request)->filterByGarden($request)->get();

            $filtered = $restaurants->filter(function($item) use ($currentPosition, $request){
                $time_bool = true;
                if ($request->filter_by_date){
                    $time_bool = false;
                    $time = $request->time;
                    $day = $request->date;
                    if($time && ($day || $day === 0 || $day === "0")){
                        $day = (int)$day;
                        $openingHours = $item->openingHours;
                        foreach ($openingHours as $openingHour) {
                            if ($this->days[$day] === $openingHour->date){
                                if ((strtotime($openingHour->m_starting_time) <= strtotime($time) &&
                                        strtotime($openingHour->m_ending_time) >= strtotime($time))
                                    || (strtotime($openingHour->a_starting_time) <= strtotime($time) &&
                                        strtotime($openingHour->a_ending_time) >= strtotime($time))){
                                    $time_bool = true;
                                }
                            }
                        }
                    }
                }
                if ($time_bool){
                    $distance = $this->distance($currentPosition["latitude"], $currentPosition["longitude"], $item->latitude, $item->longitude);
                    $item->distance = $distance;
                    return $distance < $this->MAX_DISTANCE;
                }

            });
            $filtered = $filtered->sortBy('distance');

            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            $pagedData = $filtered->slice(($currentPage - 1) * $n, $n)->all();

            return new LengthAwarePaginator($pagedData, count($filtered), $n);
        }
        return Restaurant::searchRestaurant($request)->paginate($n);


    }

    public function getRestaurantsNearby($current_position)
    {
        $restaurants = Restaurant::get();
        $restaurants = $restaurants->filter(function($item) use($current_position){
            $distance = $this->distance($current_position["latitude"], $current_position["longitude"], $item->latitude, $item->longitude);
            $item->distance = $distance;
            return $distance < $this->MAX_DISTANCE;
        });
        $restaurants = $restaurants->sortBy("distance");
        return $restaurants->take(2);


    }

    public function delete($restaurant_id){
        $restaurant = Restaurant::find($restaurant_id);
        $restaurant->delete();
        return $restaurant;
    }

    public function save($input, $user_id)
    {
        if (isset($input["id"])) {
            return $this->update($input);
        }
        $restaurant = new Restaurant();
        $restaurant->ID_user = $user_id;
        $restaurant->name = isset($input["name"]) ? $input["name"] : null;
        $restaurant->ID_restaurant_type = $input["restaurant_type"] ? $input["restaurant_type"] : null;
        $restaurant->email = $input["email"] ? $input["email"] : null;
        $restaurant->www = $input["www"] ? $input["www"] : null;
        $restaurant->phone = $input["phone"] ? $input["phone"] : null;
        $restaurant->street = $input["street"] ? $input["street"] : null;
        $restaurant->city = $input["city"] ? $input["city"] : null;
        $restaurant->post_code = $input["post_code"] ? $input["post_code"] : null;
        $restaurant->address_note = isset($input["address_note"]) ? $input["address_note"] : null;
        $restaurant->latitude = $input["latitude"] ? $input["latitude"] : null;
        $restaurant->longitude = $input["longitude"] ? $input["longitude"] : null;
        $restaurant->accept_payment = isset($input["accept_payment"]) ? $input["accept_payment"] : null;
        $restaurant->company_number = isset($input["company_number"]) ? $input["company_number"] : null;
        $restaurant->account_number = isset($input["account_number"]) ? $input["account_number"] : null;
        $restaurant->bank_code = isset($input["bank_code"]) ? $input["bank_code"] : null;
        $restaurant->company_tax_number = isset($input["company_tax_number"]) ? $input["company_tax_number"] : null;
        $restaurant->short_descr = isset($input["short_desc"]) ? $input["short_desc"] : null;
        $restaurant->long_descr = $input["long_desc"] ? $input["long_desc"] : null;
        $restaurant->save();
        return $restaurant;
    }

    public function update($input){
        $restaurant = Restaurant::find($input["id"]);
        $restaurant->ID_restaurant_type = isset($input["ID_restaurant_type"]) ? RestaurantType::where("name", $input["ID_restaurant_type"])->first()->id : $restaurant->ID_restaurant_type;
        $restaurant->email = isset($input["email"]) ? $input["email"] : $restaurant->email;
        $restaurant->www = isset($input["www"]) ? $input["www"] : $restaurant->www;
        $restaurant->name = isset($input["name"]) ? $input["name"] : $restaurant->name;
        $restaurant->phone = isset($input["phone"]) ? $input["phone"] : $restaurant->phone;
        $restaurant->street = isset($input["street"]) ? $input["street"] : $restaurant->street;
        $restaurant->city = isset($input["city"]) ? $input["city"] : $restaurant->city;
        $restaurant->post_code = isset($input["post_code"]) ? $input["post_code"] : $restaurant->post_code;
        $restaurant->address_note = isset($input["address_note"]) ? $input["address_note"] : $restaurant->address_note;
        $restaurant->latitude = isset($input["latitude"]) ? $input["latitude"] : $restaurant->latitude;
        $restaurant->longitude = isset($input["longitude"]) ? $input["longitude"] : $restaurant->longitude;
        $restaurant->accept_payment = isset($input["accept_payment"]) ? $input["accept_payment"] : $restaurant->accept_payment;
        $restaurant->company_number = isset($input["company_number"]) ? $input["company_number"] : $restaurant->company_number;
        $restaurant->account_number = isset($input["account_number"]) ? $input["account_number"] : $restaurant->account_number;
        $restaurant->bank_code = isset($input["bank_code"]) ? $input["bank_code"] : $restaurant->bank_code;
        $restaurant->company_tax_number = isset($input["company_tax_number"]) ? $input["company_tax_number"] : $restaurant->company_tax_number;
        $restaurant->short_descr = isset($input["short_descr"]) ? $input["short_descr"] : $restaurant->short_descr;
        $restaurant->long_descr = isset($input["long_descr"]) ? $input["long_descr"] : $restaurant->long_descr;
        $restaurant->save();
        return $restaurant;

    }

    public function getMenuLists($restaurantId){
        $restaurant = $this->find($restaurantId);
        return $restaurant->menu_lists;
    }

    public function getMenuTypes($restaurantId){
        $menu_types = MenuType::whereHas('menu_groups', function($query) use ($restaurantId){
            $query->whereHas('menu_subgroups', function($query) use ($restaurantId){
                $query->whereHas('menu_lists', function($query) use ($restaurantId){
                    $query->where('ID_restaurant', $restaurantId);
                });
            });
        })->get();
        $menu_types = $menu_types->filter(function($item) use ($restaurantId){
            $menu_visual_order = MenuVisualOrder::where(['level' => $this->MENU_TYPE_LEVEL, 'ID_item' => $item->ID, 'ID_restaurant' => $restaurantId])->first();
            if ($menu_visual_order){
                $item->new_cust_order = $menu_visual_order->cust_order;
            } else {
                $item->new_cust_order = $this->DEFAULT_LEVEL;
            }
            return true;
        });
        return $menu_types->sortBy('new_cust_order');
    }

    public function getMenuGroups($restaurantId, $menuTypeId)
    {
        $menu_groups = MenuGroup::where("ID_menu_type", $menuTypeId)
            ->whereHas('menu_subgroups', function($query) use($restaurantId){
                $query->whereHas('menu_lists', function($query) use ($restaurantId){
                    $query->where('ID_restaurant', $restaurantId);
                });
            })->get();
        $menu_groups = $menu_groups->filter(function($item) use ($restaurantId){
            $menu_visual_order = MenuVisualOrder::where(['level' => $this->MENU_GROUP_LEVEL, 'ID_item' => $item->ID, 'ID_restaurant' => $restaurantId])->first();
            if ($menu_visual_order){
                $item->new_cust_order = $menu_visual_order->cust_order;
            } else {
                $item->new_cust_order = $this->DEFAULT_LEVEL;
            }
            return true;
        });
        return $menu_groups->sortBy('new_cust_order');
    }

    public function getMenuSubGroups($restaurantId, $menuGroupId)
    {

        $menu_subgroups = MenuSubGroup::where("ID_menu_group", $menuGroupId)
            ->whereHas('menu_lists', function($query) use ($restaurantId){
                $query->where('ID_restaurant', $restaurantId);
            })->get();
        $menu_subgroups = $menu_subgroups->filter(function($item) use ($restaurantId){
            $menu_visual_order = MenuVisualOrder::where(['level' => $this->MENU_SUB_GROUP_LEVEL, 'ID_item' => $item->ID, 'ID_restaurant' => $restaurantId])->first();
            if ($menu_visual_order){
                $item->new_cust_order = $menu_visual_order->cust_order;
            } else {
                $item->new_cust_order = $this->DEFAULT_LEVEL;
            }
            return true;
        });

        return $menu_subgroups->sortBy('new_cust_order');
    }

    public function getMenuListsHelper($restaurantId, $menuSubGroupId)
    {
        $menu_lists = MenuList::where(
            ["ID_restaurant" => $restaurantId,
                "ID_menu_subgroup" => $menuSubGroupId]
        );
        return $menu_lists->get()->sortBy('cust_order');
    }

    public function distance($start_lat, $start_long, $end_lat, $end_long){
        $geotools = new Geotools();
        $coordA   = new Coordinate([$start_lat, $start_long]);
        $coordB   = new Coordinate([$end_lat, $end_long]);
        $distance = $geotools->distance()->setFrom($coordA)->setTo($coordB);
        return $distance->in('km')->flat();
    }

    public function getLogo($photos){
        $exterior = [];
        $interior = [];
        $garden = [];
        foreach ($photos as $photo) {
            if ($photo->item_type == "exterior"){
                $exterior[] = $photo->upload_directory . $photo->minified_image_name;
            } else if ($photo->item_type == "interior"){
                $interior[] = $photo->upload_directory . $photo->minified_image_name;
            } else if ($photo->item_type == "garden"){
                $garden[] = $photo->upload_directory . $photo->minified_image_name;
            }
        }
        if (count($exterior)){
            return $exterior[0];
        } else if (count($garden)){
            return $garden[0];
        } else if (count($interior)){
            return $interior[0];
        }
        return null;
    }



}