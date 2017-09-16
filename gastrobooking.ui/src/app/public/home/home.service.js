/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.home')
        .service('HomeService', HomeService);
    /*@ngNoInject*/
    function HomeService(TokenRestangular) {
        var service = {
            getRestaurantTypes: getRestaurantTypes,
            getKitchenTypes: getKitchenTypes,
            getRestaurantsNearby: getRestaurantsNearby,
            getTasted: getTasted,
            getPromotionsNearby: getPromotionsNearby
        };
        return service;

        function getRestaurantTypes(){
            debugger;
            return TokenRestangular.all('restaurantTypes').customGET('');
        }

        function getKitchenTypes(){
            debugger;
            return TokenRestangular.all('kitchenTypes').customGET('');
        }

        function getRestaurantsNearby(location){
            return TokenRestangular.all('restaurantsNearby').customPOST(location);
        }

        function getTasted(){
            return TokenRestangular.all('tasted').customGET('');
        }

        function getPromotionsNearby(location){
            return TokenRestangular.all('promotionsNearby').customPOST(location);
        }
    }

})();