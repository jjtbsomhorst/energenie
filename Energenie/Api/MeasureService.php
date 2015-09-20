<?php
require_once 'pojo/Measurement.php';

class MeasureService {

	const type_electricity = 1;
	const type_water = 2;
	const type_gas = 3;

	var $db = null;

	function MeasureService($db) {
		$this -> db = $db;
	}
	
	function getListByYear($user,$type = 1){
		$list = array();
		$conn = $this->db->getConn();
		
		$queryString = 'SELECT type,user_id, min(amount) as `mn`, max(amount) as `mx`, year(date) as `year`';
		$queryString.= ' from `measurement`';
		$queryString.= ' where type = :t and user_id =:u';
		$queryString.= ' group by year(`measurement`.`date`)';
		$queryString.= ' order by year(`measurement`.`date`) asc';
		
		$stmt = $conn->prepare($queryString);
		$stmt->bindParam(':u',$user);
		$stmt->bindParam(':t',$type);
		if($stmt->execute()){
			$lst = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$entries = array();
			for($i = 0; $i < count($lst);$i++){
				$entry = $lst[$i];
				
				$entries[$i]['year'] = (int) $entry['year'];
				$entries[$i]['type'] = $type;
				$entries[$i]['user'] = $user;
				$j =$i+1;
				if(count($lst) == $j){
					$entries[$i]['amount'] = $entry['mx'] - $entry['mn'];
				}else{
					$nxtEntry = $lst[$j];
					$entries[$i]['amount'] = $nxtEntry['mn'] - $entry['mn'];
				}
			}
			return $entries;
		}
	}
	
	
	function getList($user, $type = 1, $offset = 0, $limit = 10) {
		if (!is_numeric($type)) {
			throw new Exception("Type is not recognized");
		}
		
		$list = array();
		//$offset = $limit*10;
		
		$stmCount = $this -> db -> getConn()->prepare("SELECT COUNT(*) as c from `measurement` where type=:t and user_id=:u");
		$stmCount->bindParam(':t',$type);
		$stmCount->bindParam(':u',$user);
		$entryCount = $limit;
		
		if($stmCount->execute()){
			$row = $stmCount->fetch(PDO::FETCH_ASSOC);
			$entryCount = $row['c'];
		}
		$qLimit = $limit;
		if($entryCount > $qLimit){
			$qLimit = $limit+1;
		}
				
		$stmt = $this -> db -> getConn() -> prepare("SELECT * from `measurement` where type = :t and user_id = :u order by date desc LIMIT :o , :l");
		$stmt -> bindParam(":t", $type);
		$stmt -> bindParam(":o", $offset, PDO::PARAM_INT);
		$stmt -> bindParam(":l", $qLimit, PDO::PARAM_INT);
		$stmt -> bindParam(":u", $user);

		if ($stmt -> execute()) {
		
			$lst = $stmt -> fetchAll(PDO::FETCH_OBJ);
						
			$index = 0;
			$entries = array($limit);
			foreach ($lst as $entry) {

				$row = new Measurement($entry);
				if($index +1 < count($lst)){
					$nxtEntry = $lst[$index + 1];
					$row -> difference = ($entry -> amount - $nxtEntry -> amount);
				}
				
				if($index < $limit){
					$entries[$index] = $row -> asArray();
				}
				
				$index++;
			}
			
			$records = array_reverse($entries);
			$returnData = array();
			$returnData['entries'] = $records;
			$returnData['records'] = $entryCount;
		
			return $returnData;
		}
	}

	function getEntry($user, $type = 1, $id = 1) {
		$conn = $this -> db -> getConn();
		$stmt = $conn -> prepare('SELECT * from measurement where `measurement_id` = :id and `user_id` = :u and `type` = :t');
		$stmt -> bindParam(':id', $id);
		$stmt -> bindParam(':u', $user);
		$stmt -> bindParam(':t', $type);
		if ($stmt -> execute()) {
			$entry = $stmt -> fetchObject();
			return new Measurement($entry);
		}
		return null;
	}

	function delete($user, $recordid) {
		$conn = $this -> db -> getConn();
		$stmt = $conn -> prepare('DELETE * from measurement where `measurement_id` = :id and `user_id`= :u');
		$stmt -> bindParam(':id', $recordid);
		$stmt -> bindParam(':u', $user);
		return $stmt -> execute();
	}

	function update($user, $recordid, $type, $date, $value) {
		$conn = $this -> db -> getConn();
		$stmt = $conn -> prepare('UPDATE measurement set `type` = :t, `date` = :d, `amount` = :v, `user_id` = :u');
		$stmt -> bindParam(':t', $type);
		$stmt -> bindParam(':d', $date);
		$stmt -> bindParam(':v', $value);
		$stmt -> bindParam(':u', $user);
		return $stmt -> execute();
	}

	function create($user, $type = 1, $date, $value) {
		$conn = $this -> db -> getConn();
		$stmt = $conn -> prepare('INSERT INTO measurement(`type`, `date`,`amount`, `user_id`) value(:t,:d,:v,:u)');
		$stmt -> bindParam(':t', $type);
		$stmt -> bindParam(':d', $date);
		$stmt -> bindParam(':v', $value);
		$stmt -> bindParam(':u', $user);
		if ($stmt -> execute()) {
			return $this->getList($user,$type,0,1);
		}
	}

}
?>