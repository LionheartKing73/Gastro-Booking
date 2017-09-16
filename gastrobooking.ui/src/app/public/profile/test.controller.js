/**
 * Created by yonatom on 8/31/16.
 */

(function () {
    'use strict';

    angular
        .module('app.profile')
        .controller('TestController', TestController);

    /*@ngNoInject*/
    function TestController($scope, $uibModal, $log, Cropper, $timeout) {
        var vm = this;
        vm.open = function (size) {
            var modalInstance = $uibModal.open({
                animation: true,
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'test.html',
                controller: 'ModalInstanceController',
                controllerAs: 'vm',
                size: 'lg',
                resolve: {
                    items: function () {
                        return vm.image;
                    },
                    showCropper: function() {
                        return showCropper();
                    }
                }
            });

            modalInstance.result.then(function (selectedItem) {
                vm.selected = selectedItem;
            }, function () {
                $log.info('Modal dismissed at: ' + new Date());
            });
        };

        vm.ok = function () {
            $uibModalInstance.close(vm.selected.item);
        };

        vm.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };

        var file, data;
        vm.closeModal = closeModal;

        /**
         * Method is called every time file input's value changes.
         * Because of Angular has not ng-change for file inputs a hack is needed -
         * call `angular.element(this).scope().onFile(this.files[0])`
         * when input's event is fired.
         */
        $scope.onFile = function(blob) {
            debugger;
            Cropper.encode((file = blob)).then(function(dataUrl) {
                vm.dataUrl = dataUrl;
                debugger;
                $timeout(showCropper);
                var modalInstance = $uibModal.open({
                    animation: true,
                    ariaLabelledBy: 'modal-title',
                    ariaDescribedBy: 'modal-body',
                    templateUrl: 'test.html',
                    controller: 'ModalInstanceController',
                    controllerAs: 'vm',
                    size: 'lg',
                    resolve: {
                        dataUrl: function () {
                            return vm.dataUrl;
                        }
                    }
                });

                // modalInstance.opened.then(function(data){
                //     $timeout(showCropper);
                //     debugger;
                //
                // }, function(error){
                //     debugger;
                // });

                modalInstance.result.then(function (selectedItem) {
                    debugger;
                    vm.selected = selectedItem;
                }, function () {
                    debugger;
                    $log.info('Modal dismissed at: ' + new Date());
                });
                //   // wait for $digest to set image's src

            });




        };

        /**
         * Croppers container object should be created in controller's scope
         * for updates by directive via prototypal inheritance.
         * Pass a full proxy name to the `ng-cropper-proxy` directive attribute to
         * enable proxing.
         */
        vm.cropper = {};
        vm.cropperProxy = 'cropper.first';

        /**
         * When there is a cropped image to show encode it to base64 string and
         * use as a source for an image element.
         */
        vm.preview = function() {
            if (!file || !data) return;
            Cropper.crop(file, data).then(Cropper.encode).then(function(dataUrl) {
                (vm.preview || (vm.preview = {})).dataUrl = dataUrl;
            });
        };

        /**
         * Use cropper function proxy to call methods of the plugin.
         * See https://github.com/fengyuanchen/cropper#methods
         */
        vm.clear = function(degrees) {
            // if (!vm.cropper.first) return;
            // vm.cropper.first('clear');
            hideCropper();
            vm.dataUrl = null;
        };

        function closeModal(){
            hideCropper();
            vm.dataUrl = null;
            file = null;
            data = null;

        };

        vm.scale = function(width) {
            Cropper.crop(file, data)
                .then(function(blob) {
                    return Cropper.scale(blob, {width: width});
                })
                .then(Cropper.encode).then(function(dataUrl) {
                (vm.preview || (vm.preview = {})).dataUrl = dataUrl;
            });
        }

        /**
         * Object is used to pass options to initalize a cropper.
         * More on options - https://github.com/fengyuanchen/cropper#options
         */
        vm.options = {
            maximize: true,
            aspectRatio: 2 / 1,
            crop: function(dataNew) {
                data = dataNew;
            }
        };

        /**
         * Showing (initializing) and hiding (destroying) of a cropper are started by
         * events. The scope of the `ng-cropper` directive is derived from the scope of
         * the controller. When initializing the `ng-cropper` directive adds two handlers
         * listening to events passed by `ng-cropper-show` & `ng-cropper-hide` attributes.
         * To show or hide a cropper `$broadcast` a proper event.
         */
        vm.showEvent = 'show';
        vm.hideEvent = 'hide';


        function showCropper() { $scope.$broadcast(vm.showEvent); }
        function hideCropper() { $scope.$broadcast(vm.hideEvent); }
    }


    angular
        .module('app.profile')
        .controller('ModalInstanceController', ModalInstanceController);

    function ModalInstanceController($scope, $uibModalInstance, dataUrl){
        var vm = this;
        vm.dataUrl = dataUrl;
        $scope.$broadcast(vm.showEvent);
        debugger;
        vm.ok = function () {
            $uibModalInstance.close(vm.selected.item);
        };

        vm.cancel = function () {
            $uibModalInstance.dismiss('cancel');
        };
    }

})();