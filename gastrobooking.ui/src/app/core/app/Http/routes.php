<?php

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

    $api->group(['prefix' => 'oauth'], function ($api) {
        $api->post('authorize', 'App\Http\Controllers\Auth\AuthController@authorizeClient');
    });
    $api->post('user', 'App\Http\Controllers\UserController@store');
    $api->post('client', 'App\Http\Controllers\ClientController@store');
    $api->get('email', 'App\Http\Controllers\UserController@sendEmailReminder');
    $api->get('user_exists', 'App\Http\Controllers\UserController@userExistsRoute');
    $api->get('restaurantTypes', 'App\Http\Controllers\RestaurantTypeController@all');
    $api->get('kitchenTypes', 'App\Http\Controllers\KitchenTypeController@all');
    $api->get('tasted', 'App\Http\Controllers\MenuListController@getTasted');
    $api->post('promotionsNearby', 'App\Http\Controllers\MenuListController@getPromotionsNearby');
    $api->post('restaurantsNearby', 'App\Http\Controllers\RestaurantController@getRestaurantsNearby');
    $api->post('restaurants', 'App\Http\Controllers\RestaurantController@all');
    $api->get('restaurant/{restaurantId}/detail', 'App\Http\Controllers\RestaurantController@detail');
    $api->post('menu_lists', 'App\Http\Controllers\MenuListController@all');
    $api->get('restaurant/{restaurantId}/menu_types', 'App\Http\Controllers\RestaurantController@getMenuTypes');
    $api->get('restaurant/{restaurantId}/menu_groups/{menuTypeId}', 'App\Http\Controllers\RestaurantController@getMenuGroups');
    $api->get('restaurant/{restaurantId}/menu_subgroups/{menuGroupId}', 'App\Http\Controllers\RestaurantController@getMenuSubGroups');
    $api->get('restaurant/{restuarantId}/menu_lists/{menuSubGroupId}', 'App\Http\Controllers\RestaurantController@getMenuLists');
    $api->get('orders_detail_count', 'App\Http\Controllers\OrderDetailController@getOrderDetailCount');
    $api->get('restaurants', 'App\Http\Controllers\RestaurantController@all');

    $api->get('test', 'App\Http\Controllers\TestController@test');

    $api->group(['namespace' => 'App\Http\Controllers', 'middleware' => ['api.auth']], function ($api) {
        // User Routes
        $api->get('user/{user_id}', 'UserController@detail');
        $api->get('user/{user_id}/restaurants', 'UserController@getRestaurants');
        $api->get('user/{user_id}/restaurant', 'UserController@getCurrentRestaurant');
        $api->post('user/{user_id}/restaurant', 'UserController@saveRestaurant');
        $api->get('users', 'UserController@all');
        $api->delete('user/{user_id}', 'UserController@delete');
        $api->delete('user/{user_id}/restaurants', 'UserController@deleteRestaurants');
        $api->get('user', 'UserController@getCurrentUser');

        // Restaurant Routes
        $api->post('restaurant', 'RestaurantController@store');


        $api->get('restaurant/uuid/{uuid}', 'RestaurantController@findByUuid');
        $api->put('restaurant', 'RestaurantController@store');
        $api->delete('restaurant/{restaurantId}', 'RestaurantController@delete');
        $api->put('restaurant/{restaurantId}/open', 'RestaurantController@updateOpeningHours');
        $api->get('restaurant/{restaurantId}/open', 'RestaurantController@getOpeningHours');

        $api->group(['middleware' => ['restaurant-authorization']], function ($api) {
            $api->get('restaurant/{restaurantId}', 'RestaurantController@item');

        });
            
//        Client Routes

        $api->get('clients', 'ClientController@all');
        $api->put('client', 'ClientController@update');
        $api->delete('client/{clientId}', 'ClientController@delete');
        $api->post('client/friends', 'ClientController@addFriends'); // Add friends to your circle
        $api->get('client/friends', 'ClientController@getFriendsInMyCircle'); // Get friends in your circle
        $api->get('client/circles', 'ClientController@getFriendsFromOtherCircle'); // Get friends from other circle
        $api->post('client/respond', 'ClientController@respond'); // Respond to a friend request
        $api->get('client/requests', 'ClientController@getFriendRequests'); // Get friend requests
        $api->get('client/sent_requests', 'ClientController@getSentFriendRequests'); // Get Sent Friend Requests
        $api->get('client/{clientId}', 'ClientController@item');

//        Menu Lists


       // Photo Routes
        $api->post('photo/{item_id}/{item_type}', 'PhotoController@store');
        $api->post('photo/update', 'PhotoController@update');
//        $api->delete('photo/{photo_id}', 'PhotoController@delete');
        $api->post('photo/url', 'PhotoController@deleteByUrl');

//        Order Routes
        $api->post('orders_detail', 'OrderDetailController@store');
        $api->put('orders_detail', 'OrderDetailController@updateOrdersDetail');
        $api->put('orders', 'OrderDetailController@updateOrders');
        $api->get('orders/{orderId}', 'OrderDetailController@getOrder');
        $api->get('get_orders/{orderId}', 'OrderDetailController@getOrderForDashboard');
        $api->post('order', 'OrderDetailController@update');
        $api->get('orders_detail/{restaurantId}', 'OrderDetailController@getOrderDetails');
        $api->get('orders', 'OrderDetailController@getOrders');
        $api->delete('order/{orderId}', 'OrderDetailController@deleteOrder');
        $api->delete('orders_detail/{orderDetailId}', 'OrderDetailController@deleteOrderDetail');
        $api->get('restaurant_menu', 'OrderDetailController@getRestaurantMenu');

        $api->get('orders_by_status', 'OrderDetailController@getOrdersByStatus');
        $api->get('orders_detail_by_status/{orderId}', 'OrderDetailController@getOrdersDetailByStatus');
        $api->get('cancel_order', 'OrderDetailController@cancelOrder');

//        Menu Related




    });

    $api->get('free', function(){
        
    });



});
