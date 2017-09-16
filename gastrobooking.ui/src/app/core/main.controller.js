/**
 * Created by yonatom on 8/31/16.
 */
function getURLParameter(name) {
    return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)
        || [null, ''])[1].replace(/\+/g, '%20')) || null;
}
(function () {
    'use strict';

    angular
        .module('app.core')
        .controller('MainController', MainController);
    /*@ngNoInject*/
    function MainController($state, $scope, $rootScope,CoreService, AuthService, $translate, $location, $anchorScroll, appConstant, $geolocation) {
        var vm = this;
        
        $scope.webWidgetCSS = appConstant.onlineApi + "/webservice/css" + window.location.search;
        $scope.webWidgetJs = appConstant.onlineApi + "/webservice/js" + window.location.search;
        
        if(typeof(localStorage.getItem('user')) != "undefined"){
            $rootScope.currentUser = JSON.parse(localStorage.getItem('user'));
        }
        vm.totalOrder = 0;
        initializeCart();

        $scope.$on('orders-detail-changed', function(event, args){
            CoreService.getOrdersDetailCount().then(function(response){
                vm.totalOrder = response;
            }, function(error){

            });
        });

        vm.currentUser = $rootScope.currentUser;

        //var currentLanguage = JSON.parse(localStorage.getItem('current_language'));
        var currentLanguage = localStorage.getItem('current_language');
        if (currentLanguage) {
            $rootScope.language = currentLanguage;
            changeLanguage(currentLanguage);
        }
        else {
            setDefaultLanguage();
            getLangugeByLocation();
        }

        //debugger;
        vm.logout = logout;
        vm.changeLanguage = changeLanguage;

        function changeLanguage(language){
            $rootScope.language = language;
            localStorage.setItem('current_language', language);
            $translate.use(language);
        }

        function setDefaultLanguage() {
            $rootScope.language = "en";
            changeLanguage($rootScope.language);
        }

        var lang = getURLParameter('lang');
        if(lang) { vm.changeLanguage(lang); }

        function logout() {
            vm.totalOrder = 0;
            AuthService.logout();
        }

        function initializeCart() {
            CoreService.getOrdersDetailCount().then(function(response){
                //debugger;
                vm.totalOrder = response;
            }, function(error){

            });
        }

        var dropwindow = getURLParameter('dropwindow');
        $( document ).ready(function() {
            function dropwindows() {
                {
                    if (dropwindow == "off") {
                        jQuery('.widget__menutype').each(function () {
                                console.log('off');
                                if (jQuery(this).next('.list-group-child').hasClass('in')) {
                                    console.log('now');
                                    jQuery(this).trigger('click')
                                }
                            }
                        )
                    }
                    else {
                        jQuery('.widget__menutype').each(function () {
                                if (!jQuery(this).next('.list-group-child').hasClass('in')) {
                                    jQuery(this).trigger('click')
                                }
                            }
                        )
                    }

                }
            }
            setTimeout(dropwindows,1000);
        });//---dropdown windows activation

        var show= getURLParameter('showtype');
        $( document ).ready(function() {
            function showtypes() {
                {
                    if (show == "off") {

                        jQuery('.widget__menutype').each(function () {
                                if (!jQuery(this).next('.list-group-child').hasClass('in')) {
                                    jQuery(this).trigger('click')
                                }

                            }
                        )
                        jQuery('.widget__menutype').css("visibility", "hidden");
                    }
                    else {
                        jQuery('.widget__menutype').css("display", "block");

                    }

                }
            }
            setTimeout(showtypes,1000);
        });//---show sub_menus without main_menus

        $(document).ready(function(){
            $('a.back').click(function(){
                    var restaurantId = localStorage.getItem('widget__restaurantId');
                    $state.go('main.restaurant_detail', {restaurantId: restaurantId});
            });
        });
        
        function getLangugeByLocation(){
            $geolocation.getCurrentPosition({
                //timeout: 600
            }).then(function(position) {
                var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({ 'latLng': latlng }, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        if (results) {
                        var length = results.length - 1;
                        var country = results[length].address_components[0].short_name;
                        
                        if ( country === "CZ" ) {
                            $scope.$apply(function () {
                                $rootScope.language = "cs";
                                $translate.use($rootScope.language);
                                localStorage.setItem('current_language', $rootScope.language);
                            });
                        }
                    }
                }
            });
                
            }, function(error){
                //debugger;
            });
        }
    }

})();