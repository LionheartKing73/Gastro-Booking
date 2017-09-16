<?php

namespace App\Http\Controllers;

use App\Entities\Order;
use App\Entities\OrderDetail;
use App\Repositories\MenuListRepository;
use App\Repositories\OrderDetailRepository;
use App\Transformers\OrderDetailTransformer;
use App\Transformers\OrderTransformer;
use App\Transformers\AllOrderTransformer;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

use App\Http\Requests;

class OrderDetailController extends Controller
{
    use Helpers;
    public $ordersDetailRepository;
    public $menuListRepository;
    public $perPage = 10;

    public function __construct(OrderDetailRepository $orderDetailRepository, MenuListRepository $menuListRepository)
    {
        $this->ordersDetailRepository = $orderDetailRepository;
        $this->menuListRepository = $menuListRepository;
    }

    public function getOrderDetails($restaurantId){
        $orders_detail = $this->ordersDetailRepository->all($restaurantId);
        if ($orders_detail){
            return $this->response->collection($orders_detail, new OrderDetailTransformer($this->menuListRepository));
        }

        return ["error" => "You have no orders"];
    }

    public function getOrdersDetailByStatus(Request $request, $orderId){
        $orders_detail = $this->ordersDetailRepository->getOrdersDetailByStatus($request->status, $orderId, $this->perPage);
        if ($orders_detail){
            return $this->response->paginator($orders_detail, new OrderDetailTransformer($this->menuListRepository));
        }
        return ["error" => "You have no orders detail"];
    }

    public function getOrders()
    {
        $orders = $this->ordersDetailRepository->getOrders();
        if ($orders){
            return $this->response->collection($orders, new OrderTransformer($this->menuListRepository));
        }

    }

    public function getOrdersByStatus(Request $request)
    {
        $orders = $this->ordersDetailRepository->getAllOrders($this->perPage);
        foreach ($orders as $order) {
            $orders_detail = $order->orders_detail;
            $order->orders_detail = $orders_detail->filter(function($item){
                if ($item->side_dish == "0"){
                    return true;
                }
            });

        }
        if ($orders){
            return $this->response->paginator($orders, new AllOrderTransformer($this->menuListRepository));
        }
        return ["error" => "You have no orders"];
    }

    public function store(Request $request){
        $orders_detail = $this->ordersDetailRepository->store($request);
        if ($orders_detail){
            return $this->response->item($orders_detail, new OrderDetailTransformer($this->menuListRepository));
        }
        return ["error" => "You have already ordered this item!"];
    }

    public function update(Request $request){
        $orders_detail = $this->ordersDetailRepository->respond($request);
        if ($orders_detail){
            return ["success" => "Order placed successfully!"];
        }
        return ["error" => "Oops! Your order can't be placed right now. Please try again later!"];
    }

    public function updateOrdersDetail(Request $request){
        $orders_detail = $request->has("orders_detail");
        $order = Order::find($orders_detail[0]['ID_orders']);
        if ($orders_detail){
            foreach ($request->orders_detail as $detail) {
                $order_detail = OrderDetail::find($detail["ID_orders_detail"]);
                if ($detail["status"] == 6 && $order_detail->status != 2){
                    $order_detail->delete();
                }
                else if ($order_detail->status != 2 && $order_detail->status != 4){
                    $order_detail->status = $detail["status"];
                    $order_detail->x_number = (int)$detail['x_number'];
                    $order_detail->serve_at = new Carbon($detail['serve_at']);
                    $order_detail->is_child = $detail['is_child'] ? 1 : 0;
                    $order_detail->side_dish = $detail['side_dish'];
                    $order_detail->comment = $detail['comment'];
                    $order_detail->ID_client = $detail['ID_client'];
                    $order_detail->save();
                }
            }

            return ["success" => "Order Details updated successfully"];
        }
        return ["error" => "'orders_detail' key not found!"];
    }

    public function updateOrders(Request $request){
        $order = $request->has("order");
        if ($order){
            $order_obj = Order::find($request->order["ID_orders"]);
            if ($order["status"] == 6 && $order_obj->status != 2){
                $order_obj->delete();
            }
            else if ($order_obj->status != 2 && $order_obj->status != 4){
                $order_obj->status = $request->order["status"];
                $order_obj->comment = $request->order["comment"];
                $order_obj->persons = $request->order["persons"];
                $order_obj->save();
            }
            $sent = $this->ordersDetailRepository->sendEmailReminder('update', app('Dingo\Api\Auth\Auth')->user(), $order_obj, $order_obj->restaurant,
                $request->lang ? $request->lang : 'cz', $order_obj->orders_detail, 'user');
            $sent_rest = $this->ordersDetailRepository->sendEmailReminder('update', app('Dingo\Api\Auth\Auth')->user(), $order_obj, $order_obj->restaurant,
                'cz', $order_obj->orders_detail, 'rest');
            return ["success" => "Order updated successfully"];
        }
        return ["error" => "'order' key not found!"];

    }

    public function deleteOrder(Request $request, $orderId){
        $order_obj = Order::find($orderId);
        $order = $this->ordersDetailRepository->deleteOrder($orderId);
        if ($order){
            $response = $this->response->item($order, new OrderTransformer($this->menuListRepository));
            return $response;
        }
        $sent = $this->ordersDetailRepository->sendEmailReminder('update', app('Dingo\Api\Auth\Auth')->user(), $order_obj, $order_obj->restaurant,
            $request->lang ? $request->lang : 'cz', $order_obj->orders_detail, 'user');
        $sent_rest = $this->ordersDetailRepository->sendEmailReminder('update', app('Dingo\Api\Auth\Auth')->user(), $order_obj, $order_obj->restaurant,
            'cz', $order_obj->orders_detail, 'rest');
        return ["error" => "Order not found!"];
    }

    public function deleteOrderDetail($orderDetailId){
        $order_detail = $this->ordersDetailRepository->deleteOrderDetail($orderDetailId);
        if ($order_detail){
            $response = $this->response->item($order_detail, new OrderDetailTransformer($this->menuListRepository));
            return $response;
        }
        return ["error" => "Order detail not found!"];
    }

    public function getOrder($orderId)
    {
        $order = $this->ordersDetailRepository->getOrder($orderId);
        if ($order){
            $response = $this->response->item($order, new OrderTransformer($this->menuListRepository));
            return $response;
        }
        return ["error" => "Order not found!"];
    }

    public function getOrderForDashboard($orderId)
    {
        $order = $this->ordersDetailRepository->getOrder($orderId);
        $orders_detail = $order->orders_detail;
        $order->orders_detail = $orders_detail->filter(function($item){
            if ($item->side_dish == "0"){
                return true;
            }
        });
        if ($order){
            $response = $this->response->item($order, new OrderTransformer($this->menuListRepository));
            return $response;
        }
        return ["error" => "Order not found!"];
    }

    public function getOrderDetailCount()
    {
        return $this->ordersDetailRepository->getOrderDetailCount();
    }


}
