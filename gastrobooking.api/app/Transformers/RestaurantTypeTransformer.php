<?php

namespace App\Transformers;

use App\Entities\RestaurantType;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class RestaurantTypeTransformer extends TransformerAbstract
{
    public function transform(RestaurantType $restaurantType)
    {
        return [
            'id' => $restaurantType->id,
            'name' => $restaurantType->name,
            'description' => $restaurantType->description,

        ];
    }

}