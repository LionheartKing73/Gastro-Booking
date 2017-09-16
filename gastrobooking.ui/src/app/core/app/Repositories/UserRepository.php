<?php
/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 12:06 PM
 */

namespace App\Repositories;


use App\Entities\Restaurant;
use App\Transformers\RestaurantTransformer;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;


class UserRepository
{
    public function store($user_request, $profile_type){
        $user = new User();
        $user->name = $user_request["name"];
        $user->email = $user_request["email"];
        $user->password = Hash::make($user_request["password"]);
        $user->profile_type = $profile_type;
        $user->save();
        return User::where("email", $user->email)->first();

    }

    public function all(){
        return User::get();
    }

    public function getCurrentUser(){
        $user = app('Dingo\Api\Auth\Auth')->user();
        return $user;
    }

    public function detail($user_id){
        $user = User::find($user_id);
        return $user;
    }

    public function getRestaurants($user_id){
        $restaurant = Restaurant::where('ID_user', $user_id)->latest()->get();
        return $restaurant;
    }

    public function getCurrentRestaurant($user_id){
        $user = User::find($user_id);
        if ($user->restaurants){
            return $user->restaurants()->latest()->with('photos')->first();
        }
    }

    public function userExists($user_input){
        if (User::where('email', $user_input["email"])->count()){
            return true;
        }
        return false;
    }

    public function deleteRestaurants($user_id){
        $restaurants = Restaurant::where("ID_user", $user_id)->delete();
        return $restaurants;
    }

}