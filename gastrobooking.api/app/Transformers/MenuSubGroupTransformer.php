<?php

namespace App\Transformers;

use App\Entities\MenuGroup;
use App\Entities\MenuSubGroup;
use App\Entities\MenuType;
use App\Entities\Restaurant;
use App\Repositories\MenuListRepository;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */

class MenuSubGroupTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['menu_lists'];

    protected $menuListRepository;

    public function __construct(MenuListRepository $menuListRepository)
    {
        $this->menuListRepository = $menuListRepository;
    }
    
    public function transform(MenuSubGroup $menuSubGroup)
    {
        return [
            'id' => $menuSubGroup->ID,
            'name' => $menuSubGroup->name,
            'ID_menu_group' => $menuSubGroup->ID_menu_group
        ];
    }

    public function includeMenuLists(MenuSubGroup $menuSubGroup)
    {
        if ($menuSubGroup->has('menu_lists')){
            return $this->collection($menuSubGroup->menu_lists, new MenuListTransformer($this->menuListRepository));
        }
    }


}