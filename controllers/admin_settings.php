<?php
session_start();
function validate_settings() {
	// Check that the name is present
	if(!isset($_POST['name'])) {
		$_SESSION['error'] = "You need to specify what setting to change";
		return false;
	}
	// Check that the name only contains valid characters
	if(eregi("[^a-zA-Z]", $_POST['name'])) {
		$_SESSION['error'] = "The name is invalid";
		return false;
	}
	
	// Check that the value is present
	if(!isset($_POST['value'])) {
		$_SESSION['error'] = "You need to specify the value";
		return false;
	}
	// Check that the value is a number
	if(ereg("[^0-9]", $_POST['value'])) {
		$_SESSION['error'] = "The value needs to be a number";
		return false;
	}
	return true;
}
if(isset($_POST['editsettings'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/settingDao.php");
	$settingDao = new SettingDao();
	
	try {
		$name = $_POST['name'];
		$value = $_POST['value'];
		
		$settingDao->updateSetting($name, $value);
		$_SESSION['notice'] = "The value has been updated";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_settings");
} elseif(isset($_POST['addsetting'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/settingDao.php");
	$settingDao = new SettingDao();
	
	try {
		if(validate_settings()) {
			$name = $_POST['name'];
			$value = $_POST['value'];
			
			$settingDao->addSetting($name, $value);
			$_SESSION['notice'] = "Setting added";
		}
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_settings");
} else if(isset($_POST['deletesetting'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/settingDao.php");
	$settingDao = new SettingDao();
	
	try {
		if(eregi("[^a-zA-Z]", $_POST['name'])) {
			$_SESSION['notice'] = "Invalid name";
		} else {
			$name = $_POST['name'];
 			$settingDao->deleteSetting($name);
		}
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=admin_settings");
} else {
	$_SESSION['error'] = "You are not authorized to use this feature";
	header("Location: ../index.php");
}