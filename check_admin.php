<?php
if(!isset($_SESSION['dkp'])) {
	$_SESSION['error'] = "You need to login first";
	$_SESSION['page'] = $_GET['page'];
	header("Location: index.php?page=login");
	die();
} else {
	include_once("dao/genericDao.php");
	$db = new GenericDao();
	if(!$db->checkSession($_SESSION['dkp']) || !isset($_SESSION['admin'])) {
		$_SESSION['error'] = "You do not have sufficient access";
		header("Location: index.php");
		die();
	}
}