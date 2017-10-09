"use strict";

(function(app) {

    app.controller('GoalController', ['$scope', '$rootScope', '$http', 'CRUDService' , function($scope, $rootScope, $http, CRUDService) {
        $scope.inited = false;
        $scope.goals = [];
        $scope.addEditFormName = "addEditGoal";
        $scope.filterValues = ["Current", "Previous", "Future", "All"];
        $scope.filter = "0";

        $scope.init = function() {
            getGoals();
            $scope.inited = true;
        };

        function getGoals() {
            var url = 'connector.php?t=goal&a=get&f=' + $scope.filter;
            var errorMsg = 'Error while retrieving goals: ';
            CRUDService.getEntries($scope, url, errorMsg, function(response) {
                $scope.goals = response.data;
            });

        }

        $rootScope.$on('refreshGoals', function(event, args) {
            getGoals();
            console.log('refreshing goals...')
        });

        $scope.prepareAddGoal = function() {
            $scope.serverError = "";
            $scope.modalTitle = "Add goal";
            $scope.tempGoal = {};

            CRUDService.resetFormState($scope);
        };

        $scope.prepareDeleteGoal = function(id) {
            $scope.goalToDeleteId = id;
        };

        $scope.deleteGoal = function() {
            $scope.loading = true;

            var url = 'connector.php?t=goal&a=delete';
            var data = "id=" + $scope.goalToDeleteId;
            var errorMsg = 'Error while deleting goal: ';


            CRUDService.postRequest(url, data, errorMsg, function(response) {
                getGoals();
            })
        };

        $scope.saveGoal = function() {
            if ($scope[$scope.addEditFormName].$valid) {



                if ($scope.tempGoal.id) {
                    // Then we edit existing goal


                } else {
                    // Then we creating new goal

                    var url = 'connector.php?t=goal&a=add';
                    var data = ObjecttoParams($scope.tempGoal);
                    var errorMsg = 'Error while adding new goal: ';

                    CRUDService.postRequest(url, data, errorMsg, function(response) {
                        if (response.error) {
                            $scope.serverError = response.data;
                        } else {
                            $('#addEditGoal').modal('hide');
                            getGoals();
                        }

                    });
                }
            }
        }

        $scope.formGroupClass = function(fieldName) {
            return CRUDService.formGroupClass($scope, fieldName);
        };

    }]);


})(app);
