<?php
class Recipe {
	var $name;
	var $description;
	var $itemID;
	var $profession;

	function __construct($result) {
		$this->name = $result['name'];
		$this->description = $result['description'];
		$this->itemID = $result['rid'];
		$this->profession = $result['profession'];
	}
	
	function getNameAndLink() {
		return "<a href=\"http://www.wowhead.com/?spell=".$this->itemID."\">".$this->name."</a>";

/*		switch ($this->profession) {
			case "Enchanting":
				return "<a href=\"http://www.wowhead.com/?spell=".$this->itemID."\">".$this->name."</a>";
			default:
				return "<a href=\"http://www.wowhead.com/?item=".$this->itemID."\">".$this->name."</a>";
				break;
		}*/
	}
	function getName() {
		return $this->name;
	}
	
	function getProfession() {
		return $this->profession;
	}
	
	function getID() {
		return $this->itemID;
	}
	
	function getDescription() {
		return $this->description;
	}
}