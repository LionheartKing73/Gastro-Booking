<?php

namespace App\Transformers;

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

class MenuTypeTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['menu_groups'];

    protected $menuListRepository;

    public function __construct(MenuListRepository $menuListRepository)
    {
        $this->menuListRepository = $menuListRepository;
    }

    public function transform(MenuType $menuType)
    {
        return [
            'id' => $menuType->ID,
            'name' => $menuType->name,
            'collapse' => $menuType->collapse
        ];
    }

    public function includeMenuGroups(MenuType $menuType)
    {
        if ($menuType->has('menu_groups')){
            return $this->collection($menuType->menu_groups, new MenuGroupTransformer($this->menuListRepository));
        }
    }

}