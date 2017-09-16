<?php

namespace App\Transformers;

use App\Entities\RestaurantOpen;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class RestaurantOpenTransformer extends TransformerAbstract
{
    public function transform(RestaurantOpen $restaurantOpen)
    {
        return [
            'id' => $restaurantOpen->id,
            'date' => $restaurantOpen->date,
            'm_starting_time' => $restaurantOpen->m_starting_time,
            'm_ending_time' => $restaurantOpen->m_ending_time,
            'a_starting_time' => $restaurantOpen->a_starting_time,
            'a_ending_time' => $restaurantOpen->a_ending_time,
        ];
    }

}