/**
 * Created by yonatom on 8/31/16.
 */

(function() {
    'use strict';

    angular.module('app.client', [])
        .directive('convertToNumber', function() {
            return {
                require: 'ngModel',
                link: function(scope, element, attrs, ngModel) {
                    ngModel.$parsers.push(function(val) {
                        return val != null ? parseInt(val, 10) : null;
                    });
                    ngModel.$formatters.push(function(val) {
                        return val != null ? '' + val : null;
                    });
                }
            };
        });;
})();
