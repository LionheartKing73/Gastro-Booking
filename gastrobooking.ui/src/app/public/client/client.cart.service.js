/**
 * Created by Thomas on 10/14/2016.
 */

(function () {
    'use strict';

    angular
        .module('app.client')
        .service('ClientCartService', ClientCartService);
    /*@ngNoInject*/
    function ClientCartService(TokenRestangular, $rootScope, $state, moment) {
        var service = {
            getOrders: getOrders,
            getTables: getTables,
            getEnableDiscount: getEnableDiscount,
            getOrderDetail: getOrderDetail,
            getDiscountCodes:getDiscountCodes,
            placeOrder: placeOrder,
            deleteOrder: deleteOrder,
            deleteOrderDetail: deleteOrderDetail,
            saveChanges: saveChanges,
            removeSideDish: removeSideDish
        };
        return service;

        function getOrders(){

            return TokenRestangular.all('orders').customGET('');
        }
        function getTables(restaurantId){
            debugger;
            return TokenRestangular.all('tables/' + restaurantId ).customGET('');
        }
        function getEnableDiscount(restaurantId){
            debugger;
            return TokenRestangular.all('enable_discount/' + restaurantId).customGET('');
        }
        function getOrderDetail(restaurantId) {

            return TokenRestangular.all('orders_detail/' + restaurantId).customGET('');
        }

        // discount_codes
        function getDiscountCodes(){
            return TokenRestangular.all('discount_codes').customGET('');
        }

        function placeOrder(order){

            angular.forEach(order.orderDetail, function(item){
                item.serve_at = moment(item.serve_at).format();

            });
            return TokenRestangular.all('order').customPOST(order);
        }

        function deleteOrder(orderId){
            var url = 'order/' + orderId;

            return TokenRestangular.all(url).customDELETE('')
        }

        function deleteOrderDetail(orderDetailId){

            return TokenRestangular.all('orders_detail/' + orderDetailId).customDELETE('')
        }

        function saveChanges(data) {

            return TokenRestangular.all('save_changes_order').customPOST(data);
        }

        // Removes the side_dish foreign key from the sideDish to the mainDish
        function removeSideDish(sideDish){
            return TokenRestangular.one("orders_detail/side_dish", sideDish.ID_orders_detail).remove();
        }
    }
})();
