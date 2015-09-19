var app = angular.module('StarterApp', ['ngMaterial', 'ngRoute','appcontrollers','EnergyServices']);

app.config(['$routeProvider',
function($routeProvider) {
	$routeProvider.when('/list/:type', {
		templateUrl : 'partials/gridview.html',
		controller : 'listController'
	}).when('/graph/:type', {
		templateUrl : 'partials/graphview.html',
		controller : 'graphController',
	}).when('/profile',{
		templateUrl : 'partials/profile.html',
		controller: 'profileController',
	}).otherwise({
		templateUrl : 'partials/auth.html',
		controller : 'AuthController'
	});
}]);




