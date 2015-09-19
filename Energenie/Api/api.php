<?php
require "../libs/Slim/Slim.php";
require_once 'usermanager.php';
require_once 'dbmanager.php';
require_once 'MeasureService.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$dbManager = new databaseManager();
$usermanager = new userManager($dbManager);
$measureService=  new MeasureService($dbManager);

$app->get('/notify',function()use($app,$dbManager,$usermanager){
	
});

$app->get('/auth/:token',function($token)use($app,$dbManager,$usermanager){
	if($token != null && $token != ""){
		if($usermanager->validateToken($token)){
			$app->response->setStatus(202);
			return;
		}
		
	}
	$app->response->setStatus(403);
});

$app -> post('/auth', function() use ($app, $dbManager, $usermanager) {
	$body  = $app->request->getBody();
	$request = json_decode($body,true);
	
	if(array_key_exists("username",$request) && array_key_exists("password",$request)){
		$username = $request['username'];
		$password = $request['password'];
		
		if ($username != null && $password != null && $password !== "" && $username !== "") {
		if ($usermanager -> validateCredentials($username, $password)) {
			$token = $usermanager -> createToken($username, $password);
			if($token != null){
				$app->response->write(json_encode(array('token'=>$token)));
				return;
			}
		}
	}
		
	}


	
	$app -> response -> setStatus(403);
});

$app -> get('/account', function() use ($app, $dbManager, $usermanager) {
	$token = $app -> request -> headers -> get('X-AUTH-TOKEN');
	if ($usermanager -> validateToken($token)) {
		$profile = $usermanager->getSettings($token);
		if($profile!=null){
			$app->response->write(json_encode($profile));
			return $app->response->setStatus(200);
		}else{
			return $app->response->setStatus(404);
		}
		
	} else {
		$app -> response -> setStatus(403);

	}

});

$app -> post('/account', function() use ($app, $usermanager,$dbManager) {
	$username = $app -> request() -> post('username');
	$password = $app -> request() -> post('password');

	if ($username != null && $password != null) {
		$usermanager -> createAccount($username, $password);
	}

});

$app->post('/account/settings',function() use($app,$usermanager,$dbManager){
	$token = $app->request->headers->get('X-AUTH-TOKEN');
	if($usermanager->validateToken($token)){
		$arrBody = json_decode($app->request()->getBody(),true);
		$result = false;
		
		if($usermanager->containsSetting($arrBody,$token)){
			
			$result = $usermanager->updateSetting($arrBody,$token);
		}else{
			
			$result = $usermanager->createSetting($arrBody,$token);
		}
		
		if($result){
			$app->response->setStatus(201);
			return;
		}
		return $app->response->setStatus(500);
	}
	$app->response->setStatus(403);
});


$app->put('/account/password',function()use($app,$usermanager){
	$token = $app->request->headers->get('X-AUTH-TOKEN');
	if( $usermanager->validateToken($token)){
		$arrBody = json_decode($app->request->getBody(),true);
		
		if($usermanager->setPassword($token,$arrBody['password'])){
			$app->response->setStatus(202);
		}else{
			$app->response->setStatus(500);
		}
	}
});


$app->put('/account',function()use($app,$usermanager,$dbManager){
	if($usermanager->validateToken($app->request->headers->get('X-AUTH-TOKEN')))
	{
		$username = $app -> request() -> post('username');
		$password = $app -> request() -> post('password');
		if ($username != null && $password != null) {
			
		}
	}
	$app->response->setStatus(403);
});

$app->map('/:type',function() use ($app, $usermanager,$measureService,$dbManager) {
	$type = null;
	
	switch($app->request()->getResourceUri()){
		case "/electra":
			$type = MeasureService::type_electricity;
			break;
		case "/water":
			$type = MeasureService::type_water;
			break;
		case "/gas":
			$type = MeasureService::type_gas;
			break;
		default:
			$app->response->setStatus(404);
			return;
	}	
	$authToken = $app->request->headers->get('X-AUTH-TOKEN');
	$body = json_decode($app->request->getBody(),true);
	
	
	if(!$usermanager->validateToken($authToken)){
		$app->response->setStatus(403);
		return;
	}
	$userid = $usermanager->getUserByToken($authToken);
	if($userid == null){
		$app->response->setStatus(403);
		return;
	}
	
	
	$conn = $dbManager->getConn();
	$stmt = null;
	$app->response->setStatus(201);
	switch($app->request->getMethod()){
		case "GET":
			
			$app->response->write(json_encode($measureService->getList($userid,$type)));
			
			return ;
		case "POST":
			if(!$measureService->create($userid,$type,$body["date"],$body["value"])){
				$app->response->setStatus(500);
			}
			
			$app->response->write(json_encode($measureService->getList($userid,$type)));
			break;
		case "PUT":
			
			if(!$measureService->update($userid,$app->request->params('id'),$type,$app->request->params('date'),$app->request->params('value'))){
				$app->response->setStatus(500);
				
			}
			$app->response->write(json_encode($measureService->getList($userid,$type)));
			break;
		case "DELETE":
			if(!$measureService->delete($userid,$app->request->params('id'))){
				$app->response->setStatus(500);
			}
		break;
	}
	
	
}) -> VIA('GET', 'POST', 'PUT', 'DELETE')->conditions(array(":type"=>"water|gas|electra"));
// 
// $app -> map('/gas', function() use ($app, $usermanager,$measureService,$dbManager) {
// 	
// 	
	// $authToken = $app->request->headers->get('X-AUTH-TOKEN');
	// $body = json_decode($app->request->getBody(),true);
