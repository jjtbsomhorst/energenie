var TYPE_ELECTRA = "electra";
var TYPE_WATER = "water";
var TYPE_GAS = "gas";

var services = angular.module('EnergyServices', [ 'ngRoute' ]);
services.factory('authService', [ '$rootScope', '$http', '$location', '$q',
		function($scope, $http, $location, $q) {

			function checkLogin() {
				if ($scope.hasOwnProperty('authToken')) {
					var def = $q.defer();
					var request = {
						method : 'GET',
						url : '/Api/api.php/auth/token',
					}

				}
				return false;
			}

			function login(username, password) {
				$http.post('Api/api.php/auth', {
					username : username,
					password : password
				}).then(function(response) {
					if (response.data.hasOwnProperty('token')) {
						$scope.authToken = response.data.token;
						$location.path('/list/electra')
					}
				}, function(response) {
					alert('foutje');
				});
			}

			function logout(token) {

			}

			function getToken() {
				return $scope.authToken;
			}

			function isLoggedIn() {
				return $scope.hasOwnProperty('authToken');
			}

			function setPassword(password) {
				var def = $q.defer();
				var request = {
					method : 'PUT',
					url : 'Api/api.php/account/password',
					headers : {
						'X-AUTH-TOKEN' : getToken()
					},
					data : {
						password : password
					}
				}
				var def = $q.defer();
				$http(request).then(function(data) {
					def.resolve(data);
				}, function() {
					def.reject();
				});
				return def.promise;

			}

			function updateSetting(settingName, settingValue) {
				var def = $q.defer();
				var request = {
					method : 'POST',
					url : 'Api/api.php/account/settings',
					headers : {
						'X-AUTH-TOKEN' : getToken()
					},
					data : {
						name : settingName,
						value : settingValue
					}
				}
				$http(request).then(function(data) {
					def.resolve(data);
				}, function(data) {
					console.log('error');
				})
				return def.promise;
			}

			function getSettings(){
				var def = $q.defer();
				var request={
					method:'GET',
					url:'Api/api.php/account',
					headers:{
						'X-AUTH-TOKEN' : getToken()
					}
				}
				$http(request).then(function(data){
					def.resolve(data);
				},function(data){
					$location.path('/login');
				});
				return def.promise	;
			}
			
			
			return {
				login : login,
				logout : logout,
				getToken : getToken,
				isLoggedIn : isLoggedIn,
				setPassword : setPassword,
				updateSetting : updateSetting,
				profile : getSettings
				
			}

		} ]);

services.factory('energyService', [ '$http', '$q', 'authService',
		function($http, $q, auth) {

			var service = {};
			service.type = null;
			service.baseUrl = "Api/api.php/";
			service.url = "";
			service.data = {
				water : [],
				electra : [],
				gas : []
			};

			service.getBaseUrl = function() {
				return baseUrl;
			}

			service.setType = function(t) {

				if (this.type != t) {
					this.type = t;
					switch (t) {
					case 'water':
						this.url = service.baseUrl + "water";
						break;
					case 'electra':
						this.url = service.baseUrl + "electra";
						break;
					case 'gas':
						this.url = service.baseUrl + "gas";
						break;
					}
				}
			}
			service.add = function(date, value) {
				request = {
					method : 'POST',
					url : this.url,
					headers : {
						'X-AUTH-TOKEN' : auth.getToken()
					},
					data : {
						date : date,
						value : value
					}
				}
				var def = $q.defer();
				$http(request).then(function(data) {
					def.resolve(data);
				}, function() {
					console.log('error');
				});
				return def.promise;
			}
			service.update = function(id, date, value) {
				request = {
					method : 'PUT',
					url : this.url + "/" + id,
					headers : {
						'X-AUTH-TOKEN' : auth.getToken()
					},
					data : {
						date : date,
						value : value
					}
				}
			}

			service.remove = function(id) {
			}
			service.list = function(type,page,pagesize) {
				this.setType(type);
				var request = {
					method : 'GET',
					url : this.url+"?offset="+page+"&pageSize="+pagesize,
					headers : {
						'X-AUTH-TOKEN' : auth.getToken()
					}
				}
				var def = $q.defer();

				$http(request).then(function(data) {
					def.resolve(data);
				}, function() {
					console.log('error');
				});

				return def.promise;
			}

			return service;

		} ]);
