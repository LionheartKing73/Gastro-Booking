/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.restaurant')
        .controller('RestaurantSearchController', RestaurantSearchController);
    /*@ngNoInject*/
    function RestaurantSearchController($scope, $state, $stateParams, $rootScope, SearchService, appConstant, CoreService, paginationService) {
        var vm = this;
        vm.menuLists = [];
        vm.restaurants = [];
        vm.menuListsCurrentPage = 1;
        vm.restaurantsCurrentPage = 1;
        vm.menuListsItemsPerPage = 5;
        vm.restaurantsItemsPerPage = 5;
        vm.menuListsTotalItems = 0;
        vm.loading = false;
        vm.location_not_choosen = "";

        vm.restaurantsTotalItems = 0;
        vm.addMenuListToCart = addMenuListToCart;
        vm.search = search;

        getRestaurantTypes();
        getKitchenTypes();

        $scope.$watchGroup(["vm.searchParams.searchToggle"],
            function handleFooChange(  ) {
                debugger;
                vm.menuListsCurrentPage = 1;
                vm.restaurantsCurrentPage = 1;
                debugger;
                search(vm.searchParams.currentPosition);

            }
        );

        $scope.$watch("vm.searchParams.currentPosition", function(){
            if (vm.searchParams.currentPosition && vm.searchParams.currentPosition.geometry){
                vm.location_not_choosen = "";
            }
        });


        //Date To/From
        vm.filter_date = "";

        vm.timeOptions = {
            step: 15,
            timeFormat: 'H:i'
        };

        // Filter by Date
        vm.filter_by_date = false;

        // Price Slider
        vm.priceSlider = {
            min: 0,
            max: 1000,
            options: {
                floor: 0,
                ceil: 1000
            }
        };
        //Date picker
        vm.openCalendar = function(value){
            debugger;
            vm[value].open = true;
        };

        vm.date_picker = {
            date: new Date(),
            datepickerOptions: {
                showWeeks: false,
                startingDay: 1,

            }
        };

        vm.date_picker2 = {
            date: new Date(),
            datepickerOptions: {
                showWeeks: false,
                startingDay: 1,

            }
        };

        vm.searchParams = {
            "searchToggle": true,
            "filter_by_date": false,
            "time": new Date(),
            "price": {
                "min": 0,
                "max": 1000
            },
            "date": new Date(),
            "menuListSearchKeyword": "",
            "restaurantSearchKeyword": "",
            "distance": "10"
        };

        debugger;


        //searchRestaurants();
        searchInit();

        function searchInit() {
            getSearchFromStateParam();
        }

        function getSearchFromStateParam(){
            if ($stateParams.search){
                vm.searchParams = $stateParams.search;
                vm.searchParams.price = {
                    "min": 0,
                    "max": 1000
                };
                //vm.searchParams.date = vm.searchParams.date ? vm.searchParams.date.toString() : "0";
                debugger;
                var latitude = $stateParams.search.position.latitude;
                var longitude = $stateParams.search.position.longitude;
                vm.searchParams.position = {};
                vm.searchParams.position.latitude = latitude;
                vm.searchParams.position.longitude = longitude;
                debugger;
                localStorage.setItem('search', JSON.stringify(vm.searchParams));
            } else {
                getSearchFromLocalStorage();
            }
        }
        
        function getSearchFromLocalStorage(){
            if (localStorage.getItem('search')){
                vm.searchParams = JSON.parse(localStorage.getItem('search'));
                vm.searchParams.time = new Date(vm.searchParams.time);
                vm.searchParams.date = new Date(vm.searchParams.date);

            } 
        }
        
        function search(isValid) {
            debugger;
            if (isValid){
                var searchParam = angular.copy(vm.searchParams);
                var lat = null;
                var lng = null;

                if ((vm.searchParams.positionKeyword != vm.searchParams.currentPosition) && !vm.searchParams.currentPosition.geometry){
                    vm.location_not_choosen = "SEARCH.LOCATION NOT CHOSEN";
                    return;
                }

                if (vm.searchParams.position && vm.searchParams.position.latitude){
                    lat = vm.searchParams.position.latitude;
                    lng = vm.searchParams.position.longitude;
                } else {
                    try {
                        lat = vm.searchParams.currentPosition.geometry.location.lat();
                        lng = vm.searchParams.currentPosition.geometry.location.lng();
                    } catch(er){
                        lat = vm.searchParams.currentPosition.geometry.location.lat;
                        lng = vm.searchParams.currentPosition.geometry.location.lng;
                    }
                }
                debugger;
                searchParam.currentPosition = {
                    // "latitude": 9.145000000000001,
                    // "longitude": 40.48967300000004
                    "latitude": lat,
                    "longitude": lng
                };

                if (vm.searchParams.currentPosition.geometry){
                    vm.searchParams.position = undefined;
                    searchParam.position = undefined;
                }
                if (vm.searchParams.time){
                    var hour =    vm.searchParams.time.getHours().toString().length == 1 ? "0" + vm.searchParams.time.getHours().toString() : vm.searchParams.time.getHours().toString();
                    var minutes = vm.searchParams.time.getMinutes().toString().length == 1 ?  "0"  + vm.searchParams.time.getMinutes().toString(): vm.searchParams.time.getMinutes().toString();
                    searchParam.time = hour + ":" + minutes + ":00";
                }
                searchParam.date = vm.searchParams.date.getDay() == 0 ? 7 : vm.searchParams.date.getDay();
                searchParam.dateObject = moment(vm.searchParams.date).format();
                var currentPage = 1;
                if (isValid == 'paginate'){
                    currentPage = searchParam.searchToggle ? vm.menuListsCurrentPage : vm.restaurantsCurrentPage;
                } else {
                    if (vm.menuListsTotalItems > 5){
                        paginationService.setCurrentPage("searchToggleId",currentPage);
                    } else if (vm.restaurantsTotalItems > 5){
                        paginationService.setCurrentPage("restaurantId",currentPage);
                    }
                }
                vm.loading = true;
                debugger;
                SearchService.searchCommon(searchParam, currentPage).then(function(response){
                    debugger;
                    localStorage.setItem('search', JSON.stringify(vm.searchParams));
                    vm.searchResult = response.data;
                    if (searchParam.searchToggle){
                        vm.menuLists = response.data;
                        vm.menuListsTotalItems = response.meta.pagination.total;
                        vm.menuListsItemsPerPage = response.meta.pagination.per_page;
                        angular.forEach(vm.menuLists, function(menu_list){
                            if (menu_list.photo)
                                menu_list.photo = appConstant.imagePath + menu_list.photo;
                            else
                                menu_list.photo = "assets/images/meal-placeholder.png";
                        });
                        vm.loading = false;
                    } else {
                        vm.restaurants = response.data;
                        angular.forEach(vm.restaurants, function(restaurant){
                            if (restaurant.logo){
                                restaurant.logo = appConstant.imagePath + restaurant.logo;
                            } else {
                                restaurant.logo = "assets/images/placeholder.jpg";
                            }
                        });
                        vm.restaurantsTotalItems = response.meta.pagination.total;
                        vm.restaurantsItemsPerPage = response.meta.pagination.per_page;
                        vm.loading = false;
                    }

                }, function(error){
                    vm.loading = false;
                });

            }
        }

        function addMenuListToCart(item){
            debugger;
            if (!$rootScope.currentUser){
                debugger;
                $state.go("main.login");
                return;
            }
            item.loading = true;
            var data = {
                "orders_detail" : {
                    "ID_restaurant": item.ID_restaurant,
                    "ID_menu_list": item.ID_menu_list,
                    "date": vm.searchParams.date,
                    "time": vm.searchParams.time
                },
                "lang": "en",
                "source": "search"
            };
            SearchService.addMenuListToCart(data).then(function(response){
                debugger;
                item.loading = false;
                item.ordered += response.data.x_number;
                $rootScope.$broadcast('orders-detail-changed');

            },function(error){
                debugger;
                item.loading = false;
            });
        }

        function getRestaurantTypes(){
            debugger;
            CoreService.getRestaurantTypes().then(function(response){
                vm.restaurantTypes = response.data;
                angular.forEach(vm.restaurantTypes, function (value, key) {
                    value['n_type'] = "RESTAURANT_TYPE." + value.name;
                });
            },function(error){

            });
        }
        function getKitchenTypes(){
            debugger;
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