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

    public function transform(MenuSubGroup $menuSubGroup)
    {
        return [
            'id' => $menuSubGroup->ID,
            'name' => $menuSubGroup->name
        ];
    }


}