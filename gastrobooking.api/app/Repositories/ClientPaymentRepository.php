<?php
/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 12:06 PM
 */

namespace App\Repositories;


use App\Entities\ClientPayment;
use App\Entities\ClientGroup;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;


class ClientPaymentRepository
{
    protected $userRepository;
    protected $clientGroupRepository;
    
    public function __construct(UserRepository $userRepository, ClientGroupRepository $clientGroupRepository)
    {
        $this->userRepository = $userRepository;
        $this->clientGroupRepository = $clientGroupRepository;
    }

    public function store($request)
    {
        $user = app('Dingo\Api\Auth\Auth')->user();
        $clientId = $user->client->ID;
        $clientPayment = new ClientPayment();
        $clientPayment->ID_client = $clientId;
        $clientPayment->own_turnover = $request["own_turnover"];
        $clientPayment->member_turnover = $request["member_turnover"];
        $clientPayment->own_remuneration = $request["own_remuneration"];
        $clientPayment->member_remuneration = $request["member_remuneration"];
        $clientPayment->pay_date = $request["pay_date"];
        $clientPayment->save();
        return $clientPayment;
    }

    public function get()
    {
        $user = app('Dingo\Api\Auth\Auth')->user();
        $clientId = $user->client->ID;
        $orders = ClientPayment::where(["ID_client" => $clientId])->get();
        return $orders;
    }

}