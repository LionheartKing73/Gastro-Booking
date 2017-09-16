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
class ClientTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['user'];

    public function transform(Client $client)
    {
        return [
            'id' => $client->ID,
            'ID_diet' => $client->ID_diet,
            'email_new' => $client->name,
            'email_update'=> $client->email_update,
            'email_restaurant_update' => $client->email_restaurant_update,
            'address' => $client->address,
            'phone' => $client->phone,
            'lang' => $client->lang,
            'latitude' => $client->latitude,
            'longitude' => $client->longitude,
            'location' => $client->location,
            'account_number' => $client->account_number,
            'bank_code' => $client->bank_code
        ];
    }

    public function includeUser(Client $client)
    {
        if ($client->has('user')){
            return $this->item($client->user, new UserTransformer());
        }
    }

}