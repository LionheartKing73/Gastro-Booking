/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.core')
        .service('CoreService', CoreService);
    /*@ngNoInject*/
    function CoreService(TokenRestangular, $sce, $rootScope, $state) {
        var service = {
            getDays: [{"-1":"Scheduled"},{"0": "Cooked Every day"},{"1":"Monday"},{"2":"Tuesday"},{"3":"Wednesday"}
                ,{"4":"Thursday"},{"5":"Friday"},{"6":"Saturday"},{"7":"Sunday"}],
            getCousineType :["Italian","Mexican","Chinese","Indian","American","Ethiopian"],
            getOrdersDetailCount : getOrdersDetailCount,
            getRestaurantTypes: getRestaurantTypes,
            getKitchenTypes: getKitchenTypes,
            getRestaurantsNearby: getRestaurantsNearby,
            getTasted: getTasted,
            getPromotionsNearby: getPromotionsNearby
        };
        return service;

        function getOrdersDetailCount() {
            return TokenRestangular.all('orders_detail_count').customGET('');
        }

        function getRestaurantTypes(){
            //debugger;
            return TokenRestangular.all('restaurantTypes').customGET('');
        }

        function getKitchenTypes(){
            //debugger;
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
