/**
 * Created by Job on 6/27/2016.
 */
(function () {
    'use strict';

    angular
        .module('app.core')
        .config(moduleConfig);
    /*@ngNoInject*/
    function moduleConfig($stateProvider, $urlRouterProvider, RestangularProvider) {
        RestangularProvider.setErrorInterceptor(
            function (response,$rootScope,$injector, $state) {
                if (response.status == 401 && window.location.hash != "#/app/login") {
                    //debugger;

                    localStorage.removeItem('user');
                    localStorage.removeItem('access_token');
                    localStorage.removeItem('refresh_token');
                    $rootScope.currentUser = null;
                    window.location.href = 'index.html#/app/login';
                }else if(response.status == 404 || response.status_code == 404 ){
                    //debugger;

                    window.location.href = 'index.html#/app/404'

                }
                return true;
            }
        );
        $stateProvider
            .state('main', {
                url: "/:app?",
                templateUrl: function (stateParams) {
                    var template_url = '';
                    switch (stateParams.app) {
                        case 'app':
                            template_url = "app/core/main.html";
                            break;
                        case 'widget':
                            template_url = "app/core/widget.html";
                            break;
                    }

                    return template_url;
                },
                params: {app: 'app'},
                controller: 'MainController',
                controllerAs: 'vm',
            }).state('main.notFound',{
                url:'/404',
                templateUrl: "app/core/404.html"
            });


    }
})();