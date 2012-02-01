<?php
session_start();
// If we want to change the target of the raid
if(isset($_POST['edittarget'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();

	$id = $_POST['id'];
	$instance = $_POST['instance'];
		
	$raidDao->editTarget($instance, $id);
	$_SESSION['notice'] = "The target has been changed";
	header("Location: ../index.php?page=admin_event&id=$_POST[id]");
} else if(isset($_POST['startraid'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	
	try {
		$rid = $_POST['id'];
		$raidDao->startRaid($rid);
		$_SESSION['notice'] = "Raid started";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_event&id=$_POST[id]");
} else if(isset($_POST['finishraid'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	
	try {
		$rid = $_POST['id'];

		// To decay, or not to decay - that's the question
		$decay = 0;
		if(isset($_POST['decay']))
			$decay = 1;
		
		$raidDao->finishRaid($rid, $decay);
		include_once("update_activity.php");
		$_SESSION['notice'] = "Raid ended";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_event&id=$_POST[id]");
} else if(isset($_POST['reopenraid'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	
	try {
		$rid = $_POST['id'];
		
		$raidDao->reOpenRaid($rid);
		include_once("update_activity.php");
		$_SESSION['notice'] = "Raid reopened";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_event&id=$_POST[id]");
} else if(isset($_POST['startpoint'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	
	$id = $_POST['id'];
	try {
		$raidDao->addStartPoint($id);
		$_SESSION['notice'] = "Start Points added";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_event&id=$_POST[id]");
} else if(isset($_POST['hourpoint'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	
	$id = $_POST['id'];
	$amount = $_POST['amount'];
	try {
		$raidDao->addHourPoint($id, $amount);
		$_SESSION['notice'] = "Hour Points added";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_event&id=$_POST[id]");
} else if(isset($_POST['buyitem'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	
	$rid = $_POST['id'];
	$pid = $_POST['player'];

	// $iid[0]= itemlink, $iid[1]= heroic itemlink	
	$iid = split(",", $_POST['item']);
	
	if(isset($_POST['heroic']))
	{
		if(isset($_POST['offspec']))
			$comment = "heroic offspec";
		else
			$comment = "heroic";
	}
	else
	{
		if(isset($_POST['offspec']))
			$comment = "offspec";
		else
			$comment = "";
	}
	
	if(isset($_POST['heroic']) && $iid[1]==0)
		$_SESSION['error'] = "There is no heroic version of the item";
	else
	{
		try {
			if(isset($_POST['heroic']))
				$raidDao->buyItem($rid, $pid, $iid[1], $comment);
			else
				$raidDao->buyItem($rid, $pid, $iid[0], $comment);
			$_SESSION['notice'] = "Item bought";
		} catch(Exception $e) {
			$_SESSION['error'] = $e->getMessage();
		}
	}
		
	header("Location: ../index.php?page=admin_event&id=$_POST[id]");
}else if(isset($_POST['addplayers'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	try {
		foreach($_POST['players'] as $pid) 
			$raidDao->addPlayerToRaid($pid, $_POST['id']);
		
		$_SESSION['notice'] = "The players have been added to the raid";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_event&id=$_POST[id]");
} else if(isset($_POST['deleteevents'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	try {
		foreach($_POST['events'] as $eid) {
			$raidDao->deleteEvent($_POST['id'], $eid);
		}
		$_SESSION['notice'] = "Event deleted";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_event&id=$_POST[id]");
} else if(isset($_POST['status'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	try {
		$rid = $_POST['rid'];
		$pid = $_POST['pid'];
		$status = $_POST['type'];
	
		$raidDao->changeStatus($rid, $pid, $status);
		$_SESSION['notice'] = "Status updated";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_event&id=$_POST[rid]");
} else if(isset($_POST['changestart'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	try {
		if(!eregi("^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}(:[0-9]{2})?$", $_POST['start'])) {
			$_SESSION['error'] = "Invalid format of the start-time";
		} else {
			$start = $_POST['start'];
			$rid = $_POST['rid'];
			$raidDao->editStart($rid, $start);
			$_SESSION['notice'] = "The start has been updated";			
		}
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_event&id=$_POST[rid]");
} else if(isset($_POST['duplicate'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	try {
		$rid = $_POST['rid'];
		if(!eregi("^[0-9]+$",$_POST['rid'])) {
			$_SESSION['error'] = "Invalid id";
			header("Location: ../index.php?page=admin_event&id=$_POST[rid]");
		 } else {
			$raid = $raidDao->getRaid($_POST['rid']);
			$newRid = $raidDao->duplicate($raid);
			$_SESSION['notice'] = "The raid has been duplicated";
			header("Location: ../index.php?page=admin_event&id=$newRid");
		}
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
		header("Location: ../index.php?page=admin_event&id=$_POST[rid]");
	}
} else if(isset($_POST['bonus'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/raidDao.php");
	$raidDao = new RaidDao();
	try {
		if(!eregi("^[0-9]+$",$_POST['rid']) || !eregi("^[0-9]+$", $_POST['pid']) || !eregi("^[-0-9]+$", $_POST['amount']) || !eregi("^[a-zA-Z0-9 -. ]+" ,$_POST['comment'])) {
			$_SESSION['error'] = "Wrong information specified";
		} else {
			$rid = $_POST['rid'];
			$pid = $_POST['pid'];
			$amount = $_POST['amount'];
			$comment = $_POST['comment'];
			
			$raidDao->addBonus($rid, $pid, $amount, $comment);
			$_SESSION['notice'] = "Bonus added";
		}
	} catch (Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_event&id=$_POST[rid]");
}else {
	$_SESSION['error'] = "You are not authorized to use this feature";
	header("Location: ../index.php");
}