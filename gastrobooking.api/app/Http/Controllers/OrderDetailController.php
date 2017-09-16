<?php

namespace App\Http\Controllers;

use App\Entities\Order;
use App\Entities\OrderDetail;
use App\Repositories\OrderRepository;
use App\Repositories\MenuListRepository;
use App\Repositories\OrderDetailRepository;
use App\Repositories\QuizRepository;
use App\Transformers\OrderDetailTransformer;
use App\Transformers\OrderTransformer;
use App\Transformers\AllOrderTransformer;
use Carbon\Carbon;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use View;

use App\Http\Requests;

class OrderDetailController extends Controller
{
    use Helpers;
    public $ordersDetailRepository;
    public $orderRepository;
    public $menuListRepository;
    public $perPage = 5;
    private $currency = "";

    public function __construct(OrderDetailRepository $orderDetailRepository, MenuListRepository $menuListRepository, OrderRepository $orderRepository, QuizRepository $quizRepository)
    {
        $this->ordersDetailRepository = $orderDetailRepository;
        $this->menuListRepository = $menuListRepository;
        $this->orderRepository = $orderRepository;
        $this->quizRepository = $quizRepository;
    }

    public function getOrderDetails($restaurantId){

        $orders_detail = $this->ordersDetailRepository->all($restaurantId);
        if ($orders_detail){
            return $this->response->collection($orders_detail, new OrderDetailTransformer($this->menuListRepository));
        }

        return ["error" => "You have no orders"];
    }
    public function getEnableDiscount($restaurantId){
        $client = app('Dingo\Api\Auth\Auth')->user()->client;
        $orderId = $this->orderRepository->getClientOrder($client->ID, $restaurantId);
        $lang = DB::table('restaurant')->where( 'id', $restaurantId )->value('lang');

        $client_per = DB::table('quiz_client')->where('ID_client', $client->ID)->where('lang', $lang)->sum('quiz_percentage');
        $prize_per = DB::table('quiz_prize')->where('ID_client', $client->ID)->where('lang', $lang)->sum('percentage');

        return ($client_per - $prize_per);
    }
    public function getTables($restaurantId){
        $result = DB::table('rooms')->where('ID_restaurant', $restaurantId)->get();

        return $result;       
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

    public function getAllOrdersWithDetail(Request $request)
    {
        $orders = $this->ordersDetailRepository->getAllOrdersWithDetail($request);
        return ["data" => $orders];
    }

    public function getAllOrdersArray(Request $request)
    {
        $orders = $this->ordersDetailRepository->getAllOrdersArray($request);
        return ["data" => $orders];
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
        return $orders_detail;
    }

    public function updateOrdersDetail(Request $request){
        if ($request->has("orders_detail")){
            $order_obj = Order::find($request->orders_detail[0]['ID_orders']);
            $changed = false;
            foreach ($request->orders_detail as $detail) {
                $order_detail = OrderDetail::find($detail["ID_orders_detail"]);
                if ($order_detail->status != $detail["status"]){
                    $changed = true;
                } else if (count($detail["sideDish"]["data"])){
                    $order_detail_array = $order_detail->sideDish->toArray();
                    for ($i = 0; $i < count($detail["sideDish"]["data"]); $i++){
                        if (array_key_exists($i, $order_detail_array) && (!isset($detail["sideDish"]["data"][$i]["status"])
                                || $detail["sideDish"]["data"][$i]["status"] != $order_detail_array[$i]["status"])){
                            $sd_order_detail = OrderDetail::find($detail["sideDish"]["data"][$i]["ID_orders_detail"]);
                            $sd_order_detail->status = $detail["sideDish"]["data"][$i]["status"];
                            $sd_order_detail->save();
                            $changed = true;
                        }
                    }

                }
                if ($detail["status"] == 6 && $order_detail->status != 2){
                    $order_detail->delete();
                } else if ($order_detail->status != 2 && $order_detail->status != 4){
                    $order_detail->status = $detail["status"];
                    $order_detail->x_number = (int)$detail['x_number'];
                    $order_detail->serve_at = new Carbon($detail['serve_at']);
                    $order_detail->is_child = $detail['is_child'] ? 1 : 0;
                    $order_detail->price = isset($detail['t_price']) && $detail['t_price'] ? $detail['t_price'] : 0;
                    $order_detail->side_dish = $detail['side_dish'];
                    $order_detail->comment = $detail['comment'];
                    $order_detail->ID_client = $detail['ID_client'];
                    if ($order_detail->side_dish){
                        $main_dish = OrderDetail::find($order_detail->side_dish);
                        $order_detail->serve_at = $main_dish ? $main_dish->serve_at : $order_detail->serve_at;
                        $order_detail->ID_client = $main_dish ? $main_dish->ID_client : $order_detail->ID_client;
                    }
                }
                $order_detail->save();
            }

            $cancellation = $this->getCancellationTime($order_obj);
            $order_obj->cancellation = $cancellation["serve_at"];
            $order_obj->currency = $cancellation["currency"];
            $another_order_email = Order::find($order_obj->ID);
            $orders_detail_filtered = [];
            $another_order_email->orders_detail = $another_order_email->orders_detail->sortBy("serve_at");

            $order_obj = $this->orderRepository->checkAndCancel($order_obj->ID);
            if ($request->has("save") && $changed){
                if ($order_obj->status == 3) {
                    $email_type = 'cancel';
                } elseif ($order_obj->status != 5) {
                    $email_type = 'update';
                }
                $sent = $this->ordersDetailRepository->sendEmailReminder($email_type, app('Dingo\Api\Auth\Auth')->user(), $order_obj, $order_obj->restaurant,
                    $request->lang ? $request->lang : 'cz', $orders_detail_filtered, 'user');
                $sent_rest = $this->ordersDetailRepository->sendEmailReminder($email_type, app('Dingo\Api\Auth\Auth')->user(), $order_obj, $order_obj->restaurant,
                    $request->lang ? $request->lang : 'cz', $orders_detail_filtered, 'rest');
				$sent_sms = $this->ordersDetailRepository->sendSMSEmailReminder($email_type . '_short', app('Dingo\Api\Auth\Auth')->user(), $order_obj,
                    $order_obj->restaurant,$request->lang ? $request->lang : 'cz', $orders_detail_filtered, 'admin');
            }

            return ["success" => "Order Details updated successfully"];
        }
        return ["error" => "'orders_detail' key not found!"];
    }

    public function updateOrders(Request $request){
        $order = $request->has("order");

        if ($order){
            $order_obj = Order::find($request->order["ID_orders"]);

            if ($request->order["status"] == 6 && $order_obj->status != 2){
                $orders_detail = $order_obj->orders_detail;
                foreach ($orders_detail as $item) {
                    $item->delete();
                }
                $order_obj->delete();

            }
            else if ($order_obj->status != 2 && $order_obj->status != 4){
                $order_obj->status = $request->order["status"];
                $order_obj->comment = $request->order["comment"];
                $order_obj->persons = $request->order["persons"];
                $order_obj->pick_up = (isset($request->order["pick_up"]) && $request->order["pick_up"]) ? "Y" : "N";
                $order_obj->table_until = isset($request->order["table_until"]) ? $request->order["table_until"] : null;
                $order_obj->ID_tables = isset($request->order["ID_tables"]) ? $request->order["ID_tables"] : null;
                $order_obj->gb_discount = isset($request->order["gb_discount"]) ? $request->order["gb_discount"] : null;

                if (isset($request->order["delivery"]) && $request->order["delivery"] === true ) {
                    $order_obj->delivery_address = isset($request->order["delivery_address"]) ? $request->order["delivery_address"] : null;
                    $order_obj->delivery_phone = isset($request->order["delivery_phone"]) ? $request->order["delivery_phone"] : null;
                    $order_obj->delivery_latitude = isset($request->order["delivery_latitude"]) ? $request->order["delivery_latitude"] : null;
                    $order_obj->delivery_longitude = isset($request->order["delivery_address"]) ? $request->order["delivery_longitude"] : null;
                }
                else {
                    $order_obj->delivery_address = null;
                    $order_obj->delivery_phone = null;
                    $order_obj->delivery_latitude = null;
                    $order_obj->delivery_longitude = null;
                }
                /*if ($order_obj->gb_discount && $order_obj->gb_discount > 0){
                    $lang = DB::table('restaurant')->where( 'id', $request->order["ID_restaurant"] )->value('lang');
                    $this->quizRepository->storeQuizPrize( $request->order["ID_orders"], $request->order["gb_discount"], $request->order["prize"], $lang);
                }*/

                $order_obj->save();
            }
            $cancellation = $this->getCancellationTime($order_obj);
            $order_obj->cancellation = $cancellation["serve_at"];
            $order_obj->currency = $cancellation["currency"];
            if ($order_obj->status == 3){
                foreach ($order_obj->orders_detail as $o_detail) {
                    $o_detail = OrderDetail::find($o_detail->ID);
                    $o_detail->status = 3;
                    $o_detail->save();
                };

                $order_obj->orders_detail = $order_obj->orders_detail->sortBy("serve_at");

                $sent = $this->ordersDetailRepository->sendEmailReminder('cancel', app('Dingo\Api\Auth\Auth')->user(), $order_obj, $order_obj->restaurant,
                    $request->lang ? $request->lang : 'cz', $order_obj->orders_detail, 'user');
                $sent_rest = $this->ordersDetailRepository->sendEmailReminder('cancel', app('Dingo\Api\Auth\Auth')->user(), $order_obj, $order_obj->restaurant,
                    $request->lang ? $request->lang : 'cz', $order_obj->orders_detail, 'rest');

				$sent_sms = $this->ordersDetailRepository->sendSMSEmailReminder('cancel_short', app('Dingo\Api\Auth\Auth')->user(), $order_obj, $order_obj->restaurant, $request->lang ? $request->lang : 'cz', $order_obj->orders_detail, 'admin');

            }
            return ["success" => "Order updated successfully"];
        }
        return ["error" => "'order' key not found!"];

    }

    public function getCancellationTime($order)
    {
        $order_detail = $order->orders_detail;
        $filtered = $order_detail->filter(function($item){
            $current_time = Carbon::now();
            $serve_time = new Carbon($item->serve_at);
            $diffInMinutes = $serve_time->diffInMinutes($current_time, false);
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

    public function deleteOrder(Request $request, $orderId){
        $order_obj = Order::find($orderId);
        $order = $this->ordersDetailRepository->deleteOrder($orderId);
        if ($order){
            $response = $this->response->item($order, new OrderTransformer($this->menuListRepository));
            return $response;
        }
        $order_obj->cancellation = $this->getCancellationTime($order_obj);
        $sent = $this->ordersDetailRepository->sendEmailReminder('cancel', app('Dingo\Api\Auth\Auth')->user(), $order_obj, $order_obj->restaurant,
            $request->lang ? $request->lang : 'cz', $order_obj->orders_detail, 'user');
        $sent_rest = $this->ordersDetailRepository->sendEmailReminder('cancel', app('Dingo\Api\Auth\Auth')->user(), $order_obj, $order_obj->restaurant,
            $request->lang ? $request->lang : 'cz', $order_obj->orders_detail, 'rest');
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

    public function getSumPrice()
    {
        return $this->ordersDetailRepository->getSumPrice();
    }

    public function getOrderDetailCount()
    {
        return $this->ordersDetailRepository->getOrderDetailCount();
    }

    public function deleteSideDish($orderDetailId){
        $deleted = $this->ordersDetailRepository->removeSideDish($orderDetailId);
        $message = $deleted ? 'The side dish was successfully deleted' : 'Failed to delete side dish';

        return response()->json([ 'message'=> $message, 'id'=>$orderDetailId]);
    }

    public function printOrder(Request $request, $lang, $orderId) {
        if($lang && $lang === 'cs') {
            $lang = 'cz';
        }
        $render_data = $this->ordersDetailRepository->getPrintData($request, $orderId);
        $view = View::make('emails.order.order_new_print_'.$lang, $render_data);
        $contents = $view->render();
        return $contents;
    }
}
