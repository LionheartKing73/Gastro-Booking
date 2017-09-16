<?php

namespace App\Http\Controllers;

use App\Entities\KitchenType;
use App\Transformers\KitchenTypeTransformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

use App\Http\Requests;

class KitchenTypeController extends Controller
{
    use Helpers;
    public function all()
    {
        $kitchenType = KitchenType::where("lang", "ENG")->get()->sortBy('cust_order');
        return $this->response->collection($kitchenType, new KitchenTypeTransformer());
    }
}
