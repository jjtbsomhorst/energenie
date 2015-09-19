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
  
 
}]).controller('listController',['$scope','$mdSidenav','authService','$routeParams','$location','energyService',function($scope,$mdSidenav,$auth,$rp,$location,service){
	
	$scope.currentPage = 0;
	$scope.pageSize = 10;
	$scope.entrycount = 0;
	
	$scope.$watch('currentPage',function(n,o){
		if(n != null & !(n < 0)){
			service.list($rp.type,$scope.currentPage,$scope.pageSize).then(function(data){
				$scope.entries = data.data.entries.reverse();
				$scope.entrycount = parseInt(data.data.records);
			},function(data){
				console.log('kapot');
			})
		}
	});
	
	$scope.gotoPrevious = function(){
		if($scope.currentPage >= 0){
			$scope.currentPage = $scope.currentPage-1;
		}
	}
	$scope.gotoNext = function(){
		var nxtOffset = ($scope.currentPage) * $scope.pageSize;
		if($scope.entrycount > nxtOffset){
			$scope.currentPage = $scope.currentPage+1;
		}
	}
	
	if(!$auth.isLoggedIn()){
		$location.path('/login');
		return;
	}
	
	if($mdSidenav('left').isOpen()){
		$mdSidenav('left').close();
	}
	
	service.list($rp.type,$scope.currentPage,$scope.pageSize).then(function(data){
			$scope.entries = data.data.entries.reverse();
			$scope.entrycount = parseInt(data.data.records);
		},function(data){
			console.log('kapot');
		})
		
		
	$scope.saveRecord = function(){

		var r = $scope.record;		
		var dayOfMonth = r.date.getDate();
		var month = r.date.getMonth()+1;
		var year = r.date.getFullYear();
		var dtString = "";
		dtString += year;
		dtString += "-";
		if(month < 10){
			dtString+= "0"+month;
		}else{
			dtString+= month;
		}
		dtString += "-";
		if(dayOfMonth <10){
			dtString += "0"+dayOfMonth;
		}else{
			dtString += dayOfMonth;
		}
				
		service.add(dtString,r.value).then(function(data){
			$scope.entries = data.data.reverse();
		},function(data){
			console.log('kapot');
		})
	}
	
	
}]).controller('graphController',['$scope','$mdSidenav','authService','$routeParams','$location',function($scope,$mdSidenav,$auth,$rp,$location){
	if(!auth.isLoggedIn()){
		$location.path('/login');
	}
}]).controller('profileController',['$scope','$mdSidenav','authService','$location',function($scope,$mdSidenav,auth,$location){
	
	$scope.dirtypassword = false;
	
	if(!auth.isLoggedIn()){
		$location.path('/login');
		return;
	}
	
	if($mdSidenav('left').isOpen()){
		$mdSidenav('left').close();
	}
	
	
	$scope.preference = {};
	auth.profile().then(function(data){
		for(var i  = 0;i<data.data.length;i++){
			var entry = data.data[i];
			$scope.preference[entry.label]=entry.value;
		}
	},function(data){
		$location.path('/login');
	})
	
	
	$scope.savePassword= function(){
		if($scope.hasOwnProperty('password') && $scope.password != null && $scope.password!=""){
			auth.setPassword($scope.password);
		}
	}
	$scope.$watch('password',function(n,o){
		$scope.dirtypassword=false;
		if(n!=null && n != o && n != ""){
			$scope.dirtypassword = true;
		}
	})
	$scope.$watch('preference.recorddate',function(n,o){
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
	
	$scope.$watch('preference.email',function(n,o){
		if(n!=0&& n!=null && n != ""){
			
			var re = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            if(re.test(n)){
            	
            	auth.updateSetting('email',n).then(function(data){
    				
    			},function(data){
    				
    			});
            }
		}
	});

}]);