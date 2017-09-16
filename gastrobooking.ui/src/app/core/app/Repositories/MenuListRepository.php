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
        ;

        $menu_list_restaurant_builder = $menu_list_builder->join("restaurant", "menu_list.ID_restaurant", "=", "restaurant.id");
        $menu_lists_with_restaurant = $menu_list_restaurant_builder
            ->select("menu_list.*", "latitude", "longitude")->get();

        $filtered = $menu_lists_with_restaurant->filter(function($item) use ($current_position, $request) {
            $time_bool = true;
            if ($request->filter_by_date ){
                $time_bool = false;
                $time = $request->time;
                $day = $request->date;
                if($time && ($day || $day === 0 || $day === "0")){
                    $day = (int)$day;
                    if ($day === 0 || $item->is_day_menu == 0){
                        $time_bool = true;
                    } else {
                        if ($item->is_day_menu == $day){
                            if (strtotime($item->time_from) <= strtotime($time) &&
                                strtotime($item->time_to) >= strtotime($time)){
                                $time_bool = true;
                            }
                        }
                        if ($item->menu_schedule && !$time_bool){
                            $datetime_from = new Carbon($item->menu_schedule->datetime_from);
                            $day_from = $datetime_from->dayOfWeek == 0 ? 7 : $datetime_from->dayOfWeek;
                            $datetime_to = new Carbon($item->menu_schedule->datetime_to);
                            $day_to = $datetime_to->dayOfWeek == 0 ? 7 : $datetime_to->dayOfWeek;
                            if ($day_from <= $day && $day_to >= $day){
                                $openingHours = $item->restaurant->openingHours;
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

        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $pagedData = $filtered->slice(($currentPage - 1) * $this->perPage, $this->perPage)->all();

        return new LengthAwarePaginator($pagedData, count($filtered), $this->perPage);

    }

    public function distance($start_lat, $start_long, $end_lat, $end_long){
        $geotools = new Geotools();
        $coordA   = new Coordinate([$start_lat, $start_long]);
        $coordB   = new Coordinate([$end_lat, $end_long]);
        $distance = $geotools->distance()->setFrom($coordA)->setTo($coordB);
        return $distance->in('km')->flat();
    }

    public function isOrdered($menuList){
        $user = app('Dingo\Api\Auth\Auth')->user();
        if ($user){
            $client = $user->client;
            $orders_detail = OrderDetail::where(["ID_client" => $client->ID, "ID_menu_list" => $menuList->ID, "status" => 5])->first();
            return $orders_detail;
        }
    }

    public function getTasted()
    {
        $menu_lists = MenuList::get();
        $filtered = $menu_lists->filter(function($item){
            $item->orders_detail = $this->ordersBy7day($item->orders_detail);
            $item->booking_total = $item->orders_detail->count();
            return $item->booking_total > 0;
        });

        $filtered = $filtered->sortBy("booking_total");
        return $filtered->take(5);

    }

    public function getPromotionsNearby($current_position)
    {
        $menu_lists = MenuList::get();
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
        return $filtered->take(5);
        
    }
    
    public function ordersBy7day($orders_detail)
    {
        $orders_detail = $orders_detail->filter(function($item){
            $today = Carbon::today();
            $serve_at = new Carbon($item->serve_at);
            $item->date_difference = $today->diffInDays($serve_at);
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


    
    



}