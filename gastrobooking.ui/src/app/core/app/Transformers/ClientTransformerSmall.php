<?php

namespace App\Transformers;

use App\Entities\Client;
use App\Entities\Meal;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class ClientTransformerSmall extends TransformerAbstract
{

    public function transform(Client $client)
    {
        return [
            'id' => $client->ID,
            'name' => $client->user ? $client->user->name: "",
            'email' => $client->user ? $client->user->email: "",


        ];
    }


}