<?php

namespace App\Transformers;

use App\Entities\Meal;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class MealTransformer extends TransformerAbstract
{
    public function transform(Meal $meal)
    {
        return [
            'id' => $meal->id,
            'name' => $meal->name,

        ];
    }

}