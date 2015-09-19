<?php
class credentials{
	var $username;
	var $hash;
	var $salt;

	function credentials($username,$hash,$salt){
		$this->username = $username;
		$this->password = $password;
		$this->salt = $salt;
	}
	function getUsername(){
		return $this->username;
	}
	function getHash(){
		return $this->hash;	
}
	function getSalt(){
		return $this->salt;	
}
}
?>
