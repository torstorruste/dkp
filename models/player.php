<?php
class Player {
	var $pid;
	var $name;
	var $username;
	var $class;
	var $role;
	var $rights;
	var $active;
	var $activity;
	
	var $ep;
	var $gp;
	var $qp;
	
	
	function __construct($result=NULL) {
		if(isset($result))
		{
			$this->name = $result['playername'];
			$this->username = $result['username'];
			$this->class = $result['class'];
			$this->rights = $result['rights'];
			$this->activity = $result['activity'];
			$this->pid = $result['pid'];
			$this->hash = sha1($result['username'].sha1($result['password']));
			$this->role = $result['role'];
	
			$this->ep = $result['ep'];		
			$this->gp = $result['gp'];
			$this->qp = $result['qp'];
			
			if($result['active']==1)
				$this->active = true;
			else
				$this->active = false;
		}
	}
	
	function getRights()
	{
		return $this->rights;
	}
	
	function setRights($rights) {
		$this->rights = $rights;
	}
	
	function getHash()
	{
		return $this->hash;
	}
	
	function identifyPlayer($hash)
	{
		if($this->hash == $hash)
			return true;
		return false;
	}
	
	function getID() {
		return $this->pid;
	}
	
	function setID($id) {
		$this->pid = $id;
	}
	
	function getName() {
		return $this->name;
	}
	function setName($name) {
		$this->name = $name;
	}
	
	function getFormattedName() {
		return "<a href=\"index.php?page=view_items&id=".$this->getID()."\" class=\"".$this->class."\">".$this->name."</a>";
	}
	
	function getUsername() {
		return $this->username;
	}
	
	function setUsername($username) {
		$this->username = $username;
	}
	
	function getClass() {
		return $this->class;
	}
	
	function setClass($class) {
		$this->class = $class;
	}
	
	function getActivity() {
		return $this->activity;
	}
	
	function isActive() {
		return $this->active;
	}
	
	function setActive($active) {
		$this->active = $active;
	}
	
	function isOfficer() {
		return $this->rights == 'officer';
	}
	
	function getEP() {
		return round($this->ep);
	}
	
	function getQP() {
		return round($this->qp);
	}
	
	function setQP($value) {
		$this->qp = $value;
	}
	
	function addEP($ep) {
		$this->ep += $ep;
	}
	
	function getGP() {
		return round($this->gp);
	}
	
	function addGP($gp) {
		$this->gp += $gp;
	}
	
	function setRole($role) {
		$this->role = $role;
	}
	
	function getRole() {
		return $this->role;
	}
	
	function getPriority() {
		global $baseGP;
		if(!isset($baseGP)) {
			if(is_file("dao/settingDao.php"))
				include_once("dao/settingDao.php");
			else if(is_file("../dao/settingDao.php"))
				include_once("../dao/settingDao.php");
			$settingDao = new settingDao();
			
			$baseGP = $settingDao->getSetting('BaseGP');
		}
		return round($this->ep / ($this->gp + $baseGP), 3);
	}
	
	function decay($factor) {
		$this->ep = $factor * $this->ep;
		$this->gp = $factor * $this->gp;
	}
	
	function unDecay($factor) {
		$this->ep = $this->ep / $factor;
		$this->gp = $this->gp / $factor;
	}
}
