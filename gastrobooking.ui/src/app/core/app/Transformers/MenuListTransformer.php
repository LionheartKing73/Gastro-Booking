<?php

namespace App\Transformers;

use App\Entities\Client;
use App\Entities\Meal;
use App\Entities\MenuList;
use App\Entities\MenuSubGroup;
use App\Repositories\MenuListRepository;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class MenuListTransformer extends TransformerAbstract
{
    protected $menuListRepository;

    public function __construct(MenuListRepository $menuListRepository)
    {
        $this->menuListRepository = $menuListRepository;
    }

    public function transform(MenuList $menuList)
    {

        return [
            'ID_menu_list' => $menuList->ID,
            'name' => $menuList->name,
            'price' => (float)$menuList->price,
            'distance' => $menuList->distance? number_format($menuList->distance, 2) : "",
            'restaurant_type' => $menuList->restaurant ? $menuList->restaurant->type ? $menuList->restaurant->type->name : null : null,
            'ID_restaurant' => $menuList->restaurant->id,
            'restaurant_name' => $menuList->restaurant->name,
            'time_from' => $menuList->time_from,
            'time_to' => $menuList->time_to,
            "is_day_menu" => $menuList->is_day_menu,
            "comment" => $menuList->comment,
            "price_child"=> (float)$menuList->price_child,
            "currency" => $menuList->currency,
            "delivered" => $menuList->delivered,
            "ordered" => $this->menuListRepository->isOrdered($menuList) ? (int)$this->menuListRepository->isOrdered($menuList)->x_number : 0,
            "total_orders" => $menuList->orders_detail->count(),
            "loading" => 0,
            "prefix" => $menuList->prefix,
            "menu_subgroup_id" => $this->menuListRepository->getMenuSubGroup($menuList) ? $this->menuListRepository->getMenuSubGroup($menuList)->ID : "",
            "menu_group_id" => $this->menuListRepository->getMenuGroup($menuList) ? $this->menuListRepository->getMenuGroup($menuList)->ID : "",
            "menu_type_id" => $this->menuListRepository->getMenuType($menuList) ? $this->menuListRepository->getMenuType($menuList)->ID : "",
            "menu_subgroup_name" => $this->menuListRepository->getMenuSubGroup($menuList) ? $this->menuListRepository->getMenuSubGroup($menuList)->name : "",
            "menu_group_name" => $this->menuListRepository->getMenuGroup($menuList) ? $this->menuListRepository->getMenuGroup($menuList)->name : "",
            "menu_type_name" => $this->menuListRepository->getMenuType($menuList) ? $this->menuListRepository->getMenuType($menuList)->name : "",

        ];
    }


}