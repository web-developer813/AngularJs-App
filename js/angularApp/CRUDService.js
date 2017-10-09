"use strict";

(function(app) {

    app.factory('CRUDService', function($http, $rootScope) {
        return {
            getEntries: function($scope, url, errorMsg, clb) {
                $scope.loading = true;

                $http.get(url).success(function(response) {
                    if (response.error || typeof response == 'string') {
                        console.log(errorMsg + (response.data ? response.data : response));

                    } else {
                        clb(response);
                    }
                    $scope.loading = false;
                });
            },

            postRequest: function(url, data, errorMsg, clb) {
                $http({
                    method: 'POST',
                    url: url,
                    data: data,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                }).success(function(response) {
                    if (response.error || typeof response == 'string') {
                        console.log(errorMsg + (response.data ? response.data : response));
                    }
                    clb(response);



                })
            },

            formGroupClass: function($scope, fieldName) {

                var $addEditForm = $scope[$scope.addEditFormName];

                return {'has-error':  $addEditForm.$submitted && $addEditForm[fieldName].$invalid};
            },

            formFieldErrorShow: function($scope, fieldName) {
                var $addEditForm = $scope[$scope.addEditFormName];

                return ($addEditForm.$submitted && $addEditForm[fieldName].$invalid);
            },

            resetFormState: function($scope) {
                var $addEditForm = $scope[$scope.addEditFormName];

                $addEditForm.$setPristine();
                $addEditForm.$setUntouched();
            }
        }
    });
})(app);

