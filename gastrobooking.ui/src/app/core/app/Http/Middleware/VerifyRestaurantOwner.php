<?php

namespace App\Http\Middleware;

use App\Entities\Restaurant;
use Closure;
use Mockery\Matcher\Not;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class VerifyRestaurantOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = app('Dingo\Api\Auth\Auth')->user();
        $restaurant = Restaurant::find($request->restaurantId);
        //dd($restaurant->ID_user, $user->toArray());
        if(empty($restaurant)){
            $response = array("status"=>404,"message"=>"Not found");
            return response($response,404);
        }
        if ($restaurant->ID_user != $user->id) {
            $response = array("status"=>401,"message"=>"Unauthorized");
            return response($response,401);
        }
        return $next($request);
    }
}
