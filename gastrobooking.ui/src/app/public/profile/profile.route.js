/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.profile')
        .config(moduleConfig);
    /*@ngNoInject*/
    function moduleConfig($stateProvider) {

        $stateProvider
            .state('main.profile', {
                url: "/profile",
                templateUrl: "app/public/profile/profile.html",
                controller: 'ProfileController',
                controllerAs: 'vm'
            }).state('main.restaurant', {
                url: "/add-restaurant",
                templateUrl: "app/public/profile/restaurant.html",
                controller: 'ProfileController',
                controllerAs: 'vm'
            }).state('main.upload', {
                url: "/upload",
                templateUrl: "app/public/profile/upload.html",
                controller: 'ProfileController',
                controllerAs: 'vm'
            }).state('main.restaurantDetail', {
                url: "/restaurant/{restaurantId}/edit",
                templateUrl: "app/public/profile/restaurant_detail_edit.html",
                controller: 'RestaurantEditController',
                controllerAs: 'vm'
            }).state('main.test', {
                url: "/test",
                templateUrl: "app/public/profile/test.html",
                controller: 'TestController',
                controllerAs: 'vm'
            })
            .state('main.editor', {
                url: "/editor",
                templateUrl: "app/public/profile/editor.html",
                controller: 'EditorController',
                controllerAs: 'vm'
            });
        }
})();