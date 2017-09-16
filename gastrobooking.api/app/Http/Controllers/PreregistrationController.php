<?php
/**
 * Created by Hamid Shafer, 2017-02-25
 */

namespace App\Http\Controllers;

use App\Entities\Restaurant;
use App\Repositories\RestaurantRepository;
use App\Repositories\UserRepository;
use App\Transformers\UserTransformer;
use App\Transformers\RestaurantTransformer;
use App\User;
// use UserController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests;

use Webpatser\Uuid\Uuid;
use Dingo\Api\Routing\Helpers;
use Dingo\Api\Http\Response;
use Illuminate\Support\Arr;

class PreregistrationController extends Controller
{
    use Helpers;
    protected $restaurantRepository;
    protected $userRepository;
    public $perPage = 10;

    public function __construct(UserRepository $userRepository, RestaurantRepository $restaurantRepository)
    {
        $this->restaurantRepository = $restaurantRepository;
        $this->userRepository = $userRepository;
    }

    public function queryEx($user_id = null)
    {
        $query = Restaurant::query()
            ->leftJoin('user', 'user.id', '=', 'restaurant.ID_user')
            ->leftJoin('district', 'district.id', '=', 'restaurant.ID_district')
            ->leftJoin('preregistrations', 'preregistrations.user_id', '=', 'restaurant.ID_user')
            ->where('restaurant.status', 'N')
            ->orderBy('restaurant.updated_at', 'DESC')
            ->select(
                'restaurant.id as restaurant_id',
                'restaurant.ID_user',
                'restaurant.ID_user_data',
                'restaurant.ID_user_acquire',
                'restaurant.ID_user_contract',
                'restaurant.ID_district',
                'district.country as country',
                'restaurant.name as restaurant_name',
                'restaurant.phone as restaurant_phone',
                'restaurant.email as restaurant_email',
                'restaurant.www as restaurant_www',
                'restaurant.dealer_note as restaurant_dealer_note',
                'user.name as owner_name',
                'user.phone as owner_phone',
                'user.email as owner_email',
                'preregistrations.password as owner_password'
            );
        if($user_id) {
            $query = $query->where('restaurant.ID_user_data', $user_id);
        }
        return $query;
    }

    public function all(Request $request)
    {
        $data = $this->queryEx()->get();

        return compact('data');
    }

    public function get_owner_restaurants(Request $request)
    {
        $user_id = isset($request->owner['id']) ? $request->owner['id'] : null;
        $data = $this->queryEx()->get($user_id);

        return compact('data');
    }

    public function districts(Request $request)
    {
        $districts = DB::table('district')->get();

        $countries = array_keys(Arr::pluck($districts, 'id', 'country'));

        return compact('countries', 'districts');
    }
    public function assignments(Request $request)
    {

        $result = $this->restaurantRepository->getAssignments($request);
        $result->current_page = $request->currentPage;
        return compact('result');
    }
    public function userStatus(Request $request)
    {
        $result = DB::table('employee')->where('id_user', $request->id)->count();

        return $result;
    }
    public function turnovers(Request $request)
    {
        $result = $this->restaurantRepository->getTurnovers($request, false);
        return $result;
    }
    public function sumturnovers(Request $request)
    {
        $result = $this->restaurantRepository->getTurnovers($request, true);
        return $result;
    }

    public function updateDealerForAssignmet( Request $request){
        $response = [
            "success" => false,
            "message" => "",
        ];

        $res = Restaurant::where('id', $request->id)
            ->update( ['id_user_dealer' =>( $request->status == true ? $request->user_id : NULL )]);

        if ($res) $response["success"] = true;

        return $response;
    }
    public function updateContractForAssignmet( Request $request ){
        $response = [
            "success" => false,
            "message" => "",
        ];

        $res = Restaurant::where('id', $request->id)
            ->update( ['id_user_contract' =>( $request->status == true ? $request->user_id : NULL )]);
        if ($res) $response["success"] = true;

        return $response;
    }   

    public function store(Request $request)
    {
        $response = [
            "success" => false,
            "message" => "",
        ];

        if (!$request->has("owner"))
        {
            $response['message'] = "Request field 'owner' is missing!";
            return $response;
        }
        if (!$request->has("restaurants"))
        {
            $response['message'] = "Request field 'restaurants' is missing!";
            return $response;
        }

        $update = isset($request->owner['id']);

        if (!$update && $this->userRepository->userExists($request->owner))
        {
            $response['message'] = "User already exists!";
            return $response;
        }

        DB::beginTransaction();

        // store owner user
        $owner = $this->userRepository->storePreregOwner($request->owner, "restaurant");
        // $this->userController->sendEmailReminder($owner);

        // store restaurant
        $user = app('Dingo\Api\Auth\Auth')->user();
        $saved_restaurants = [];
        foreach ($request->restaurants as $restaurant) {
            $restaurantObj = $this->restaurantRepository->saveAsPreregistration($restaurant, $owner, $user->id);
            $saved_restaurants[] = $restaurantObj->toArray();
        }

        DB::commit();

        $response['success'] = true;
        $response['message'] = $update ? "Preregistration update successful." : "Preregistration successful.";
        $response['owner'] = $owner->toArray();
        $response['restaurants'] = $saved_restaurants;

        return $response;
    }
}
