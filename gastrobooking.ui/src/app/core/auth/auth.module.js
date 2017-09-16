/**
 * Created by Job on 6/27/2016.
 */
(function () {
    'use strict';
    var translateEn = {
        FULL_NAME : "Full Name",
        EMAIL: "Email",
        PASSWORD: "Password"
    };
    var translateCz = {
        FULL_NAME : "Maqaa Guutuu",
        EMAIL: "Imeelii",
        PASSWORD: "Paaswordii"
    };

    angular.module('app.auth', [])
        .config(['$translateProvider', function ($translateProvider) {
            $translateProvider.useStaticFilesLoader({
                prefix: 'assets/languages/locale-',
                suffix: '.json'
            });

            $translateProvider
                .registerAvailableLanguageKeys(['en', 'cs'])
                .determinePreferredLanguage()
                .fallbackLanguage(['en'])
                .useLocalStorage();
    }]);

})();
