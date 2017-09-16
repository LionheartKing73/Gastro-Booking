<?php

namespace App\Http\Controllers;

use App\Entities\Restaurant;
use App\Repositories\MenuListRepository;
use App\Transformers\MenuListTransformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

use App\Http\Requests;

class MenuListController extends Controller
{
    use Helpers;
    protected $menuListRepository;

    public function __construct(MenuListRepository $menuListRepository)
    {
        $this->menuListRepository = $menuListRepository;
    }
    public function all(Request $request){
        $menu_lists = $this->menuListRepository->all($request);
        $response = $this->response->paginator($menu_lists, new MenuListTransformer($this->menuListRepository));
        return $response;
    }

    public function getTasted()
    {
        $menu_lists = $this->menuListRepository->getTasted();
        $response = $this->response->collection($menu_lists, new MenuListTransformer($this->menuListRepository));
        return $response;
    }

    public function getPromotionsNearby(Request $request)
    {
        $menu_lists = $this->menuListRepository->getPromotionsNearby($request->currentPosition);
//        return $menu_lists;
        $response = $this->response->collection($menu_lists, new MenuListTransformer($this->menuListRepository));
        return $response;
    }

}
