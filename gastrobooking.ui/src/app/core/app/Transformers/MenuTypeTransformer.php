<?php

namespace App\Transformers;

use App\Entities\MenuType;
use App\Entities\Restaurant;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */

class MenuTypeTransformer extends TransformerAbstract
{
//    protected $defaultIncludes = ['menu_group'];

    public function transform(MenuType $menuType)
    {
        return [
            'id' => $menuType->ID,
            'name' => $menuType->name
        ];
    }

    public function includeMenuGroup(MenuType $menuType)
    {
        if ($menuType->has('menu_group')){
            return $this->collection($menuType->menu_group, new MenuGroupTransformer());
        }
    }

}