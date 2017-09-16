/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.restaurant')
        .service('SearchService', SearchService);
    /*@ngNoInject*/
    function SearchService($rootScope, $state, TokenRestangular, moment) {
        var service = {
            getMenuLists: getMenuLists,
            getMenuSchedule: getMenuSchedule,
            getRestaurants: getRestaurants,
            searchMenuLists: searchMenuLists,
            addMenuListToCart: addMenuListToCart,
            searchCommon: searchCommon
        };
        return service;

        function getMenuLists(){
            return TokenRestangular.all('menu_lists').customGET('');

        }

        function getMenuSchedule(){

        }

        function getRestaurants(search, currentPage){
            return TokenRestangular.all('restaurants?search=' + search + '&page=' + currentPage).customGET('');
        }

        function searchMenuLists(currentPage,filter){
            debugger;
            return TokenRestangular.all('menu_lists?search='+filter.search+'&page='+currentPage).customPOST(filter);
        }
        function addMenuListToCart(data){
            debugger;
            data.orders_detail.time = moment(data.orders_detail.time).format();
            data.orders_detail.date = moment(data.orders_detail.date).format();
            return TokenRestangular.all('orders_detail').customPOST(data);
        }

        function searchCommon(search, currentPage){
            debugger;
            return search.searchToggle ? TokenRestangular.all('menu_lists?search=' + search.menuListSearchKeyword+'&page='+currentPage).customPOST(search) :
                TokenRestangular.all('restaurants?search=' + search.restaurantSearchKeyword + '&page=' + currentPage).customPOST(search);

        }

    }

})();
