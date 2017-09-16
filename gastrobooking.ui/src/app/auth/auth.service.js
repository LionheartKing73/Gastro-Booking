/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.auth')
        .directive('pwCheck', [function () {
            return {
                require: 'ngModel',
                link: function (scope, elem, attrs, ctrl) {
                    var firstPassword = '#' + attrs.pwCheck;
                    elem.add(firstPassword).on('keyup', function(){
                        scope.$apply(function(){
                            var v = elem.val() === $(firstPassword).val();
                            ctrl.$setValidity('pwmatch', v);
                        });
                    });
                }
            }
        }])
        .service('AuthService', AuthService);
    /*@ngNoInject*/
    function AuthService(TokenRestangular, $state, $rootScope, $stateParams) {
        var service = {
            login: login,
            authorize: authorize,
            register: register,
            registerClient: registerClient,
            logout: logout,
            userExists: userExists,
            sendResetPasswordEmail: sendResetPasswordEmail,
            resetPassword: resetPassword,
            getTokenEmail: getTokenEmail

        };
        return service;

        function register(user) {
            return TokenRestangular.all('user').customPOST(user);
        }

        function getTokenEmail(token) {
            return TokenRestangular.all('password/reset/token/' + token + '/email').customGET('');
        }

        function authorize(data){
            return TokenRestangular.all('oauth/authorize').customPOST(data);
        }

        function login() {
            //debugger;
            return TokenRestangular.all('user').customGET('');
        }

        function logout() {
            //debugger;
            localStorage.removeItem('user');
            localStorage.removeItem('access_token');
            localStorage.removeItem('refresh_token');
            localStorage.removeItem('search');
            localStorage.removeItem('menuGroup');
            localStorage.removeItem('menuOfTheDay');
            TokenRestangular.setDefaultHeaders({Authorization: 'Bearer ' + ''});
            //debugger;
            $rootScope.currentUser = null;

            if($stateParams.app == 'widget') {
                $state.go('main.restaurant_detail', {restaurantId: localStorage.getItem('widget__restaurantId')});
                return;
            }

            $state.go('main.home');
        }
        function  registerClient (client){
            //debugger;
            return TokenRestangular.all('client').customPOST(client);
        }

        function sendResetPasswordEmail(email) {
            //debugger;
            return TokenRestangular.all('send_reset_password_email').customPOST({email: email});
        }

        function userExists(email) {
            //debugger;
            return TokenRestangular.all('user_exists?email=' + email).customGET();
        }

        function resetPassword(token, password) {
            //debugger;
            return TokenRestangular.all('password/reset').customPUT({password: password, token: token});
        }

    }

})();
