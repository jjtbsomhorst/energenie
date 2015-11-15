var c =  angular.module('appcontrollers',['ngMessages','EnergyServices',"ng-fusioncharts"]);
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
  },
  {
	  text: 'Grafiekjes',
	  route: '/graph',
	  icon: 'fa fa-bar-chart'
  }
  ];
  
 
}]).controller('listController',['$scope','$mdSidenav','authService','$routeParams','$location','energyService',function($scope,$mdSidenav,$auth,$rp,$location,service){
	
	if(!$auth.isLoggedIn()){
		$location.path('/login');
		return;
	}
	
	
	$scope.currentPage = 0;
	$scope.pageSize = 10;
	$scope.entrycount = 0;
	$scope.pageCount = 0;
	$scope.disableNext = false;
	$scope.disablePrevious = true;
	
	$scope.$watch('currentPage',function(n,o){
		if(n != null & !(n < 0)){
			service.list($rp.type,$scope.currentPage,$scope.pageSize).then(function(data){
				$scope.entries = data.data.entries.reverse();
				$scope.entrycount = parseInt(data.data.records);
				var mod = $scope.entrycount % $scope.pageSize;
				$scope.pageCount = ($scope.entrycount - mod )/$scope.pageSize;
				if(mod > 0 ){
					$scope.pageCount++;
				}
				
				
				
			},function(data){
				console.log('kapot');
			})
		}
	});
	
	
	$scope.hasPrevious = function(){
		return $scope.currentPage > 0;
	}
	
	$scope.hasNext = function(){
		return ($scope.currentPage * $scope.pageSize)+$scope.pageSize < $scope.entrycount;
	}
	
	$scope.gotoPrevious = function(){
		if($scope.currentPage > -1){
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
			$scope.entries = data.data.entries.reverse();
			$scope.entrycount = parseInt(data.data.records);
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

}]).controller('chartController',['$scope','authService','energyService','$location',function($scope,auth,energyService,$location){
	
	for(var i = 0;i < energyService.types.length;i++){
		var t = energyService.types[i];
		energyService.list(t,0,10,'year').then(function(data){
			if(!$scope.hasOwnProperty('charts')){
				$scope.charts = [];
			}
			
			var chart = {
				"chart": {
		                "paletteColors": "#0075c2",
		                "bgColor": "#ffffff",
		                "showBorder": "0",
		                "showCanvasBorder": "0",
		                "usePlotGradientColor": "0",
		                "plotBorderAlpha": "10",
		                "placeValuesInside": "1",
		                "valueFontColor": "#ffffff",
		                "showYAxisValues": "0",
		                "axisLineAlpha": "25",
		                "divLineAlpha": "10",
		                "alignCaptionWithCanvas": "0",
		                "showAlternateVGridColor": "0",
		                "captionFontSize": "14",
		                "subcaptionFontSize": "14",
		                "subcaptionFontBold": "0",
		                "toolTipColor": "#ffffff",
		                "toolTipBorderThickness": "0",
		                "toolTipBgColor": "#000000",
		                "toolTipBgAlpha": "80",
		                "toolTipBorderRadius": "2",
		                "toolTipPadding": "5"					
				},
				"data":[]
			}
			
			for(var j = 0;j<data.data.length;j++){
				chart.data.push({
					"label": data.data[j].year,
					"value": data.data[j].amount
				});
			}
			
			$scope.charts.push(chart);
		},function(data){
			console.log('grafiek error');
		});	
	}
}]);