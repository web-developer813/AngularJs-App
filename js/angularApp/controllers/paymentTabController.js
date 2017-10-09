"use strict";

(function(app) {
    var controllerName = 'PaymentTabController';
    app.controller(controllerName, ['$scope', '$rootScope' , function($scope, $rootScope) {



        $scope.inited = false;
        $scope.init = function() {
            $rootScope.$emit('initPaymentTab', {});
            $scope.inited = true;
        };

        $rootScope.$on('uninit', function(event, args) {
            if (args.target == controllerName) {
                $scope.inited = false;
            }
        });

    }]);
})(app);
