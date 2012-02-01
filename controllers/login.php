<?php
session_start();
function validate() {
	// If no username is specified
	if(empty($_POST['username'])) {
		$_SESSION['error'] = "You must specify a username";
		return false;
	}
	else if(eregi("[^a-zA-Z]", $_POST['username'])) {
		$_SESSION['error'] = "The username is invalid";
		return false;
	}
	// If no password is specified
	else if(empty($_POST['password'])) {
		$_SESSION['error'] = "You must specify a password";
		return false;
	}
	return true;
}

if(isset($_POST['login'])) {
	if(!validate()) {
		header("Location: ../index.php?page=login");
	} else {
		include_once("../dao/genericDao.php");
		$genericDao = new GenericDao();
		
		if($genericDao->login($_POST['username'], $_POST['password'], $_POST['remember'])) {
			$_SESSION['notice'] = "Welcome";
			if(isset($_SESSION['page'])) {
				$page = $_SESSION['page'];
				unset($_SESSION['page']);
				header("Location: ../index.php?page=".$page);
			} else 
				header("Location: ../index.php");
		} else {
			$_SESSION['error'] = "Invalid username/password";
			header("Location: ../index.php?page=login");
		}
	}
}
else {
	$_SESSION['error'] = "You are not authorized to use this feature";
	header("Location: ../index.php");
}