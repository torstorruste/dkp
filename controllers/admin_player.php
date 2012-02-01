<?php
function validateNew() {
	// Check if the name is set
	if(empty($_POST['name'])) {
		$_SESSION['error'] = "The name must be at least one character long";
		return false;
	}
	// Check if the name is valid
	if(eregi("[^a-zA-Z]", $_POST['name'])) {
		$_SESSION['error'] = "The name can only contain letters from a-z or A-Z";
		return false;
	}
	// Check if the class is set
	if(empty($_POST['class'])) {
		$_SESSION['error'] = "You must select a class";
		return false;
	}
	/* Should probably add checks for a correct class */
	return true;
}

function validateEdit() {
	// Check if the name is set
	if(empty($_POST['playername'])) {
		$_SESSION['error'] = "The name must be at least one character long";
		return false;
	}
	// Check if the name is valid
	if(eregi("[^a-zA-Z]", $_POST['playername'])) {
		$_SESSION['error'] = "The name can only contain letters from a-z or A-Z";
		return false;
	}
	// Check if the username is set
	if(empty($_POST['username'])) {
		$_SESSION['error'] = "The username must be at least one character long";
		return false;
	}
	// Check if the username is valid
	if(eregi("[^a-zA-Z]", $_POST['username'])) {
		$_SESSION['error'] = "The username can only contain letters from a-z or A-Z";
		return false;
	}
	// Check if the class is set
	if(empty($_POST['class'])) {
		$_SESSION['error'] = "You must select a class";
		return false;
	}
	return true;
}
session_start();
// If we want to add a player
if(isset($_POST['newplayer'])) {
	$classes = array('Deathknight','Druid','Hunter','Mage','Paladin','Priest','Shaman','Rogue','Warlock','Warrior');

	// Check that all the information specified is correct
	if(!validateNew())
		header("Location: ../index.php?page=admin_player");
	else {
		$name = $_POST['name'];
		$class = $_POST['class'];
		$role = $_POST['role'];
		// If we have gotten this far - everything is good, and we can proceed

		include_once("../dao/genericDao.php");
		include_once("../dao/playerDao.php");
		include_once("../models/player.php");
		$playerDao = new PlayerDao();

		try {
			$player = new Player();
			$player->setName($name);
			$player->setClass($class);
			$player->setRole($role);

			$playerDao->addPlayer($player);
			$_SESSION['notice'] = "Added the player to the database";
		} catch(Exception $e) {
			$_SESSION['error'] = $e->getMessage();
		}
		header("Location: ../index.php?page=admin_player");
	}

}
// If we want to edit a player
else if(isset($_POST['editplayer'])) {
	// If the name and class aren't correct
	if(!validateEdit())
		header("Location: ../index.php?page=admin_player");
	else {
		include_once("../dao/genericDao.php");
		include_once("../dao/playerDao.php");
		include_once("../models/player.php");
		$playerDao = new PlayerDao();
		try {
			if($_POST['rights'] == "true")
				$rights = 'Officer';
			else
				$rights = 'Raider';
			$player = new Player();
			$player->setID($_POST['pid']);
			$player->setName($_POST['playername']);
			$player->setUsername($_POST['username']);
			$player->setClass($_POST['class']);
			$player->setRights($rights);
			$player->setActive($_POST['active']=='true'?true:false);
			$player->setRole($_POST['role']);

			$playerDao->editPlayer($player);
			$_SESSION['notice'] = "The player has been edited";
		}catch(Exception $e) {
			$_SESSION['error'] = $e->getMessage();
		}
		header("Location: ../index.php?page=admin_player");
	}
}
// If we want to delete a player
else if(isset($_POST['deleteplayer'])) {
	include_once("../dao/genericDao.php");
	include_once("../dao/playerDao.php");
	$playerDao = new PlayerDao();
	try{
		$playerDao->removePlayer($_POST['pid']);
		$_SESSION['notice'] = "The player has been removed from the database";
		header("Location: ../index.php?page=admin_player");
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
		header("Location: ../index.php?page=admin_player");
	}
}
// Else, redirect to the default view
else {
	$_SESSION['error'] = "You are not authorized to use this feature";
	header("Location: ../index.php");
}