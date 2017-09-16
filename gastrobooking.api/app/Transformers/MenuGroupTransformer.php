<?php

namespace App\Transformers;

use App\Entities\MenuGroup;
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

class MenuGroupTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['menu_subgroups'];

    protected $menuListRepository;

    public function __construct(MenuListRepository $menuListRepository)
    {
        $this->menuListRepository = $menuListRepository;
    }

    public function transform(MenuGroup $menuGroup)
    {
        return [
            'id' => $menuGroup->ID,
            'name' => $menuGroup->name,
            'ID_menu_type' => $menuGroup->ID_menu_type
        ];
    }

    public function includeMenuSubGroups(MenuGroup $menuGroup)
    {
        if ($menuGroup->has('menu_subgroups')){
            return $this->collection($menuGroup->menu_subgroups, new MenuSubGroupTransformer($this->menuListRepository));
        }
    }

}