"use strict";

(function(app) {
    app.controller('UserController', ['$scope', '$http', 'CRUDService' , function($scope, $http, CRUDService) {
        $scope.inited = false;
        $scope.users = [];
        $scope.userRoles = ['Salesman', 'Appointment setter', 'Admin', 'Regional Manager', 'District Manager','Sales Manager'];
        $scope.addEditFormName = "addEditUser";

        $scope.init = function() {
            getUsers();
            $scope.inited = true;
        };

        function getUsers() {
            var url = 'connector.php?t=user&a=get';
            var errorMsg = 'Error while retreiving users: ';
            CRUDService.getEntries($scope, url, errorMsg, function(response) {
                for (var i = 0; i < response.data.length; i++) {
                    if (response.data[i].fee1 !== null) {
                        response.data[i].fee1 = parseInt(response.data[i].fee1);
                    }
                    if (response.data[i].fee2 !== null) {
                        response.data[i].fee2 = parseInt(response.data[i].fee2);
                    }
                }
                $scope.users = response.data;

            });
        }

        $scope.prepareAddUser = function() {
            $scope.serverError = "";
            $scope.editMode = false;
            $scope.modalTitle = "Add user";
            $scope.tempUser = {
                role: "0"
            };

            CRUDService.resetFormState($scope);
        };

        $scope.prepareEditUser = function(index) {
            $scope.serverError = "";
            $scope.editMode = true;
            $scope.modalTitle = "Edit user";
            $scope.tempUser = angular.copy($scope.users[index]);

            $scope.tempUser.changePass = false;
            $scope.lastEditedUserIndex = index;


            $scope.tempUser.hasFee = $scope.tempUser.hasFee == "1" ? true : false;
            $scope.tempUser.level  = $scope.tempUser.level  == "1" ? true : false;
            $scope.getFollowUsers()
            $scope.tempUser.parent = $scope.tempUser.parent_user_id.toString();
            CRUDService.resetFormState($scope);
        };

        $scope.prepareDeleteUser = function(id) {
            $scope.userToDeleteId = id;
        };

        $scope.deleteUser = function() {
            $scope.loading = true;

            var url = 'connector.php?t=user&a=delete';
            var data = "id=" + $scope.userToDeleteId;
            var errorMsg = 'Error while deleting user: ';


            CRUDService.postRequest(url, data, errorMsg, function(response) {
                getUsers();
                $scope.getTinyUsers();
            })
        };

        $scope.getFollowUsers = function(){
           var errorMsg;
           var url;
           var data;
           var inData;
           var user_level = $scope.tempUser.role;
           if(($scope.tempUser.role == 0) || ($scope.tempUser.role == 4) || ($scope.tempUser.role == 5)){
               if($scope.tempUser.level == 1){
                    inData = {
                       'user_level' : user_level
                   }
                    data = ObjecttoParams(inData);
                   url="connector.php?t=user&a=get_level";
                   errorMsg = 'Error while editing user: ';

                   CRUDService.postRequest(url, data, errorMsg, function(response) {
                        $scope.userLists = response.data;
                   });
               }else{
                   $scope.selection = []
               }
            }else{
               $scope.selection = []
           }
        }

        $scope.saveUser = function() {
            var errorMsg;
            var data;
            var url;
            if ($scope[$scope.addEditFormName].$valid) {
                $scope.loading = true;

                if ($scope.tempUser.id) {
                    // Then we editing existing user
                    url = 'connector.php?t=user&a=edit';
                    data = ObjecttoParams($scope.tempUser);
                    errorMsg = 'Error while editing user: ';

                    CRUDService.postRequest(url, data, errorMsg, function(response) {
                        getUsers();
                        $scope.getTinyUsers();
                        $('#addEditUser').modal('hide');
                    });

                } else {
                    // Then we creating new user

                    url = 'connector.php?t=user&a=add';
                    data = ObjecttoParams($scope.tempUser);
                    errorMsg = 'Error while adding new user: ';

                    CRUDService.postRequest(url, data, errorMsg, function(response) {
                        if (response.error) {
                            $scope.serverError = response.data;
                            $scope.loading = false;
                        } else {
                            getUsers();
                            $scope.getTinyUsers();
                            $('#addEditUser').modal('hide');
                        }

                    });
                }
            }
        };

        $scope.formGroupClass = function(fieldName) {
            return CRUDService.formGroupClass($scope, fieldName);
        };

        $scope.formFieldErrorShow = function(fieldName) {
            return CRUDService.formFieldErrorShow($scope, fieldName);
        };
    }]);
})(app);
