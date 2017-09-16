/**
 * Created by Job on 6/27/2016.
 */
(function () {
    'use strict';

    angular
        .module('app.client')
        .config(moduleConfig);
    /*@ngNoInject*/
    function moduleConfig($stateProvider, $urlRouterProvider) {

        
        $stateProvider
            .state('main.clientDashboard', {
                url: "/clientDashboard",
                templateUrl: "app/public/client/client.dashboard.html",
                controller: 'ClientDashboardController',
                controllerAs: 'vm'
            }).state('main.clientCart', {
                url: "/client/orders/{restaurantId}",
                templateUrl: "app/public/client/client.cart.html",
                controller: 'ClientCartController',
                controllerAs: 'vm'
            }).state('main.clientOrders', {
                url: "/client/orders",
                templateUrl: "app/public/client/client.orders.html",
                controller: 'ClientCartController',
                controllerAs: 'vm'
            });

    }
})();