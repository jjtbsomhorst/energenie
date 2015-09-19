<?php
require_once('pojo/credentials.php');
class databaseManager{

	var $conn = null;
	
	 
	function databaseManager(){
		$this->conn = new PDO('mysql:host=localhost;dbname=energymeter;charset=utf8', 'root', 'root');
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	function getCredentials($username){
		$stmt = $this->conn->prepare("select username,password,salt from user where username = :u");
		$stmt->bindParam(':u',$username);
		if(!$stmt->execute()){
			print_r($stmt->errorInfo());
			return null;
		}
		
		return $stmt->fetch(PDO::FETCH_OBJ);
		
	}
	
	function updateAccount($username,$password,$salt){
		$stmt = $this->conn->prepare("UPDATE user SET password = :p,salt = :s WHERE username = :u");
		$stmt->bindParam(':u',$username);
		$stmt->bindParam(':p',$password);
		$stmt->bindParam(':s',$salt);
		if($stmt->execute()){
			return true;
		}
		return false;
		
	}
	
	function createAccount($username,$password,$salt){
		$stmt = $this->conn->prepare("INSERT INTO user(`username`,`password`,`salt`) values(:u,:p,:s)");
		$stmt->bindParam(':u',$username);
		$stmt->bindParam(':p',$password);
		$stmt->bindParam(':s',$salt);
		
		if($stmt->execute()){
			return true;
		}
		return false;
	}

	function addToken($username,$token){
		$tokenTime = new DateTime("now");
		$interval = new DateInterval('PT30M');
		$tokenTime->add($interval);
		$fmt = $tokenTime->format("Y-m-d H:i:s");
		$userStmt = $this->conn->prepare('SELECT `user_id` FROM user WHERE username = :u ');
		$userStmt->bindParam(':u',$username);
		if($userStmt->execute()){
			
			$row = $userStmt->fetch(PDO::FETCH_OBJ);
			$rmvStatement = $this->conn->prepare('DELETE FROM token WHERE `user_id` = :id');
			$rmvStatement->bindParam(':id',$row->user_id);
			$rmvStatement->execute();
						
			$stmt = $this->conn->prepare('INSERT INTO token(`user_id`,`token`,`generated`) values(:u,:t,:d)');
			$stmt->bindParam(':u',$row->user_id);
			$stmt->bindParam(':t',$token);
			$stmt->bindParam(':d',$fmt);
			if($stmt->execute()){
				return $token;
			}
		}
		return null;
	}
	function getToken($token){
		
		$stmt = $this->conn->prepare("SELECT * FROM `token` WHERE `token` = :id");
		$stmt->bindParam(":id",$token);
		if($stmt->execute()){
			$tokenRow = $stmt->fetch(PDO::FETCH_OBJ);
			return $tokenRow;
		}
	
		return null;
	}
	

	
	function getConn(){
		return $this->conn;
	}
}
?>
