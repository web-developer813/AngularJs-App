"use strict";

(function(app) {

    app.directive("datepicker", function () {
        return {
            restrict: "A",
            require: "ngModel",
            scope: {
                ngModel: '='
            },
            link: function (scope, elem, attrs, ngModelCtrl) {

                var updateModel = function () {
                    scope.$apply(function () {
                        //ngModelCtrl.$modelValue = elem.val();
                        scope.ngModel = elem.val();
                    });
                };


                $('#datetimepicker1').datetimepicker({format: 'MMM D YYYY h:mm A'});
                $('#datetimepicker1').on("dp.change", function (e) {
                    updateModel();
                });
            }
        }
    });


    app.directive('salesmanStars', function() {
        return {
            restrict: 'E',
            scope: {
                amount: '='
            },
            link: function(scope, element, attrs){
                var output = '<span class="salesman-stars">';
                for (var i = scope.amount; i < 3; i++)
                    output += '<span class="glyphicon glyphicon-star invisible" aria-hidden="true"></span>';
                for (var i = 0; i < scope.amount; i++)
                    output += '<span class="glyphicon glyphicon-star" aria-hidden="true"></span>';
                output += '</span>';

                element.append(output);
            }
        };
    });

    app.directive('dateRangeCollapse', ['$q', 'CRUDService', function($q, CRUDService) {
        return {
            require: 'ngModel',
            scope: {
                tempGoal: "="
            },
            link: function(scope, elm, attrs, ctrl) {
                ctrl.$asyncValidators.dateRangeCollapse = function(modelValue, viewValue) {

                    var def = $q.defer();



                    console.log(attrs.ngModel);

                    if (attrs.ngModel == 'tempGoal.startDate') {
                        scope.tempGoal.startDate = modelValue;
                    }
                    if (attrs.ngModel == 'tempGoal.endDate') {
                        scope.tempGoal.endDate = modelValue;
                    }
                    if(scope.tempGoal.salesman && scope.tempGoal.startDate && scope.tempGoal.endDate) {
                        if (ctrl.$isEmpty(modelValue)) {
                            // consider empty model valid
                            return $q.when();
                        }


                        console.log(scope.tempGoal);

                        var url = 'connector.php?t=goal&a=check';
                        var data = ObjecttoParams(scope.tempGoal);
                        var errorMsg = 'Error while checking goal: ';
                        console.log('http...');
                        /*                    CRUDService.postRequest(url, data, errorMsg, function(response) {
                         if (response.data > 0) {
                         // Means that we have goals that current collapse with
                         def.reject();
                         } else {
                         def.resolve();
                         }
                         });*/

                    } else {
                        def.resolve();
                    }
                    return def.promise;
                };
            }
        };
    }]);

})(app);
