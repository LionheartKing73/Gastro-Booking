/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.home')
        .controller('HomeController', HomeController);
    /*@ngNoInject*/
    function HomeController($scope,$rootScope, $timeout, $geolocation, $state, $location, $anchorScroll, appConstant, $translate, uiDatetimePickerConfig, CoreService) {
        var vm = this;
        var locationCopy, latitudeCPY, longitudeCPY;
        vm.location_not_choosen = "";
        if ($state.current.name == "main.home"){
            $rootScope.currentState = "home";
            angular.element(document).ready(function(){
                loadMap();
            });
        } else if ($state.current.name == "main.contact_us"){
            $rootScope.currentState = "contact us";
        } else if ($state.current.name == "main.about_us"){
            $rootScope.currentState = "about us";
        } else if ($state.current.name == "main.career"){
            $rootScope.currentState = "career";
        }


        vm.slickConfig = {
          dots: true,
          infinite: false,
          adaptiveHeight: true,
          slidesToShow: 5,
          slidesToScroll: 5,
          responsive: [
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 3,
                slidesToScroll: 3,
                infinite: true,
                dots: true
              }
            },
            {
              breakpoint: 600,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 2
              }
            },
            {
              breakpoint: 480,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1
              }
            }
          ]
        };
        vm.slickConfig1 = {
          dots: true,
          infinite: true,
          adaptiveHeight: true,
          slidesToShow: 2,
          slidesToScroll: 2,
          responsive: [
            {
              breakpoint: 600,
              settings: {
                slidesToShow: 1,
                slidesToScroll: 1
              }
            }
          ]
        };
        vm.searchParams = {
            "keyword": "",
            "position": {
                "latitude": "",
                "longitude": ""
            },
            "searchToggle":true,
            "time": moment().add(1, 'hours').toDate(),
            "date": new Date(),
            "menuListSearchKeyword": "",
            "restaurantSearchKeyword": "",
            "restaurantType": "",
            "filter_by_date": true,
            "distance": "10"
        };

        //debugger;

        vm.latitude = "";
        vm.longitude = "";
        vm.restaurantTypes = [];
        vm.tasted = [];
        vm.restaurantsNearby = [];
        vm.promotionsNearby = [];
        scrollToTop();
        vm.mealSearch = true;
        vm.hidemap = false;
        vm.tastedLoading = false;
        vm.promotedLoading = false;
        vm.restLoading = false;
        vm.distances = ["1", "3", "5", "10", "20", "50", "100"];
        vm.locationPicker = null;

        $translate(['DATE_PICKER.NOW', 'DATE_PICKER.CLEAR', 'DATE_PICKER.CLOSE', 'DATE_PICKER.DATE', 'DATE_PICKER.TIME', 'DATE_PICKER.TODAY'])
            .then(function(translations){
                uiDatetimePickerConfig.buttonBar.now.text = translations["DATE_PICKER.NOW"];
                uiDatetimePickerConfig.buttonBar.clear.text = translations["DATE_PICKER.CLEAR"];
                uiDatetimePickerConfig.buttonBar.close.text = translations["DATE_PICKER.CLOSE"];
                uiDatetimePickerConfig.buttonBar.date.text = translations["DATE_PICKER.DATE"];
                uiDatetimePickerConfig.buttonBar.time.text = translations["DATE_PICKER.TIME"];
                uiDatetimePickerConfig.buttonBar.today.text = translations["DATE_PICKER.TODAY"];
            }, function(translationId){
                //debugger;
                uiDatetimePickerConfig.buttonBar.now.text = translationId["DATE_PICKER.NOW"];
                uiDatetimePickerConfig.buttonBar.clear.text = translationId["DATE_PICKER.CLEAR"];
                uiDatetimePickerConfig.buttonBar.close.text = translationId["DATE_PICKER.CLOSE"];
                uiDatetimePickerConfig.buttonBar.date.text = translationId["DATE_PICKER.DATE"];
                uiDatetimePickerConfig.buttonBar.time.text = translationId["DATE_PICKER.TIME"];
                uiDatetimePickerConfig.buttonBar.today.text = translationId["DATE_PICKER.TODAY"];
            });
        $rootScope.$on('$translateChangeSuccess', function(){
            $translate(['DATE_PICKER.NOW', 'DATE_PICKER.CLEAR', 'DATE_PICKER.CLOSE', 'DATE_PICKER.DATE', 'DATE_PICKER.TIME', 'DATE_PICKER.TODAY']).then(function(translations){
                //debugger;
                uiDatetimePickerConfig.buttonBar.now.text = translations["DATE_PICKER.NOW"];
                uiDatetimePickerConfig.buttonBar.clear.text = translations["DATE_PICKER.CLEAR"];
                uiDatetimePickerConfig.buttonBar.close.text = translations["DATE_PICKER.CLOSE"];
                uiDatetimePickerConfig.buttonBar.date.text = translations["DATE_PICKER.DATE"];
                uiDatetimePickerConfig.buttonBar.time.text = translations["DATE_PICKER.TIME"];
                uiDatetimePickerConfig.buttonBar.today.text = translations["DATE_PICKER.TODAY"];
            }, function(translationId){
                //debugger;
                uiDatetimePickerConfig.buttonBar.now.text = translationId["DATE_PICKER.NOW"];
                uiDatetimePickerConfig.buttonBar.clear.text = translationId["DATE_PICKER.CLEAR"];
                uiDatetimePickerConfig.buttonBar.close.text = translationId["DATE_PICKER.CLOSE"];
                uiDatetimePickerConfig.buttonBar.date.text = translationId["DATE_PICKER.DATE"];
                uiDatetimePickerConfig.buttonBar.time.text = translationId["DATE_PICKER.TIME"];
                uiDatetimePickerConfig.buttonBar.today.text = translationId["DATE_PICKER.TODAY"];
            });
        });


        vm.start_time = new Date();
        vm.timeOptions = {
            step: 15,
            timeFormat: 'H:i'
        };
        var count = 0;


        $scope.$watch("vm.searchParams.currentPosition", function(){
            //debugger;
            if (vm.searchParams.currentPosition && count == 0){
                //debugger;
                count++;
            }
            if ((vm.longitude != longitudeCPY) || (vm.latitude != latitudeCPY)){
                locationCopy = vm.searchParams.currentPosition;
                latitudeCPY = vm.latitude;
                longitudeCPY = vm.longitude;
                //debugger;
            }
            //debugger;

            vm.location_not_choosen = "";
        });


        // $scope.$watch("vm.location_not_choosen", function(){
        //     if (vm.location_not_choosen){
        //         $timeout(function(){
        //             vm.location_not_choosen = "";
        //             vm.searchParams.currentPosition = locationCopy;
        //             debugger;
        //         }, 3000);
        //     }
        // });

        // $scope.$watchGroup(["vm.latitude", "vm.longitude"], function(){
        //     latitudeCPY = vm.latitude;
        //     longitudeCPY = vm.longitude;
        //     // locationCopy = vm.searchParams.currentPosition;
        //     debugger;
        // });


        vm.search = search;

        vm.openCalendar = function(){
            //debugger;
            vm.date_picker.open = true;
        };

        vm.date_picker = {
            date: new Date(),
            datepickerOptions: {
                showWeeks: false,
                minDate: moment().add(-1, 'days').toDate(),
                startingDay: 1

            }
        };
        getRestaurantTypes();
        getKitchenTypes();
        getTasted();

        function loadMap(){
            //debugger;
            vm.hidemap = false;
            var location = {
                "currentPosition": {
                    "latitude": 50.0755381,
                    "longitude": 14.43780049999998
                }
            };
            if (localStorage.getItem('search')){
                var search = JSON.parse(localStorage.getItem('search'));
                //debugger;
                if (search.position && search.position.latitude && search.positionKeyword){
                    location.currentPosition = search.position;
                    vm.searchParams.keyword = search.keyword;
                    //debugger;
                }
            }
            getRestaurantsNearby(location);
            getPromotionsNearby(location);
            vm.locationPicker = $('#home_map_holder').locationpicker({
                location: {latitude: location.currentPosition.latitude , longitude: location.currentPosition.longitude},
                radius: 300,
                zoom: 15,
                inputBinding: {
                    latitudeInput: $("#latitude"),
                    longitudeInput: $("#longitude"),
                    locationNameInput: $('#searchLocationInput')
                },
                enableAutocomplete: true,
                autocompleteOptions: {
                    types: ['(regions)']
                },
                onlocationnotfound: function(locationName){}

            });

        }

        vm.locate = function locate(){
            //debugger;
            vm.hidemap = true;
            vm.mapLoading = true;
            $geolocation.getCurrentPosition({
                timeout: 6000
            }).then(function(position) {
                vm.latitude = position.coords.latitude;
                vm.longitude = position.coords.longitude;
                latitudeCPY = position.coords.latitude;
                longitudeCPY = position.coords.longitude;

                var location = {
                    "currentPosition": {
                        "latitude": vm.latitude,
                        "longitude": vm.longitude
                    }
                };
                getRestaurantsNearby(location);
                getPromotionsNearby(location);

                $('#home_map_holder2').locationpicker({
                    location: {latitude: vm.latitude, longitude: vm.longitude},
                    radius: 300,
                    zoom: 15,
                    inputBinding: {
                        latitudeInput: $("#latitude"),
                        longitudeInput: $("#longitude"),
                        locationNameInput: $('#searchLocationInput')
                    },
                    enableAutocomplete: true,
                    autocompleteOptions: {
                        types: ['(regions)']
                    },
                    onlocationnotfound: function(locationName){
                        console.log(locationName);

                    }
                });

                vm.mapLoading = false;


            }, function(error){
                vm.mapLoading = false;
                loadMap();

            });
        };

        function getTasted(){
            vm.tastedLoading = true;
            CoreService.getTasted().then(function(response){
                //debugger;
                vm.tastedLoading = false;
                vm.tasted = response.data;
                angular.forEach(vm.tasted, function(menu_list){
                    if (menu_list.photo)
                        menu_list.photo = appConstant.imagePath + menu_list.photo;
                    else
                        menu_list.photo = "assets/images/meal-placeholder.png";
                    //debugger;
                });

            }, function (error){
                //debugger;
                vm.tastedLoading = false;
            });

        }

        function getRestaurantsNearby(location){
            vm.restLoading = true;
            CoreService.getRestaurantsNearby(location).then(function(response){
                //debugger;
                vm.restaurantsNearby = response.data;
                vm.restLoading = false;
                if (vm.restaurantsNearby){
                    angular.forEach(vm.restaurantsNearby, function(item){
                        if (item.logo){
                            item.logo = appConstant.imagePath +  item.logo;
                        } else {
                            item.logo = "assets/images/placeholder.jpg"
                        }
                    });
                }
            }, function (error){
                //debugger;
                vm.restLoading = false;
            });
        }

        function getPromotionsNearby(location) {
            vm.promotedLoading = true;
            CoreService.getPromotionsNearby(location).then(function(response){
                //debugger;
                vm.promotedLoading = false;
                vm.promotionsNearby = response.data;
                angular.forEach(vm.promotionsNearby, function(menu_list){
                    if (menu_list.photo)
                        menu_list.photo = appConstant.imagePath + menu_list.photo;
                    else
                        menu_list.photo = "assets/images/meal-placeholder.png";
                });
            }, function (error){
                vm.promotedLoading = false;
            });
        }


        function scrollToTop(){
            $location.hash("u");
            $anchorScroll();
        }

        function search(isValid){
            debugger;
            if(isValid){
                if ((locationCopy != vm.searchParams.currentPosition) && (latitudeCPY == vm.latitude && longitudeCPY == vm.longitude)){
                    vm.location_not_choosen = "SEARCH.LOCATION NOT CHOSEN";
                    return;
                }
                vm.searchParams.position.latitude = vm.latitude;
                vm.searchParams.position.longitude = vm.longitude;
                vm.searchParams.positionKeyword = vm.searchParams.currentPosition;
                vm.searchParams.searchToggle = vm.searchParams.keyword ? vm.searchParams.searchToggle : false; // if keyword is empty perform restaurant search
                if (vm.searchParams.searchToggle){
                    vm.searchParams.menuListSearchKeyword = vm.searchParams.keyword;
                } else {
                    vm.searchParams.restaurantSearchKeyword = vm.searchParams.keyword;
                }

                debugger;
                $state.go("main.search",{"search":vm.searchParams});
            }
        }
        function getRestaurantTypes(){
            CoreService.getRestaurantTypes().then(function(response){
                vm.restaurantTypes = response.data;
                angular.forEach(vm.restaurantTypes, function (value, key) {
                    value['n_type'] = "RESTAURANT_TYPE." + value.name;
                });
            },function(error){

            });
        }

        function getKitchenTypes(){
            CoreService.getKitchenTypes().then(function(response){
                vm.kitchenTypes = response.data;
                angular.forEach(vm.kitchenTypes, function (value, key) {
                    value['n_type'] = "KITCHEN_TYPE." + value.name;
                });
            },function(error){

            });
        }

    }

})();