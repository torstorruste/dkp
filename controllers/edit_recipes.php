<?php
session_start();
if(isset($_POST['addRecipe'])) {
	include_once("../dao/professionDao.php");
	$professionDao = new ProfessionDao();
	try {
		$recipe = $_POST['recipe'];
		$professionDao->addRecipeToPlayer($recipe);
		$_SESSION['notice'] = "Recipe added";
	} catch (Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=edit_recipes");
} else if (isset($_POST['removeRecipe'])) {
	include_once("../dao/professionDao.php");
	$professionDao = new ProfessionDao();
	try {
		$recipes = $_POST['recipe'];
		foreach($recipes as $recipe) {
			$professionDao->removeRecipeFromPlayer($recipe);
		}
		$_SESSION['notice'] = "Recipes removed";
	} catch (Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=edit_recipes");
} else if(isset($_POST['changeMastery'])) {
	include_once("../dao/professionDao.php");
	$professionDao = new ProfessionDao();
	
	try {
		$profession = $_POST['profession'];
		$mastery = $_POST['mastery'];
		
		$professionDao->changeMastery($profession, $mastery);
		
		$_SESSION['notice'] = "Mastery changed";
	} catch(Exception $e) {
		$_SESSION['error'] = $e->getMessage();
	}
	header("Location: ../index.php?page=edit_recipes");
} else {
	$_SESSION['error'] = "You should not be here";
	header("Location: ../index.php");
}