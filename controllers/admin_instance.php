<?php
session_start();
function validateInstance() {
	// Check that the name is present
	if(empty($_POST['name'])) {
		$_SESSION['error'] = "You need to specify a name for the instance";
		return false;
	}
	// Check that the name is correct
	if(eregi("[^a-zA-Z'\ -0-9]", $_POST['name'])) {
		$_SESSION['error'] = "The name cannot contain special characters";
		return false;
	}
	// Check that the multiplier is ok
	if(!eregi("[0-9](\.[0-9])?", $_POST['multiplier'])) {
		$_SESSION['error'] = "The multiplier is incorrect";
		return false;
	}
	return true;
}

if(isset($_POST['addinstance'])) {
	if(validateInstance()) {
		include_once("../dao/genericDao.php");
		include_once("../dao/instanceDao.php");
		$instanceDao = new InstanceDao();
		
		try {
			$name = $_POST['name'];
			$multiplier = $_POST['multiplier'];
			
			$instanceDao->addInstance($name, $multiplier);
			$_SESSION['notice'] = "The instance has been added";
		} catch(Exception $e) {
			$_SESSION['error'] = $e->getMessage();
		}
	}
	header("Location: ../index.php?page=admin_instance");
} elseif(isset($_POST['editinstance'])) {
	if(validateInstance()) {
		include_once("../dao/genericDao.php");
		include_once("../dao/instanceDao.php");
		$instanceDao = new InstanceDao();
	
		try {
			$name = $_POST['name'];
			$id = $_POST['id'];
			$multiplier = $_POST['multiplier'];
			
			$instanceDao->editInstance($name, $multiplier, $id);
			$_SESSION['notice'] = "The instance has been edited";
		} catch(Exception $e) {
			$_SESSION['error'] = $e->getMessage();
		}
	}
	header("Location: ../index.php?page=admin_instance");
} elseif(isset($_POST['deleteinstance'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/instanceDao.php");
	$instanceDao = new InstanceDao();
	
	try {
		$id = $_POST['id'];
		
		$instanceDao->deleteInstance($id);
		$_SESSION['notice'] = "The instance has been deleted";
	}catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_instance");
} else {
	$_SESSION['error'] = "You are not authorized to use this feature";
	header("Location: ../index.php");
}