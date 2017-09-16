/**
 * Created by Job on 6/27/2016.
 */
(function () {
    'use strict';

    angular
        .module('app.auth')
        .config(moduleConfig);
    /*@ngNoInject*/
    function moduleConfig($stateProvider, $urlRouterProvider) {

        //$urlRouterProvider.otherwise("app/home");
        $urlRouterProvider.otherwise(function($injector, $location){
            var state = $injector.get('$state');
            var rootScope = $injector.get('$rootScope');

            // if(!localStorage.getItem('user'))
            // {
            //     state.go('main.login');
            // }else{
            //     state.go('main.home');
            // }
            //debugger;
            state.go('main.home');

            return $location.path();
        });
        $stateProvider
            .state('main.register', {
                url: "/register",
                templateUrl: "app/public/register/register.html",
                controller: 'RegisterController',
                controllerAs: 'vm'
            }).state('main.login', {
                url: "/login",
                templateUrl: "app/public/login/login.html",
                controller: 'LoginController',
                controllerAs: 'vm'
            }).state('main.registerClient', {
                url: "/registerClient",
                templateUrl: "app/public/register/register.client.html",
                controller: "RegisterClientController",
                controllerAs: 'vm'
            });

    }
})();