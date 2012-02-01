<?php
class Instance {
	var $name;
	var $id;
	var $multiplier;
	
	function __construct($result) {
		$this->name = $result['name'];
		$this->id = $result['inid'];
		$this->multiplier = $result['multiplier'];
	}
	
	function getName() {
		return $this->name;
	}
	
	function getId() {
		return $this->id;
	}
	
	function getMultiplier() {
		return $this->multiplier;
	}
}