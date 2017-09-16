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
use App\Entities\Photo;
use App\Entities\RestaurantOpen;
use App\Entities\Restaurant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;


class OrderRepository
{

    public function save($restaurantId){

        $client = app('Dingo\Api\Auth\Auth')->user()->client;
        $order = $this->getClientOrder($client->ID, $restaurantId);
        if ($order){
            return $order;
        }
        $order = new Order();
        $order->ID_restaurant = $restaurantId;
        $order->ID_client = $client->ID;
        $order->status = 5;
//        $order->order_number = $this->getOrderNumber($restaurantId);
        $order->save();
        return $order;

    }

    public function getClientOrder($client_id, $restaurantId){
        return Order::where(["ID_client" => $client_id, "status" => 5, "ID_restaurant" => $restaurantId])->first();

    }

    public function getOrderNumber($rest_id){
        $orders = Order::where(["ID_restaurant"=>$rest_id])->orderBy("id", "desc")->get();
        $today = Carbon::now("Europe/Prague");
        $order_number = 1;
        if (count($orders)){
            $order = $orders;
            if(!$today->diffInDays(new Carbon($order->created_at))){

                $order_number = $order->order_number + 1;
            }
        }
        return $order_number;
    }

    public function checkAndCancel($id) {
        $order = Order::find($id);
        foreach($order->orders_detail as $order_detail) {
            if ($order_detail->status != 3) {
                return $order;
            }
        }
        $order->status = 3;
        $order->save();

        return $order;
    }
}