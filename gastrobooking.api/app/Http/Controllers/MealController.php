<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class MealController extends Controller
{
    public function all(){
//        return Restaurant::get();
        return ["tom"=>"hydra"];
    }

    public function item(Restaurant $restaurant){
        return $restaurant;
    }

    public function store(Request $request){

    }

    public function update(Request $request){

    }

    public function delete(Restaurant $restaurant){

    }
}
