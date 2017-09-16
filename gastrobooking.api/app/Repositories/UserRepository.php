<?php
/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 12:06 PM
 */

namespace App\Repositories;


use App\Entities\Preregistration;
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
        // Changed by Hamid Shafer, 2017-02-25
        $user->phone = isset($user_request["phone"]) ? $user_request["phone"] : null;
        $user->profile_type = $profile_type;
        $user->save();
        return User::where("email", $user->email)->first();

    }

    // Added by Hamid Shafer, 2017-02-27
    public function storePreregOwner($user_request, $profile_type)
    {
        if (isset($user_request['id']) && !empty($user_request['id']))
            $user = User::find($user_request['id']);
        else
            $user = new User();
        $user->name = $user_request["name"];
        $user->email = $user_request["email"];
        if (isset($user_request["password"]))
            $user->password = Hash::make($user_request["password"]);
        $user->phone = isset($user_request["phone"]) ? $user_request["phone"] : null;
        $user->profile_type = $profile_type;
        $user->save();

        if (isset($user_request["password"]) && $user_request["password"] != '') {
            $user->preregistration()->updateOrCreate([
                'password' => $user_request["password"]
            ]);
        }

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
