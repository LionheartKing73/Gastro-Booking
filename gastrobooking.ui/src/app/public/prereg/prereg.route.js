/**
 * Created by Hamid Shafer on 2017-02-24.
 */

(function () {
    'use strict';

    angular
        .module('app.prereg')
        .config(moduleConfig);
    /*@ngNoInject*/
    function moduleConfig($stateProvider) {

        $stateProvider
            .state('main.prereg', {
                url: "/prereg",
                templateUrl: "app/public/prereg/prereg.html",
                controller: 'PreregController',
                controllerAs: 'vm'
            });
        }
})();