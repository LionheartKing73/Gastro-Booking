/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.public.profile')
        .controller('ProfileController', ProfileController);
    /*@ngNoInject*/
    function ProfileController($state,$scope, $rootScope, Upload, $geolocation, ProfileService, Cropper, $timeout, appConstant) {
        var vm = this;
        vm.latitude = "";
        vm.longitude = "";
        vm.interior_cropper = false;
        vm.exterior_cropper = false;
        vm.garden_cropper = false;
        vm.registrationError = "";
        var file, data, item_type;
        $rootScope.restaurant = {};
        $rootScope.restaurants = {};
        $rootScope.currentRestaurant = {};
        var rest = {};
        $rootScope.loading1 = false;
        $rootScope.loading2 = false;
        $rootScope.loading3 = false;
        vm.upload_error1 = "";
        vm.upload_error2 = "";
        vm.upload_error3 = "";
        vm.server_error = "";
        vm.location_error = "";
        vm.restaurant_type = "";
        vm.uploadFile = uploadFile;
        vm.getLocation = getLocation;
        vm.onFile = onFile;
        vm.crop = crop;
        vm.clear = clear;
        vm.closeAlert = closeAlert;
        vm.deleteAll = deleteAllRestaurants;
        vm.deletePicture = deletePicture;

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
        if ($state.current.name == "main.public.restaurant"){
            // getLocation();
            getRestaurantTypes();
        }
        if ($state.current.name == "main.public.profile"){
            getRestaurants();
        }
        if ($state.current.name == "main.public.upload"){
            getCurrentRestaurant();
        }

        function getRestaurantTypes(){
            ProfileService.getRestaurantTypes().then(function(response){
                debugger
               vm.restaurantTypes = response.restaurant_types;
            }, function(error){
                debugger;

            });
        }

        function getRestaurants(){
            ProfileService.getRestaurants($rootScope.currentUser.id).then(function(response){
                debugger;
                $rootScope.restaurants = response.restaurants;
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
                var restaurant = {
                    "restaurant": {
                        "name": vm.name,
                        "restaurant_type": vm.restaurant_type,
                        "email": vm.email,
                        "phone": vm.phone,
                        "street": vm.street,
                        "city": vm.city,
                        "post_code": vm.post_code,
                        "latitude": vm.latitude,
                        "longitude": vm.longitude,
                        "long_desc": vm.description
                    }
                };

                ProfileService.saveRestaurant(restaurant, $rootScope.currentUser.id).then(function(response){
                    $rootScope.currentRestaurant = response.restaurant;
                    rest = response.restaurant;
                    $state.go("main.public.upload");
                    debugger;
                }, function(error){
                    debugger;
                });
            }
        }

        function getCurrentRestaurant(){
            debugger;
            ProfileService.getCurrentRestaurant($rootScope.currentUser.id).then(function(response){
                debugger;
                if (response.restaurant){
                    $rootScope.currentRestaurant = response.restaurant;
                    if (response.restaurant.photos){
                        vm.interiorImages = [];
                        vm.exteriorImages = [];
                        vm.gardenImages = [];
                        for (var i = 0; i < response.restaurant.photos.length; i++){
                            debugger;
                            var photo = response.restaurant.photos[i];
                            if (photo.item_type == "interior"){
                                vm.interiorImages.push(appConstant.imagePath + photo.upload_directory + photo.minified_image_name);
                            } else if (photo.item_type == "exterior"){
                                vm.exteriorImages.push(appConstant.imagePath + photo.upload_directory + photo.minified_image_name);
                            } else if (photo.item_type == "garden"){
                                vm.gardenImages.push(appConstant.imagePath + photo.upload_directory + photo.minified_image_name);
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
                    if (response.restaurant){
                        $rootScope.currentRestaurant = response.restaurant;
                        ProfileService.uploadFile(file, response.restaurant.id, item_type).then(function(resp){
                            debugger;
                            if (resp.error){
                                vm.server_error = "Upload Error! Please try again!";
                                return;
                            }
                            getCurrentRestaurant();
                            clear();
                        }, function(error){
                            debugger;
                            vm.server_error = "Error uploading your file! Please try again! ID = " + response.restaurant ? response.restaurant.id : "";
                            if (error.data && error.data.message){
                                vm.server_error += "Detail: " + error.data.message;
                            }
                            if (error.statusText){
                                vm.server_error += error.statusText;
                            }
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
            }
        }

        function onFile(blob, type) {
            debugger;
            if (blob){
                item_type = type;
                if (item_type == "interior"){
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
                    if (vm.exteriorImages.length >= 2){
                        debugger;
                        vm.upload_error2 = "You can't upload more than 2 images!";
                        $rootScope.loading2 = false;
                        vm.dataUrl = null;
                        return;
                    }
                    vm.exterior_cropper = true;
                    vm.interior_cropper = false;
                    vm.garden_cropper = false;
                } else if (item_type == "garden") {
                    if (vm.gardenImages.length >= 1){
                        debugger;
                        vm.upload_error3 = "You can't upload more than 1 image!";
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


        };

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
        };

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


    }

})();