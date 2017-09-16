<?php

namespace App\Http\Controllers;

use App\Entities\Diet;
use App\Transformers\DietTransformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

use App\Http\Requests;

class DietController extends Controller
{
    use Helpers;
    public function find($id){
        $type = Diet::find($id);
        $response = $this->response->item($type, new DietTransformer());
        return $response;
    }

    public function all(){
        $diets = Diet::all()->sortBy("cust_order");
        $response = $this->response->collection($diets, new DietTransformer());
        return $response;
    }
}
