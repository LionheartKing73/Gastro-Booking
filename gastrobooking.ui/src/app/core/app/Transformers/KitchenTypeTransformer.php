<?php

namespace App\Transformers;

use App\Entities\KitchenType;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class KitchenTypeTransformer extends TransformerAbstract
{
    public function transform(KitchenType $kitchenType)
    {
        return [
            'id' => $kitchenType->ID,
            'name' => $kitchenType->name,

        ];
    }

}