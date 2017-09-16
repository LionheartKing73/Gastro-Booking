<?php

namespace App\Http\Controllers;

use App\Entities\MenuList;
use App\Entities\OrderDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;

class TestController extends Controller
{
    private $initialTime = "0001-01-01 00:00:00";
    public function test()
    {


        $filtered = MenuList::find(7)->orders_detail->filter(function($item){
            $today = Carbon::today();
            $serve_at = new Carbon($item->serve_at);
            $item->date_difference = $today->diffInDays($serve_at);
            return $item->date_difference <= 7;
        });
        return $filtered->sortBy('date_difference')->take(7);

        $menuList = MenuList::find(1);
        $orderDetail = OrderDetail::find(8);
        $inital_time = Carbon::instance(new \DateTime($this->initialTime));
        $cancel_until = Carbon::instance(new \DateTime($menuList->cancel_until));
        $serve_at = Carbon::instance(new \DateTime($orderDetail->serve_at));
        $current_time = Carbon::now();

        return [
            "cancel_until" => $cancel_until,
            "serve_at" => $serve_at,
            "initial_time" => $inital_time,
            "current_time" => $current_time,
            "serve_at_and_current_time" => $current_time->diffInMinutes($serve_at),
            "cancel_until_and_initial_time" => $cancel_until->diffInHours($inital_time),
            "compare" => $cancel_until->gt($inital_time)

        ];

    }
}
