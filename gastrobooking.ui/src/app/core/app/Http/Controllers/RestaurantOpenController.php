<?php

namespace App\Http\Controllers;

use App\Entities\RestaurantOpen;
use Illuminate\Http\Request;

use App\Http\Requests;
use Webpatser\Uuid\Uuid;

class RestaurantOpenController extends Controller
{
    public function all(){
        return RestaurantOpen::get();
    }

    public function item($RestaurantOpen_id){
        $RestaurantOpen = RestaurantOpen::find($RestaurantOpen_id);
        return $RestaurantOpen;
    }


    public function store($input, $restaurantId){
        $restaurantOpen = new RestaurantOpen();
        $restaurantOpen->id = $id = Uuid::generate(4);
        $restaurantOpen->ID_restaurant = $restaurantId;
        $restaurantOpen->date = $input["date"] ? $input["date"] : null;
        $restaurantOpen->m_starting_time = $input["m_starting_time"] ? $input["m_starting_time"] : null;
        $restaurantOpen->m_ending_time = $input["m_ending_time"] ? $input["m_ending_time"] : null;
        $restaurantOpen->a_ending_time = $input["a_ending_time"] ? $input["a_ending_time"] : null;
        $restaurantOpen->a_starting_time = $input["a_starting_time"] ? $input["a_starting_time"] : null;
        $restaurantOpen->save();
        return RestaurantOpen::find($id);

    }

    public function update(Request $request){

    }

    public function delete($RestaurantOpen_id){
        $RestaurantOpen = RestaurantOpen::find($RestaurantOpen_id);
        $RestaurantOpen->delete();
        return $RestaurantOpen;

    }
}
