<?php

namespace App\Http\Controllers;

use App\Repositories\MenuListRepository;
use App\Repositories\RestaurantOpenRepository;
use App\Repositories\RestaurantRepository;

use App\Transformers\MenuGroupTransformer;
use App\Transformers\MenuListTransformer;
use App\Transformers\MenuSubGroupTransformer;
use App\Transformers\MenuTypeTransformer;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;

class FrameController extends Controller {

	use Helpers;

	public function __construct(RestaurantRepository $restaurantRepository, RestaurantOpenRepository $restaurantOpen, MenuListRepository $menuListRepository)
    {
        $this->restaurantRepository = $restaurantRepository;
        $this->restaurantOpenRepository = $restaurantOpen;
        $this->menuListRepository = $menuListRepository;
    }

	public function handle(Request $request, $restaurantId)
	{
		$menuSubGroupId = 38;
		$items = $this
			->restaurantRepository
			->getMenuListsHelper($request, $restaurantId, $request->get('sub'));

		return view('frame', compact('items'));
	}
}