/**
 * Created by Hamid Shafer on 2017-02-24.
 */

(function () {
    'use strict';

    angular
        .module('app.prereg')
        .service('PreregService', PreregService);
    /*@ngNoInject*/
    function PreregService(TokenRestangular, $translate) {
        var service = {
            getSuppliers: getSuppliers,
            getDistricts: getDistricts,
            getAssignments: getAssignments,
            getUserTuronverStatus: getUserTuronverStatus,
            getTurnovers: getTurnovers,
            getSumTurnovers: getSumTurnovers,
            updateDealerForAssignment: updateDealerForAssignment,
            updateContractForAssignment: updateContractForAssignment,
            saveSupplier: saveSupplier,
            getRestaurants: getRestaurants,
            getInvoiceSetting: getInvoiceSetting,
            getLanguageCode: getLanguageCode,
            getInvoiceNumber:getInvoiceNumber,
            saveInvoiceData:saveInvoiceData,
            getAllInvoices:getAllInvoices,
            exportToPdf:exportToPdf
        };
        return service;

        function getSuppliers() {
            return TokenRestangular.all('prereg').customGET('');
        }

        function getDistricts() {
            return TokenRestangular.all('prereg/districts').customGET('');
        }

        function getAssignments(params){
            return TokenRestangular.all('prereg/assignments').customGET('', params);
        }
        function updateDealerForAssignment(params ){
            return TokenRestangular.all('prereg/update_assign_dealer').customPOST(params);
        }
        function updateContractForAssignment( params){
            return TokenRestangular.all('prereg/update_assign_contract').customPOST(params);   
        }
        function getUserTuronverStatus(params){
            return TokenRestangular.all('prereg/user_turnover_status').customGET('', params);
        }
        function getTurnovers(params){
            return TokenRestangular.all('prereg/turnovers').customGET('', params);
        }
        function getSumTurnovers(params){
            return TokenRestangular.all('prereg/sumturnovers').customGET('', params);
        }
        function getRestaurants(user_id){
            return TokenRestangular.all('restaurant/' + user_id + '/get_active_restaurants').customGET('');
        }

        function getInvoiceNumber(restaurant_id){
            return TokenRestangular.all('invoice/' + restaurant_id + '/get_invoice_number').customGET('');
        }

        function getInvoiceSetting(){
            return TokenRestangular.all('invoice_setting/get_invoice_setting').customGET('');
        }

        function saveSupplier(supplier, user_id) {
            return TokenRestangular.all('prereg/' + user_id).customPOST(supplier);
        }

        function saveInvoiceData(invoice) {
            return TokenRestangular.all('invoice/save_invoice').customPOST(invoice);
        }

        function getAllInvoices(invoice) {
            return TokenRestangular.all('invoice/get_all_invoices').customPOST(invoice);
        }

        function exportToPdf(data,vm) {
            TokenRestangular.one('invoice/export_to_pdf_send_email')
                .withHttpConfig({ responseType: 'arraybuffer' })
                .customPOST(data)
                .then(function (response) {
                    var fileName = 'invoice_'+data.invoice_number +'.pdf';
                    var file = new Blob([response], {type: 'application/pdf'});
                    if (window.Blob && window.navigator.msSaveBlob) {
                        window.navigator.msSaveBlob(file, fileName);
                    } else {
                        var a = document.createElement('a');
                        document.body.appendChild(a);
                        a.style = 'display: none';
                        var fileURL = (window.URL || window.webkitURL).createObjectURL(file);

                        a.href = fileURL;
                        a.download = fileName;
                        a.click();
                        (window.URL || window.webkitURL).revokeObjectURL(file);
                    }
                    vm.loading = false;
                }, function (response) {
                    //error
                });
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