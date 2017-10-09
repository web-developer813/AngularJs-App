"use strict";

(function(app) {
    var controllerName = 'PendingAwardController';
    app.controller(controllerName, ['$scope', '$rootScope', '$http', 'CRUDService' , function($scope, $rootScope, $http, CRUDService) {
        var entriesPerPage = 10;
        var pageCurrent = 1;

        $scope.inited = false;
        $scope.pendingAwards = [];
        $scope.statuses = ['Pending', 'Sale', 'No Sale', 'Re-Schedule', 'No demo', 'Not approved'];
        $scope.installations = ['Pending', 'Installed', 'Not installed'];
        $scope.filter = "0";
        $scope.appointmentsToPay = [];

        var orderBy = 'a.appointmentDate';
        var orderDesc = true;

        $rootScope.$on('initPaymentTab', function(event, args) {
            if (!$scope.inited) {
                getAccounts();
                $scope.inited = true;
            }
        });

        $rootScope.$on('uninit', function(event, args) {
            if (args.target == controllerName) {
                $scope.inited = false;
            }
        });

        $rootScope.$on('reinit', function(event, args) {
            if (args.target == controllerName) {
                getAccounts();
            }
        });

        $scope.payAppointments = function() {

            var url = 'connector.php?t=appointment&a=pay';
            var data = "ids=" + $scope.appointmentsToPay;
            var errorMsg = 'Error while paying appointments: ';


            CRUDService.postRequest(url, data, errorMsg, function(response) {
                $scope.appointmentsToPay = [];
                $rootScope.$emit('reinit', {target: 'PendingAwardController'});
                $rootScope.$emit('reinit', {target: 'PaidAccountsController'});
            })
        };

        $scope.getAccounts = function() {
            getAccounts();
        };

        function getAccounts() {
            var url = 'connector.php?t=appointment&a=get&n=' + entriesPerPage +
                '&p=' + pageCurrent +
                '&orderBy=' + orderBy;
            if (orderDesc) {
                url += '&orderDesc=1';
            }


            url += ($scope.searchQuery ? '&s=' + $scope.searchQuery : '');
            url += '&f=penaw';
            var errorMsg = 'Error while retreiving pending awards: ';
            CRUDService.getEntries($scope, url, errorMsg, function(response) {
                for (var i = 0; i < response.data.length; i++) {
                    response.data[i].appointmentDate = moment(response.data[i].appointmentDate, ["YYYY-MM-DD HH:mm:ss"]).format("MMM D YYYY h:mm A");
                }
                $scope.pendingAwards = response.data;
                $scope.pagesTotal = Math.ceil(response.total / entriesPerPage);
            });
        }

        $scope.setOrderBy = function(field) {
            if (field == orderBy) { // Changing desc to asc when doubleclick on same field
                orderDesc = !orderDesc;
            } else {
                orderDesc = true;
            }

            orderBy = field;
            getAccounts();

        };

        $scope.showOrderArrow = function(field) {
            if (field == orderBy) {
                if (orderDesc)
                    return "▼";
                else
                    return '▲';
            }
            return " ";
        };

        $scope.getNumber = function(num) {
            return new Array(num);
        };

        $scope.getCSSClass = function(index) {
            if (index == 'prev')
                return pageCurrent == 1 ? 'disabled' : '';

            if (index == 'next')
                return pageCurrent == $scope.pagesTotal ? 'disabled' : '';

            return (index + 1) == pageCurrent ? 'active' : '';
        };

        $scope.setPage = function(p) {
            if (p == 'prev' && pageCurrent != 1)
                pageCurrent--;
            if (p == 'next' && pageCurrent != $scope.pagesTotal)
                pageCurrent++;
            else
                pageCurrent = p;

            getAccounts();
        };

    }]);
})(app);
