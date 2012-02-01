<?php
session_start();
function validateNewRaid() {
	// Check that the instance is set
	/*if(empty($_POST['instance'])) {
		$_SESSION['error'] = "You must select an instance";
		return false;
	}*/
	// Check that a time and date is specified
	if(empty($_POST['start'])) {
		$_SESSION['error'] = "You must specify a start-time";
		return false;
	}
	// Check that a status is set
	if(empty($_POST['status'])) {
		$_SESSION['error'] = "You must specify a status for the raid";
		return false;
	}
	// Validate the timestamp
	if(!eregi("^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{1,2}$", $_POST['start'])) {
		$_SESSION['error'] = "Invalid format of the start-time";
		return false;
	}
	return true;
}
if(isset($_POST['addraid'])) {
	if(validateNewRaid()) {
		include_once("../dao/genericDao.php");	
		include_once("../dao/raidDao.php");
		$raidDao = new RaidDao();
		$playerDao = new PlayerDao();
	
		try {
			$instance = $_POST['instance'];
			$status = $_POST['status'];
			$start = $_POST['start'];
			
			$rid = $raidDao->addRaid($instance, $start, $status);
			
			$players = $playerDao->getRaiders();
			
			foreach($players as $player)
			{
				$raidDao->addPlayerToRaid($player->getID(), $rid);
			}
		
			$_SESSION['notice'] = "The raid was successfully added";
		} catch(Exception $e) {
			$_SESSION['error'] = $e->getMessage();
		}
	}
	header("Location: ../index.php?page=admin_raid");
} else if(isset($_GET['deleteraid'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	
	try {
		$rid = $_GET['id'];
		
		$raidDao->deleteRaid($rid);
		$_SESSION['notice'] = "Raid successfully delete";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_raid");
} else {
	$_SESSION['error'] = "You are not authorized to use this feature";
	header("Location: ../index.php");
}