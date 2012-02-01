<?php
class Raid {
	var $rid;
	var $instance;
	var $inid;
	var $status;
	var $leader;
	var $start;
	
	// The players in the raid
	var $players;
	// The events that happen during the raid
	var $events;
	
	function __construct($result) {
		$this->rid = $result['rid'];
		$this->instance = $result['name'];
		$this->inid = $result['inid'];
		$this->status = $result['status'];
		$this->leader = $result['leader'];
		$this->start = $result['start'];
		$this->decay = $result['decay'];
	}
	
	function getTarget() {
		if($this->instance != NULL)
			return $this->instance;
		else
			return "Undetermined";
	}
	
	function getInid() {
		return $this->inid;
	}
	
	function getDecay() {
		return $this->decay;
	}
	
	function getStart() {
		// Create the timestamp
		$date = explode("-", $this->start);
		$time = explode(" ", $date[2]);
		$date[2] = $time[0];
		$time = explode(":", $time[1]);
		
		$t = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
		
		return date("l j. F H:i",  $t);
	}
	
	function getStartDay() {
		// Create the timestamp
		$date = explode("-", $this->start);
		$time = explode(" ", $date[2]);
		$date[2] = $time[0];
		$time = explode(":", $time[1]);
		
		$t = mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);
		
		return date("l j. F",  $t);
	}
	
	function getStartSQL() {
		return $this->start;
	}
	
	function getStatus() {
		return $this->status;
	}
	
	function getId() {
		return $this->rid;
	}
	
	function getPlayers()
	{
		return $this->players;
	}
	
	function setPlayers($players)
	{
		usort($players, "sortByPlayername");
		$this->players = $players;
	}
	
	function getEvents()
	{
		return $this->events;
	}
	
	function setEvents($event)
	{
		$this->events = $event;
	}
}
	function sortByPlayername($a,$b) {
    	return strtolower($a->getName())>strtolower($b->getName());
	}