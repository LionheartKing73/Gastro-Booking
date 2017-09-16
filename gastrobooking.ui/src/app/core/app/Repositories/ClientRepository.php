<?php
/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 12:06 PM
 */

namespace App\Repositories;


use App\Entities\Client;
use App\Entities\ClientGroup;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;


class ClientRepository
{
    protected $userRepository;
    protected $clientGroupRepository;
    
    public function __construct(UserRepository $userRepository, ClientGroupRepository $clientGroupRepository)
    {
        $this->userRepository = $userRepository;
        $this->clientGroupRepository = $clientGroupRepository;
    }

    public function item($clientId){
        return Client::find($clientId);
    }

    public function store($request)
    {
        $user_exists = $this->userRepository->userExists($request->user);
        if ($user_exists){
            return false;
        }
        $user = $this->saveUser($request->user);
        $request_client = $request->client;
        $client = new Client();
        $client->ID_user = $user->id;
        $client->email_new = $request_client["email_new"] ? 1 : 0;
        $client->email_update = $request_client["email_update"] ? 1 : 0;
        $client->email_restaurant_update = $request_client["email_restaurant_update"] ? 1 : 0;
        $client->save();
        return $user;
    }
    
    public function saveUser($user){
        return $this->userRepository->store($user, "client");
    }

    public function all($request)
    {
        $user = app('Dingo\Api\Auth\Auth')->user();
        $friends = ClientGroup::where("ID_client", $user->client->ID)->where("approved", "Y")->orWhere("approved", "R")->orWhere("approved", "N");
        $friend_ids = $friends->pluck("ID_grouped_client");
        $friends2 = ClientGroup::where("ID_grouped_client", $user->client->ID)->where("approved", "Y")->orWhere("approved", "R")->orWhere("approved", "N");
        $friend_ids2 = $friends2->pluck("ID_client");
        return Client::whereNotIn("ID", $friend_ids)->whereNotIn("ID", $friend_ids2)->where('ID','<>',$user->client->ID)->searchClient($request)->get();
    }

    public function getFriends(){
        $user = app('Dingo\Api\Auth\Auth')->user();

        return $user;
    }

    public function getCurrentUser(){
        $user = app('Dingo\Api\Auth\Auth')->user();
        return $user;
    }

}