<?php
/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 12:06 PM
 */

namespace App\Repositories;


use App\Entities\Client;
use App\Entities\ClientGroup;
use App\Entities\MenuList;
use App\Entities\MenuSchedule;
use App\Entities\Order;
use App\Entities\OrderDetail;
use App\Entities\Restaurant;
use App\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Geotools;
use Sofa\Eloquence\Eloquence;


class MenuListRepository
{
    use Eloquence;
    protected $userRepository;
    protected $clientGroupRepository;
    protected $perPage = 5;
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
    
    public function __construct(UserRepository $userRepository, ClientGroupRepository $clientGroupRepository)
    {
        $this->userRepository = $userRepository;
        $this->clientGroupRepository = $clientGroupRepository;
    }

    public function all($request){
        $current_position = $request->currentPosition;
        if ($request->has("distance"))
            $this->MAX_DISTANCE = $request->distance;

        $menu_list_builder = MenuList::filterByActive($request)
            ->searchMeal($request)
            ->filterByPrice($request)
            ->filterByGarden($request)
            ->filterByIsDayMenu($request)
            ->filterByDelivery($request)
            ->filterByRestaurantType($request)
            ->filterByKitchenType($request)
        ;

        $menu_list_restaurant_builder = $menu_list_builder->join("restaurant", "menu_list.ID_restaurant", "=", "restaurant.id");
        $menu_lists_with_restaurant = $menu_list_restaurant_builder
            ->select("menu_list.*", "latitude", "longitude", "status")->where("status", "A")->get();

        $filtered = $menu_lists_with_restaurant->filter(function($item) use ($current_position, $request) {
            $time_bool = true;
            if ($request->filter_by_date ){
                $time_bool = false;
                $time = $request->time;
                $day = $request->date;
                if($time && ($day || $day === 0 || $day === "0")){
                    $day = (int)$day;
                    $restOpeningHours = $item->restaurant ? $item->restaurant->openingHours : false;
                    $time_bool = $this->isRestaurantOpen($restOpeningHours, $day, $time);
                    if ($time_bool){
                        $time_bool = false;
                        if ($item->is_day_menu == $day || $item->is_day_menu == 0){
                            if (strtotime($item->time_from) <= strtotime($time) &&
                                strtotime($item->time_to) >= strtotime($time)){
                                $time_bool = true;
                            }
                        }
                        else if ($item->menu_schedule){
                            $time_bool = $this->isMenuScheduleValid($item->menu_schedule, $day, $time);
                        }
                        if ($time_bool && $request->has("dateObject")){
                            $current_time = Carbon::now("Europe/Prague");
                            $serve_at = new Carbon($request->dateObject);
                            $time_carbon = new Carbon($request->time);
                            $serve_at->setTime($time_carbon->hour, $time_carbon->minute);
                            $init_time = new Carbon("0001-01-01 00:00:00");
                            $book_to = new Carbon($item->book_to);
                            $book_from = $item->book_from;
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
                    }
                }
            }

            if ($time_bool){
                $distance = $this->distance($current_position["latitude"], $current_position["longitude"], $item->latitude, $item->longitude);
                $item->distance = $distance;
                return $distance < $this->MAX_DISTANCE;
            }
            return false;
        });

        $filtered = $filtered->sortBy('distance');

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $pagedData = $filtered->slice(($currentPage - 1) * $this->perPage, $this->perPage)->all();

        return new LengthAwarePaginator($pagedData, count($filtered), $this->perPage);

    }
    private function getDiffInTime(Carbon $time1, Carbon $time2)
    {
        return strtotime($time1->toDateTimeString()) - strtotime($time2->toDateTimeString());
    }

    public function distance($start_lat, $start_long, $end_lat, $end_long){
        $geotools = new Geotools();
        $coordA   = new Coordinate([$start_lat, $start_long]);
        $coordB   = new Coordinate([$end_lat, $end_long]);
        $distance = $geotools->distance()->setFrom($coordA)->setTo($coordB);
        return $distance->in('km')->flat();
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

    public function isOrdered($menuList){
        $user = app('Dingo\Api\Auth\Auth')->user();
        if ($user && $user->client){
            $client = $user->client;
            $sum = OrderDetail::where(["ID_client" => $client->ID, "ID_menu_list" => $menuList->ID, "status" => 5])
                ->whereHas("order", function($query) use ($client){
                return $query->where("ID_client", $client->ID);
            })->sum('x_number');
            return $sum;
        }
    }

    public function getTasted($request)
    {
        $menu_lists = MenuList::filterByActive($request)->get();
        $filtered = $menu_lists->filter(function($item){
            $item->orders_detail = $this->ordersBy7day($item->orders_detail);
            $item->booking_total = $item->orders_detail->count();
            return $item->booking_total > 0;
        });

        $filtered = $filtered->sortBy("booking_total");
        return $filtered->take(10);

    }

    public function getPromotionsNearby($request, $current_position)
    {
        $menu_lists = MenuList::filterByActive($request)->get();
        $filtered = $menu_lists->filter(function($item) use ($current_position){
            $item->orders_detail = $this->ordersBy7day($item->orders_detail);
            $item->booking_total = $item->orders_detail->count();
            if ($item->restaurant){
                $distance = $this->distance($current_position["latitude"], $current_position["longitude"], $item->restaurant->latitude, $item->restaurant->longitude);
                $item->distance = $distance;
                return $item->booking_total > 0;
            }
        });

        $filtered = $filtered->sortBy('distance')->unique('ID_restaurant');
        return $filtered->take(10);
        
    }
    
    public function ordersBy7day($orders_detail)
    {
        $orders_detail = $orders_detail->filter(function($item){
            $today = Carbon::now("Europe/Prague");
            $serve_at = new Carbon($item->serve_at);
            $item->date_difference = $this->getDiffInTime($serve_at, $today) / (3600 * 24);
            return $item->date_difference <= 7;
        });
        return $orders_detail;
    }

    public function getMenuSubGroup($menuList)
    {
        if ($menuList->menu_subgroup)
            return $menuList->menu_subgroup;
    }

    public function getMenuGroup($menuList)
    {
        if ($menuList->menu_subgroup && $menuList->menu_subgroup->menu_group)
            return $menuList->menu_subgroup->menu_group;
    }

    public function getMenuType($menuList)
    {
        if ($menuList->menu_subgroup && $menuList->menu_subgroup->menu_group && $menuList->menu_subgroup->menu_group->menu_type)
            return $menuList->menu_subgroup->menu_group->menu_type;

    }

    public function transformSideDishesToOrderItems($sideDishes){
        $sideDishOrder = [];
        foreach ($sideDishes as $sideDish) {
            $order = new OrderDetail();
            $order->menu_list = ['data' => $sideDish];
            $order->hasSideDishes = false;
            $order->isSideDish = false;
            $order->side_dish = 0;
            $sideDishOrder[] = $order;
        }

        return $sideDishOrder;
    }


    
    



}