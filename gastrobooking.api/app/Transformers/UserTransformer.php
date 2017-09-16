<?php

namespace App\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class UserTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['restaurants'];

    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'profile_type' => $user->profile_type,
            'phone' => $user->phone

        ];
    }

    public function includeRestaurants(User $user)
    {
        if ($user->has('restaurants')){
            return $this->collection($user->restaurants, new RestaurantTransformer());
        }
    }
}