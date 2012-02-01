<?php
session_start();
if(isset($_POST['newRecipe'])) {
	include_once("../dao/professionDao.php");
	$professionDao = new ProfessionDao();
	try {
		$name = mysql_escape_string(stripslashes($_POST['name']));
		$id = mysql_escape_string(stripslashes($_POST['rid']));
		$profession = mysql_escape_string(stripslashes($_POST['profession']));
		if(verifyInput($name, $id, $profession)) {
			$professionDao->addRecipe($name, $id, $profession);
			
			$_SESSION['notice'] = "Recipe added";
		}
		
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=new_recipes");
} else if(isset($_POST['editRecipe'])) {
	include_once("../dao/professionDao.php");
	$professionDao = new ProfessionDao();
	try {
		$name = mysql_escape_string(stripslashes($_POST['name']));
		$id = mysql_escape_string(stripslashes($_POST['rid']));
		$profession = mysql_escape_string(stripslashes($_POST['profession']));
		$oldid = mysql_escape_string(stripslashes($_POST['oldrid']));
		if(verifyInput($name, $id, $profession)) {
			$professionDao->editRecipe($name, $id, $profession, $oldid);
			
			$_SESSION['notice'] = "Recipe changed";
		}
		
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=new_recipes");
} else {
	$_SESSION['error'] = "You should not be here";
	header("Location: ../index.php");
}

function verifyInput($name, $id, $profession) {
	if(!isset($name)) {
		$_SESSION['error'] = "The recipe needs a name";
		return false;
	}
	
	if(!isset($id)) {
		$_SESSION['error'] = "The recipe needs the proper id";
	}
	
	if(ereg("[^0-9]", $id)) {
		$_SESSION['error'] = "The ID consists of numbers only";
		return false;
	}
	return true;
}