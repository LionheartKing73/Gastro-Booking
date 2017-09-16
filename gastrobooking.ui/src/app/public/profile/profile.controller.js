/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.profile')
        .controller('ProfileController', ProfileController);
    /*@ngNoInject*/
    function ProfileController($state,$scope, $rootScope, Upload, $geolocation, ProfileService, Cropper, $timeout, appConstant, $translate) {
        var vm = this;
        vm.latitude = "";
        vm.longitude = "";
        vm.interior_cropper = false;
        vm.exterior_cropper = false;
        vm.garden_cropper = false;
        vm.registrationError = "";
        vm.restPicture = "";

        var file, data, item_type;
        var extPic, intPic, gardenPic = [];
        $rootScope.restaurant = {};
        $rootScope.restaurants = {};
        $rootScope.currentRestaurant = {};
        $rootScope.currentState = "profile";
        var rest = {};
        $rootScope.loading1 = false;
        $rootScope.loading2 = false;
        $rootScope.loading3 = false;
        $rootScope.uploading = false;
        vm.upload_error1 = "";
        vm.upload_error2 = "";
        vm.upload_error3 = "";
        vm.server_error = "";
        vm.location_error = "";
        vm.restaurant_type = "";
        vm.loading = false;
        vm.uploadFile = uploadFile;
        vm.getLocation = getLocation;
        vm.onFile = onFile;
        vm.crop = crop;
        vm.clear = clear;
        vm.closeAlert = closeAlert;
        vm.deleteAll = deleteAllRestaurants;
        vm.deletePicture = deletePicture;
        vm.deleteRestaurant = deleteRestaurant;

        vm.numberPattern = (function() {
            var regexp = /^\(?(\d{3})\)?[ .-]?(\d{3})[ .-]?(\d{4})$/;
            return {
                test: function(value) {
                    return regexp.test(value);
                }
            };
        })();

        vm.openCalendar = function(e, picker) {
            vm[picker].open = true;
        };

        vm.m_start_picker = {
            date: new Date('2015-03-01T12:30:00Z'),
            timepickerOptions: {

            }
        }
        vm.m_end_picker = {
            date: new Date('2015-03-01T12:30:00Z'),
            timepickerOptions: {

            }
        }
        vm.a_start_picker = {
            date: new Date('2015-03-01T12:30:00Z'),
            timepickerOptions: {

            }
        }
        vm.a_end_picker = {
            date: new Date('2015-03-01T12:30:00Z'),
            timepickerOptions: {

            }
        }


        debugger;
        vm.saveRestaurant = saveRestaurant;

        if ($state.current.name == "main.restaurant"){
            getLocation();
            getRestaurantTypes();
        }

        if ($state.current.name == "main.profile"){
            getRestaurants();
        }

        if ($state.current.name == "main.upload"){
            getCurrentRestaurant();
        }

        function getRestaurantTypes(){
            ProfileService.getRestaurantTypes().then(function(response){
                debugger
               vm.restaurantTypes = response.data;
                angular.forEach(vm.restaurantTypes, function (value, key) {
                    value['n_type'] = "RESTAURANT_TYPE." + value.name;
                });
                debugger;
            }, function(error){
                debugger;

            });
        }

        function getRestaurants(){
            ProfileService.getRestaurants($rootScope.currentUser.id).then(function(response){
                debugger;
                $rootScope.restaurants = response.data;
                angular.forEach(response.data, function(restaurant){
                    debugger;
                   if (restaurant.logo){
                       restaurant.restPicture = appConstant.imagePath + restaurant.logo;
                   } else {
                       restaurant.restPicture = "assets/images/placeholder.jpg";
                   }
                });
            }, function(error){
                debugger;
            });
        }

        function deleteAllRestaurants(){
            ProfileService.deleteRestaurants($rootScope.currentUser.id).then(function(response){
                debugger;
                getRestaurants();
            }, function(error){
                debugger;
            });
        }

        function saveRestaurant(isValid){
            debugger;
            if(isValid){
                vm.loading = true;
                var restaurant = {
                    "restaurant": {
                        "name": vm.name,
                        "restaurant_type": vm.restaurant_type,
                        "email": vm.email,
                        "www": vm.www,
                        "phone": vm.phone,
                        "street": vm.street,
                        "city": vm.city,
                        "post_code": vm.post_code,
                        "latitude": vm.latitude,
                        "address_note": vm.address_input,
                        "longitude": vm.longitude,
                        "bank_code": vm.bank_code,
                        "short_desc": vm.short_description,
                        "long_desc": vm.description,
                        "company_number": vm.company_number,
                        "account_number": vm.account_number,
                        "company_tax_number": vm.company_tax_number,
                        "company_name" : vm.company_name,
                        "company_address" : vm.company_address,
                        "lang": getLanguageCode(),
                        "sms_phone": vm.sms_phone
                    }
                };

                ProfileService.updateUser($rootScope.currentUser).then(function(response){
                    var user = JSON.stringify(response.data);
                    localStorage.setItem('user', user);
                });

                ProfileService.saveRestaurant(restaurant, $rootScope.currentUser.id).then(function(response){
                    $rootScope.currentRestaurant = response.data;
                    rest = response.data;
                    vm.loading = false;
                    $state.go("main.upload");
                    debugger;
                }, function(error){
                    debugger;
                    vm.loading = false;
                });
            }
        }

        function getCurrentRestaurant(){
            debugger;
            ProfileService.getCurrentRestaurant($rootScope.currentUser.id).then(function(response){
                debugger;
                if (response.data){
                    $rootScope.currentRestaurant = response.data;
                    if (response.data.photos){
                        vm.interiorImages = [];
                        vm.exteriorImages = [];
                        vm.gardenImages = [];
                        for (var i = 0; i < response.data.photos.data.length; i++){
                            debugger;
                            var photo = response.data.photos.data[i];
                            if (photo.item_type == "interior"){
                                vm.interiorImages.push(appConstant.imagePath + photo.file_path);
                            } else if (photo.item_type == "exterior"){
                                vm.exteriorImages.push(appConstant.imagePath + photo.file_path);
                            } else if (photo.item_type == "garden"){
                                vm.gardenImages.push(appConstant.imagePath + photo.file_path);
                            }
                        }
                    }
                }
                $rootScope.loading1 = false;
                $rootScope.loading2 = false;
                $rootScope.loading3 = false;
            }, function(error){
                debugger;
            });
        }

        function deletePicture(url){
            debugger;
            var jsonURL = {
                "url": url
            };
            ProfileService.deletePicture(jsonURL).then(function(response){
                debugger;
                getCurrentRestaurant();
            }, function(error){
                debugger;
            });
        }

        function getLocation(){
            $geolocation.getCurrentPosition({
                timeout: 60000
            }).then(function(position) {
                vm.latitude = position.coords.latitude;
                vm.longitude = position.coords.longitude;
                $('#map_holder').locationpicker({
                    location: {latitude: vm.latitude, longitude: vm.longitude},
                    radius: 300,
                    zoom: 15,
                    inputBinding: {
                        latitudeInput: $("#latitude"),
                        longitudeInput: $("#longitude"),
                        radiusInput: $('#radius'),
                        locationNameInput: $('#address_input')
                    },
                    enableAutocomplete: true
                });
            }, function(error){
                vm.location_error = "We couldn't locate your location! Please enter your location in the following box";
                $('#map_holder').locationpicker({
                    location: {latitude: 49.8209226, longitude: 18.262524299999995},
                    radius: 300,
                    zoom: 15,
                    inputBinding: {
                        latitudeInput: $("#latitude"),
                        longitudeInput: $("#longitude"),
                        radiusInput: $('#radius'),
                        locationNameInput: $('#address_input')
                    },
                    enableAutocomplete: true
                });
            });
        }

        function uploadFile(file) {
            debugger;
            if (file && $rootScope.currentRestaurant) {
                debugger;
                if (item_type == "interior"){
                    $rootScope.loading1 = true;
                } else if(item_type == "exterior"){
                    $rootScope.loading2 = true;
                } else if (item_type == "garden"){
                    $rootScope.loading3 = true;
                }
                ProfileService.getCurrentRestaurant($rootScope.currentUser.id).then(function(response){
                    if (response.data){
                        $rootScope.currentRestaurant = response.data;
                        ProfileService.uploadFile(file, response.data.id, item_type).then(function(resp){
                            debugger;
                            if (resp.error){
                                vm.server_error = "Upload Error! Please try again!";
                                return;
                            }
                            getCurrentRestaurant();
                            clear();
                        }, function(error){
                            debugger;
                            vm.server_error = "Error uploading your file! Please try again!";

                            clear();
                            $rootScope.loading1 = false;
                            $rootScope.loading2 = false;
                            $rootScope.loading3 = false;
                        });
                    }


                }, function(error){
                    debugger;
                    vm.server_error = "Internal Server Error! Please try again later!";
                    clear();
                    $rootScope.loading1 = false;
                    $rootScope.loading2 = false;
                    $rootScope.loading3 = false;
                });
            }
        }

        function closeAlert(value){
            if (value == 1){
                vm.upload_error1 = "";
            } else if (value == 2){
                vm.upload_error2 = "";
            } else if (value == 3){
                vm.upload_error3 = "";
            } else if (value == "server"){
                vm.server_error = "";
            } else if (value == "restaurant"){
                vm.location_error = "";
            }
        }

        function onFile(blob, type) {
            debugger;
            if (blob){
                item_type = type;
                if (item_type == "interior"){
                    debugger;
                    if (vm.interiorImages.length >= 3){
                        vm.upload_error1 = "You can't upload more than 3 images!";
                        debugger;
                        $rootScope.loading1 = false;
                        vm.dataUrl = null;
                        return;
                    }
                    vm.interior_cropper = true;
                    vm.exterior_cropper = false;
                    vm.garden_cropper = false;
                } else if (item_type == "exterior"){
                    if (vm.exteriorImages.length >= 1){
                        debugger;
                        vm.upload_error2 = "You can't upload more than 1 images!";
                        $rootScope.loading2 = false;
                        vm.dataUrl = null;
                        return;
                    }
                    vm.exterior_cropper = true;
                    vm.interior_cropper = false;
                    vm.garden_cropper = false;
                } else if (item_type == "garden") {
                    if (vm.gardenImages.length >= 2){
                        debugger;
                        vm.upload_error3 = "You can't upload more than 2 image!";
                        $rootScope.loading3 = false;
                        vm.dataUrl = null;
                        return;
                    }
                    vm.garden_cropper = true;
                    vm.interior_cropper = false;
                    vm.exterior_cropper = false;
                }
                vm.dataUrl = null;
                Cropper.encode((file = blob)).then(function(dataUrl) {
                    vm.dataUrl = dataUrl;
                    $timeout(showCropper);  // wait for $digest to set image's src

                });
            }


        }

        function crop(){
            if (!file || !data) return;
            Cropper.crop(file, data).then(function(blob){
                blob.name = file.name;
                vm.uploadFile(blob);
                vm.dataUrl = null;
                file = null;
                hideCropper();
                vm.interior_cropper = false;
                vm.exterior_cropper = false;
                vm.garden_cropper = false;

            });
        }

        vm.cropper = {};
        vm.cropperProxy = 'cropper.first';

        function clear() {
            debugger;
            // if (!vm.cropper.first) return;
            // vm.cropper.first('clear');
            $timeout(hideCropper);
            vm.dataUrl = null;
            vm.interior_cropper = false;
            vm.exterior_cropper =false;
            vm.garden_cropper = false;
        }

        vm.options = {
            maximize: true,
            aspectRatio: 4/3,
            crop: function(dataNew) {
                data = dataNew;
            }
        };

        vm.showEvent = 'show';
        vm.hideEvent = 'hide';

        function showCropper() { $scope.$broadcast(vm.showEvent); }
        function hideCropper() { $scope.$broadcast(vm.hideEvent); }

        function deleteRestaurant(restaurantId) {
            debugger;
            ProfileService.deleteRestaurant(restaurantId).then(function(response){
                debugger;
                getRestaurants();
            }, function(error){
                debugger;
            });

        }

        function closeModal(modalId) {
            debugger;
        }

        function getLanguageCode() {
            var langCode = {
                "en" : "ENG",
                "cs" : "CZE"
            }
            var currentLang = $translate.use(); 
            if (currentLang && currentLang in langCode) {
                return langCode[currentLang];
            }
            return "ENG";
        }
    }

})();