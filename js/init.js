"use strict";

$(function() {
    $('#mainTabs a').click(function (e) {
        e.preventDefault()
        $(this).tab('show')
    }).on('shown.bs.tab', function(e) {
        var targetContent = $($(e.target).attr('href'));
        initTabController(targetContent);
    });

    initTabController($('.tab-pane.active'));

    function initTabController($tabElmnt) {
        var tabControllerScope = angular.element($tabElmnt).scope();
        if (tabControllerScope.inited === false) {
            tabControllerScope.init();
        }

    }

    $('#printBtn').on('click', function() {
        window.print();
    });


    // Payment tab
    // Usability feature
    $("#pending-award-table, #paid-accounts-table").on('click', 'tr', function(e) {
        if (!$(e.target).is("input.appCb")) {
            $(this).find('input.appCb').click();
        }
    });

    $('#pending-award-table .cbToggle').on('click', function() {
        var $this = $(this);

        $this.parents('table').find('.appCb').prop('checked', !$this.is(':checked')).click(); // To make angular model react click is needed
    });



    var $paidAccountTable = $("#paid-accounts-table");
    $paidAccountTable.find('tbody').bind("DOMSubtreeModified", showHidePaidAccountsTableCbToggle);

    function showHidePaidAccountsTableCbToggle() {
        var appSetterIDs = [];
        $paidAccountTable.find('.appCb:not(:disabled)').each(function(elem) {
            appSetterIDs.push($(this).attr('data-app-setter-id'));
        });

        function onlyUnique(value, index, self) {
            return self.indexOf(value) === index;
        }

        appSetterIDs = appSetterIDs.filter(onlyUnique);

        if (appSetterIDs.length == 1) {
            $paidAccountTable.find('.cbToggle').show();
        } else {
            $paidAccountTable.find('.cbToggle').hide();
        }
    }

    $paidAccountTable.find('.cbToggle').on('click', function() {
        var $this = $(this);

        $paidAccountTable.find('.appCb:not(:disabled)').prop('checked', !$this.is(':checked')).click();
    });

    // Disabling checkboxes from other app. setters

    $paidAccountTable.on('change', '.appCb', function() {
        //console.log('change' + $(this).attr('data-app-setter-id'));
        var currAppSetter = $paidAccountTable.find('.appCb:checked').first().attr('data-app-setter-id');

        if (typeof currAppSetter !== "undefined") {
            $paidAccountTable.find('.appCb[data-app-setter-id!=' + currAppSetter + ']').attr('disabled', true);
        } else {
            $paidAccountTable.find('.appCb').removeAttr("disabled");
        }

        showHidePaidAccountsTableCbToggle();
    });

    var $appointmentsTable = $('#appointments-table');

    $appointmentsTable.on('click', 'tr', function(e) {
       if (e.target.tagName !== 'A' && e.target.parentNode.tagName !== 'A' ) {
            var $this = $(this);
            $this.toggleClass('info');
       }
    });

    // Fixing some columns on appointments table
/*    var $appointmentsTableWrapper = $('.table-responsive');
    var fixedColumnsNums = [0, 3, 4];
    var fixedCols = [];
    fixedColumnsNums.forEach(function(colNum) {
        var $col = $appointmentsTableWrapper.find('th:eq(' + colNum + ')');
        fixedCols.push({$el: $col, num: colNum});
    });

    $appointmentsTableWrapper.on('scroll', function() {
        var $this = $(this);
        var $table = $appointmentsTableWrapper.find('table');

        fixedCols.forEach(function(col, index) {
            if (col.$el.position().left < 0) {
                if (index == 0) {
                    var cssLeft = 0;
                } else {

                }

                $table.find('th:eq(' + col.num + '), tr td:eq(' + col.num + ')').css('position', 'absolute').css('left', cssLeft);
            }
            $appointmentsTableWrapper.css('padding-right', col.$el.outerWidth());
        });

        //console.log($this.scrollLeft());
    });*/
});

// Auto-logout after 30 minutes of inactivity
(function() {
    var timer;
    var numOfMinutes = 30;

    function setLogoutTimer() {
        clearTimeout(timer);
        timer = setTimeout(function() {
            console.log('timer');
            $.post(window.location, {logout: 1})
                .done(function(data) {
                    location.reload();
                })
                .fail(function() {
                    setLogoutTimer();
                });
        }, 1000 * 60 * numOfMinutes);
    }

    $(document).ready(setLogoutTimer);
    $('html').on('mousemove', setLogoutTimer);
})();

google.charts.load('current', {'packages':['corechart']});
google.charts.setOnLoadCallback(onChartsLoaded);

var chartBurnDownDashboard,
    chartPerformanceDashboard,
    chartBurnDownChartstab,
    chartPerformanceChartstab;

var burnDownChartOptions = {//'title':'Week sales',
    chartArea: {top: 12},
    legend: {position: 'none'},
    vAxis: {
        format: 'decimal',
        viewWindow: {
            min: 0
        }
    }
};

var performanceChartOptions = {
    chartArea: {top: 15},
    legend: {position: 'none'},
    pieHole: 0.4
};



var chartStates = [];
var chartsData;

function drawCharts() {
    if (chartStates.indexOf('loaded') != - 1 && chartStates.indexOf('gotData') != - 1) {
        var generalChartData = new google.visualization.DataTable();
        generalChartData.addColumn('string', 'Day');
        generalChartData.addColumn('number', 'Sales');
        generalChartData.addRows(chartsData.chart);

        var performanceChartData = new google.visualization.DataTable();
        performanceChartData.addColumn('string', 'Status');
        performanceChartData.addColumn('number', 'Number');
        performanceChartData.addRows(chartsData.performance);

        chartBurnDownDashboard.draw(generalChartData, burnDownChartOptions);
        chartBurnDownChartstab.draw(generalChartData, burnDownChartOptions);



        chartPerformanceDashboard.draw(performanceChartData, performanceChartOptions);
        chartPerformanceChartstab.draw(performanceChartData, performanceChartOptions);

    }
}

function onChartsLoaded() {
    // Instantiate and draw our chart, passing in some options.
    chartBurnDownDashboard = new google.visualization.LineChart(document.getElementById('chart-burn-down-dashboard'));
    chartPerformanceDashboard = new google.visualization.PieChart(document.getElementById('chart-performance-dashboard'));

    chartBurnDownChartstab = new google.visualization.LineChart(document.getElementById('chart-burn-down-chartstab'));
    chartPerformanceChartstab = new google.visualization.PieChart(document.getElementById('chart-performance-chartstab'));

    chartStates.push('loaded');

    drawCharts();
}