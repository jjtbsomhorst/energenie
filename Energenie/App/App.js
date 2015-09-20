var app = angular.module('StarterApp', ['ngMaterial', 'ngRoute','appcontrollers','EnergyServices']);

app.config(['$routeProvider',
function($routeProvider) {
	$routeProvider.when('/list/:type', {
		templateUrl : 'partials/overviewcards.html',
		controller : 'listController'
	}).when('/graph/:type', {
		templateUrl : 'partials/graphview.html',
		controller : 'graphController',
	}).when('/profile',{
		templateUrl : 'partials/profile.html',
		controller: 'profileController',
	}).when('/graph',{
		templateUrl : 'partials/graphView.html',
		controller: 'chartController'
	}).otherwise({
		templateUrl : 'partials/auth.html',
		controller : 'AuthController'
	});
}]).config(function($mdThemingProvider) {
	  $mdThemingProvider.theme('default')
	    .primaryPalette('pink')
	    .accentPalette('orange');
	});;




