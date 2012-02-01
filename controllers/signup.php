<?php
session_start();
function validate_signup() {
	// Checks the id is present
	if(!isset($_POST['id'])) {
		$_SESSION['error'] = "No such raid exists";
		return false;
	}
	// Check that the comment contains no strange symbols
	if(isset($_POST['comment']) && eregi("^[a-z0-9.,:]$", $_POST['comment'])) {
		$_SESSION['error'] = "The comment cannot contain special symbols";
		return false;
	}
	return true;
}

if(!isset($_SESSION['dkp'])) {
	$_SESSION['error'] = "You need to login before you can sign up";
	header("Location: ../index.php?page=login");
} elseif(isset($_POST['signup'])) {
	if(validate_signup()) {
		try {
			include_once("../dao/genericDao.php");
			include_once("../dao/raidDao.php");
			$raidDao = new RaidDao();
			$rid = $_POST['id'];
			$comment = $_POST['comment'];
			
			$raidDao->signUp($rid, $_POST['comment']);
			$_SESSION['notice'] = "Successfully signed up";
		} catch(Exception $e) {
			$_SESSION['error'] = $e->getMessage();
		}
	}
	header("Location: ../index.php?page=show_raid&id=$_POST[id]");
} elseif(isset($_POST['unsign'])) {
	if(validate_signup()) {
		try {
			include_once("../dao/genericDao.php");
			include_once("../dao/raidDao.php");
			$raidDao = new RaidDao();
			$rid = $_POST['id'];
			$comment = $_POST['comment'];
			
			$raidDao->unSign($rid, $comment);
			$_SESSION['notice'] = "Successfully unsigned";
		} catch(Exception $e) {
			$_SESSION['error'] = $e->getMessage();
		}
	}
	header("Location: ../index.php?page=show_raid&id=$_POST[id]");
} else {
	$_SESSION['error'] = "An error has occured";
	header("Location: ../index.php");
}