<?php

namespace App\Transformers;

use App\Entities\Order;
use App\Entities\Photo;
use App\Repositories\MenuListRepository;
use App\Repositories\OrderDetailRepository;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class OrderTransformer extends TransformerAbstract
{

    protected $defaultIncludes = ['orders_detail'];
    public $menuListRepository;
    public $currency = "";

    public function __construct(MenuListRepository $menuListRepository)
    {
        $this->menuListRepository = $menuListRepository;
    }

    public function transform(Order $order)
    {
        return [
            'ID_orders' => $order->ID,
            'ID_restaurant' => $order->ID_restaurant,
            'ID_client'=> $order->ID_client,
            'restaurant_name' => $order->restaurant->name,
            'total_order_details' => $this->getTotalOrders($order),
            'total_order_details_by_status' => $this->getTotalOrdersByStatus($order),
            'status' => $order->status,
            'comment'=> $order->comment,
            'cancellation' => $this->getCancellationTime($order),
            'persons' => (int)$order->persons,
            'order_number'=>$order->order_number,
            'created_at' => $order->created_at,
            'total_price' => $this->getTotalPrice($order)

        ];
    }

    public function includeOrdersDetail(Order $order)
    {
        if ($order->has('orders_detail')){
            $orders_detail = $order->orders_detail;
            $orders_detail = $orders_detail->filter(function($item){
                if ($item->status == 5){
                    return true;
                }
                return false;
            });
            return $this->collection($orders_detail,  new OrderDetailTransformer($this->menuListRepository));
        }
    }

    public function getTotalOrders($order){
        if ($order->has('orders_detail')){
            $orders_detail = $order->orders_detail;
            $orders_detail = $orders_detail->filter(function($item){
                if ($item->status == 5){
                    return true;
                }
                return false;
            });
            return $orders_detail->count();
        }
    }

    public function getTotalOrdersByStatus($order)
    {
        if ($order->has('orders_detail')){
            $orders_detail = $order->orders_detail;
            return $orders_detail->count();
        }
    }

    public function getCancellationTime($order)
    {
        $order_detail = $order->orders_detail;
        $filtered = $order_detail->filter(function($item){
            $current_time = Carbon::now();
            $serve_time = new Carbon($item->serve_at);
            $diffForHumans = $serve_time->diffForHumans($current_time);
            $diffInMinutes = $serve_time->diffInMinutes($current_time, false);
            $item->cancellation = $diffForHumans;
            $item->difference = $diffInMinutes;
            return true;
        });
        $filtered = $filtered->sortByDesc("difference");
        $filtered_order_detail = $filtered->first();
        if ($filtered_order_detail && $filtered_order_detail->difference >= 0){
            return "no";
        }
        return $filtered_order_detail ? $filtered_order_detail->serve_at : "";
    }

    public function getTotalPrice($order)
    {
        $order_details = $order->orders_detail;
        $order_details = $order_details->filter(function($item) {
            $item->total_price = $item->price * $item->x_number;
            $this->currency = $item->menu_list->currency;
            return true;
        });
        return $order_details->sum('total_price') . ' ' .$this->currency;
    }


}