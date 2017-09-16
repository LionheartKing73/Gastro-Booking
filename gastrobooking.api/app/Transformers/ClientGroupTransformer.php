<?php

namespace App\Transformers;

use App\Entities\Client;
use App\Entities\ClientGroup;
use App\Entities\Meal;
use App\Repositories\ClientGroupRepository;
use League\Fractal\TransformerAbstract;

/**
 * Created by PhpStorm.
 * User: tOm_HydRa
 * Date: 9/10/16
 * Time: 11:38 AM
 */
class ClientGroupTransformer extends TransformerAbstract
{

    protected $clientGroupRepository;

    public function __construct(ClientGroupRepository $clientGroupRepository)
    {
        $this->clientGroupRepository = $clientGroupRepository;
    }

    public function transform(ClientGroup $clientGroup)
    {
        $client = app('Dingo\Api\Auth\Auth')->user()->client;

        return [
            'id' => $clientGroup->ID_grouped_client == $client->ID? $clientGroup->ID_client : $clientGroup->ID_grouped_client,
            'name' => $this->clientGroupRepository->getClientName($clientGroup->ID_grouped_client == $client->ID? $clientGroup->ID_client : $clientGroup->ID_grouped_client),
            'connections' => $this->clientGroupRepository->getConnections($clientGroup->ID_grouped_client == $client->ID? $clientGroup->ID_client : $clientGroup->ID_grouped_client)
        ];
    }




}