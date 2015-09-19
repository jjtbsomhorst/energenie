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

	function handleRequest(){
		die(print_r($app));
	}

	function getList($user, $type = 1, $offset = 0, $limit = 10) {
		if (!is_numeric($type)) {
			throw new Exception("Type is not recognized");
		}

		$list = array();
		$stmt = $this -> db -> getConn() -> prepare("SELECT * from `measurement` where type = :t and user_id = :u order by date ASC LIMIT :o , :l");
		$stmt -> bindParam(":t", $type);
		$stmt -> bindParam(":o", $offset, PDO::PARAM_INT);
		$stmt -> bindParam(":l", $limit, PDO::PARAM_INT);
		$stmt -> bindParam(":u", $user);

		if ($stmt -> execute()) {

			$lst = $stmt -> fetchAll(PDO::FETCH_OBJ);
			$index = 0;
			$entries = array();
			foreach ($lst as $entry) {

				$row = new Measurement($entry);

				if ($index > 0) {
					$prevEntry = $lst[$index - 1];
					$row -> difference = ($entry -> amount - $prevEntry -> amount);
				}
				$entries[$index] = $row -> asArray();
				$index++;
			}

			return $entries;
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
			$entries = $this->getList($user,$type,0,2);
			return $entries[0];
		}
	}

}
?>