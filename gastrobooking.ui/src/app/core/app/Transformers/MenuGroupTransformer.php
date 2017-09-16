<?php

namespace App\Transformers;

use App\Entities\MenuGroup;
use App\Entities\MenuType;
use App\Entities\Restaurant;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */

class MenuGroupTransformer extends TransformerAbstract
{
//    protected $defaultIncludes = ['menu_sub_group'];

    public function transform(MenuGroup $menuGroup)
    {
        return [
            'id' => $menuGroup->ID,
            'name' => $menuGroup->name
        ];
    }

    public function includeMenuSubGroup(MenuGroup $menuGroup)
    {
        if ($menuGroup->has('menu_sub_group')){
            return $this->collection($menuGroup->menu_sub_group, new MenuSubGroupTransformer());
        }
    }

}