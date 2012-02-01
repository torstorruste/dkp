<?php
class Item {
	var $name;
	var $id;
	var $heroicId;
	var $quality;
	var $instance;
	var $comment;
	
	function __construct($result=NULL) {
		if($result!=NULL) {
			$this->id = $result['iid'];
			$this->name = $result['name'];
			$this->quality = $result['quality'];
			$this->instance = $result['instance'];
			$this->heroicId = $result['hid'];
			if(isset($result['comment']))
				$this->comment = $result['comment'];
		}
	}
	
	function getLink() {
		if($this->comment=='heroic' || $this->comment=='heroic offspec')
			return "<a href=\"http://www.wowhead.com/?item=".$this->heroicId."\" class=\"".$this->quality."\">".$this->name."</a> (heroic)";
		return "<a href=\"http://www.wowhead.com/?item=".$this->id."\" class=\"".$this->quality."\">".$this->name."</a>";
	}
	function getHeroicLink() {
		return "<a href=\"http://www.wowhead.com/?item=".$this->heroicId."\" class=\"".$this->quality."\">".$this->name."</a>";
	}
	
	function getName() {
		return $this->name;
	}
	
	function setName($name) {
		$this->name = $name;
	}
	
	function getId() {
		return $this->id;
	}
	
	function setId($id) {
		$this->id = $id;
	}
	
	function getHid() {
		return isset($this->heroicId)?$this->heroicId:0;
	}
	
	function setHid($hid) {
		$this->heroicId=$hid;
	}
	
	function getQuality() {
		return $this->quality;
	}
	
	function setQuality($quality) {
		$this->quality = $quality;
	}
	
	function getInstance() {
		return $this->instance;
	}
}