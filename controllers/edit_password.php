<?php
session_start();

function validate_password() {
	// Check that the old password is present
	if(empty($_POST['oldpassword'])) {
		$_SESSION['error'] = "You must provide an old password";
		return false;
	}
	// Check that the new password is specified
	if(empty($_POST['password'])) {
		$_SESSION['error'] = "You must provide a new password";
		return false;
	}
	// Check that the password has been confirmed
	if(empty($_POST['confirm'])) {
		$_SESSION['error'] = "You must confirm your new password";
		return false;
	}
	// Check that the two passwords are equal
	if($_POST['confirm'] != $_POST['password']) {
		$_SESSION['error'] = "The passwords do not match";
		return false;
	}
	return true;
}

if(isset($_POST['changepassword'])) {
	try {
		if(validate_password()) {
			include_once("../dao/genericDao.php");
			include_once("../dao/playerDao.php");
			$playerDao = new PlayerDao();
			
			$oldpass = $_POST['oldpassword'];
			$newpass = $_POST['password'];
			
			$playerDao->changePassword($oldpass, $newpass);
			$_SESSION['notice'] = "Password successfully changed";
			header("Location: ../index.php");
		} else {
			header("Location: ../index.php?page=edit_password");
		}
	} catch(Exception $e) {
		$_SESSION['notice'] = $e->getMessage();
		header("Location: ../index.php?page=edit_password");
	}
} else {
	$_SESSION['error'] = "You are not authorized to use this feature";
	header("Location: ../index.php");
}