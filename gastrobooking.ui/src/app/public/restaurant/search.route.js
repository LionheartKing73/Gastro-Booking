/**
 * Created by Job on 6/27/2016.
 */
(function () {
    'use strict';

    angular
        .module('app.restaurant')
        .config(moduleConfig);
    /*@ngNoInject*/
    function moduleConfig($stateProvider) {

        $stateProvider
            .state('main.search', {
                url: "/search",
                params: {'search':""},
                templateUrl: "app/public/restaurant/restaurant_search.html",
                controller: 'RestaurantSearchController',
                controllerAs: 'vm'
            }).state('main.restaurant_detail', {
                url: "/restaurant/{restaurantId}",
                params: {'menuList':"", 'restaurantId':""},
                templateUrl: function (stateParams) {
                    var template_url = '';
                    switch (stateParams.app) {
                        case 'app':
                            template_url = "app/public/restaurant/restaurant_detail.html";
                            break;
                        case 'widget':
                            template_url = "app/public/restaurant/restaurant_menu.html";
                            break;
                    }

                    return template_url;
                },
                controller: "RestaurantDetailController",
                controllerAs: "vm"
            });


    }
})();