"use strict";

(function(app) {
    app.controller('CommentsController', ['$scope', '$http', 'CRUDService', function($scope, $http, CRUDService) {
        $scope.comments = [];
        $scope.commentsLoading = false;
        $scope.tempComment = {};
        $scope.commentsAppointmentID;


        $scope.prepareAndLoadComments = function(appointmentID, appointmentIndex) {
            $scope.commentsAppointmentID = appointmentID;
            $scope.commentsAppointmentIndex = appointmentIndex;
            $scope.commentedAppointment = $scope.appointments[appointmentIndex];

            $scope.loadComments();
        }


        $scope.loadComments = function() {

            $scope.commentsLoading = true;

            var url = 'connector.php?t=comment&a=get';
            var data = "appointmentID=" + $scope.commentsAppointmentID;
            var errorMsg = 'Error while retreiving comments for app. id ' + $scope.commentsAppointmentID + ' : ';

            CRUDService.postRequest(url, data, errorMsg, function(response) {
                $scope.comments = response.data;
                $scope.commentsLoading = false;
            });
        }

        $scope.postComment = function() {
            if ($scope.addComment.$valid) {
                var url = 'connector.php?t=comment&a=add';
                $scope.tempComment.date = new Date();
                var data = "appointmentID=" + $scope.commentsAppointmentID + "&" + ObjecttoParams($scope.tempComment);
                var errorMsg = 'Error while adding comment for app. id ' + $scope.commentsAppointmentID + ' : ';

                CRUDService.postRequest(url, data, errorMsg, function(response) {
                    $scope.tempComment = {};
                    $scope.appointments[$scope.commentsAppointmentIndex].commentsNumber++;

                    $scope.loadComments();
                });
            }
        }
    }]);
})(app);
