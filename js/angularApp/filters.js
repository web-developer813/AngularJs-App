"use strict";

(function(app) {
    app.filter('unsafe', function($sce) {
        return function(val) {
            return $sce.trustAsHtml(val);
        };
    });

    app.filter('myDateFormatString', function($sce) {
        return function(input, format, inputFormat) {
            inputFormat = inputFormat || "YYYY-MM-DD HH:mm:ss";
            return moment(input, [inputFormat]).format(format);
        };
    });


    app.filter('highlightSearchResults', function() {
        return function(input, searchQuery) {

            input = input || '';

            if (searchQuery) {
                var output = input.replace(new RegExp(searchQuery, 'gi'), "<span style='color: red'>" + searchQuery + "</span>");

                return output;
            }

            return input;
        };
    });

    app.filter('hrefTel', function() {
        return function(input) {

            return input.replace(/[^0-9]/g, '');

        };
    });

    app.filter('urlencode', function() {
        return window.encodeURIComponent;
    });

    app.filter('tel', function () {
        return function (tel) {
            if (!tel) { return ''; }

            var value = tel.toString().trim().replace(/^\+/, '');

            if (value.match(/[^0-9]/)) {
                return tel;
            }

            var country, city, number;

            switch (value.length) {
                case 10: // +1PPP####### -> C (PPP) ###-####
                    country = 1;
                    city = value.slice(0, 3);
                    number = value.slice(3);
                    break;

                case 11: // +CPPP####### -> CCC (PP) ###-####
                    country = value[0];
                    city = value.slice(1, 4);
                    number = value.slice(4);
                    break;

                case 12: // +CCCPP####### -> CCC (PP) ###-####
                    country = value.slice(0, 3);
                    city = value.slice(3, 5);
                    number = value.slice(5);
                    break;

                default:
                    return tel;
            }

            if (country == 1) {
                country = "";
            }

            number = number.slice(0, 3) + '-' + number.slice(3);

            return (country + " (" + city + ") " + number).trim();
        };
    });

})(app);
