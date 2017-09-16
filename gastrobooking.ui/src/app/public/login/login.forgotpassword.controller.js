/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.auth')
        .controller('ForgotPasswordController', ForgotPasswordController);
    /*@ngNoInject*/
    function ForgotPasswordController($state, $rootScope, AuthService, appConstant, TokenRestangular, $stateParams) {
        var vm = this;
        vm.loginError = "";
        vm.closeAlert = closeAlert;
        $rootScope.currentState = "login";
        vm.login = login;
        vm.loading = false;
        function login(isValid){
            if (isValid) {
                vm.loading = true;
                var data = {
                    "grant_type": appConstant.grant_type,
                    "client_id": appConstant.client_id,
                    "client_secret": appConstant.client_secret,
                    "username": vm.email,
                    "password": vm.password
                };

                debugger;
                AuthService.authorize(data).then(function (response) {
                    debugger;

                    localStorage.setItem('access_token', response.access_token);
                    localStorage.setItem('refresh_token', response.refresh_token);
                    TokenRestangular.setDefaultHeaders({Authorization: 'Bearer ' + localStorage.getItem('access_token')});
                    AuthService.login().then(function (response) {
                        var user = JSON.stringify(response.user);
                        localStorage.setItem('user', user);
                        $rootScope.currentUser = JSON.parse(localStorage.getItem('user'));
                        debugger;
                        vm.loading = false;
                        $rootScope.$broadcast('orders-detail-changed');

                        if($stateParams.app == 'widget') {
                            $state.go('main.restaurant_detail', {restaurantId: localStorage.getItem('widget__restaurantId')});
                            return;
                        }

                        $state.go("main.home");
                    });
                }, function (error) {
                    debugger;
                    vm.loading = false;
                    if (error.statusText == 'Unauthorized') {
                        vm.loginError = "Invalid username or password!";
                    }
                    else {
                        vm.loginError = error.statusText;
                    }
                });
            }
        }

        function closeAlert(){
            vm.loginError = "";
        }



    }

})();