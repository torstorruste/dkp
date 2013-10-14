<?php
session_start();
include_once("../dao/genericDao.php");
include_once("../dao/raidDao.php");

if(isset($_POST['eid'])) {
	$raidDao = new RaidDao();
	$raidDao->moveEvent($_POST['eid'], $_POST['time']);

	header("Location: ../index.php?page=move_event&id=$_POST[rid]");
}
