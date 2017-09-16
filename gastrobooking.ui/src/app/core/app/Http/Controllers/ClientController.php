<?php

namespace App\Http\Controllers;

use App\Repositories\ClientGroupRepository;
use App\Transformers\ClientGroupTransformer;
use App\Transformers\ClientRequestTransformer;
use App\Transformers\ClientTransformer;
use App\Transformers\ClientTransformerSmall;
use App\Transformers\NewClientGroupTransformer;
use App\Transformers\UserTransformer;
use App\User;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use App\Repositories\ClientRepository;

use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    use Helpers;
    protected $clientRepository;
    protected $clientGroupRepository;

    public function __construct(ClientRepository $clientRepository, ClientGroupRepository $clientGroupRepository)
    {
        $this->clientRepository = $clientRepository;
        $this->clientGroupRepository = $clientGroupRepository;
    }

    public function item($clientId){
        $client = $this->clientRepository->item($clientId);
        $response = $this->response->item($client, new ClientTransformer());
        return $response;

    }

    public function store(Request $request){
        $user = $this->clientRepository->store($request);
        if (!$user){
            return ["error" => "User already exists!"];
        }
        $this->sendEmailReminder($user);
        $response = $this->response->item($user, new UserTransformer());

        return $response;
    }

    public function update(){
        $client = $this->clientRepository->update();
        $response = $this->response->item($client, new ClientTransformer());
        return $response;
    }

    public function delete(){
        $client = $this->clientRepository->delete();
        $response = $this->response->item($client, new ClientTransformer());
        return $response;
    }

    public function all(Request $request){
        $clients = $this->clientRepository->all($request);
        $response = $this->response->collection($clients, new ClientTransformerSmall());
        return $response;
    }

    public function addFriends(Request $request){
        $user = app('Dingo\Api\Auth\Auth')->user();
        $friends = $this->clientGroupRepository->store($request, $user);
        $response = $this->response->item($friends, new ClientTransformerSmall());
        return $response;
    }

    public function getFriends(){
        $user = app('Dingo\Api\Auth\Auth')->user();
        $friends = $this->clientGroupRepository->getFriends($user->client->ID, 'Y');
        if ($friends){
            return $this->response->collection($friends, new ClientGroupTransformer($this->clientGroupRepository));
        }
        return ["error" => "No friends found!"];
    }

    public function getOtherFriends(){
        $user = app('Dingo\Api\Auth\Auth')->user();
        $friends = $this->clientGroupRepository->getFriends($user->client->ID, 'Y');
        if ($friends){
            return $this->response->collection($friends, new ClientGroupTransformer($this->clientGroupRepository));
        }
        return ["error" => "No friends found!"];
    }

    public function getFriendsInMyCircle(){
        $user = app('Dingo\Api\Auth\Auth')->user();
        $friends = $this->clientGroupRepository->getFriendsInMyCircle($user->client->ID);
        if ($friends && count($friends)){
            return $this->response->collection($friends, new NewClientGroupTransformer($this->clientGroupRepository, true));
        }
        return ["error" => "No friends found!"];
    }

    public function getFriendsFromOtherCircle(){
        $user = app('Dingo\Api\Auth\Auth')->user();
        $friends = $this->clientGroupRepository->getFriendsFromOtherCircle($user->client->ID);
        if ($friends && count($friends)){
            return $this->response->collection($friends, new NewClientGroupTransformer($this->clientGroupRepository, false));
        }
        return ["error" => "No friends found!"];
    }

    public function getFriendRequests(){
        $user = app('Dingo\Api\Auth\Auth')->user();
        $friends = $this->clientGroupRepository->getFriendRequests($user->client->ID);
        if ($friends){
            return $this->response->collection($friends, new ClientRequestTransformer($this->clientGroupRepository));
        }
        return ["error" => "No friend Requests found!"];
    }

    public function getSentFriendRequests(){
        $user = app('Dingo\Api\Auth\Auth')->user();
        $friends = $this->clientGroupRepository->getSentRequests($user->client->ID);
        if ($friends){
            return $this->response->collection($friends, new ClientGroupTransformer($this->clientGroupRepository));
        }
        return ["error" => "No sent friend requests found!"];
    }

    public function respond(Request $request){
        if ($request->has("response")){
            $client = app('Dingo\Api\Auth\Auth')->user()->client;
            $client_group = $this->clientGroupRepository->respond($client->ID, $request->response["ID_grouped_client"], $request->response["response"]);
            if ($client_group){
                return $this->response->item($client_group, new ClientGroupTransformer($this->clientGroupRepository));
            }
            return ["error" => "No record found!"];
        }
    }

    public function sendEmailReminder(User $user)
    {
        Mail::send('emails.client', ['user' => $user], function ($m) use($user){
            $m->from('cesko@gastro-booking.com', "Gastro Booking");
            $m->to($user->email, $user->name)->subject('Gastro Booking registration successful');
        });

        return ['message' => "Email sent"];
    }

}
