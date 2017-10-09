"use strict";

(function(app) {
    app.controller('ChartsController', ['$scope', '$http', 'CRUDService' , function($scope, $http, CRUDService) {

        $scope.chartData = {time: 'week', salesman: null}; // Object for following reason: http://stackoverflow.com/questions/32034518/use-ng-model-in-nested-angularjs-controller

        $scope.globalviewData = {};
        $scope.performanceData = {};

        $scope.getChartsData = function() {
            $scope.chartsLoading = true;
            var urlSalesmanPart = $scope.chartData.salesman === null ? '' : '&user=' + $scope.chartData.salesman;

            var url = 'connector.php?t=chart&a=get&time=' + $scope.chartData.time + urlSalesmanPart;
            var errorMsg = 'Error while retreiving charts data: ';
            CRUDService.getEntries($scope, url, errorMsg, function(response) {

                chartsData = response.data;
                $scope.globalviewData = response.data.globalview;
                $scope.performanceData = response.data.performance;
                chartStates.push('gotData');

                drawCharts();
                $scope.chartsLoading = false;
            });
        };

        $scope.getChartsData();



        $scope.isPossibleToDisplayPerformanceChart = function() {
            return (typeof $scope.performanceData[0] !== "undefined") &&
                ($scope.performanceData[0][1] !== 0 || $scope.performanceData[1][1] !== 0);
        };

        $scope.getAppointmentReturn = function() {
            if ($scope.globalviewData.appointments == 0)
                return '0 appointments';

            if (typeof $scope.globalviewData.appointments !== "undefined")
                return (Math.round($scope.globalviewData.demos / $scope.globalviewData.appointments * 1000) / 10) + '%';
        };

    }]);
})(app);
