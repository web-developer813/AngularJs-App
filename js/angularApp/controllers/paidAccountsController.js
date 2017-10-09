"use strict";

(function(app) {
    var controllerName = 'PaidAccountsController';
    app.controller(controllerName, ['$scope', '$rootScope', '$http', 'CRUDService' , function($scope, $rootScope, $http, CRUDService) {
        var entriesPerPage = 10;
        var pageCurrent = 1;

        $scope.inited = false;
        $scope.paidAccounts = [];
        $scope.statuses = ['Pending', 'Sale', 'No Sale', 'Re-Schedule', 'No demo', 'Not approved'];
        $scope.installations = ['Pending', 'Installed', 'Not installed'];
        $scope.filter = "0";
        $scope.appointmentsToGetInvoice = [];

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

        $scope.markInvoiceDownloaded = function() {

            var url = 'connector.php?t=appointment&a=invoiceSeen';
            var data = "ids=" + $scope.appointmentsToGetInvoice;
            var errorMsg = 'Error while marking appointments as invoiceSeen: ';

            CRUDService.postRequest(url, data, errorMsg, function(response) {
                if (!response.error) {
                    $scope.appointmentsToGetInvoice.forEach(function(id) {
                        $scope.paidAccounts.forEach(function(account) {
                            if (account.id == id) {
                                account.invoiceSeen = 1;
                            }
                        });
                    });
                }
            });
        };

        $scope.idsForPOST = function(ids) {
            return ids.join(',');
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
            url += '&f=paidacc';
            var errorMsg = 'Error while retreiving paid accounts: ';
            CRUDService.getEntries($scope, url, errorMsg, function(response) {
                for (var i = 0; i < response.data.length; i++) {
                    response.data[i].appointmentDate = moment(response.data[i].appointmentDate, ["YYYY-MM-DD HH:mm:ss"]).format("MMM D YYYY h:mm A");
                }
                $scope.paidAccounts = response.data;
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
