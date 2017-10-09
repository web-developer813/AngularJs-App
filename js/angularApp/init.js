"use strict";

function ObjecttoParams(obj) {
	var p = [];
	for (var key in obj) {
		var value = obj[key];
		if (value instanceof Date) {
			value = value.toMysqlFormat();
		}
		if (key == "appointmentDate") {
			value = moment(value, ["MMM D YYYY h:mm A"]).format("YYYY-MM-DD HH:mm:ss");
		}

		p.push(key + '=' + encodeURIComponent(value));
	}
	return p.join('&');
};

function twoDigits(d) {
	if(0 <= d && d < 10) return "0" + d.toString();
	if(-10 < d && d < 0) return "-0" + (-1*d).toString();
	return d.toString();
}


Date.prototype.toMysqlFormat = function() {
	return this.getFullYear() + "-" + twoDigits(1 + this.getMonth()) + "-" + twoDigits(this.getDate()) + " " + twoDigits(this.getHours()) + ":" + twoDigits(this.getMinutes()) + ":" + twoDigits(this.getSeconds());
};



var app = angular.module('appointmentsApp', ["checklist-model"]);



