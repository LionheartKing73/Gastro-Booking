<?php

namespace App\Http\Controllers;

use App\Entities\MenuList;
use App\Entities\MenuSubGroup;
use App\Entities\Restaurant;
use App\Entities\RestaurantOpen;
use App\Repositories\MenuListRepository;
use App\Repositories\RestaurantOpenRepository;
use App\Repositories\RestaurantRepository;
use App\Transformers\DetailedRestaurantTransformer;
use App\Transformers\MenuGroupTransformer;
use App\Transformers\MenuListTransformer;
use App\Transformers\MenuSubGroupTransformer;
use App\Transformers\MenuTypeTransformer;
use App\Transformers\RestaurantTransformer;
use App\Transformers\RestaurantTransformerWithLogo;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use Webpatser\Uuid\Uuid;
use Dingo\Api\Routing\Helpers;

class RestaurantController extends Controller
{
    use Helpers;
    protected $restaurantRepository;
    protected $restaurantOpenRepository;
    protected $menuListRepository;
    public $itemPerPage = 5;

    public function __construct(RestaurantRepository $restaurantRepository, RestaurantOpenRepository $restaurantOpen, MenuListRepository $menuListRepository)
    {
        $this->restaurantRepository = $restaurantRepository;
        $this->restaurantOpenRepository = $restaurantOpen;
        $this->menuListRepository = $menuListRepository;
    }

    public function all(Request $request){
        $restaurant = $this->restaurantRepository->all($request, $this->itemPerPage);
        $response = $this->response->paginator($restaurant, new RestaurantTransformerWithLogo($this->restaurantRepository));
        return $response;
    }

    public function getRestaurantsNearby(Request $request){
        $restaurant = $this->restaurantRepository->getRestaurantsNearby($request->currentPosition);
        $response = $this->response->collection($restaurant, new RestaurantTransformerWithLogo($this->restaurantRepository));
        return $response;
    }


    public function item($restaurant_id){
        $restaurant = $this->restaurantRepository->find($restaurant_id);
        $response = $this->response->item($restaurant, new RestaurantTransformer());
        return $response;
    }

    public function detail($restaurant_id){
        $restaurant = $this->restaurantRepository->find($restaurant_id);
        $response = $this->response->item($restaurant, new RestaurantTransformer());
        return $response;
//        return $this->restaurantRepository->getMenuTypes($restaurant_id);
//        return $this->restaurantRepository->getMenuJoinValues($restaurant_id);
//        $collection = $this->restaurantRepository->getCollections($restaurant_id);
//        return $collection;
//        return $response;
    }

    public function findByUuid($uuid){
        $restaurant = Restaurant::where('uuid', $uuid)->first();
        return $restaurant;
    }

    public function store(Request $request){
        $user = app('Dingo\Api\Auth\Auth')->user();
        $restaurant = $this->restaurantRepository->store($request, $user);
        $response = $this->response->item($restaurant, new RestaurantTransformer());
        return $response;
    }

    public function delete($restaurant_id){
        $restaurant = $this->restaurantRepository->delete($restaurant_id);
        $response = $this->response->item($restaurant, new RestaurantTransformer());
        return $response;

    }

    public function updateOpeningHours(Request $request, $restaurantId){
        $restaurant = $this->restaurantOpenRepository->save($request, $restaurantId);
        $response = $this->response->item($restaurant, new RestaurantTransformer());
        return $response;
    }

    public function getOpeningHours($restaurantId){
        return $this->restaurantOpenRepository->find($restaurantId);
    }

    public function getMenuTypes($restaurantId)
    {
        $menu_types = $this->restaurantRepository->getMenuTypes($restaurantId);
        $response = $this->response->collection($menu_types, new MenuTypeTransformer());
        return $response;
    }
    public function getMenuGroups($restaurantId, $menuTypeId)
    {
        $menu_groups = $this->restaurantRepository->getMenuGroups($restaurantId, $menuTypeId);
        $response = $this->response->collection($menu_groups, new MenuGroupTransformer());
        return $response;
    }
    public function getMenuSubGroups($restaurantId, $menuGroupId)
    {
        $menu_subgroups = $this->restaurantRepository->getMenuSubGroups($restaurantId, $menuGroupId);
        $response = $this->response->collection($menu_subgroups, new MenuSubGroupTransformer());
        return $response;
    }
    public function getMenuLists($restaurantId, $menuSubGroupId)
    {
        $menu_lists = $this->restaurantRepository->getMenuListsHelper($restaurantId, $menuSubGroupId);
        $response = $this->response->collection($menu_lists, new MenuListTransformer($this->menuListRepository));
        return $response;
    }
}
