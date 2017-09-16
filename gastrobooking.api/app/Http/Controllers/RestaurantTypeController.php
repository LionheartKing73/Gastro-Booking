<?php

namespace App\Http\Controllers;

use App\Entities\RestaurantType;
use App\Transformers\RestaurantTypeTransformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

use App\Http\Requests;

class RestaurantTypeController extends Controller
{
    use Helpers;
    public function find($id){
        $type = RestaurantType::find($id);
        $response = $this->response->item($type, new RestaurantTypeTransformer());
        return $response;
    }

    public function all(){
        $types = RestaurantType::where("lang", "ENG")->get()->sortBy('cust_order');
        $response = $this->response->collection($types, new RestaurantTypeTransformer());
        return $response;
    }
}
