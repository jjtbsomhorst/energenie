<?php
class userManager {

	const mastersalt = "XUX#C^1\paTTF:<d@N!/Ra=BzM(2;XYxr/]H[}Xj=d(D:ui!Q7f:\p>rjFTO3Ht";
	const saltlength = 64;
	var $db = null;

	function userManager($db) {
		$this -> db = $db;
	}

	function getSettings($token){
		$userid = $this->getUserByToken($token);
		if($userid != null){
			$conn = $this->db->getConn();
			$stmt = $conn->prepare('SELECT key,value FROM `profilesettings` WHERE user_id = :u');
			$stmt->bindParam(':u',$userid);
			if($stmt->execute()){
				return $stmt -> fetchAll(PDO::FETCH_ASSOC);
			}
		}
		return null;
	}

	function createsetting($setting, $token){
		$userid = $this->getUserByToken($token);
		if($userid == null){
			return false;
		}
		
		if(count($setting) > 0 ){
			$conn = $this->db->getConn();
			$stmt = $conn->prepare('INSERT INTO profilesettings (`user_id`,`key`,`value`) values(:u,:k,:v)');
			$stmt->bindParam(':u',$userid);
			$stmt->bindParam(':k',$setting['name']);
			$stmt->bindParam(':v',$setting['value']);
			return $stmt->execute();
		}
	}

	function updateSetting($setting,$token){
		
		$userid = $this->getUserByToken($token);
		if($userid != null && count($setting) > 0){
			$conn = $this->db->getConn();
			$stmt = $conn->prepare('UPDATE `profilesettings` SET value = :v WHERE user_id = :u and `key`= :k');
			$stmt->bindParam(':v',$setting['value']);
			$stmt->bindParam(':k',$setting['name']);
			$stmt->bindParam(':u',$userid);
			return $stmt->execute();
		}
		return false;
	}

	function containsSetting($setting,$token){
		$user = $this->getUserByToken($token);
		
		$conn = $this->db->getConn();
		$stmt= $conn->prepare('SELECT * FROM `profilesettings` WHERE user_id = :u AND profilesettings.key = :k');
		$stmt->bindParam(':u',$user);
		$stmt->bindParam(':k',$setting['name']);
		
		
		if($stmt->execute()){
			
			$tokenRow = $stmt->fetch(PDO::FETCH_OBJ);
			return $tokenRow != null;
		}else{
			die('foutje');
		}
	}


	function createToken($username, $password) {
		if(!$this->validateToken($username)){
			if ($username != null && $password != null) {
				$tokenString = $this->GUID();
				
				if($this->db->addToken($username,$tokenString) != null){
					return $tokenString;
				}
				
			}
		}
		return $this->db->getToken($username)->token;
	}
	
	

	function validateToken($token) {
		
		$token = $this->db -> getToken($token);
		if ($token != null) {
			$tokenTime = new DateTime($token->generated);
			$currentTime = new DateTime();
			
			return ($currentTime < $tokenTime);
		}
		return false;
	}

	function validateCredentials($username, $password) {
		$credentials = $this->db -> getCredentials($username);
		if ($credentials != null) {
			$hash = password_hash($credentials -> salt . ":" . $password, PASSWORD_BCRYPT, array('salt' => self::mastersalt));
			return ($hash === $credentials->password);
		}
		return false;
	}

	function getUserByToken($token){
		$token = $this->db->getToken($token);
		if($token == null){
			return null;
		}
		return $token->user_id;
	}

	function createAccount($username, $password) {

		$salt = $this -> generateRandomSalt();
		$hash = password_hash($salt . ":" . $password, PASSWORD_BCRYPT, array('salt' => self::mastersalt));

		return $this -> db -> createAccount($username, $hash, $salt);
	}

	function updateCredentials($username, $password) {
		$credentials = $this->db -> getCredentials($username);
		$credentials -> salt = $this -> generateRandomSalt();
		$credentials -> hash = password_hash($credentials -> salt . ":" . $credentials -> password, PASSWORD_BCRYPT, array('salt' => self::mastersalt));

		return $this->db -> updateAccount($username, $credentials -> hash, $credentials -> salt);
	}

	function generateRandomSalt() {
		$tokens = "abcdefghijklmnopqrstuvwxyz1234567890!@#$%*()_+=-/.,][\?><}{|~`";
		$salt = array();
		for ($i = 0; $i < self::saltlength; $i++) {
			for ($j = 0; $j < self::saltlength; $j++) {
				$number = rand(1, 61);
			}
			$salt[] = $tokens[$number];
		}

		return implode($salt);
	}

	function GUID() {
		if (function_exists('com_create_guid') === true) {
			return trim(com_create_guid(), '{}');
		}

		return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}

}
?>
