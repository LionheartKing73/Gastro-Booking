<?php
/**
 * Created by PhpStorm.
 * RestaurantOpen: tOm_HydRa
 * Date: 9/10/16
 * Time: 12:06 PM
 */

namespace App\Repositories;

use App\Entities\RestaurantOpen;
use App\Entities\Restaurant;
use Illuminate\Http\Request;
use Webpatser\Uuid\Uuid;


class RestaurantOpenRepository
{

    public function save($request, $restaurantId){
        if ($request->has('time')){
            $times = $request->time;

            foreach ($times as $key => $value) {
                if (RestaurantOpen::where("ID_restaurant", $restaurantId)->where("date", $key)->count()){
                    $openingHour = RestaurantOpen::where("ID_restaurant", $restaurantId)->where("date", $key)->first();
                } else {
                    $openingHour = new RestaurantOpen();
                    $openingHour->ID_restaurant = $restaurantId;
                    $openingHour->date = $key;
                }

                foreach ($value as $item) {
                    if(1 !== preg_match('~[0-9]~', trim($value["m_start"]))){
                        $value["m_start"] = '';
                    }

                    if(1 !== preg_match('~[0-9]~', trim($value["m_end"]))){
                        $value["m_end"] = '';
                    }

                    if(1 !== preg_match('~[0-9]~', trim($value["a_start"]))){
                        $value["a_start"] = '';
                    }

                    if(1 !== preg_match('~[0-9]~', trim($value["a_end"]))){
                        $value["a_end"] = '';
                    }

                    $openingHour->m_starting_time = $value["m_start"];
                    $openingHour->m_ending_time = $value["m_end"];
                    $openingHour->a_starting_time = $value["a_start"];
                    $openingHour->a_ending_time = $value["a_end"];
                }
                $openingHour->save();
            }
            return Restaurant::find($restaurantId);

        }

    }

    public function find($restaurantId){
        $restaurant = Restaurant::find($restaurantId);
        $openingHours = RestaurantOpen::where('ID_restaurant', $restaurant->id)->get();
        $time = [];
        foreach ($openingHours as $openingHour) {
            $time[$openingHour->date]["m_start"] = $openingHour->m_starting_time;
            $time[$openingHour->date]["m_end"] = $openingHour->m_ending_time;
            $time[$openingHour->date]["a_start"] = $openingHour->a_starting_time;
            $time[$openingHour->date]["a_end"] = $openingHour->a_ending_time;
        }
        return ["data" => ["time" => $time]];
    }


}