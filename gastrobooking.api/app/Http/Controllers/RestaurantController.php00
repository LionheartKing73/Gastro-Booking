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
        $restaurant = $this->restaurantRepository->getRestaurantsNearby($request, $request->currentPosition);
        $response = $this->response->collection($restaurant, new RestaurantTransformerWithLogo($this->restaurantRepository));
        return $response;
    }

    public function getActiveRestaurants(Request $request){
        $restaurant = $this->restaurantRepository->getActiveRestaurants($request);
        $response = $this->response->collection($restaurant, new RestaurantTransformerWithLogo($this->restaurantRepository));
        return $response;
    }

    public function confirmSelf($restId)
    {
      # code...
      $user = app('Dingo\Api\Auth\Auth')->user();
      $restaurant = Restaurant::find($restId);
      //dd($restaurant->ID_user, $user->toArray());
      if(empty($restaurant)){
          return ["error" => "No"];
      }
      if ($restaurant->ID_user != $user->id) {
          return ["error" => "No"];
      }
      return ["error" => "success"];
    }
    
    public function item($restaurant_id){
        $restaurant = $this->restaurantRepository->find($restaurant_id);

        if($restaurant) {
            $restaurant['password_added'] = $restaurant->password?true:false;
        }

        $response = $this->response->item($restaurant, new RestaurantTransformer());
        return $response;
    }

    public function detail($restaurant_id){
        $restaurant = $this->restaurantRepository->find($restaurant_id);
        if(empty($restaurant)){
            return response("Not found",404);
        }
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

    public function syncservown(Request $request){
        $response = $this->restaurantRepository->updateSyncServOwn($request->input('id'));
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

    public function organizeMenu(Request $request, $restaurantId){
        $menu_types = $this->restaurantRepository->organizeMenu($request, $restaurantId);
        $response = $this->response->collection($menu_types, new MenuTypeTransformer($this->menuListRepository));
        return $response;
    }

    public function getOpeningHours($restaurantId){
        return $this->restaurantOpenRepository->find($restaurantId);
    }

//    public function getMenuTypes($restaurantId)
//    {
//        $menu_types = $this->restaurantRepository->getMenuTypes($restaurantId);
//        $response = $this->response->collection($menu_types, new MenuTypeTransformer());
//        return $response;
//    }
//    public function getMenuGroups($restaurantId, $menuTypeId)
//    {
//        $menu_groups = $this->restaurantRepository->getMenuGroups($restaurantId, $menuTypeId);
//        $response = $this->response->collection($menu_groups, new MenuGroupTransformer());
//        return $response;
//    }
//    public function getMenuSubGroups($restaurantId, $menuGroupId)
//    {
//        $menu_subgroups = $this->restaurantRepository->getMenuSubGroups($restaurantId, $menuGroupId);
//        $response = $this->response->collection($menu_subgroups, new MenuSubGroupTransformer());
//        return $response;
//    }
    public function getMenuLists(Request $request, $restaurantId, $menuSubGroupId)
    {
        $menu_lists = $this->restaurantRepository->getMenuListsHelper($request, $restaurantId, $menuSubGroupId);
        $response = $this->response->collection($menu_lists, new MenuListTransformer($this->menuListRepository));
        return $response;
    }
    public function getMenuGroupAndSubGroupId($restaurantId){
        $menu_lists = Restaurant::find($restaurantId)->menu_lists;
        $menu_list = $menu_lists->sortBy('cust_order')->first();
        if ($menu_list){
            return $this->response->item($menu_list, new MenuListTransformer($this->menuListRepository));
        }
        return ["error" => "No menu list found!"];
    }

    public function menuLists(Request $request ,$restaurantId){
        if($request->has('is_day_menu')){
            $menu_lists = $this->restaurantRepository->getMenuOfTheDay($request, $restaurantId);
            //dd($menu_lists);
            $response = $this->response->collection($menu_lists, new MenuListTransformer($this->menuListRepository));
            return $response;
        }
        return;
    }
}
