<?php
session_start();
function validateItem() {
	// Make sure a name is present
	if(empty($_POST['name'])) {
		$_SESSION['error'] = "You need to specify a name";
		return false;
	}
	// Make sure the name is properly formated
	if(eregi("[^a-zA-Z'-\ ]", $_POST['name'])) {
		$_SESSION['error'] = "The name cannot contain special characters";
		return false;
	}
	// Make sure the id is here
	if(empty($_POST['id'])) {
		$_SESSION['error'] = "You must specify an id";
		return false;
	}
	// Make sure the id is correct
	if(!eregi("[0-9]{5,6}", $_POST['id'])) {
		$_SESSION['error'] = "The id must contain only numbers";
		return false;
	}
	// Checks that the instance is present
	if(empty($_POST['instance'])) {
		$_SESSION['error'] = "You must specify an instance the item belongs to";
		return false;
	}
	// checks that the item quality is present
	if(empty($_POST['quality'])) {
		$_SESSION['error'] = "You must specify an item quality";
		return false;
	}

	if(isset($_POST['edititem'])) {
		// Have to check if the old itemid is present
		if(empty($_POST['oldid'])) {
			$_SESSION['error'] = "You must specify the old item id";
			return false;
		}
	}

	/* Should probably add checks for the drop-down lists as well */
	return true;
}
if(isset($_POST['newitem'])) {
	if(validateItem()) {
		include_once("../dao/genericDao.php");
		include_once("../dao/itemDao.php");
		$itemDao = new ItemDao();

		try {
			$id = $_POST['id'];
			$hid = $_POST['hid'];
			$name = mysql_real_escape_string($_POST['name']);
			$quality = $_POST['quality'];
			$instance = $_POST['instance'];

			$itemDao->addItem($id, $hid, $name, $quality, $instance);
			$_SESSION['notice'] = "Item added";
		} catch(Exception $e) {
			$_SESSION['error'] = $e->getMessage();
		}
	}
	header("Location: ../index.php?page=admin_item&instance=$instance");
} else if(isset($_POST['edititem'])) {
	if(validateItem()) {
		include_once("../dao/genericDao.php");
		include_once("../dao/itemDao.php");
		$itemDao = new ItemDao();

		try {
			$id = $_POST['id'];
			$hid = $_POST['hid'];
			$oldid = $_POST['oldid'];
			$name = mysql_real_escape_string($_POST['name']);
			$quality = $_POST['quality'];
			$instance = $_POST['instance'];

			$itemDao->editItem($id, $hid, $name, $quality, $instance, $oldid);
			$_SESSION['notice'] = "Item edited";
		} catch(Exception $e) {
			$_SESSION['error'] = $e->getMessage();
		}
	}
	header("Location: ../index.php?page=admin_item&instance=$instance");
} else if(isset($_POST['deleteitem'])) {
		include_once("../dao/genericDao.php");
	include_once("../dao/itemDao.php");
	$itemDao = new ItemDao();
	try {
		$itemDao->deleteItem($_POST['id']);
		$_SESSION['notice'] = "Item deleted";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	$instance = $_POST['instance'];
	header("Location: ../index.php?page=admin_item&instance=$instance");
} else {
	$_SESSION['error'] = "You are not authorized to use this feature";
	header("Location: ../index.php");
}