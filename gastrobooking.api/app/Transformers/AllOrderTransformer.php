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
class AllOrderTransformer extends TransformerAbstract
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
            'restaurant_name' => $order->restaurant->name,
            'restaurant_phone' => $order->restaurant->phone,
            'total_order_details' => $this->getTotalOrders($order),
            'total_order_details_by_status' => $this->getTotalOrdersByStatus($order),
            'status' => $order->status,
            'comment'=> $order->comment,
            'order_number'=> $order->order_number,
            'persons' => $order->persons ? (int)$order->persons : "",
            'cancellation' => $order->cancellation,
            'created_at' => $order->created_at->format('d.m.Y H:i'),
            'total_price' => $this->getTotalPrice($order)

        ];
    }

    public function includeOrdersDetail(Order $order)
    {
        if ($order->has('orders_detail')){
            $orders_detail = $order->orders_detail;
            $orders_detail = $orders_detail->sortBy("serve_at");
            return $this->collection($orders_detail,  new OrderDetailTransformer($this->menuListRepository));
        }
    }

    public function getTotalOrders($order){
        if ($order->has('orders_detail')){
            $orders_detail = $order->orders_detail;
            $count = 0;
            foreach ($orders_detail as $order_detail) {
                if ($order_detail->status == 5){
                    $count += $order_detail->x_number;
                }
            }
            return $count;
        }
    }

    public function getTotalOrdersByStatus($order)
    {
        if ($order->has('orders_detail')){
            $ord = Order::find($order->ID);
            $orders_detail = $ord->orders_detail;
            $count = 0;
            foreach ($orders_detail as $item) {
                if ($ord->status == 0){
                    if ($item->status == 0){
                        $count += $item->x_number;
                    }
                } else {
                    $count += $item->x_number;
                }
            }
            return $count;
        }
    }

    public function getCancellationTime($order)
    {
        $order_detail = $order->orders_detail;
        $filtered = $order_detail->filter(function($item){
            $current_time = Carbon::now();
            $serve_time = new Carbon($item->serve_at);
            $diff = $this->getDiffInTime($serve_time, $current_time);
            $item->difference = $diff;
            return true;
        });
        $filtered = $filtered->sortByDesc("difference");
        $filtered_order_detail = $filtered->first();
        if ($filtered_order_detail && $filtered_order_detail->difference >= 0){
            return ["status" => "error", "serve_at" => \DateTime::createFromFormat('Y-m-d H:i:s', $filtered_order_detail->serve_at)->format('d.m.Y H:i') ];
        }
        return $filtered_order_detail ? ["status" => "success", "serve_at" => \DateTime::createFromFormat('Y-m-d H:i:s', $filtered_order_detail->serve_at)->format('d.m.Y H:i')] : "";
    }

    private function getDiffInTime(Carbon $time1, Carbon $time2)
    {
        return strtotime($time1->toDateTimeString()) - strtotime($time2->toDateTimeString());
    }

    public function getTotalPrice($order)
    {
        $ord = Order::find($order->ID);
        $order_details = $ord->orders_detail;
        $order_details = $order_details->filter(function($item) {
            if ($item->status != 3){
                $item->total_price = ($item->is_child ? $item->menu_list->price_child : $item->menu_list->price) * $item->x_number;
                $this->currency = $item->menu_list->currency;
                return true;
            }
        });
        return $order_details->sum('total_price') . ' ' .$this->currency;
    }


}