"use strict";

// A controller to get user names and ids for selects in html

(function(app) {

    app.controller('TinyUserController', ['$scope', '$http', 'CRUDService' , function($scope, $http, CRUDService) {
        $scope.tinyUsers = [];



        $scope.getTinyUsers = function(clb) {
            $http.get('connector.php?t=tinyUser&a=get').success(function(response) {
                if (response.error || typeof response == 'string') {
                    console.log('Error while retreiving tiny users: ' + (response.data ? response.data : response));
                } else {

                    $scope.tinyUsers = response.data;
                    $scope.salesmen = $scope.tinyUsers.filter(function(user) {
                        return user.role == 0;
                    });
                    $scope.appointmentSetters = $scope.tinyUsers.filter(function(user) {
                        return user.role == 1;
                    });

                    if (clb)
                        clb();
                }
            });
        };

        $scope.getTinyUsers();

        $scope.resetSalesmanStars = function(id) {
            var url = 'connector.php?t=tinyUser&a=resetStars';
            var data = "id=" + id;
            var errorMsg = 'Error while reseting salesman stars: ';

            CRUDService.postRequest(url, data, errorMsg, function(response) {
                $scope.getTinyUsers();
            });
        }
    }]);


})(app);
