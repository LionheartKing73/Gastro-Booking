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
class NewClientGroupTransformer extends TransformerAbstract
{

    protected $clientGroupRepository;
    protected $myCircle;

    public function __construct(ClientGroupRepository $clientGroupRepository, $myCircle)
    {
        $this->clientGroupRepository = $clientGroupRepository;
        $this->myCircle = $myCircle;
    }

    public function transform(ClientGroup $clientGroup)
    {
        if ($this->myCircle){
            return [
                'ID_client' => $clientGroup->ID_client,
                'ID_grouped_client' => $clientGroup->ID_grouped_client,
                'name' => $this->clientGroupRepository->getClientName($clientGroup->ID_grouped_client),
                'connections' => $this->clientGroupRepository->getConnections($clientGroup->ID_grouped_client),
                'precedings' => $this->clientGroupRepository->getPrecedings($clientGroup->ID_grouped_client, $clientGroup->ID)
            ];
        }

        return [
            'ID_client' => $clientGroup->ID_grouped_client,
            'ID_grouped_client' => $clientGroup->ID_client,
            'name' => $this->clientGroupRepository->getClientName($clientGroup->ID_client),
            'connections' => $this->clientGroupRepository->getConnections($clientGroup->ID_client),
            'precedings' => $this->clientGroupRepository->getPrecedings($clientGroup->ID_grouped_client, $clientGroup->ID)
        ];


    }




}