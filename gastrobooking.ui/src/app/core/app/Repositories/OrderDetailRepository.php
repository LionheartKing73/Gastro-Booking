<?php
/**
 * Created by PhpStorm.
 * RestaurantOpen: tOm_HydRa
 * Date: 9/10/16
 * Time: 12:06 PM
 */

namespace App\Repositories;

use App\Entities\Client;
use App\Entities\MenuList;
use App\Entities\Order;
use App\Entities\OrderDetail;
use App\Entities\RestaurantOpen;
use App\Entities\Restaurant;
use App\Entities\RestaurantOrderNumber;
use App\User;
use Carbon\Carbon;
use Faker\Provider\zh_TW\DateTime;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Mockery\CountValidator\Exception;
use Webpatser\Uuid\Uuid;


class OrderDetailRepository
{
    private $initialTime = "0001-01-01 00:00:00";
    private $currency = "";
    public $orderRepository;
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

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;

    }

    public function store(Request $request){
        if ($request->has("orders_detail")){
            $client = app('Dingo\Api\Auth\Auth')->user()->client;
            $orders_detail = OrderDetail::where(["ID_menu_list" => $request->orders_detail["ID_menu_list"], "ID_client" => $client->ID, "status" => 5])
                ->whereHas("order", function($query) use ($client){
                    return $query->where("ID_client", $client->ID);
                });
            if ($orders_detail->count()){
                $orders_detail = $orders_detail->first();
                $orders_detail->x_number += 1;
                $orders_detail->save();
                return $orders_detail;
            }
            $input = $request->orders_detail;
            $menu_list = MenuList::find($input["ID_menu_list"]);
            $orders_detail = new OrderDetail();
            $order = $this->orderRepository->save($menu_list->restaurant->id);
            $orders_detail->ID_orders = $order->ID ? $order->ID : $order->id;
            $orders_detail->ID_client = $client->ID;
            $orders_detail->ID_menu_list = $input["ID_menu_list"];
            $orders_detail->is_child = 0;
            $serve_date = new Carbon($request->orders_detail['date']);
            if ($request->source == "search"){
                $serve_time = new Carbon($request->orders_detail['time']);
                $serve_date->setTime($serve_time->hour, $serve_time->minute);
            }
            $orders_detail->serve_at = $serve_date;
            $orders_detail->price = $menu_list->price;
            $orders_detail->side_dish = 0;
            $orders_detail->status = 5;
            $orders_detail->x_number = 1;
            $orders_detail->save();
            return $orders_detail;
        }
    }

    public function update($input, &$wrong_orders_details){
        $orders_detail = OrderDetail::find($input["ID_orders_detail"]);
        $orders_detail->ID_client = isset($input["ID_client"]) ? $input["ID_client"] : $orders_detail->ID_client;
        $orders_detail->x_number = isset($input["x_number"]) ? $input["x_number"] : $orders_detail->x_number;
        $orders_detail->comment = isset($input["comment"]) ? $input["comment"] : $orders_detail->comment;
        $orders_detail->is_child = isset($input["is_child"]) ? $input["is_child"] : $orders_detail->is_child;
        $orders_detail->price = isset($input["t_price"]) ? $input["t_price"] : $orders_detail->price;
        $orders_detail->side_dish = isset($input["side_dish"]) ? $input["side_dish"] : $orders_detail->side_dish;
        if ($orders_detail->side_dish){
            $main_dish = OrderDetail::find($orders_detail->side_dish);
            $orders_detail->serve_at = $main_dish ? $main_dish->serve_at : $orders_detail->serve_at;
            $orders_detail->ID_client = $main_dish ? $main_dish->ID_client : $orders_detail->ID_client;
            $input["serve_at"] = $orders_detail->serve_at;
        }
        if ($input["serve_at"]){
            $serve_at = new Carbon($input["serve_at"]);
            $orderAllowed = $this->isOrderingAllowed($orders_detail, $serve_at);
            if($orderAllowed["error"]  == "opening_hour"){
                $orders_detail->serve_at = new Carbon($input["serve_at"]);
                $orders_detail->save();
                $wrong_orders_details[] =  $this->generateServingTime($orders_detail, "opening_hour");
                return false;
            }
            if ($orderAllowed){
                $current_time = new Carbon();
                $init_time = new Carbon("0001-01-01 00:00:00");
                $book_to = new Carbon($orders_detail->menu_list->book_to);
                $book_from = $orders_detail->menu_list->book_from;
                $current_time_serve_at_difference = $this->getDiffInTime($serve_at, $current_time);
                $init_time_book_to_difference = $this->getDiffInTime($book_to, $init_time);
                $current_time_serve_at_days_difference = $current_time_serve_at_difference / (3600 * 24);
                if ($init_time_book_to_difference > $current_time_serve_at_difference){
                    $orders_detail->serve_at = new Carbon($input["serve_at"]);
                    $orders_detail->save();
                    $wrong_orders_details[] =  $this->generateServingTime($orders_detail, "book_to");
                    return false;
                } else if ($current_time_serve_at_days_difference > $book_from) {
                    $orders_detail->serve_at = new Carbon($input["serve_at"]);
                    $orders_detail->save();
                    $wrong_orders_details[] =  $this->generateServingTime($orders_detail, "book_from");
                    return false;
                }

            }
            else {
//                $orders_detail->serve_at = Carbon::createFromFormat("d-m-Y H:i", $input["serve_at"]);
                $orders_detail->serve_at = new Carbon($input["serve_at"]);
                $orders_detail->save();
                $wrong_orders_details[] =  $this->generateServingTime($orders_detail, false);
                return false;
            }
        }
        $orders_detail->serve_at = isset($input["serve_at"]) ? new Carbon($input["serve_at"]) : Carbon::now("Europe/Prague");
//        $orders_detail->serve_at = isset($input["serve_at"]) ? Carbon::createFromFormat("d-m-Y H:i", $input["serve_at"]) : Carbon::now("Europe/Prague");
        $orders_detail->status = 0;
        $orders_detail->save();
        return true;
    }

    private function getDiffInTime(Carbon $time1, Carbon $time2)
    {
        return strtotime($time1->toDateTimeString()) - strtotime($time2->toDateTimeString());
    }

    public function respond($input){
        $order = $input->order;
        $order_detail = $input->orderDetail;
        $order_detail = collect($order_detail)->sortBy("side_dish")->toArray();

        if ($order && $order_detail){
            $order_email = Order::with('orders_detail')->find($order["ID_orders"]);
            $order_email->persons = $order["persons"];
            $order_email->comment = $order["comment"];
            $order_email->save();
            // Save Order's Details
            $order_failed = false;
            $wrong_order_details = [];
            foreach ($order_detail as $orders_detail) {
                if (!$this->update($orders_detail, $wrong_order_details)){
                    $order_failed =  true;
                }
            }

            if ($order_failed){
                $ord = Order::find($order["ID_orders"]);
                foreach ($ord->orders_detail as $orderDetail) {
                    if ($orderDetail->status == 0){
                        $orderDetail->status = 5;
                        $orderDetail->save();
                    }
                }
                return ["wrongServingTime" => $wrong_order_details];
            }

            if (!$order_failed){
                $order_email->status = 0;
            }

            $order_email->created_at = Carbon::now("Europe/Prague");
            $order_email->order_number = $this->generateOrderNumber($order_email);
            $order_email->save();

            $another_order_email = Order::find($order_email->ID);
            $current_time = Carbon::now("Europe/Prague");
            $orders_detail_copy = $another_order_email->orders_detail;
            $currency = "";
            foreach ($orders_detail_copy as $order_detail) {
                $serve_time = new Carbon($order_detail->serve_at);
                $diffInMinutes = $this->getDiffInTime($serve_time, $current_time);
                $order_detail->difference = $diffInMinutes;
                if (!$currency){
                    $currency = $order_detail->menu_list->currency;
                }
            }
            $filtered_order_detail = $orders_detail_copy->sortBy("difference")->first();
            $another_order_email->cancellation = \DateTime::createFromFormat('Y-m-d H:i:s', $filtered_order_detail->serve_at)->format('d.m.Y H:i');
            $another_order_email->currency = $currency;

            $orders_detail_filtered = [];
            $another_order_email->orders_detail = $another_order_email->orders_detail->sortBy("serve_at");
            foreach ($another_order_email->orders_detail as $order_detail) {
                if ($order_detail->side_dish == 0 ){
                    $orders_detail_filtered[] = $order_detail;
                }
            }
            $sent = $this->sendEmailReminder('new', app('Dingo\Api\Auth\Auth')->user(), $another_order_email,$order_email->restaurant,
                $input->lang ? $input->lang: 'cz', $orders_detail_filtered, 'user');
            $sent_rest = $this->sendEmailReminder('new', app('Dingo\Api\Auth\Auth')->user(), $another_order_email, $order_email->restaurant,
                $input->lang ? $input->lang: 'cz', $orders_detail_filtered, 'rest');
            return ["data" => "Order placed successfully!"];
        }
        return ["requestError" => "Request error!"];

    }



    public function generateOrderNumber($order){
        $orders = Order::where("ID_restaurant", $order->ID_restaurant)->where("ID", "<>", $order->ID)->get();
        $serve_time = $this->getCancellationTimeMini($order);
        $ord_number = 1;
        if (count($orders)){
            $ord_collection = collect();
            foreach ($orders as $ord) {
                $order_date = $this->getCancellationTimeMini($ord);
                if (!$order_date->diffInDays($serve_time)){
                    $ord_collection->push($ord);

                }
            }
            if (!$ord_collection->isEmpty()){
                $ord_number = $ord_collection->max("order_number") + 1;
            }
        }

        return $ord_number;
    }

    public function getCancellationTimeMini($order)
    {
        $current_time = Carbon::now("Europe/Prague");
        foreach ($order->orders_detail as $order_detail) {
            $serve_time = new Carbon($order_detail->serve_at);
            $diffInMinutes = $this->getDiffInTime($serve_time, $current_time);
            $order_detail->difference = $diffInMinutes;
        }
        $filtered_order_detail = $order->orders_detail->sortBy("difference")->first();
        return new Carbon($filtered_order_detail->serve_at);
    }

    public function generateServingTime($orders_detail, $error_type)
    {
        $menu_list = $orders_detail->menu_list;
        $data = [];
        $data["name"] = $menu_list->name;
        $data["id"] = $orders_detail->ID;
        if ($orders_detail->side_dish){
            $data["side_dish"] = $orders_detail->side_dish;
        }

        if($error_type == "opening_hour"){
            $data["type"] = "opening_hour" ;
            $data["collapsed"] = true;
            $openingHours = $menu_list->restaurant ? $menu_list->restaurant->openingHours : null;
            foreach ($openingHours as $openingHour) {
                $data["serve"][] = [
                    "day" => $openingHour->date,
                    "from" => $openingHour->m_starting_time  == 'From' ? null : $openingHour->m_starting_time ,
                    "to" => $openingHour->m_ending_time == 'Until' ? null : $openingHour->m_ending_time,
                    "afrom" =>$openingHour->a_starting_time == 'From' ? null : $openingHour->a_starting_time,
                    "ato" => $openingHour->a_ending_time == 'Until' ? null : $openingHour->a_ending_time
                ];
            }
            return $data;
        }

        if ($error_type == "book_from") {
            $data["book_from"] = $menu_list->book_from;
        } else if ($error_type == "book_to"){
            $b_to = new Carbon($menu_list->book_to);
            if ($b_to->day > 1) $data["book_to"]["day"] = $b_to->day - 1;
            if ($b_to->hour > 0) $data["book_to"]["hour"] = $b_to->hour;
            if ($b_to->minute > 0) $data["book_to"]["minute"] = $b_to->minute;
        }

        if ($menu_list->is_day_menu == -1 && $menu_list->menu_schedule){
            $schedule = $this->parseMenuSchedule($menu_list->menu_schedule);
            $start_time = $schedule["start_time"];
            $end_time = $schedule["end_time"];
            $data["type"] = "scheduled";
            $data["time"]["from"] = $start_time;
            $data["time"]["to"] = $end_time;
            $data["collapsed"] = true;
            $openingHours = $menu_list->restaurant ? $menu_list->restaurant->openingHours : null;
            foreach ($openingHours as $openingHour) {
                $data["serve"][] = [
                    "day" => $openingHour->date,
                    "from" => $openingHour->m_starting_time  == 'From' ? null : $openingHour->m_starting_time ,
                    "to" => $openingHour->m_ending_time == 'Until' ? null : $openingHour->m_ending_time,
                    "afrom" =>$openingHour->a_starting_time == 'From' ? null : $openingHour->a_starting_time,
                    "ato" => $openingHour->a_ending_time == 'Until' ? null : $openingHour->a_ending_time
                ];
            }

            return $data;
        }
        $data["type"] = "menuOfTheDay";
        $data["serve"]["from"] = $menu_list->time_from;
        $data["serve"]["to"] = $menu_list->time_to;
        if ($menu_list->is_day_menu == 0){
            $data["label"] = "CLIENT.SERVED EVERYDAY";
        } else if (in_array($menu_list->is_day_menu, [1,2,3,4,5,6,7])){
            $data["label"] = "CLIENT.SERVED EVERY" . $menu_list->is_day_menu;
            $data["day"] = $this->days[$menu_list->is_day_menu];
        }

        return $data;

    }

    public function isOrderingAllowed($orders_detail, $serve_at)
    {
        $return = array();
        $day = $serve_at->dayOfWeek == 0 ? 7 : $serve_at->dayOfWeek;
        $hour = strlen($serve_at->hour . "") == 1 ? "0" . $serve_at->hour : $serve_at->hour;
        $minute = strlen($serve_at->minute . "") == 1 ? "0" . $serve_at->minute : $serve_at->minute;
        $time = $hour . ':' . $minute . ":00";
        $menu_list = $orders_detail->menu_list;
        $restOpeningHours = $menu_list->restaurant ? $menu_list->restaurant->openingHours : false;
        $restaurantOpen = $this->isRestaurantOpen($restOpeningHours, $day, $time);

        if ($restaurantOpen){
            if ($menu_list->is_day_menu == $day || $menu_list->is_day_menu == 0){
                if (strtotime($menu_list->time_from) <= strtotime($time) &&
                    strtotime($menu_list->time_to) >= strtotime($time)){
                    return true;
                }
            }
            else if ($menu_list->menu_schedule){
                return $this->isMenuScheduleValid($menu_list, $day, $time);
            }
        }else{
            $return["error"] = "opening_hour";
            return $return;
        }


        return false;
    }

    public function isMenuScheduleValid($menu_list, $day, $time)
    {
        $schedule = $this->parseMenuSchedule($menu_list->menu_schedule);
        $start_day = $schedule["start_day"];
        $end_day = $schedule["end_day"];
        if ($start_day > $end_day ){
            if (($start_day <= $day && $day <= 7) || (1 <= $day && $day <= $end_day)) {
                $openingHours = $menu_list->restaurant->openingHours;
                if ($openingHours){
                    foreach ($openingHours as $openingHour) {
                        if ($openingHour->date == $this->days[$day]){
                            if ((strtotime($openingHour->m_starting_time) <= strtotime($time) &&
                                    strtotime($openingHour->m_ending_time) >= strtotime($time))
                                || (strtotime($openingHour->a_starting_time) <= strtotime($time) &&
                                    strtotime($openingHour->a_ending_time) >= strtotime($time))){
                                return true;
                            }
                        }
                    }
                }
                else return true;
            }
        }
        else if ($start_day < $day && $day < $end_day) {
            $openingHours = $menu_list->restaurant->openingHours;
            if ($openingHours){
                foreach ($openingHours as $openingHour) {
                    if ($openingHour->date == $this->days[$day]){
                        if ((strtotime($openingHour->m_starting_time) <= strtotime($time) &&
                                strtotime($openingHour->m_ending_time) >= strtotime($time))
                            || (strtotime($openingHour->a_starting_time) <= strtotime($time) &&
                                strtotime($openingHour->a_ending_time) >= strtotime($time))){
                            return true;
                        }
                    }
                }
            }
            else return true;
        }
        return false;
    }

    public function parseMenuSchedule($menu_schedule){
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
        return [
            "start_day" => $start_day,
            "end_day" => $end_day,
            "start_time" => $start_time,
            "end_time" => $end_time
        ];
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

    public function getCancellationTime($order)
    {
        $order_detail = $order->orders_detail;
        $filtered = $order_detail->filter(function($item){
            $current_time = Carbon::now("Europe/Prague");
            $serve_time = new Carbon($item->serve_at);
            $diffInMinutes = $this->getDiffInTime($serve_time, $current_time);
            $item->difference = $diffInMinutes;
            $this->currency = $item->menu_list->currency;
            return true;
        });
        $filtered = $filtered->sortByDesc("difference");
        $filtered_order_detail = $filtered->first();
        if ($filtered_order_detail && $filtered_order_detail->difference >= 0){
            return [
                "status" => "error",
                "currency" => $this->currency ,
                "serve_at" => \DateTime::createFromFormat('Y-m-d H:i:s', $filtered_order_detail->serve_at)->format('d.m.Y H:i') ];
        }
        return $filtered_order_detail ? [
            "status" => "success",
            "currency" => $this->currency ,
            "serve_at" => \DateTime::createFromFormat('Y-m-d H:i:s', $filtered_order_detail->serve_at)->format('d.m.Y H:i')] : "";
    }

    public function all($restaurantId){
        $client = app('Dingo\Api\Auth\Auth')->user()->client;
        $order = Order::where(["ID_client" => $client->ID, "ID_restaurant" => $restaurantId, "status" => 5])->first();
        if ($order){
            $orders_detail =  $order->orders_detail->filter(function($item){
                if ($item->status == 5){
                    if ($item->side_dish){
                        $item->order_by_side_dish = $item->side_dish + .1;
                        $item->serve_at_sort = OrderDetail::find($item->side_dish)->serve_at_sort;
                    } else {
                        $item->order_by_side_dish = $item->ID;
                        $item->serve_at_sort = $item->serve_at;
                    }
                    return true;

                }
                return false;
            });
            $orders_detail = $orders_detail->sortBy(function($order_detail) {
                return sprintf('%-12s%s', $order_detail->order_by_side_dish, $order_detail->serve_at);
            });
            return $orders_detail;
        }
        return false;
    }

    public function getOrders()
    {
        $client = app('Dingo\Api\Auth\Auth')->user()->client;
        $order_obj = Order::where(["ID_client" => $client->ID, "status" => 5]);
        if ($order_obj->count()){
            $orders = $order_obj->get();
            foreach ($orders as $order) {
                if (!count($order->orders_detail)){
                    $order->delete();
                }
            }
        }
        return $order_obj->get();

    }

    public function getOrdersDetailByStatus($status, $orderId, $n)
    {
        $orders_detail = OrderDetail::where(["ID_orders" => $orderId, "status" => $status])->paginate($n);
        if ($orders_detail){
            return $orders_detail;
        }
        return false;
    }

    public function getOrdersByStatus($status, $n)
    {
        $client = app('Dingo\Api\Auth\Auth')->user()->client;
        $orders = Order::where(["ID_client" => $client->ID, "status" => $status])->paginate($n);
        if ($orders){
            return $orders;
        }
        return false;
    }

    public function getAllOrders($n)
    {
        $client = app('Dingo\Api\Auth\Auth')->user()->client;
        $orders = Order::where(["ID_client" => $client->ID])->get();
        $before = collect();
        $after = collect();
        if ($orders){
            foreach ($orders as $order) {
                $orders_detail = $order->orders_detail;
                $current_time = Carbon::now("Europe/Prague");
                $can_cancel = true;
                foreach ($orders_detail as $order_detail) {
                    $serve_time = new Carbon($order_detail->serve_at);
                    $diffInMinutes = $this->getDiffInTime($serve_time, $current_time);
                    $order_detail->difference = $diffInMinutes;
                    $order_detail->can_cancel = $this->canCancel($order_detail);
                    if(!$order_detail->can_cancel){
                        $can_cancel = false;
                    }
                }

                $orders_detail = $orders_detail->sortBy("difference");
                $filtered_order_detail = $orders_detail->first();
                if ($filtered_order_detail){
                    if ($can_cancel){
                        $order->cancellation = ["status" => "success", "serve_at" => \DateTime::createFromFormat('Y-m-d H:i:s', $filtered_order_detail->serve_at)->format('d.m.Y H:i') ];
                    } else {
                        $order->cancellation = ["status" => "error", "serve_at" => \DateTime::createFromFormat('Y-m-d H:i:s', $filtered_order_detail->serve_at)->format('d.m.Y H:i') ];
                    }
                    $serve_at = new Carbon($filtered_order_detail->serve_at);
                    $diff = $this->getDiffInTime($serve_at, $current_time);
                    $order->diff = $diff;
                    $order->cancellation_time =  \DateTime::createFromFormat('Y-m-d H:i:s', $filtered_order_detail->serve_at)->format('d.m.Y H:i');
                    if ($diff >= 0){
                        $before->push($order);
                    } else {
                        $after->push($order);
                    }
                }
            }

            $before = $before->sortBy('diff');

            $after = $after->sortByDesc('diff');
            $merged = $before->merge($after);

            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            $pagedData = $merged->slice(($currentPage - 1) * $n, $n)->all();

            return new LengthAwarePaginator($pagedData, count($merged), $n);
        }
        return false;
    }


    public function deleteOrder($orderId){
        $order = Order::find($orderId);
        if ($order){
            $order_details = $order->orders_detail;
            foreach ($order_details as $order_detail) {
                $ord_detail = OrderDetail::find($order_detail->ID);
                if ($ord_detail->status == 5){
                    $ord_detail->delete();
                } else {
                    $ord_detail->status = 3;
                    $ord_detail->save();
                }
            }
            if ($order->status == 5) {
                $order->delete();
                return $order;
            }
            $order->status = 3;
            $order->save();
            return $order;
        }
        return false;
    }

    public function getOrder($orderId)
    {
        $order = Order::find($orderId);
        $orders_detail = $order->orders_detail;
        $orders_detail = $orders_detail->filter(function($item){
            if ($item->side_dish){
                $item->order_by_side_dish = $item->side_dish + .1;
                $item->serve_at_sort = OrderDetail::find($item->side_dish)->serve_at_sort;
            } else {
                $item->order_by_side_dish = $item->ID;
                $item->serve_at_sort = $item->serve_at;
            }
            return true;
        });
        $order->orders_detail = $orders_detail->sortBy(function($order_detail) {
            return sprintf('%-12s%s', $order_detail->order_by_side_dish, $order_detail->serve_at);
        });

        if ($order) {
            return $order;
        }
        return false;
    }

    public function deleteOrderDetail($orderDetailId){
        $orderDetail = OrderDetail::find($orderDetailId);
        if ($orderDetail){
            $order = Order::find($orderDetail->ID_orders);
            if ($orderDetail->status == 5){
                $orderDetail->delete();
            } else {
                $orderDetail->status = 3;
                $orderDetail->save();
            }
            if ($orderDetail->sideDish && count($orderDetail->sideDish)){
                foreach ($orderDetail->sideDish as $item) {
                    if ($item->status == 5){
                        $item->delete();
                    } else {
                        $item->status = 3;
                        $item->save();
                    }
                }
            }
            if (!count($order->orders_detail)){
                $order->delete();
            }
            return $orderDetail;
        }
        return false;
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

    public function sendEmailReminder($type, User $user, Order $order, $restaurant, $lang, $orders_detail_filtered, $to)
    {
        $lang = $lang == "cs" ? "cz" : $lang;         // upload
        $path = 'emails.order.order_'.$type. '_'.$lang;
        try {
            Mail::send($path,
                ['user' => $user, 'order' => $order, 'restaurant'=> $restaurant,
                    'orders_detail_count'=> count($order->orders_detail), 'orders_detail_filtered' => $orders_detail_filtered,
                    'orders_detail_total_price' => $this->getTotalPrice($order->orders_detail)],
                function ($m) use($user, $restaurant, $to, $type){
                    if ($to == 'user'){
                        $m->from('cesko@gastro-booking.com', "Gastro Booking");
                        $m->replyTo($restaurant->email, $restaurant->name);
                        $m->to($user->email, $user->name);

                    }
                    else if ($to == 'rest'){
                        $m->from('cesko@gastro-booking.com',  "Gastro Booking");
                        $m->replyTo($user->email, $user->name);
                        $m->to($restaurant->email, $restaurant->name);
                    }
                    if ($type == 'new'){
                        $m->subject('Gastro Booking - Order');
                    }
                    else if ($type == 'update'){
                        $m->subject('Gastro Booking - Order Update');
                    } else {
                        $m->subject('Gastro Booking - CANCELLATION Order');
                    }
                });
        } catch(Exception $e){
            return false;
        }
    }

    private function getTotalPrice($orders_detail)
    {
        $price = 0;
        foreach ($orders_detail as $order_detail) {
            if ($order_detail->status != 3){
                if ($order_detail->is_child){
                    $price += ($order_detail->menu_list->price_child ? $order_detail->menu_list->price_child : $order_detail->menu_list->price);
                } else {
                    $price += $order_detail->menu_list->price;
                }
            }

        }
        return $price;
    }

    public function getOrderDetailCount()
    {
        $user = app('Dingo\Api\Auth\Auth')->user();
        if (!$user || ($user && !$user->client)){
            return 0;
        }
        $client = $user->client;
        $order_detail_count = 0;
        $order_total = Order::where(["ID_client" => $client->ID, "status" => 5])->get();
        if (count($order_total) == 1){
            $order_detail_count = OrderDetail::where(["ID_orders" => $order_total[0]->ID, "status" => 5])->count();
        } else if (count($order_total) > 1){
            $order_detail_count = count($order_total);
        }
        return $order_detail_count;
    }

    public function removeSideDish($orderDetailId){
        $user = app('Dingo\Api\Auth\Auth')->user();

        $order_detail = OrderDetail::where('ID', '=', $orderDetailId)->first();
        $order_detail->side_dish = '0';
        $deleted = $order_detail->save();
        return $deleted;
    }

}
