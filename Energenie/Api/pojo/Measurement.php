<?php
class Measurement {
	public $record = null;
	public $difference = 0;
	
	function Measurement($record) {
		$this->record = $record;
	}

	function setDifference($dif) {
		$this -> difference = $dif;
	}

	function getDifference() {
		return $this -> difference;
	}

	function getDate() {
		return $this->record-> date;
	}

	function getAmount() {
		return $this->record-> amount;
	}

	function getType() {
		return $this->record-> type;
	}

	function getId() {
		return $this->record->measurement_id;
	}
	
	function asArray(){
		return array(
			"id" => $this->getId(),
			"type"=>$this->getType(),
			"date"=>$this->getDate(),
			"amount"=>$this->getAmount(),
			"difference"=>$this->getDifference()		
		
		);
	}

}
?>