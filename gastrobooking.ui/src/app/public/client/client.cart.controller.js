/**
 * Created by Thomas on 10/14/2016.
 */

(function () {
    'use strict';

    angular
        .module('app.client')
        .controller('ClientCartController', ClientCartController);
    /*@ngNoInject*/
    function ClientCartController($state, $rootScope,$scope,$timeout,ClientCartService,ClientService, uiDatetimePickerConfig, $translate, $stateParams, moment, appConstant, RestaurantDetailService, $geolocation, AuthService) {
        var vm = this;
        vm.show = true;

        $rootScope.currentState = 'cart';
        vm.getOrders = getOrders;
        vm.getOrderDetail = getOrderDetail;
        vm.getDiscountCodes = getDiscountCodes;
        vm.getFriends = getFriends;
        vm.getTotalPrice = getTotalPrice;
        vm.changePrice = changePrice;
        vm.placeOrder = placeOrder;
        vm.deleteOrder = deleteOrder;
        vm.deleteOrderFromCart = deleteOrderFromCart;
        vm.deleteOrderDetail = deleteOrderDetail;
        vm.saveChanges = saveChanges;
        vm.changeSideDishFromSelect = changeSideDishFromSelect;
        vm.closeAlert = closeAlert;
        vm.getMainDishName = getMainDishName;
        vm.availableSideDishes = availableSideDishes;
        vm.orders = [];
        vm.order = null;
        vm.orderDetail = [];
        vm.isOrderDetailEmpty = false;
        vm.isOrderEmpty = false;
        vm.friends = [];
        vm.selectedFriend = null;
        vm.totalPrice = 0;
        vm.orderSuccess = false;
        vm.orderError = false;
        vm.errorMessage = '';
        vm.successMessage = '';
        vm.restaurantId = $state.params.restaurantId;
        vm.loading = false;
        vm.removeSideDish = removeSideDish;
        vm.redirectWidget = redirectWidget;
        vm.openMap = openMap;
        vm.clearDelivery = clearDelivery;
        vm.deliveryAvailable = true;
        vm.pickUpAvailable = true;
        vm.clearTimeList = clearTimeList;
        vm.currentPosition = "";
        vm.timeList = [
            { minute: "10", value: "10 min" },
            { minute: "20", value: "20 min" },
            { minute: "30", value: "30 min" },
            { minute: "40", value: "40 min" },
            { minute: "50", value: "50 min" },
            { minute: "60", value: "1 hour" },
            { minute: "75", value: "1 hour 15 min" },
            { minute: "90", value: "1 hour 30 min" },
            { minute: "105", value: "1 hour 45 min"},
            { minute: "120", value: "2 hours"}
        ];



        vm.currentClientId = 0;
        $translate(['DATE_PICKER.NOW', 'DATE_PICKER.CLEAR', 'DATE_PICKER.CLOSE', 'DATE_PICKER.DATE', 'DATE_PICKER.TIME', 'DATE_PICKER.TODAY'])
            .then(function(translations){
                uiDatetimePickerConfig.buttonBar.now.text = translations["DATE_PICKER.NOW"];
                uiDatetimePickerConfig.buttonBar.clear.text = translations["DATE_PICKER.CLEAR"];
                uiDatetimePickerConfig.buttonBar.close.text = translations["DATE_PICKER.CLOSE"];
                uiDatetimePickerConfig.buttonBar.date.text = translations["DATE_PICKER.DATE"];
                uiDatetimePickerConfig.buttonBar.time.text = translations["DATE_PICKER.TIME"];
                uiDatetimePickerConfig.buttonBar.today.text = translations["DATE_PICKER.TODAY"];
            }, function(translationId){

                uiDatetimePickerConfig.buttonBar.now.text = translationId["DATE_PICKER.NOW"];
                uiDatetimePickerConfig.buttonBar.clear.text = translationId["DATE_PICKER.CLEAR"];
                uiDatetimePickerConfig.buttonBar.close.text = translationId["DATE_PICKER.CLOSE"];
                uiDatetimePickerConfig.buttonBar.date.text = translationId["DATE_PICKER.DATE"];
                uiDatetimePickerConfig.buttonBar.time.text = translationId["DATE_PICKER.TIME"];
                uiDatetimePickerConfig.buttonBar.today.text = translationId["DATE_PICKER.TODAY"];
            });
        $rootScope.$on('$translateChangeSuccess', function(){
            $translate(['DATE_PICKER.NOW', 'DATE_PICKER.CLEAR', 'DATE_PICKER.CLOSE', 'DATE_PICKER.DATE', 'DATE_PICKER.TIME', 'DATE_PICKER.TODAY']).then(function(translations){

                uiDatetimePickerConfig.buttonBar.now.text = translations["DATE_PICKER.NOW"];
                uiDatetimePickerConfig.buttonBar.clear.text = translations["DATE_PICKER.CLEAR"];
                uiDatetimePickerConfig.buttonBar.close.text = translations["DATE_PICKER.CLOSE"];
                uiDatetimePickerConfig.buttonBar.date.text = translations["DATE_PICKER.DATE"];
                uiDatetimePickerConfig.buttonBar.time.text = translations["DATE_PICKER.TIME"];
                uiDatetimePickerConfig.buttonBar.today.text = translations["DATE_PICKER.TODAY"];
            }, function(translationId){

                uiDatetimePickerConfig.buttonBar.now.text = translationId["DATE_PICKER.NOW"];
                uiDatetimePickerConfig.buttonBar.clear.text = translationId["DATE_PICKER.CLEAR"];
                uiDatetimePickerConfig.buttonBar.close.text = translationId["DATE_PICKER.CLOSE"];
                uiDatetimePickerConfig.buttonBar.date.text = translationId["DATE_PICKER.DATE"];
                uiDatetimePickerConfig.buttonBar.time.text = translationId["DATE_PICKER.TIME"];
                uiDatetimePickerConfig.buttonBar.today.text = translationId["DATE_PICKER.TODAY"];
            });
        });


        // load order details if state is main.clientCart
        if ($state.current.name == "main.clientCart"){
            getOrderDetail($state.params.restaurantId);
        }

        getOrders();
        getFriends();
        getTotalPrice();
        getDiscountCodes();

        vm.date_picker = {
            date: new Date('d.m.y H:i'),
            datepickerOptions: {
                showWeeks: false,
                minDate: new Date(),
                startingDay: 1
            }
        };

        vm.storage = {
            discount_code: "PQR",
            discount_percentage : 5
        };
        vm.apply_code = false;
        vm.apply_code_used = false;
        vm.apply_amount = 0;
        vm.discountId = null;

        $scope.calculateDiscount = function(){
            var calculate = '';
            var discount_per = 0;
            var discount_amount = null;
            vm.apply_code = false;
            vm.apply_code_used = false;

            if(vm.order !== null){
                discount_per = vm.order.gb_discount;
            }

            if(vm.discount_codes != null && vm.discount_codes.length > 0){

                angular.forEach(vm.discount_codes, function(value, key) {

                    if(value.code === vm.entered_discount_code && value.used === null){
                        discount_amount = value.value;
                        vm.apply_code = true;
                        vm.apply_code_used = false;
                        vm.apply_amount = value.value;
                        vm.discountId = value.ID;
                        vm.order.gb_discount = vm.order.gb_discount || 0;
                        vm.order.gb_discount_amount = value.value;
                    }
                    else if(value.code === vm.entered_discount_code && value.used !== null){
                        vm.apply_code = false;
                        vm.apply_code_used = true;
                    }
                });
            }

            if(discount_per && !discount_amount){
                discount_amount = vm.totalPrice * discount_per / 100;
            }
            else if(discount_per && discount_amount){
                discount_amount = (vm.totalPrice * discount_per / 100) + parseInt(discount_amount);
            }

            // Discount Price Is Grater Then total price
            if( parseInt(discount_amount ) > parseInt(vm.totalPrice) ){
                //  Math.abs(input);
                calculate = 0;
            }else{

                if(vm.orderDetail.length){
                    vm.order.total_price = vm.totalPrice;
                    calculate = parseInt( vm.totalPrice - discount_amount ) + ' ' + vm.orderDetail[0].menu_list.data.currency;
                }
            }
            // vm.totalPrice = vm.totalPrice - vm.apply_amount;

            return calculate;
        };

        /**
         * Logout user after 3 times improper filling
         * */
        var __wrongCodeCount = 0;
        $scope.checkDiscountCode = function(){

            if(__wrongCodeCount === 2 && !vm.apply_code){
                AuthService.logout();
            }
            else if(!vm.apply_code){
                __wrongCodeCount++;
            }
            else if(vm.apply_code){
                __wrongCodeCount = 0;
            }
        };

        $scope.$watch('vm.order.gb_discount', function(nV){

            var newValue = parseInt(nV);

            console.log('ORDERS: ', vm.order);
            if(!vm.order.max_gb_discount){
                vm.order.gb_discount = 0;
            }
            else if(newValue > vm.order.max_gb_discount){
                vm.order.gb_discount = vm.order.max_gb_discount;
            }
            else if(newValue < 0){
                vm.order.gb_discount = 0;
            }
        });

        vm.openCalendar = function(e, picker) {
            vm.orderDetail[picker].date_picker = true;
        };

        function getOrders(){
            vm.loading = true;
            ClientCartService.getOrders().then(function (response) {

                if(response.data && response.data.length == 0){
                    vm.isOrderEmpty = true;
                } else {
                    var orders = response.data;
                    vm.orders = [];
                    angular.forEach(orders,function(order){
                        if(order.total_order_details != 0){
                            vm.orders.push(order);
                        }
                    });
                    if (vm.orders.length == 1){
                        $state.go("main.clientCart", {restaurantId: vm.orders[0].ID_restaurant});
                    }
                }
                console.log('ORDERS: ', orders);
                vm.loading = false;
            }, function (error) {

                vm.loading = true;
            });
        }

        function getDiscountCodes(){
            vm.loading = true;
            ClientCartService.getDiscountCodes().then(function (response) {
                if(response.data && response.data.length == 0){
                    vm.isOrderEmpty = true;
                } else {
                    var discount_codes = response.data;
                    vm.discount_codes = [];
                    angular.forEach(discount_codes,function(discount_code){
                        vm.discount_codes.push(discount_code);
                    });
                }
                vm.loading = false;
            }, function (error) {

                vm.loading = true;
            });
        }

        function addNewKeys(orders) {

            angular.forEach(orders, function (value) {

                value['visible'] = true;
                value['date_picker'] = false;
                value['serve_at'] = value['serve_at'] == "30.11.-0001 00:00" ? new Date() : moment(value['serve_at'], "DD.MM.YYYY HH:mm").toDate();

                if (value.menu_list.data.photo){
                    value['photo'] = appConstant.imagePath + value.menu_list.data.photo;
                } else {
                    value['photo'] = "assets/images/meal-placeholder.png";
                }

                value['side_dish_bool'] = value['side_dish'] != '0';
                value['is_child'] = value['is_child'] != 0;
                value['friends'] = vm.friends;

                if (value.is_child){
                    value['t_price'] = (value.menu_list.data.price_child > 0 ? value.menu_list.data.price_child :value.menu_list.data.price ) * value.x_number;
                } else {
                    value['t_price'] = value.menu_list.data.price * value.x_number;
                }
            });
        }

        function getFriends() {
            ClientService.getFriendCircle().then(function (response) {

                if(!response.error) {
                    vm.friends = response.data;
                }
            }, function (error) {

            });
        }

        function getTotalPrice() {
            vm.totalPrice = 0;

            angular.forEach(vm.orderDetail, function (value, key) {

                if (value.is_child){
                    vm.totalPrice += (value.menu_list.data.price_child ? value.menu_list.data.price_child : value.menu_list.data.price) * value.x_number;
                } else {
                    vm.totalPrice += value.menu_list.data.price * value.x_number;
                }

            });
            vm.totalPrice = vm.totalPrice.toFixed(2);
        }

        function changePrice(order) {

            if(order.side_dish == 0) {

                angular.forEach(order.sideDish.data, function(side_dish) {

                    if(side_dish.side_dish == order.ID_orders_detail) {
                        side_dish.x_number = order.x_number;
                    }
                });

                angular.forEach(vm.orderDetail, function(side_dish) {

                    if(side_dish.side_dish == order.ID_orders_detail) {
                        side_dish.x_number = order.x_number;

                        if (side_dish.is_child){
                            side_dish.t_price = (side_dish.menu_list.data.price_child > 0 ? side_dish.menu_list.data.price_child :side_dish.menu_list.data.price ) * side_dish.x_number;
                        } else {
                            side_dish.t_price = side_dish.menu_list.data.price * side_dish.x_number;
                        }
                    }
                });
            }

            if (order.is_child){
                order.t_price = (order.menu_list.data.price_child > 0 ? order.menu_list.data.price_child :order.menu_list.data.price ) * order.x_number;
            } else {
                order.t_price = order.menu_list.data.price * order.x_number;
            }

            getTotalPrice();
        }

        function getOrder(orderId) {

            ClientService.getOrder(orderId).then(function (response) {

                vm.order = response.data;

                /** Set max value of quiz discount */
                vm.order.max_gb_discount = parseInt(vm.order.gb_discount) || 0;

                vm.currentClientId = response.data.ID_client;

                for (var i = 0; i < vm.order.orders_detail.data.length; i++ ) {
                    if (vm.order.orders_detail.data[i].menu_list.data.delivered == 0){
                        vm.deliveryAvailable = false;
                        break;
                    }
                }

                for (var i = 0; i < vm.order.orders_detail.data.length; i++ ) {
                    if (vm.order.orders_detail.data[i].menu_list.data.pick_up !== "Y"){
                        vm.pickUpAvailable = false;
                        break;
                    }
                }

                if (vm.deliveryAvailable && (vm.order.delivery_address !== null || vm.order.delivery_phone !== null)) {
                    vm.order.delivery = true;
                    vm.pickUpAvailable = false;
                    vm.order.pick_up = false;
                }
                else {
                    vm.order.delivery = false;
                    vm.currentPosition = "";
                }
                vm.order.pick_up = (vm.pickUpAvailable && (vm.order.pick_up === "Y")) ? true : false;
                vm.order.table_until = (vm.order.delivery || vm.order.pick_up)? "" : vm.timeList[2];
                getMyLocation();
            }, function (error) {

            })
        }

        function getOrderDetail(restaurantId) {
            vm.loading = true;

            ClientCartService.getOrderDetail(restaurantId).then(function (response) {

                if (response.error || (response.data && response.data.length == 0)) {
                    vm.isOrderDetailEmpty = true;
                } else {
                    vm.orderDetail = response.data;


                    initializeAvailableSideDishes(vm.orderDetail);
                    addNewKeys(vm.orderDetail);
                    getTotalPrice();
                    getOrder(vm.orderDetail[0].ID_orders);
                }

                vm.loading = false;
            }, function (error) {

                vm.loading = true;
            });
        }

        function getURLParameter(name) {
            return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)
                || [null, ''])[1].replace(/\+/g, '%20')) || null;
        }

        function placeOrder(isValid) {

            if (isValid)
            {

                if (!checkTableInterval()) {
                    return;
                }
                vm.loading = true;

                vm.order.partner = getURLParameter('partner');
                vm.order.code = vm.entered_discount_code;

                changeOrdersServeAt();

                var order = {
                    "order": vm.order,
                    "orderDetail" : vm.orderDetail,
                    "discountId": vm.discountId,
                    "lang": localStorage.getItem('NG_TRANSLATE_LANG_KEY') ? localStorage.getItem('NG_TRANSLATE_LANG_KEY') : $rootScope.language
                };
                console.log(order);

                ClientCartService.placeOrder(order).then(function (response) {

                    if (response.data){
                        vm.orderSuccess = "CLIENT.ORDER SUCCESS MSG";
                        vm.orderError = "";
                        vm.orderDetail = [];
                        $rootScope.$broadcast('orders-detail-changed');
                        $timeout(function(){
                            vm.orderSuccess = "";
                            $rootScope.currentTab="booking";
                            $state.go('main.clientDashboard');
                        }, 4000);
                    }
                    else if(response.wrongServingTime) {
                        vm.orderError = "CLIENT.WRONG SERVING TIME";
                        vm.wrongServing = response.wrongServingTime;
                        // $timeout(function(){
                        //     vm.orderError = "";
                        // }, 5000);
                    } else if (response.requestError){
                        vm.orderError = "CLIENT.ORDER ERROR";
                        // $timeout(function(){
                        //     vm.orderError = "";
                        // }, 3500);
                    }
                    vm.getOrderDetail($state.params.restaurantId);
                    vm.loading = false;
                }, function (error) {

                    vm.orderError = "CLIENT.ORDER ERROR";
                    $rootScope.$broadcast('orders-detail-changed');
                    getOrderDetail($state.params.restaurantId)
                    $timeout(function(){
                        vm.orderError = "";
                    }, 3500);
                    vm.loading = false;
                });
            }
        }

        function closeAlert(ers) {

            if (ers == "error") {
                vm.orderError = "";
            } else {
                vm.orderSuccess = "";
            }
        }

        function deleteOrder(order) {

            ClientCartService.deleteOrder(order.ID_orders).then(function (response) {
                $rootScope.$broadcast('orders-detail-changed');
                vm.getOrders()
            }, function (error) {

            });
        }
        function deleteOrderFromCart(order) {

            ClientCartService.deleteOrder(order.ID_orders).then(function (response) {
                $rootScope.$broadcast('orders-detail-changed');
                $state.go("main.clientOrders");
            }, function (error) {

            });
        }

        function deleteOrderDetail(orderDetail) {

            ClientCartService.deleteOrderDetail(orderDetail.ID_orders_detail).then(function (response) {

                // Delete from side dishes of main dish if it is side_dish
                var mainDish = findDishById(orderDetail.side_dish);
                if(angular.isDefined(mainDish)) {
                    var sideDishes = mainDish.sideDish.data;
                    for (var i = 0; i < sideDishes.length; i++) {
                        if (sideDishes[i].ID_orders_detail == orderDetail.ID_orders_detail) {
                            sideDishes.splice(i, 1);
                            break;
                        }
                    }
                }

                //Remove all side dishes from vm.ordersDetail if it has any side dishes
                for(var i = 0; i < vm.orderDetail.length; i++) {
                    if(vm.orderDetail[i].side_dish == orderDetail.ID_orders_detail) {
                        vm.orderDetail.splice(i, 1);
                    }
                }

                //Remove main dish from order
                var delete_index = vm.orderDetail.indexOf(findDishById(orderDetail.ID_orders_detail));
                if(delete_index > -1) {
                    vm.orderDetail.splice(delete_index, 1);
                }
                getTotalPrice();
                $rootScope.$broadcast('orders-detail-changed');

            }, function (error) {
                alert("Error. Please call administrator!")
            });
        }

        function saveChanges(isValid, changeState) {

            if (!vm.orderDetail.length){
                if (changeState) {
                    $state.go("main.restaurant_detail", { "restaurantId" : vm.restaurantId});
                    return;
                } else {
                    return;
                }
            }
            if (vm.orderDetail.length > 0 && isValid){
                if (!checkTableInterval()) {
                    return;
                }
                vm.loading = true;
                var lang = localStorage.getItem('NG_TRANSLATE_LANG_KEY') ? localStorage.getItem('NG_TRANSLATE_LANG_KEY') : $rootScope.language;
                var order = {
                    "order": vm.order,
                    "lang": lang,
                    "discountId": vm.discountId
                };

                var orders_detail = {
                    "orders_detail": vm.orderDetail,
                    "lang": lang

                };
                var resp = 0;

                ClientService.updateOrder(order).then(function (response) {

                    getOrder(vm.orderDetail[0].ID_orders);
                    if (resp){
                        vm.loading = false;
                    } else {
                        resp = 1;
                    }
                    if (changeState){
                        angular.forEach(vm.orderDetail, function(item){

                            item.date_picker = false;
                        });

                        $state.go("main.restaurant_detail", { "restaurantId" : vm.restaurantId})
                    }
                    vm.orderSuccess = "CLIENT.ORDER SAVE SUCCESS MSG";
                    if (vm.order.delivery_address === null && vm.order.delivery_phone === null) {
                        vm.currentPosition = "";
                    }
                    resetLocationHTML();
                    $timeout(function(){
                        vm.orderSuccess = "";
                    }, 3000);
                }, function (error) {
                    if (resp){
                        vm.loading = false;
                    } else {
                        resp = 1;
                    }
                });

                ClientService.updateOrderDetails(orders_detail).then(function (response) {

                    vm.getOrderDetail(vm.restaurantId);
                    if (resp){
                        vm.loading = false;
                    } else {
                        resp = 1;
                    }
                }, function (error) {

                    if (resp){
                        vm.loading = false;
                    } else {
                        resp = 1;
                    }
                })
            }


        }

        // NOTE that order_detail.side_dish is actually referring to the id of the order_detail which is the main dish
        // for this side dish
        function removeSideDish(order_detail){
            if(order_detail.recommended_side_dish == 1) {
                vm.deleteOrderDetail(order_detail);
            } else {
                removeSideDishFromMainDish(order_detail);
                ClientCartService.removeSideDish(order_detail);
                order_detail.side_dish = 0;
                $rootScope.$broadcast('orders-detail-changed');
            }
        }

        // Removes the sideDish from the list of side dishes (mainDish.sideDish) of the mainDish
        function removeSideDishFromMainDish(sideDish){
            var mainDishObj = findDishById(sideDish.side_dish)
            var sideDishes = mainDishObj.sideDish.data;

            for(var i=0; i<sideDishes.length; i++){
                if(sideDishes[i].ID_orders_detail == sideDish.ID_orders_detail){
                    sideDishes.splice(i, 1);
                    break;
                }
            }
        }
        // Returns the number of side dishes that a dish contains. Main dish is object of type order_detail
        function countSideDishesForMainDish(mainDish){
            var counter = 0;

            angular.forEach(vm.orderDetail, function(orderDetail){
                if(orderDetail.side_dish == mainDish){
                    counter++;
                }
            });

            return counter;
        }

        // This function is called when the dishes are first loaded from the server. We iterate each dish and set the
        // additional properties that we need for proper rendering of the dishes and their side dishes lists.
        // Again, NOTE that the property side_dish refers to the id of the main dish and is only set if the dish is
        // actually a side dish. When the dish does not have a main dish, side_dish is set to '0' or ''. This is poor
        // implementation and it should be set to NULL, but for now I won't change it since that is out of my scope
        function initializeAvailableSideDishes(order_details){
            angular.forEach(order_details, function(order_detail){
                convertMenuListsToOrdersDetails(order_detail.menu_list.data.recommended_side_dishes);
            });
        }

        function addSideDishToCart(mainDish, side_dish){
            var menuList = side_dish.menu_list.data;
            mainDish.loading = true;
            var data = {
                "orders_detail" : {
                    "ID_restaurant": menuList.ID_restaurant,
                    "ID_menu_list": menuList.ID_menu_list ? menuList.ID_menu_list: menuList.ID,
                    "date": mainDish.serve_at,
                    "time": undefined,
                    "side_dish": mainDish.ID_orders_detail,
                    "x_number": mainDish.x_number,
                    "recommended_side_dish": side_dish.ID_orders_detail ? 0 : 1
                },
                "lang": "en",
                "source": "detail"
            };

            RestaurantDetailService.addMenuListToCart(data).then(function(response){

                var addedSideDish = response.data;
                if (side_dish.ID_orders_detail && side_dish.x_number > mainDish.x_number) {
                    side_dish.x_number = side_dish.x_number - mainDish.x_number;
                    var lang = localStorage.getItem('NG_TRANSLATE_LANG_KEY') ? localStorage.getItem('NG_TRANSLATE_LANG_KEY') : $rootScope.language;
                    ClientService.updateOrderDetails({"orders_detail": [side_dish], "lang": lang }).then(function (response) {
                        console.info('response', response);
                    }, function (error) {
                        alert('Could not update dish data!');
                    })
                }
                addedSideDish.visible = true;
                changePrice(addedSideDish);
                addedSideDish.menu_list.data.photo = addedSideDish.menu_list.data.photo ?
                    appConstant.imagePath + addedSideDish.menu_list.data.photo : "assets/images/meal-placeholder.png"
                vm.orderDetail.push(addedSideDish);
                mainDish.sideDish.data.push(addedSideDish);
                mainDish.loading = false;
                getTotalPrice();

                $rootScope.$broadcast('orders-detail-changed');

            },function(error){
                mainDish.loading = false;
            });
        }

        function setAsSideDishInCart(mainDish, side_dish){
            var menuList = side_dish.menu_list.data;
            mainDish.loading = true;
            var data = {
                "orders_detail" : {
                    "ID_orders_detail": side_dish.ID_orders_detail,
                    "ID_restaurant": menuList.ID_restaurant,
                    "ID_menu_list": menuList.ID_menu_list ? menuList.ID_menu_list: menuList.ID,
                    "date": mainDish.serve_at,
                    "time": undefined,
                    "side_dish": mainDish.ID_orders_detail,
                    "x_number": mainDish.x_number,
                    "recommended_side_dish": side_dish.ID_orders_detail ? 0 : 1
                },
                "lang": "en",
                "source": "detail"
            };

            RestaurantDetailService.addMenuListToCart(data).then(function(response){
                side_dish.mainDish = mainDish.ID_orders_detail;
                side_dish.side_dish = mainDish.ID_orders_detail;
                side_dish.isSideDish = true;
                side_dish.serve_at = mainDish.serve_at;
                side_dish.x_number = mainDish.x_number;
                mainDish.hasSideDishes = true;
                mainDish.sideDish.data.push(side_dish);
                mainDish.loading = false;
                // item.ordered = response.data.x_number;
                $rootScope.$broadcast('orders-detail-changed');

            },function(error){

                mainDish.loading = false;
            });
        }

        // This callback is invoked when the user selects a side dish from the dropdown menu.
        function changeSideDishFromSelect(order, selectedSideDish) {

            //Swap item from order
            if(angular.isUndefined(selectedSideDish.ID_orders_detail)) {
                var swapDish = getAvailableDishFromOrder(order, selectedSideDish);
                if(swapDish) {
                    selectedSideDish = swapDish;
                }
            }

            if(angular.isUndefined(selectedSideDish.ID_orders_detail) ||
                (angular.isDefined(selectedSideDish.ID_orders_detail) && order.x_number < selectedSideDish.x_number)) {
                addSideDishToCart(order, selectedSideDish);
            } else if (angular.isDefined(selectedSideDish.ID_orders_detail) && order.x_number == selectedSideDish.x_number) {
                setAsSideDishInCart(order, selectedSideDish);
            }
            vm.tempSideDish = null;
        }

        function removeSideDishesForMainDish(mainDish){
            angular.forEach(vm.orderDetail, function(orderDetail){
                if(orderDetail.side_dish == mainDish){
                    orderDetail.side_dish = null;
                    orderDetail.mainDish = null;
                    orderDetail.isSideDish = false;
                }
            });
        }

        // Iterates through our vm.orderDetail object (which is really just and array of dishes), and returns the one
        // with the id of ID_orders_detial
        function findDishById(ID_orders_detail){
            for(var i=0; i<vm.orderDetail.length; i++){
                var orderDetail = vm.orderDetail[i];
                if(orderDetail.ID_orders_detail == ID_orders_detail){
                    return orderDetail;
                }
            }
        }

        function getMainDishName(sideDish) {
            if(angular.isDefined(sideDish.side_dish) && sideDish.side_dish != 0 && sideDish.side_dish) {
                var mainDishObj = findDishById(sideDish.side_dish);
                if(angular.isDefined(mainDishObj)) {
                    return mainDishObj.menu_list.data.name;
                }
            } else
                return "";
        }


        // New functionality

        function availableSideDishes(mainDish) {
            return function(sideDish) {
                if(mainDish.menu_list == undefined) {
                    console.info('mainDish', mainDish)
                }
                if(sideDish.menu_list == undefined) {
                    console.info('sideDish', sideDish)
                }
                if(mainDish.menu_list.data.name == sideDish.menu_list.data.name) {
                    return false;
                }
                return (mainDish.x_number <= sideDish.x_number || !sideDish.ID_orders_detail)
                    && !dishHasSideDishes(sideDish) && !mainDishHasDishAsSideDish(mainDish, sideDish)
                    && !dishIsSideDish(sideDish);
            }
        }

        function sideDishIsAddedToMainDish(mainDish, sideDish) {
            return mainDish.sideDish.data.some(function (item) {
                return (item.menu_list.data.ID_menu_list == sideDish.menu_list.data.ID_menu_list ||
                item.menu_list.data.ID == sideDish.menu_list.data.ID_menu_list)
            });
        }

        function dishIsSideDish(dish) {
            return dish.side_dish && dish.side_dish != 0;
        }

        function dishHasSideDishes(dish) {
            return dish.sideDish && dish.sideDish.data && dish.sideDish.data.length > 0;
        }

        function convertMenuListsToOrdersDetails(menuListsArray) {
            for(var i=0; i < menuListsArray.length; i++) {
                menuListsArray[i] = {menu_list: {data: menuListsArray[i]}};
            }
        }

        function mainDishHasDishAsSideDish(mainDish, sideDish) {
            for(var i = 0; i < mainDish.sideDish.data.length; i++) {
                if(mainDish.sideDish.data[i].menu_list.data.name == sideDish.menu_list.data.name) {
                    return true;
                }
            }
            return false;
        }

        function getAvailableDishFromOrder(mainDish, sideDish) {
            for(var i = 0; i < vm.orderDetail.length; i++) {
                //Check if names are equal and other requirements are valid
                if(vm.orderDetail[i].menu_list.data.name == sideDish.menu_list.data.name && !vm.orderDetail[i].sideDish.length
                    && vm.orderDetail[i].side_dish == 0 && mainDish.x_number <= vm.orderDetail[i].x_number) {
                    return vm.orderDetail[i];
                }
            }
        }

        function redirectWidget() {
            var restaurantId = localStorage.getItem('widget__restaurantId')
            return $stateParams.app == 'widget' ?
                $state.href('main.restaurant_detail', {restaurantId: restaurantId}) :
                $state.href('main.search');
        }

        function getMyLocation(){
            if (vm.order.delivery_latitude === null || vm.order.delivery_longitude === null) {
                $geolocation.getCurrentPosition({
                    timeout: 6000
                }).then(function(position) {
                    vm.order.delivery_latitude = position.coords.latitude;
                    vm.order.delivery_longitude = position.coords.longitude;
                    locale();
                }, function(error){

                });
            }
            else {
                locale();
            }
        }

        function locale() {
            var locationpicker = $('#map_holder');
            locationpicker.locationpicker('autosize');
            locationpicker.locationpicker({
                location: {latitude: vm.order.delivery_latitude, longitude: vm.order.delivery_longitude},
                radius: 0,
                zoom: 15,
                markerDraggable: false,
                enableAutocomplete: true,
                inputBinding: {
                    locationNameInput: $('#locationInput')
                },
                autocompleteOptions: {
                    types: ['(regions)']
                },
                onlocationnotfound: function(locationName){
                    console.log(locationName);
                },
                onchanged: function(currentLocation){
                    vm.order.delivery_latitude = currentLocation.latitude;
                    vm.order.delivery_longitude = currentLocation.longitude;
                }
            });
        }

        function openMap() {
            $("#rest_location").modal();
            $timeout(locale, 1000);
        }

        function clearDelivery(valid) {

            if (valid === true) {
                $scope.cartForm.delivery_address.$setUntouched();
                $scope.cartForm.delivery_phone.$setUntouched();
                $scope.cartForm.currentPosition.$setUntouched();
                $scope.cartForm.$setPristine();
                vm.order.pick_up = false;
                vm.pickUpAvailable = false;
            }
            else {
                vm.pickUpAvailable = true;
            }
            clearTimeList(vm.order.pick_up || valid);
        }

        function clearTimeList(valid){
            vm.order.table_until = valid ? "" : vm.timeList[2];
            $scope.cartForm.table_until.$setUntouched();
        }

        function resetLocationHTML() {
            var cloneLocation = $("#rest_location").clone();
            $("#rest_location").remove();
            cloneLocation.appendTo("body");
        }

        function checkTableInterval() {
            if (vm.order.delivery == false && vm.order.pick_up == false && vm.orderDetail.length) {
                var lowestServeTime, maxServeTime;

                for (var i = 0; i < vm.orderDetail.length; i++){
                    if (vm.orderDetail[i].side_dish == 0) {
                        lowestServeTime = vm.orderDetail[i].serve_at;
                        maxServeTime = vm.orderDetail[i].serve_at;
                        break;
                    }
                }
                for (var i = 0; i < vm.orderDetail.length; i++){
                    if (vm.orderDetail[i].side_dish != 0) {
                        continue;
                    }
                    lowestServeTime = (lowestServeTime > vm.orderDetail[i].serve_at) ? vm.orderDetail[i].serve_at : lowestServeTime;
                    maxServeTime = (maxServeTime < vm.orderDetail[i].serve_at) ? vm.orderDetail[i].serve_at : maxServeTime;
                }

                var sumServeTime = new Date(lowestServeTime.getTime() + vm.order.table_until.minute * 60000);

                if (sumServeTime < maxServeTime) {
                    vm.loading = false;
                    vm.orderError = "the lowest and maximum serving time should be in the range of " + vm.order.table_until.value + "!";
                    return false;
                }
                else {
                    vm.order.table_until = sumServeTime;
                }
            }
            else {
                vm.order.table_until = null;
            }
            vm.orderError = false;
            return true;
        };

        function changeOrdersServeAt() {
            if (vm.order.delivery == true || vm.order.pick_up == true && vm.orderDetail.length) {
                var lowestServeTime;

                for (var i = 0; i < vm.orderDetail.length; i++){
                    if (vm.orderDetail[i].side_dish == 0) {
                        lowestServeTime = vm.orderDetail[i].serve_at;
                        break;
                    }
                }

                for (var i = 0; i < vm.orderDetail.length; i++){
                    if (vm.orderDetail[i].side_dish != 0) {
                        continue;
                    }
                    lowestServeTime = (lowestServeTime > vm.orderDetail[i].serve_at) ? vm.orderDetail[i].serve_at : lowestServeTime;
                }

                for (var i = 1; i < vm.orderDetail.length; i++){
                    vm.orderDetail[i].serve_at = lowestServeTime;
                }
            }
        }



    }
})();