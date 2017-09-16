/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.auth')
        .controller('RegisterClientController', RegisterClientController);
    /*@ngNoInject*/
    function RegisterClientController($state, AuthService, appConstant, $rootScope, $stateParams, TokenRestangular) {
        var vm = this;
        vm.registrationError = "";
        $rootScope.loginLoading = false;
        vm.register = register;
        vm.email_update = false;
        vm.email_new = false;
        vm.email_restaurant_update = false;
        vm.loading = false;

        $rootScope.currentState = "register";
        vm.closeAlert = function(){
            vm.registrationError = "";
        };

        function register(isValid){
            debugger;
            if (isValid){
                vm.loading = true;
                debugger;
                var user = {
                    "user" : {
                        "name": vm.name,
                        "email": vm.email,
                        "password": vm.password
                    },
                    "client" : {
                        "email_update" : true,
                        "email_new" : true,
                        "email_restaurant_update" : true
                    }
                };
                AuthService.registerClient(user).then(function(response){
                    $rootScope.loginLoading = true;
                    debugger;
                    if (response.error){
                        vm.registrationError = "User already exists!";
                        $rootScope.loginLoading = false;
                        vm.loading = false;
                        return;
                    }
                    var user = JSON.stringify(response.data);
                    localStorage.setItem('user', user);
                    $rootScope.currentUser = JSON.parse(localStorage.getItem('user'));
                    var data = {
                        "grant_type": appConstant.grant_type,
                        "client_id": appConstant.client_id,
                        "client_secret": appConstant.client_secret,
                        "username": vm.email,
                        "password": vm.password
                    };
                    AuthService.authorize(data).then(function (response) {
                        localStorage.setItem('access_token', response.access_token);
                        localStorage.setItem('refresh_token', response.refresh_token);
                        TokenRestangular.setDefaultHeaders({Authorization: 'Bearer ' + localStorage.getItem('access_token')});
                        debugger;
                        $rootScope.loginLoading = false;
                        vm.loading = false;
                        debugger;

                        if($stateParams.app == 'widget') {
                            $state.go('main.restaurant_detail', {restaurantId: localStorage.getItem('widget__restaurantId')});
                            return;
                        }

                        $state.go("main.home");

                    });

                }, function(error){
                    debugger;
                    AuthService.userExists(vm.email).then(function (response) {
                        debugger;
                        if (response.success){
                            debugger;
                            var data = {
                                "grant_type": appConstant.grant_type,
                                "client_id": appConstant.client_id,
                                "client_secret": appConstant.client_secret,
                                "username": vm.email,
                                "password": vm.password
                            };
                            AuthService.authorize(data).then(function (response) {
                                debugger;
                                localStorage.setItem('access_token', response.access_token);
                                localStorage.setItem('refresh_token', response.refresh_token);
                                TokenRestangular.setDefaultHeaders({Authorization: 'Bearer ' + localStorage.getItem('access_token')});
                                debugger;
                                $rootScope.loginLoading = false;
                                AuthService.login().then(function (response) {
                                    debugger;
                                    var user = JSON.stringify(response.user);
                                    localStorage.setItem('user', user);
                                    $rootScope.currentUser = JSON.parse(localStorage.getItem('user'));
                                    $rootScope.$broadcast('orders-detail-changed');
                                    $state.go("main.home");
                                }, function (error) {
                                    debugger;
                                });
                                vm.loading = false;
                                debugger;


                            }, function (error) {
                                debugger;
                            });
                        }
                    });
                    $rootScope.loginLoading = false;
                    vm.loading = false;
                });
            }
        }


    }

})();