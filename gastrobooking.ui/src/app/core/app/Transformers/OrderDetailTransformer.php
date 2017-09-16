<?php

namespace App\Transformers;

use App\Entities\MenuList;
use App\Entities\OrderDetail;
use App\Entities\Photo;
use App\Repositories\MenuListRepository;
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
    public $menuListRepository;

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
            "serve_at" => $orderDetail->serve_at,
            "visible" => 0,
            'order_by_side_dish' => $orderDetail->order_by_side_dish

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
            return $this->collection($orderDetail->sideDish, new OrderDetailTransformer($this->menuListRepository));
        }
    }




}