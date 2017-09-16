/**
 * Created by Hamid Shafer on 2017-02-24.
 */

(function () {
    'use strict';

    angular
        .module('app.prereg')
        .controller('PreregController', PreregController);

    /*@ngNoInject*/
    function PreregController($state, $scope, $rootScope, $filter, PreregService, $translate) {

        var vm = this;
        $rootScope.currentState = "prereg";

        $rootScope.$watch('language', function(new_language, old_language){
            if(new_language != old_language){
                changeInvoiceSettingLang();
            }
        });

        vm.registerSupplier = registerSupplier;
        vm.closeAlert = closeAlert;
        vm.setActiveTab = setActiveTab;
        vm.editPreregistration = editPreregistration;
        vm.addEditRestaurant = addEditRestaurant;
        vm.editOwnerPreregistration = editOwnerPreregistration;
        vm.clearRestaurant = clearRestaurant;
        vm.clearOwner = clearOwner;

        vm.pageChangedforAssignments = pageChangedforAssignments;
        vm.getAssignments = getAssignments;
        vm.updateDealerForAssignment = updateDealerForAssignment;
        vm.updateContractForAssignment = updateContractForAssignment;
        vm.previousAssignment = {};
        vm.getTurnovers = getTurnovers;
        vm.getUserTuronverStatus = getUserTuronverStatus;
        vm.getSumTurnovers = getSumTurnovers;
        vm.assignment = {
            "currentPage": 1,
            "perPage": 10
        };
        vm.turnover = {
            "currentPage": 1,
            "perPage": 10,
            "userStatus": 0      
        }

        vm.turnover.daterange = {
            startDate: moment().subtract(7, 'days').format('YYYY-MM-DD'),
            endDate:moment().format('YYYY-MM-DD')
        };
        vm.drp_start = moment().subtract(7, 'days').format('YYYY-MM-DD');
        vm.drp_end = moment().format('YYYY-MM-DD');

        console.log(vm.turnover.daterange);
        vm.turnover_sum = {};
        vm.turnover.companies = "1";
        //vm.assignment.currentPage = 1;
        //vm.assignment.perPage = 10;
        //vm.turnover.currentPage = 1;
        //vm.turnover.perPage = 10;
        vm.copyRestorantData = copyRestorantData;
        vm.ClearData = ClearData;
        vm.copyAddressData = copyAddressData;
        vm.saveInvoiceData = saveInvoiceData;
        vm.getAllInvoices = getAllInvoices;
        vm.exportToPdf = exportToPdf;
        vm.changeInvoiceSettingLang = changeInvoiceSettingLang;
        vm.Total = Total;

        if ($state.current.name == "main.prereg"){
            getSuppliers();
            getDistricts();
            getInvoiceSetting();
            if($rootScope.currentUser.id){
                getRestaurants($rootScope.currentUser.id);
            }
        }
        vm.invoice_list= {};
        vm.invoice_list.unpaid = false;
        vm.invoice_list.paid = false;
        vm.invoice_list.overpaid = false;
        vm.invoice_list.partlypaid = false;
        vm.invoice_list.daterange = {
            startDate: moment().subtract(30, 'days').format('YYYY-MM-DD'),
            endDate:moment().subtract(-30, 'days').format('YYYY-MM-DD')
        };
        vm.pay_start = moment().subtract(30, 'days').format('YYYY-MM-DD');
        vm.pay_end = moment().subtract(-30, 'days').format('YYYY-MM-DD');

        vm.alertClass = "";
        vm.successMessage = "";
        vm.registrationError = "";
        vm.alertClassInvoice = "";
        vm.invoiceSuccessMessage = "";
        vm.invoiceError = "";
        vm.active_tab = $rootScope.prereg_active_tab ? $rootScope.prereg_active_tab : '';
        vm.loading = false;

        // server data
        vm.suppliers = [];
        vm.countries = [];
        vm.districts = [];
        vm.supplier = {};
        vm.restaurants = {};
        vm.selected_restaurant = {};
        vm.invoice_setting = [];
        vm.invoice_settings = {};
        vm.supplier.restaurants = [];
        vm.currentUser = JSON.parse(localStorage.getItem('user'));

        vm.getUserTuronverStatus();
        vm.invoice = {};
        vm.invoice.price = 0;
        vm.invoice.vat = 0;
        vm.invoice.invoice_value = 0;
        vm.invoice.invoice_payment = 0;
        vm.invoice.signature = false;

        function getSuppliers(){
            PreregService.getSuppliers(/*$rootScope.currentUser.id*/).then(function(response){
                vm.suppliers = response.data; //response.restaurants;
                // angular.forEach(response.data, function(supplier){
                   // console.log(supplier);
                // });
            }, function(error){
                debugger;
            });
        }


        function getDistricts(){
            PreregService.getDistricts().then(function(response){
                // console.log(response);
                vm.countries = response.countries;
                vm.districts = response.districts;
                // angular.forEach(response.data, function(supplier){
                //    console.log(supplier);
                // });
            }, function(error){
                debugger;
            });
        }
        function updateDealerForAssignment( assignment_id, status, assignment ){
            console.log(assignment_id, status);
            var params = {};
            params.id = angular.copy(assignment_id);
            params.status = status;
            params.user_id = vm.currentUser.id;

            PreregService.updateDealerForAssignment( params ).then(function(response){
                console.log(response);
                if (response.success){
                    //vm.assignments[id_dealer = assignment_id] = status;
                    assignment.id_user_dealer = status ? vm.currentUser.id : null;
                }
            },function(error){
                debugger;
            });
        }
        function updateContractForAssignment( assignment_id, status, assignment ){
            console.log(assignment_id, status);
            var params = {};
            params.id = angular.copy(assignment_id);
            params.status = status;
            params.user_id = vm.currentUser.id;

            PreregService.updateContractForAssignment( params ).then(function(response){
                console.log(response);
                if (response.success){
                    //vm.assignments[id_dealer = assignment_id] = status;
                    assignment.id_user_contract = status ? vm.currentUser.id : null;
                }
            },function(error){
                debugger;
            });
        }
        
        function pageChangedforAssignments(newPageNumber) {
            //console.log(newPageNumber);
            console.log("assignments:" + newPageNumber);
            vm.assignment.currentPage = newPageNumber;
            //debugger;
            getAssignments();
        }

        function getRestaurants(user_id){
            PreregService.getRestaurants(user_id).then(function(response){

                vm.restaurants = response.data;
            }, function(error){
                // debugger;
            });
        }
        function getAssignments(){

            /*if (( vm.assignment.id == "" || vm.assignment.id == null) && (vm.assignment.name == null || vm.assignment.name.length < 3 ) ){
                vm.assignment.error = 1;
                //alert("Please input ID or name.The length of name is 3 at least.");
                return;
            }*/
            vm.assignment.error = 0;
            var AssignmentParams = angular.copy(vm.assignment);
            console.log(AssignmentParams);

            PreregService.getAssignments(AssignmentParams).then(function(response){
                console.log(response.result);
                console.log(response.result.per_page);

                //vm.assignment.currentPage =  response.result.current_page;
                console.log(response.result.current_page);

                vm.assignment.totalItems =  response.result.total;
                vm.assignments = response.result.data;
                if (vm.assignments.length == 0 && vm.assignment.id != null ){
                    vm.assignment.no_match_with_id = "ID " + vm.assignment.id + " not found";
                }else{
                    vm.assignment.no_match_with_id = "";
                }
            },function(error){
                debugger;
            });
        }
        function getUserTuronverStatus(){
            var params = {};
            params.id = vm.currentUser.id;
             PreregService.getUserTuronverStatus(params).then(function(response){
                console.log(response);
                //vm.trunovers = response.result.result;
                vm.turnover.userStatus = response;
            },function(error){
                //debugger;
            });
        }
        function getTurnovers(isValid){
            if (!isValid) return;
            var TurnoverParams = angular.copy(vm.turnover);
            TurnoverParams.user_id = vm.currentUser.id;
            console.log(TurnoverParams);

            PreregService.getTurnovers(TurnoverParams).then(function(response){
                console.log(response);
                //vm.trunovers = response.result.result;
                vm.turnovers = response;
            },function(error){
                //debugger;
            });
        }
        function getSumTurnovers(){
            var TurnoverParams = angular.copy(vm.turnover);
            TurnoverParams.user_id = vm.currentUser.id;
            console.log(TurnoverParams);

            PreregService.getSumTurnovers(TurnoverParams).then(function(response){
                console.log(response);
                //vm.trunovers = response.result.result;
                vm.turnover_sum = response[0];
            },function(error){
                //debugger;
            });
        }

        function getInvoiceNumber(restaurant_id){
            PreregService.getInvoiceNumber(restaurant_id).then(function(response){

                if(response) {
                    if(parseInt(response) + 1 <= (vm.selected_restaurant.id * 1000 + 999)) {
                        vm.invoice_number = parseInt(response) + 1;
                    }
                    else {
                        vm.invoice_number = "Limit Invoice";
                        vm.alertClassInvoice = "danger";
                        vm.invoiceError = "Limit Invoice";
                    }
                }
                else {
                    vm.invoice_number = vm.selected_restaurant.id + '001';
                }
                // console.log(response);
            }, function(error){
                // debugger;
            });
        }

        function getInvoiceSetting(){
            PreregService.getInvoiceSetting().then(function(response){

                vm.invoice_settings = response.invoice_settings;

                vm.invoice.payment_form = vm.invoice_setting.payment_form_1;

                // Default language "ENG"

                angular.forEach(vm.invoice_settings, function(value, key) {

                    if(vm.invoice_settings[key].lang == "ENG")
                    {
                        vm.invoice_setting = vm.invoice_settings[key];
                    }
                });
                changeInvoiceSettingLang();

            }, function(error){
                // debugger;
            });
        }

        function changeInvoiceSettingLang(){
            angular.forEach(vm.invoice_settings, function(value, key) {

                if(vm.invoice_settings[key].lang == PreregService.getLanguageCode())
                {
                    vm.invoice_setting = vm.invoice_settings[key];
                }
            });
        }

        function registerSupplier(isValid)
        {
            if (!isValid || !vm.supplier.restaurants.length) return;

            vm.loading = true;

            PreregService.saveSupplier(vm.supplier, $rootScope.currentUser.id).then(function(response)
            {
                if (response.success)
                {
                    vm.edit_mode = 1;
                    vm.supplier.owner = response.owner;
                    vm.supplier.restaurants = response.restaurants;
                    for (var i = 0; i < vm.supplier.restaurants.length; i++) {
                        vm.supplier.restaurants[i].acquired = (vm.supplier.restaurants[i].ID_user_acquire === null) ? false : true;
                        delete vm.supplier.restaurants[i].ID_user_acquire;

                        vm.supplier.restaurants[i].signed = (vm.supplier.restaurants[i].ID_user_contract === null) ? false : true;
                        delete vm.supplier.restaurants[i].ID_user_contract;
                    }
                    vm.alertClass = "success";
                    vm.successMessage = response.message;
                    vm.registrationError = "";
                    getSuppliers();
                    $scope.preregOwnerForm.$setUntouched();
                    $scope.preregForm.$setUntouched();
                }
                else
                {
                    vm.alertClass = "danger";
                    vm.registrationError = response.message;
                }

                vm.loading = false;
                // $state.go("somewhere");
                
            }, function(error)
            {
                console.log(error);
                vm.alertClass = "danger";
                vm.registrationError = "Server Error" + ": " + (error.data.message);
                vm.loading = false;
            });
        }

        function editPreregistration(restaurant_id)
        {
            vm.edit_mode = 1;
            // vm.prereg_edit_restaurant_id = restaurant_id;
            for (var i = 0; i < vm.suppliers.length; i++)
            {
                // console.log(vm.suppliers[i]);
                if (vm.suppliers[i].restaurant_id == restaurant_id)
                {
                    vm.input_country = "";
                    vm.supplier = {
                        owner: {
                            id: vm.suppliers[i].ID_user,
                            name: vm.suppliers[i].owner_name,
                            email: vm.suppliers[i].owner_email,
                            phone: vm.suppliers[i].owner_phone,
                            password: '',
                            confirm_password: ''
                        },
                        restaurant: {
                            id: vm.suppliers[i].restaurant_id,
                            ID_district: vm.suppliers[i].ID_district,
                            name: vm.suppliers[i].restaurant_name,
                            email: vm.suppliers[i].restaurant_email,
                            phone: vm.suppliers[i].restaurant_phone,
                            www: vm.suppliers[i].restaurant_www,
                            acquired: vm.suppliers[i].ID_user_acquire || vm.suppliers[i].acquired ? true: false,
                            signed: vm.suppliers[i].ID_user_contract || vm.suppliers[i].signed ? true: false,
                            dealer_note: vm.suppliers[i].restaurant_dealer_note
                        }
                    };
                    vm.supplier.restaurants = $filter('filter')( vm.suppliers, {ID_user: vm.supplier.owner.id});
                    angular.forEach(vm.supplier.restaurants, function (restaurant) {
                        restaurant.id = restaurant.restaurant_id;
                        restaurant.ID_district = restaurant.ID_district;
                        restaurant.name = restaurant.restaurant_name;
                        restaurant.email = restaurant.restaurant_email;
                        restaurant.phone = restaurant.restaurant_phone;
                        restaurant.www = restaurant.restaurant_www;
                        restaurant.acquired = restaurant.ID_user_acquire || restaurant.acquired ? true: false;
                        restaurant.signed = restaurant.ID_user_contract || restaurant.signed ? true: false;
                        restaurant.dealer_note = restaurant.restaurant_dealer_note;
                    });
                    for (var i = 0; i < vm.supplier.restaurants.length; i++) {
                        if (restaurant_id == vm.supplier.restaurants[i].id) {
                            vm.editRestaurantIndex = i;
                            break;
                        }
                    }
                    for (var j = 0; j < vm.districts.length; j++)
                    {
                        if (vm.districts[j].ID == vm.suppliers[i].ID_district)
                        {
                            vm.input_country = vm.districts[j].country;
                            // console.log(vm.input_country);
                            break;
                        }
                    }
                    break;
                }
            }
            $scope.preregOwnerForm.$setUntouched();
            vm.setActiveTab('home');
        }

        function editOwnerPreregistration(index)
        {
            debugger;
            vm.editRestaurantIndex = index;
            vm.supplier.restaurant = {
                ID_district: vm.supplier.restaurants[index].ID_district,
                name: vm.supplier.restaurants[index].name,
                email: vm.supplier.restaurants[index].email,
                phone: vm.supplier.restaurants[index].phone,
                www: vm.supplier.restaurants[index].www,
                acquired: vm.supplier.restaurants[index].acquired,
                signed: vm.supplier.restaurants[index].signed,
                dealer_note: vm.supplier.restaurants[index].dealer_note
            };

            if(vm.supplier.restaurants[index].id) {
                vm.supplier.restaurant.id = vm.supplier.restaurants[index].id;
            }

            for (var j = 0; j < vm.districts.length; j++)
            {
                if (vm.districts[j].ID == vm.supplier.restaurants[index].ID_district)
                {
                    vm.input_country = vm.districts[j].country;
                    // console.log(vm.input_country);
                    break;
                }
            }

            $scope.preregOwnerForm.$setUntouched();
            vm.setActiveTab('home');
            // location.href
        }

        function closeAlert()
        {
            vm.alertClass = "";
            vm.successMessage = "";
            vm.registrationError = "";
            vm.alertClassInvoice = "";
            vm.invoiceSuccessMessage = "";
            vm.invoiceError = "";
        }

        function setActiveTab($tab)
        {
            if ($tab == undefined) {
                vm.active_tab = $rootScope.prereg_active_tab;
            }
            else {
                vm.active_tab = $tab;
                $rootScope.prereg_active_tab = $tab;
                if($tab == 'list_of_invoice'){
                    getAllInvoices();
                }
            }
        }

        function addEditRestaurant(isValid) {
            if (!$scope.preregForm.$valid) {
                return;
            }

            var restaurant = angular.copy(vm.supplier.restaurant);
            restaurant.lang = PreregService.getLanguageCode();
            if (angular.isDefined(vm.editRestaurantIndex) && vm.editRestaurantIndex >= 0) {
                vm.supplier.restaurants[vm.editRestaurantIndex] = restaurant;
                delete vm.editRestaurantIndex;
            } else {
                vm.supplier.restaurants.push(restaurant);
            }
            angular.copy({}, vm.supplier.restaurant);
            $scope.preregForm.$setUntouched();
        }

        function clearRestaurant() {
            delete vm.editRestaurantIndex;
            var district = vm.supplier.restaurant.ID_district;
            vm.supplier.restaurant = {};
            vm.supplier.restaurant.ID_district = district;
            $scope.preregForm.$setUntouched();
        }

        function clearOwner() {
            vm.edit_mode = false;
            vm.supplier.owner = {};
            vm.supplier.restaurants = [];
            clearRestaurant();
        }

        function ClearData() {
            vm.invoce_restorant_name = null;
            vm.currentUser_phone = null;
            vm.currentUser_name = null;
            vm.currentUser_email = null;

            vm.invoice.invoice_number = null;
            vm.invoice.invoice_taxable = null;
            vm.invoice.invoice_due = null;
            vm.invoice.restaurant = null;

            vm.selected_restaurant_company_number = null;
            vm.selected_restaurant_company_tax_number = null;
            vm.selected_restaurant_name = null;
            vm.invoice.price = 0;
            vm.invoice.vat = 0;
            vm.invoice.invoice_value = 0;
            vm.invoice.invoice_payment = 0;
            vm.invoice.note = '';
            vm.invoice.signature = false;
        }

        function Total(){
            vm.invoice.invoice_value = parseInt(vm.invoice.price * vm.invoice.vat / 100) + parseInt(vm.invoice.price);
        }

        function copyAddressData(selected_restaurant) {
            if(selected_restaurant != undefined)
            {
                getInvoiceNumber(vm.selected_restaurant.id);
                vm.today = new Date();
                vm.restaurant_address = vm.selected_restaurant.street +' '+ vm.selected_restaurant.city +' '+ vm.selected_restaurant.post_code;
            }
        }

        function copyRestorantData(selected_restaurant) {
            // console.log($scope.selected_restaurant);

            if(selected_restaurant != undefined)
            {
                vm.selected_restaurant = selected_restaurant;
                vm.invoce_restorant_name = selected_restaurant.name;
                vm.currentUser_phone = $rootScope.currentUser.phone;
                vm.currentUser_name = $rootScope.currentUser.name;
                vm.currentUser_email = $rootScope.currentUser.email;

                vm.selected_restaurant_company_number = vm.selected_restaurant.company_number;
                vm.selected_restaurant_company_tax_number = vm.selected_restaurant.company_tax_number;
                vm.invoice.company_name = vm.selected_restaurant.company_name;
                vm.invoice.company_address = vm.selected_restaurant.company_address;
            }
        }

        function saveInvoiceData(){
            vm.invoice.invoice_number = vm.invoice_number;
            vm.invoice.issue_date = vm.today;
            vm.invoice.restaurant = vm.selected_restaurant;
            vm.invoice.user = $rootScope.currentUser;
            vm.invoice.lang = PreregService.getLanguageCode();
            vm.invoice.subject_text = vm.invoice_setting.subject_text;

            if(vm.invoice.restaurant && parseInt(vm.invoice.invoice_number) && vm.invoice.invoice_value
                && vm.invoice.user && vm.invoice.payment_form && vm.invoice.invoice_taxable && vm.invoice.invoice_due)
            {
                PreregService.saveInvoiceData(vm.invoice).then(function(response){

                    if(response.success)
                    {
                        vm.alertClassInvoice = "success";
                        vm.invoiceSuccessMessage = response.message;
                        vm.invoiceError = "";
                        ClearData();
                        getInvoiceNumber(vm.selected_restaurant.id);
                    }
                    else
                    {
                        vm.alertClassInvoice = "danger";
                        vm.invoiceError = response.message;
                    }

                }, function(error){
                    // debugger;
                    vm.alertClassInvoice = "danger";
                    vm.invoiceError = "Server Error" + ": " + (error.data.message);
                });
            }
            else{
                vm.alertClassInvoice = "danger";
                vm.invoiceError = "Invoice not saved";
            }
        }

        function getAllInvoices(){
            var InvoiceListParams = angular.copy(vm.invoice_list);
            console.log(InvoiceListParams);
            PreregService.getAllInvoices(InvoiceListParams).then(function(response){
                vm.invoices = response.invoices;
            }, function(error){
                // debugger;
            });
        }

        function exportToPdf(invoice_id, invoice_number, to_email){
            var lang = PreregService.getLanguageCode();
            vm.loading = true;
            var data = {invoice_id: invoice_id, lang: lang, invoice_number: invoice_number, to_email: to_email};
            PreregService.exportToPdf(data,vm);
        }

        vm.openCalendarTaxable = function(){
            // debugger;
            vm.date_picker_taxable.open = true;
        };

        vm.date_picker_taxable = {
            date: new Date(),
            datepickerOptions: {
                showWeeks: false,
                minDate: moment().add(-1, 'days').toDate(),
                startingDay: 1

            }
        };
        vm.openCalendarDue = function(){
            // debugger;
            vm.date_picker_due.open = true;
        };

        vm.date_picker_due = {
            date: new Date(),
            datepickerOptions: {
                showWeeks: false,
                minDate: moment().add(-1, 'days').toDate(),
                startingDay: 1

            }
        };
    }

    $(document).ready(function(){

        $.getScript('//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.min.js',function(){

            $("#mySel").select2({

            });
        });//script
    });

})();