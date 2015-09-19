var c =  angular.module('appcontrollers',['ngMessages','EnergyServices']);
c.controller('AuthController',['$scope','$location','authService',function($scope,$l,$auth){

	if($auth.isLoggedIn()){
		$l.path('/list/electra');
		return;
	}
	
	$scope.performRegister = function(){
		if($scope.hasOwnProperty('user')){
			$auth.register($scope.user.username,$scope.user.password);
		}
	}
	
	$scope.performLogin = function(){
		if($scope.hasOwnProperty('user')){
			$auth.login($scope.user.username,$scope.user.password);
			
		}
	};
}]).controller('AppCtrl', ['$scope', '$mdSidenav','authService', '$location',function($scope, $mdSidenav,$auth,$location){
  $scope.toggleSidenav = function(menuId) {
    $mdSidenav(menuId).toggle();
  };
  $scope.navigateTo = function(route){
  	$location.path(route);
  }
  $scope.menuitems = [{
  	text : "Water",
  	route : "/list/water",
  	icon : "fa fa-tint"
  },{
  	text : "Electriciteit",
  	route : "/list/electra",
  	icon : "fa fa-bolt"
  },
  {
  	text: "Gas",
  	route: "/list/gas",
  	icon: "fa fa-fire"
  },
  {
  	text: "Profiel",
  	route: "/profile",
  	icon: "fa fa-user"
  }
  ];
  
 
}]).controller('listController',['$scope','authService','$routeParams','$location','energyService',function($scope,$auth,$rp,$location,service){
	
	
	
	if(!$auth.isLoggedIn()){
		$location.path('/login');
		return;
	}
	service.list($rp.type).then(function(data){
			$scope.entries = data.data.reverse();
		},function(data){
			console.log('kapot');
		})
		
		
	$scope.saveRecord = function(){
		
		var r = $scope.record;
		service.add(r.date,r.value).then(function(data){
			$scope.entries = data.data.reverse();
		},function(data){
			console.log('kapot');
		})
	}
	
	
}]).controller('graphController',['$scope','authService','$routeParams','$location',function($scope,$auth,$rp,$location){
	if(!auth.isLoggedIn()){
		$location.path('/login');
	}
}]).controller('profileController',['$scope','authService','$location',function($scope,auth,$location){
	if(!auth.isLoggedIn()){
		$location.path('/login');
		return;
	}else{
		$scope.preference = {};
		auth.getSettings().then(function(data){
			
		},function(data){
			console.log('error');
		})
	}
	
	$scope.savePassword= function(){
		if($scope.hasOwnProperty('password') && $scope.password != null && $scope.password!=""){
			auth.setPassword($scope.password);
		}
	}
	$scope.$watch('preference.enterdayofmonth',function(n,o){
		if(n != o && n != null){
			auth.updateSetting('recorddate',n).then(function(data){
			console.log('Setting succesfully updated');
		},function(data){
			console.log('could not update setting');
		})
		}
	});
	
	$scope.$watch('preference.notify',function(n,o){
		if(n != o && n != null){
			auth.updateSetting('notify',n).then(function(data){
			console.log('Setting succesfully updated');
		},function(data){
			console.log('could not update setting');
		})
		}
	});	

}]);