// 	
// 	
	// if(!$usermanager->validateToken($authToken)){
		// $app->response->setStatus(403);
	// }
	// $userid = $usermanager->getUserByToken($authToken);
	// if($userid == null){
		// $app->response->setStatus(403);
		// return;
	// }
// 	
// 	
	// $conn = $dbManager->getConn();
	// $stmt = null;
	// $app->response->setStatus(201);
	// switch($app->request->getMethod()){
		// case "GET":
// 			
			// $app->response->write(json_encode($measureService->getList($userid,MeasureService::type_gas)));
// 			
			// return ;
		// case "POST":
			// if(!$measureService->create($userid,MeasureService::type_gas,$body["date"],$body["value"])){
				// $app->response->setStatus(500);
			// }
// 			
			// $app->response->write(json_encode($measureService->getList($userid,MeasureService::type_gas)));
			// break;
		// case "PUT":
// 			
			// if(!$measureService->update($userid,$app->request->params('id'),measureService::type_gas,$app->request->params('date'),$app->request->params('value'))){
				// $app->response->setStatus(500);
// 				
			// }
			// break;
		// case "DELETE":
			// if(!$measureService->delete($userid,$app->request->params('id'))){
				// $app->response->setStatus(500);
			// }
		// break;
	// }
// 	
// 	
// }) -> VIA('GET', 'POST', 'PUT', 'DELETE');
// 
// $app -> map('/water', function() use ($app, $usermanager,$measureService,$dbManager) {
// 	
// 	
	// $authToken = $app->request->headers->get('X-AUTH-TOKEN');
	// $body = json_decode($app->request->getBody(),true);
// 	
// 	
	// if(!$usermanager->validateToken($authToken)){
		// $app->response->setStatus(403);
	// }
	// $userid = $usermanager->getUserByToken($authToken);
	// if($userid == null){
		// $app->response->setStatus(403);
		// return;
	// }
// 	
// 	
	// $conn = $dbManager->getConn();
	// $stmt = null;
	// $app->response->setStatus(201);
	// switch($app->request->getMethod()){
		// case "GET":
// 			
			// $app->response->write(json_encode($measureService->getList($userid,MeasureService::type_water)));
// 			
			// return ;
		// case "POST":
			// if(!$measureService->create($userid,MeasureService::type_water,$body["date"],$body["value"])){
				// $app->response->setStatus(500);
			// }
// 			
			// $app->response->write(json_encode($measureService->getList($userid,MeasureService::type_water)));
			// break;
		// case "PUT":
// 			
			// if(!$measureService->update($userid,$app->request->params('id'),measureService::type_water,$app->request->params('date'),$app->request->params('value'))){
				// $app->response->setStatus(500);
// 				
			// }
			// break;
		// case "DELETE":
			// if(!$measureService->delete($userid,$app->request->params('id'))){
				// $app->response->setStatus(500);
			// }
		// break;
	// }
// 	
// 	
// }) -> VIA('GET', 'POST', 'PUT', 'DELETE');
// 
// $app -> map('/electra', function() use ($app, $usermanager,$measureService,$dbManager) {
	// $authToken = $app->request->headers->get('X-AUTH-TOKEN');
	// $body = json_decode($app->request->getBody(),true);
// 	
// 	
	// if(!$usermanager->validateToken($authToken)){
		// $app->response->setStatus(403);
	// }
	// $userid = $usermanager->getUserByToken($authToken);
	// if($userid == null){
		// $app->response->setStatus(403);
		// return;
	// }
// 	
// 	
	// $conn = $dbManager->getConn();
	// $stmt = null;
	// $app->response->setStatus(201);
	// switch($app->request->getMethod()){
		// case "GET":
// 			
			// $app->response->write(json_encode($measureService->getList($userid,MeasureService::type_electricity)));
// 			
			// return ;
		// case "POST":
			// if(!$measureService->create($userid,MeasureService::type_electricity,$body["date"],$body["value"])){
				// $app->response->setStatus(500);
			// }
// 			
			// $app->response->write(json_encode($measureService->getList($userid,MeasureService::type_electricity)));
			// break;
		// case "PUT":
// 			
			// if(!$measureService->update($userid,$app->request->params('id'),measureService::type_electricity,$app->request->params('date'),$app->request->params('value'))){
				// $app->response->setStatus(500);
// 				
			// }
			// break;
		// case "DELETE":
			// if(!$measureService->delete($userid,$app->request->params('id'))){
				// $app->response->setStatus(500);
			// }
		// break;
	// }
// 	
// 	
// }) -> VIA('GET', 'POST', 'PUT', 'DELETE');
// 


$app -> run();
?>
