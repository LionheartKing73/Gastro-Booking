/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.home')
        .config(moduleConfig);
    /*@ngNoInject*/
    function moduleConfig($stateProvider) {

        $stateProvider
            .state('main.home', {
                url: "/home",
                templateUrl: "app/public/home/home.html",
                controller: 'HomeController',
                controllerAs: 'vm'
            }).state('main.faq', {
                url: "/faq",
                templateUrl: "app/public/static/faq.html",
                controller: 'HomeController',
                controllerAs: 'vm'
            }).state('main.how_it_works', {
                url: "/how-it-works",
                templateUrl: "app/public/static/how_it_works.html",
                controller: 'HomeController',
                controllerAs: 'vm'
            }).state('main.terms_and_conditions', {
                url: "/terms-and-conditions",
                templateUrl: "app/public/static/terms_and_conditions.html",
                controller: 'HomeController',
                controllerAs: 'vm'
            }).state('main.faq_client', {
                url: "/faq-client",
                templateUrl: "app/public/static/faq_client.html",
                controller: 'HomeController',
                controllerAs: 'vm'
            }).state('main.terms_and_conditions_client', {
                url: "/terms-and-conditions-client",
                templateUrl: "app/public/static/terms_and_conditions_client.html",
                controller: 'HomeController',
                controllerAs: 'vm'
            }).state('main.about_us', {
                url: "/about-us",
                templateUrl: "app/public/static/about_us.html",
                controller: 'HomeController',
                controllerAs: 'vm'
            }).state('main.career', {
                url: "/career",
                templateUrl: "app/public/static/career.html",
                controller: 'HomeController',
                controllerAs: 'vm'
            }).state('main.contact_us', {
                url: "/contact-us",
                templateUrl: "app/public/static/contact_us.html",
                controller: 'HomeController',
                controllerAs: 'vm'
            });
        }
    
    
})();