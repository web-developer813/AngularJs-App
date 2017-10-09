"use strict";



(function(app) {


    app.controller('AppointmentsListController', ['$scope', '$rootScope', '$http', 'CRUDService', function($scope, $rootScope, $http, CRUDService) {
        var entriesPerPage = 10;
        var pageCurrent = 1;

        $scope.inited = false;
        $scope.appointments = [];
        $scope.statuses = ['Pending', 'Sale', 'No Sale', 'Re-Schedule', 'No demo', 'Not approved'];
        $scope.installations = ['Pending', 'Installed', 'Not installed'];
        $scope.profiles = ['NH', 'Kids', 'Bad taste/odor'];
        $scope.declineReasons = ['Time conflict', 'Previous demo', 'Sickness', 'Weather', 'Other', 'Repeat'];
        $scope.filter = "0";

        var orderBy = 'a.id';
        var orderDesc = true;

        $scope.addEditFormName = "addEditAppointment";
        $scope.init = function() {
            getAppointments();
            getSalesMans();
            $scope.inited = true;
        };

        $scope.salesmanLists = [];
        function getSalesMans(){
            var url ='connector.php?t=appointment&a=get_salesman';
            var errorMsg = 'Error while retreiving salesmans: ';
            var data = "";
            CRUDService.postRequest(url, data, errorMsg, function(response) {

                $scope.salesmenLists = response.data;
            });
        }

        function getAppointments() {
            var url = 'connector.php?t=appointment&a=get&n=' + entriesPerPage +
                '&p=' + pageCurrent +
                '&orderBy=' + orderBy;
            if (orderDesc) {
                url += '&orderDesc=1';
            }

            url += ($scope.searchQuery ? '&s=' + $scope.searchQuery : '');
            url += ($scope.filter ? '&f=' + $scope.filter : '');
            var errorMsg = 'Error while retreiving appointments: ';
            CRUDService.getEntries($scope, url, errorMsg, function(response) {
                for (var i = 0; i < response.data.length; i++) {
                    //response.data[i].creationDate = new Date(response.data[i].creationDate);
                    //response.data[i].appointmentDate = new Date(response.data[i].appointmentDate);

                    response.data[i].appointmentDate = moment(response.data[i].appointmentDate, ["YYYY-MM-DD HH:mm:ss"]).format("MMM D YYYY h:mm A");
                    response.data[i].showPhoneNumber = response.data[i].showPhoneNumber == 1;
                    response.data[i].profile = decodeProfile(response.data[i].profile);
                }

                $scope.appointments = response.data;

                $scope.pagesTotal = Math.ceil(response.total / entriesPerPage);
            });
        }


        $scope.toFirstPageAndGetAppointments = function() {
            pageCurrent = 1;
            getAppointments();
        };

        $scope.addAppointment = function() {

            $scope.modalTitle = "New appointment";
            $scope.tempAppointment = {
                status: "0",
                installation: "0",
                commentsNumber: "0"
            };

            CRUDService.resetFormState($scope);
        };

        $scope.editAppointment = function(index) {
            $scope.modalTitle = "Edit appointment";
            $scope.tempAppointment = angular.copy($scope.appointments[index]);

            $scope.lastEditedAppointmentIndex = index;

            CRUDService.resetFormState($scope);
        };

        $scope.prepareDeleteAppointment = function(id) {
            $scope.appointmentToDeleteId = id;
        };

        $scope.deleteAppointment = function() {
            var url = 'connector.php?t=appointment&a=delete';
            var data = "id=" + $scope.appointmentToDeleteId;
            var errorMsg = 'Error while deleting appointment: ';

            $scope.loading = true;

            CRUDService.postRequest(url, data, errorMsg, function(response) {
                var pagesTotal = Math.ceil(response.total / entriesPerPage);
                pageCurrent = pageCurrent > pagesTotal ? pagesTotal : pageCurrent;
                if (pageCurrent == 0)
                    pageCurrent = 1;
                getAppointments();
            })
        };

        $scope.saveAppointment = function() {

            if ($scope[$scope.addEditFormName].$valid) {

                $('#addEditAppointment').modal('hide');
                $scope.loading = true;

                if ($scope.tempAppointment.id) {
                    // Then we edit existing appointment
                    var dataToSend = Object.create($scope.tempAppointment);
                    dataToSend.profile = encodeProfile(dataToSend.profile);
                    var oldStatus = $scope.appointments[$scope.lastEditedAppointmentIndex].status;


                    var url = 'connector.php?t=appointment&a=edit';
                    var data = "oldStatus=" + oldStatus + "&" + ObjecttoParams(dataToSend);

                    if ($scope.tempAppointment.salesman !== null &&
                        $scope.appointments[$scope.lastEditedAppointmentIndex].salesman !== $scope.tempAppointment.salesman) {
                        /*
                                So we have new salesman assigned to this appointment
                                That means we need to create new hash and new salesman needs to accept or decline this app.
                         */

                        data += "&newSalesman=1";
                    }
                    if($scope.appointments[$scope.lastEditedAppointmentIndex].appointmentDate !== $scope.tempAppointment.appointmentDate) {
                        data += "&newDate=1";
                    }

                    var errorMsg = 'Error while editing appointment: ';

                    CRUDService.postRequest(url, data, errorMsg, function(response) {

                        $rootScope.$emit('refreshGoals', {});

                        // To update Pending Awards table
                        $rootScope.$emit('uninit', {target: 'PaymentTabController'});
                        $rootScope.$emit('uninit', {target: 'PendingAwardController'});
                        //
                        // if (!userIsSalesman) {
                        //     $scope.getTinyUsers(getAppointments);
                        // } else {
                        //     getAppointments();
                        // }
                        if(!userIsSalesman && !userIsDistrict && !userIsRegional && !userIsSales){
                            $scope.getTinyUsers(getAppointments);
                        }else{
                            getAppointments()
                        }


                    });

                } else {
                    // Then we creating new appointment

                    $scope.tempAppointment.creationDate = new Date();
                    var dataToSend = Object.create($scope.tempAppointment);
                    dataToSend.profile = encodeProfile(dataToSend.profile);

                    var url = 'connector.php?t=appointment&a=add';
                    var data = ObjecttoParams(dataToSend);
                    var errorMsg = 'Error while adding new appointment: ';

                    CRUDService.postRequest(url, data, errorMsg, function(response) {
                        pageCurrent = 1;
                        getAppointments();
                    });
                }
            }

        };

/*        $scope.actualShowPhoneNumber = function (appointment) {
            return
        };*/

        $scope.setOrderBy = function(field) {
            if (field == orderBy) { // Changing desc to asc when doubleclick on same field
                orderDesc = !orderDesc;
            } else {
                orderDesc = true;
            }

            orderBy = field;
            getAppointments();

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

        $scope.formGroupClass = function(fieldName) {
            return CRUDService.formGroupClass($scope, fieldName);
        };
        // Pagination

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

            getAppointments();
        };


        function decodeProfile(profileAsNumber) {
            var output = [];

            profileAsNumber = parseInt(profileAsNumber);

            for (var i = $scope.profiles.length - 1; i >= 0; i--) {
                var testProfile = $scope.profiles[i];
                if (profileAsNumber % 2 == 1) {
                    output.push(testProfile);
                }
                profileAsNumber = profileAsNumber >> 1;
            }

            output = output.reverse();

            return output;
        }

        function encodeProfile(profileArray) {
            profileArray = profileArray || [];
            var output = 0;

            for (var i = 0; i < $scope.profiles.length; i++) {
                var testProfile = $scope.profiles[i];

                if (profileArray.indexOf(testProfile) !== -1) {
                    output++;
                }

                output = output << 1;
            }

            output = output >> 1;

            return output;
        }
    }]);
})(app);
