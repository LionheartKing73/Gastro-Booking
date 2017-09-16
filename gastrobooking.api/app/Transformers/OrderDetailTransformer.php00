<?php

namespace App\Transformers;

use App\Entities\MenuList;
use App\Entities\OrderDetail;
use App\Entities\Photo;
use App\Repositories\MenuListRepository;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class OrderDetailTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['menu_list', 'sideDish'];
    private $initialTime = "0001-01-01 00:00:00";
    public $menuListRepository;
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

    public function __construct(MenuListRepository $menuListRepository)
    {
        $this->menuListRepository = $menuListRepository;
    }

    public function transform(OrderDetail $orderDetail)
    {
        return [
            'ID_orders_detail' => $orderDetail->id ? $orderDetail->id : $orderDetail->ID,
            "ID_orders" => (int)$orderDetail->ID_orders,
            'ID_menu_list' => $orderDetail->menu_list->ID,
            'x_number' => (int)$orderDetail->x_number,
            'price' =>  (float)$orderDetail->price,
            'is_child' => $orderDetail->is_child,
            'status' => (int)$orderDetail->status,
            'comment' => $orderDetail->comment,
            'side_dish' => '' . $orderDetail->side_dish,
            "ID_client" => $orderDetail->ID_client,
            'can_cancel' => $orderDetail->can_cancel,
            "serve_at" => \DateTime::createFromFormat('Y-m-d H:i:s', $orderDetail->serve_at)->format('d.m.Y H:i'),
            "visible" => 0,
            "recommended_side_dish" => $orderDetail->recommended_side_dish,
            'order_by_side_dish' => $orderDetail->order_by_side_dish,
            "serve_at_readable" => $this->getReadableServeAt($orderDetail)

        ];
    }

    public function includeMenuList(OrderDetail $orderDetail)
    {
        if ($orderDetail->has('menu_list')){
            return $this->item($orderDetail->menu_list, new MenuListTransformer($this->menuListRepository));
        }
    }

    public function includeSideDish(OrderDetail $orderDetail)
    {
        if ($orderDetail->has('sideDish')){
            foreach ($orderDetail->sideDish as $side_dish) {
                $side_dish->can_cancel = $this->canCancel($side_dish);
            }
            return $this->collection($orderDetail->sideDish, new OrderDetailTransformer($this->menuListRepository));
        }
    }

    public function getReadableServeAt($orderDetail){
        $is_day_menu = $orderDetail->menu_list->is_day_menu;
        $time_from = $orderDetail->menu_list->time_from;
        $time_to = $orderDetail->menu_list->time_to;

        if ($is_day_menu == 0){
            if (!$time_from || !$time_to){
//                return "Served Everyday";
                return ["type" => "everyday", "data" => "CLIENT.SERVED EVERYDAY"];
            }
//            return "Served Everyday from " . substr($time_from, 0, 5) . " to " . substr($time_to, 0, 5);
            return ["type" => "everyday_with_time", "data" => "CLIENT.SERVED EVERYDAY", "from" => substr($time_from, 0, 5) , "to" => substr($time_to, 0, 5)];
        } else if ($is_day_menu == -1) {
            $menu_schedule = $orderDetail->menu_list->menu_schedule;
            if ($menu_schedule){
                $start_date = new Carbon($menu_schedule->datetime_from);
                $end_date = new Carbon($menu_schedule->datetime_to);
                $start_day = $start_date->dayOfWeek == 0 ? 7 : $start_date->dayOfWeek;
                $end_day = $end_date->dayOfWeek == 0 ? 7 : $end_date->dayOfWeek;
                $start_time_hour = strlen($start_date->hour . "") == 1 ? "0" . $start_date->hour : $start_date->hour;
                $start_time_minute = strlen($start_date->minute . "") == 1 ? "0" . $start_date->minute : $start_date->minute;
                $start_time = $start_time_hour . ':' . $start_time_minute;
                $end_time_hour = strlen($end_date->hour . "") == 1 ? "0" . $end_date->hour : $end_date->hour;
                $end_time_minute = strlen($end_date->minute . "") == 1 ? "0" . $end_date->minute : $end_date->minute;
                $end_time = $end_time_hour . ':' . $end_time_minute;
//                return "Served every " . $this->days[$start_day] . " from " . $start_time  .  " to " .$this->days[$end_day]  . " " . $end_time;
                return ["type"=>"menu_schedule", "data" => "CLIENT.SERVED EVERY" . $orderDetail->menu_list->is_day_menu, "day_from" => $this->days[$start_day], "time_from" => $start_time,  "day_to" => $this->days[$end_day], "time_to" => $end_time];

            }
        } else if (in_array($is_day_menu, [1,2,3,4,5,6,7])) {
            if (!$time_from || !$time_to){
//                return "Served every "  . $this->days[$orderDetail->menu_list->is_day_menu];
                return ["type" => "day_with_no_time","data" => "CLIENT.SERVED EVERY" . $orderDetail->menu_list->is_day_menu, "day" => $this->days[$orderDetail->menu_list->is_day_menu]];
            }
//            return "Served every " . $this->days[$orderDetail->menu_list->is_day_menu] . " from " . substr($time_from, 0, 5) . " to " . substr($time_to, 0, 5);
            return ["type" => "day_with_time", "data" => "CLIENT.SERVED EVERY" . $orderDetail->menu_list->is_day_menu, "day" => $this->days[$orderDetail->menu_list->is_day_menu], "from" => substr($time_from, 0, 5),  "to" => substr($time_to, 0, 5)];
        }
    }

    public function canCancel($orderDetail){
        $initial_time = Carbon::instance(new \DateTime($this->initialTime));
        $cancel_until = Carbon::instance(new \DateTime($orderDetail->menu_list->cancel_until));

        $serve_at = Carbon::instance(new \DateTime($orderDetail->serve_at));
        $current_time = Carbon::now("Europe/Prague");

        if ($current_time->gte($serve_at)){
            if ($orderDetail->status == 0){
                return false;
            }
        }

        $diff_serve_at_and_current_time = $this->getDiffInTime($serve_at, $current_time);
        $diff_cancel_until_and_initial_time = $this->getDiffInTime($cancel_until, $initial_time);

        if ($diff_serve_at_and_current_time > $diff_cancel_until_and_initial_time){
            return true;
        }
        return false;

    }

    private function getDiffInTime(Carbon $time1, Carbon $time2)
    {
        return strtotime($time1->toDateTimeString()) - strtotime($time2->toDateTimeString());
    }

}