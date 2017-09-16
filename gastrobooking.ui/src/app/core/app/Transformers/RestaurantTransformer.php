<?php

namespace App\Transformers;

use App\Entities\Restaurant;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class RestaurantTransformer extends TransformerAbstract
{
    protected $defaultIncludes = ['photos', 'openingHours'];

    public function transform(Restaurant $restaurant)
    {
        return [
            'id' => $restaurant->id,
            'ID_restaurant_type' => $restaurant->type ? $restaurant->type->name: "",
            'name' => $restaurant->name,
            'type' => $restaurant->type ? $restaurant->type->name: "",
            'email' => $restaurant->email,
            'www' => $restaurant->www,
            'phone' => $restaurant->phone,
            'street' => $restaurant->street,
            'city' => $restaurant->city,
            'post_code' => $restaurant->post_code,
            'address_note' => $restaurant->address_note,
            'latitude' => $restaurant->latitude,
            'longitude' => $restaurant->longitude,
            'accept_payment' => $restaurant->accept_payment,
            'company_number' => $restaurant->company_number,
            'company_tax_number' => $restaurant->company_tax_number,
            'account_number' => $restaurant->account_number,
            'short_descr' => $restaurant->short_descr,
            'long_descr' => $restaurant->long_descr,
            'bank_code' => $restaurant->bank_code,
            'distance' => $restaurant->distance ? number_format($restaurant->distance, 2) : "",



        ];
    }

    public function includePhotos(Restaurant $restaurant)
    {
        if ($restaurant->has('photos')){
            return $this->collection($restaurant->photos, new PhotoTransformer());
        }
    }

    public function includeOpeningHours(Restaurant $restaurant)
    {
        if ($restaurant->has('openingHours')){
            return $this->collection($restaurant->openingHours, new RestaurantOpenTransformer());
        }
    }

}