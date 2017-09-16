/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.profile')
        .controller('RestaurantEditController', RestaurantEditController);
    /*@ngNoInject*/
    function RestaurantEditController($state, $scope, Cropper, $timeout, appConstant, $location, $anchorScroll, $stateParams, $rootScope, ProfileService) {
        var vm = this;
        var data, file, item_type;
        vm.restaurant = [];
        vm.restaurantProfile = [];
        vm.userProfile = [];
        vm.interiorImages = [];
        vm.exteriorImages = [];
        vm.gardenImages = [];
        vm.editError = "";
        vm.updateRestaurantSuccess = "";
        vm.updateOpeningHoursSuccess = "";
        vm.updateRestaurantPasswordSuccess = "";
        vm.updateUserProfileSuccess = "";
        vm.updateError = "";
        vm.interior_cropper = false;
        vm.exterior_cropper = false;
        vm.garden_cropper = false;
        $rootScope.pic1 = false;
        $rootScope.pic2 = false;
        $rootScope.pic3 = false;
        vm.upload_error1 = "";
        vm.upload_error2 = "";
        vm.upload_error3 = "";
        vm.server_error = "";
        vm.uploadFile = uploadFile;
        vm.onFile = onFile;
        vm.crop = crop;
        vm.clear = clear;
        vm.closeAlert = closeAlert;
        vm.deletePicture = deletePicture;
        vm.updateRestaurant = updateRestaurant;
        vm.deleteRestaurant = deleteRestaurant;
        vm.updateRestaurantProfile = updateRestaurantProfile;
        vm.updateUserProfile = updateUserProfile;
        vm.updateRestaurantPassword = updateRestaurantPassword;
        vm.bshowDownload = false;
        vm.timeError = false;

        vm.time = {
            monday:{},
            tuesday: {},
            wednesday: {},
            thursday: {},
            friday: {},
            saturday: {},
            sunday: {}
        };
        getUser();
        getRestaurant();
        getRestaurantTypes();

        function getUser(){
            ProfileService.getCurrentUser().then(function(response){
                var user = response.user;

                vm.userProfile.name = user.name;
                vm.userProfile.phone = user.phone;
                vm.userProfile.email = user.email;

            }, function(error){

            });
        }

        function getRestaurant(){
            ProfileService.getRestaurant($stateParams.restaurantId).then(function(response){
                vm.restaurant = response.data;

                vm.userProfile.id = vm.restaurant.owner_id;
                vm.userProfile.name = vm.restaurant.owner_name;
                vm.userProfile.phone = vm.restaurant.owner_phone;
                vm.userProfile.email = vm.restaurant.owner_email;

                vm.restaurantProfile = response.data;
                if (vm.restaurant.photos){
                    vm.interiorImages = [];
                    vm.exteriorImages = [];
                    vm.gardenImages = [];
                    for (var i = 0; i < vm.restaurant.photos.data.length; i++){

                        var photo = vm.restaurant.photos.data[i];
                        if (photo.item_type == "interior"){
                            vm.interiorImages.push(appConstant.imagePath + photo.file_path);
                        } else if (photo.item_type == "exterior"){
                            vm.exteriorImages.push(appConstant.imagePath + photo.file_path);
                        } else if (photo.item_type == "garden"){
                            vm.gardenImages.push(appConstant.imagePath + photo.file_path);
                        }
                    }
                }
                if (vm.restaurant.openingHours && vm.restaurant.openingHours.data && vm.restaurant.openingHours.data.length){
                    ProfileService.getOpeningHours(vm.restaurant.id).then(function(response){

                        vm.time = response.data.time;
                    }, function(error){

                    });
                } else {
                    initialTimeSetup();
                }
                $('#map_holder2').locationpicker({
                    location: {latitude: vm.restaurant.latitude, longitude: vm.restaurant.longitude},
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

                $rootScope.pic1 = false;
                $rootScope.pic2 = false;
                $rootScope.pic3 = false;

                vm.bshowDownload = vm.restaurantProfile.password_added;
            }, function(error){

            });
        }

        function getRestaurantTypes(){
            ProfileService.getRestaurantTypes().then(function(response){

                vm.restaurantTypes = response.data;
                angular.forEach(vm.restaurantTypes, function (value, key) {
                    value['n_type'] = "RESTAURANT_TYPE." + value.name;
                });
            }, function(error){


            });
        }

        function updateRestaurant(isValid){

            if (isValid){
                if ($rootScope.currentUser.profile_type === "data" && vm.restaurant.ID_user_active === null){
                    vm.restaurant.ID_user_active = $rootScope.currentUser.id;
                }
                
                if (vm.restaurant.status === 'N') {
                    vm.restaurant.status = "A";
                }
                
                var restaurant = {
                    "restaurant" : vm.restaurant
                };
                

                
                ProfileService.updateRestaurant(restaurant).then(function(response){

                    vm.updateRestaurantSuccess = "Restaurant updated successfully!";
                    $location.hash("u");
                    $anchorScroll();

                }, function(error){


                });

                var owner = {
                    id: vm.restaurant.owner_id,
                    name: vm.restaurant.owner_name,
                    phone: vm.restaurant.owner_phone
                };

                ProfileService.updateUser(owner).then(function(response){
                    console.log(response);
                });


            }
        }

        function updateRestaurantPassword(isValid){

            if (isValid || 1){

                var restaurantProfile = {
                    "restaurant" : vm.restaurantProfile
                };



                ProfileService.updateSyncServOwn(restaurantProfile.restaurant.id).then(function(response){
                    ProfileService.updateRestaurantProfile(restaurantProfile).then(function(response){
                        restaurantProfile.password = "";

                        vm.updateRestaurantPasswordSuccess = "Restaurant Profile updated successfully!";
                        $location.hash("u");
                        $anchorScroll();
                        vm.bshowDownload = true;
                    }, function(error){


                    });
                }, function(error){


                });
            }
        }

        function updateRestaurantProfile(isValid){

            if (isValid){
                var restaurantProfile = {
                    "restaurant" : vm.restaurantProfile
                };



                ProfileService.updateRestaurantProfile(restaurantProfile).then(function(response){
                    restaurantProfile.password = "";

                    vm.updateSuccess = "Restaurant Profile updated successfully!";
                    $location.hash("u");
                    $anchorScroll();

                }, function(error){


                });
            }
        }

        function updateUserProfile(isValid){

            if (isValid){

                var user = {
                    "id" : vm.restaurant.owner_id,
                    "name" : vm.userProfile.name,
                    "phone" : vm.userProfile.phone,
                    "email" : vm.userProfile.email,
                    "password" : vm.userProfile.password
                };



                ProfileService.updateUser(user).then(function(response){

                    vm.updateUserProfileSuccess = "User Profile updated successfully!";
                    $location.hash("u");
                    $anchorScroll();

                }, function(error){


                });
            }
        }

        function deleteRestaurant(restaurantId){
            ProfileService.deleteRestaurant(restaurantId).then(function(response){

                $state.go("main.profile");
            }, function(error){

            });
        }

        vm.start_time = "From";
        vm.changeTime = function(date, shift, value){
            vm.time[date][shift] = value;

            if(isNaN(parseInt(value))){
                var shift_other = '';

                if(shift == 'm_start'){
                    shift_other = 'm_end';
                } else if(shift == 'm_end'){
                    shift_other = 'm_start';
                } else if(shift == 'a_start'){
                    shift_other = 'a_end';
                } else if(shift == 'a_end'){
                    shift_other = 'a_start';
                }

                vm.time[date][shift_other] = value;

            }
        };
        vm.changeOthers = function(shift, value){

            var keys = Object.keys(vm.time);

            for (var i = 0; i < keys.length; i++){
                vm.time[keys[i]][shift] = value;
            }

            if(isNaN(parseInt(value))){
                var shift_other = '';

                if(shift == 'm_start'){
                    shift_other = 'm_end';
                } else if(shift == 'm_end'){
                    shift_other = 'm_start';
                } else if(shift == 'a_start'){
                    shift_other = 'a_end';
                } else if(shift == 'a_end'){
                    shift_other = 'a_start';
                }

                for (var i = 0; i < keys.length; i++){
                    vm.time[keys[i]][shift_other] = value;
                }
            }
        };

        vm.initialTimeSetup = initialTimeSetup;

        function initialTimeSetup(){
            var keys = Object.keys(vm.time);
            for (var i = 0; i < keys.length; i++){
                vm.time[keys[i]].m_start = vm.time[keys[i]].a_start = "From";
                    vm.time[keys[i]].m_end = vm.time[keys[i]].a_end = "Until";

            }

        }

        function timed(time1, time2, param ){
            if(param != 'not'){
                if((time1.length > 0 && time2.length == 0) || (time1.length == 0 && time2.length > 0)){
                    return true;
                }
            }

            if ( time1.match(/^[0-9]{4}$/g) && time2.match(/^[0-9]{4}$/g) )
            {
                //lets calculate the difference. But values consist of four digits.
                var time1Seconds = toSeconds(time1.substr(0,2), time1.substr(2));
                var time2Seconds = toSeconds(time2.substr(0,2), time2.substr(2));

                if (!time1Seconds || !time2Seconds)
                {
                    //input is not correct.
                    return false;
                }

                var difference = time1Seconds - time2Seconds;
                if (difference < 0)
                {
                    return false;
                    // difference = Math.abs(difference);
                }

                return true;
                // var hours = parseInt(difference/3600)
                // hours = hours < 10 ? "0" + hours : hours;
                // var minutes =  parseInt((difference/3600) % 1 *60)
                // minutes = minutes < 10 ? "0" + minutes : minutes;

                // return hours + ":" + minutes;
            }
        }

        function toSeconds(hours, minutes)
        {
            var seconds = 0;
            if ( (hours >= 0 && hours <= 24) && (minutes >= 0 && minutes < 60))
            {
                seconds += (parseInt(hours)*3600) + (parseInt(minutes)*60);
                return seconds
            }
            else
            {
                return false;
            }

        }

        function checkValid(){
            var keys = Object.keys(vm.time);

            for (var i = 0; i < keys.length; i++){
                if (vm.time[keys[i]].m_start == "From" ||
                    vm.time[keys[i]].m_end == "Until"){
                    return {'success' : false, 'error' : 'emptyField'};
                }

                var a_starting_time = vm.time[keys[i]].a_start.replace(/\D/g,'');
                var a_ending_time   = vm.time[keys[i]].a_end.replace(/\D/g,'');
                var m_starting_time = vm.time[keys[i]].m_start.replace(/\D/g,'');
                var m_ending_time   = vm.time[keys[i]].m_end.replace(/\D/g,'');

                if(m_starting_time.length == 0 && m_ending_time.length == 0 && (a_starting_time.length > 0 || a_ending_time.length > 0 )){
                    return {'success' : false, 'error' : 'emptyField'};
                }

                if(a_starting_time.length == 3){
                    a_starting_time = '0' + a_starting_time;
                }

                if(a_ending_time.length == 3){
                    a_ending_time = '0' + a_ending_time;
                }

                if(m_starting_time.length == 3){
                    m_starting_time = '0' + m_starting_time;
                }

                if(m_ending_time.length == 3){
                    m_ending_time = '0' + m_ending_time;
                }

                var timeError1 = timed(m_starting_time, m_ending_time, 'm');
                var timeError2 = timed(a_starting_time, a_ending_time, 'a');
                var timeError3 = timed(m_ending_time, a_starting_time, 'not');

                if(timeError1 || timeError2 || timeError3){
                    return {'success' : false, 'error' : 'timeError'};
                }

            }

            return {'success' : true, 'error' : ''};
        }

        vm.updateOpeningHours = function(time){
            var time = {
                "time": time
            };

            var resultCheck = checkValid();
            console.log('resultCheck = ', resultCheck);

            if (resultCheck.success){
                ProfileService.updateOpeningHours($stateParams.restaurantId, time).then(function(response){

                    vm.updateError = "";
                    vm.updateOpeningHoursSuccess = "Opening Hours updated successfully!";
                }, function(error){

                });
            } else {
                vm.updateOpeningHoursSuccess = "";

                if(resultCheck.error == "emptyField"){
                    vm.timeError = false;
                    vm.updateError = "You must fill morning opening hours!";
                } else {
                    vm.timeError = true;
                    vm.updateError = "Start date cannot be big then end date!";
                }
            }
        };

        vm.times = [
            "7:00", "7:30", "8:00", "8:30", "9:00", "9:30", "10:00", "10:30", "11:00", "11:30",
            "12:00", "12:30", "13:00", "13:30", "14:00", "14:30", "15:00", "15:30", "16:00", "16:30",
            "17:00", "17:30", "18:00", "18:30", "19:00", "19:30", "20:00", "20:30",
            "21:00", "21:30", "22:00", "22:30", "23:00", "23:30", "24:00", "0:00","0:30", "1:00", "1:30",
            "2:00", "2:30", "3:00", "3:30", "4:00", "4:30", "5:00", "5:30",
            "6:00", "6:30"
        ];

        vm.openCalendar = function(e, picker) {
            vm[picker].open = true;
        };

        vm.time_picker = {
            date: new Date('2015-03-01T12:30:00Z'),
            timepickerOptions: {

            }
        };
        function deletePicture(url){

            var jsonURL = {
                "url": url
            };
            ProfileService.deletePicture(jsonURL).then(function(response){

                getRestaurant();
            }, function(error){

            });
        }

        vm.changePic = function() {
            $rootScope.pic1 = true;
        }

        function uploadFile(file) {

            if (file) {

                if (item_type == "interior"){

                    $rootScope.pic1 = true;
                } else if(item_type == "exterior"){

                    $rootScope.pic2 = true;
                } else if (item_type == "garden"){

                    $rootScope.pic3 = true;
                }
                ProfileService.uploadFile(file, $stateParams.restaurantId, item_type).then(function(response){

                    getRestaurant();
                    clear();

                }, function(error){

                    vm.server_error = "Error uploading your file! Please try again!";
                    clear();
                    $rootScope.pic1 = false;
                    $rootScope.pic2 = false;
                    $rootScope.pic3 = false;
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
            } else if (value == "updateRestaurantSuccess"){
                vm.updateRestaurantSuccess = "";
            } else if (value == "updateOpeningHoursSuccess"){
                vm.updateOpeningHoursSuccess = "";
            } else if (value == "updateRestaurantPasswordSuccess"){
                vm.updateRestaurantPasswordSuccess = "";
            } else if (value == "updateUserProfileSuccess") {
                vm.updateUserProfileSuccess = "";
            } else if (value == "error"){
                vm.updateError = "";
            }
        }

        function onFile(blob, type) {

            if (blob){
                item_type = type;
                if (item_type == "interior"){
                    if (vm.interiorImages.length >= 3){
                        vm.upload_error1 = "You can't upload more than 3 images!";

                        $rootScope.pic1 = false;
                        vm.dataUrl = null;
                        return;
                    }
                    vm.interior_cropper = true;
                    vm.exterior_cropper = false;
                    vm.garden_cropper = false;
                } else if (item_type == "exterior"){
                    if (vm.exteriorImages.length >= 1){

                        vm.upload_error2 = "You can't upload more than 1 image!";
                        $rootScope.pic2 = false;
                        vm.dataUrl = null;
                        return;
                    }
                    vm.exterior_cropper = true;
                    vm.interior_cropper = false;
                    vm.garden_cropper = false;
                } else if (item_type == "garden") {
                    if (vm.gardenImages.length >= 2){

                        vm.upload_error3 = "You can't upload more than 2 images!";
                        $rootScope.pic3 = false;
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