<?php
class ProfessionDao extends GenericDao{

	function getRecipeProfessions() {
		$query = mysql_query("SELECT DISTINCT profession FROM wow_recipe ORDER BY profession ASC");
		if(mysql_num_rows($query)==0)
			throw new Exception("No recipes found");
		while($result = mysql_fetch_assoc($query)) {
			$array[] = $result['profession'];
		}
		return $array;
	}

	function getRecipes($profession) {
		$query = mysql_query("SELECT * FROM wow_recipe WHERE profession='$profession' ORDER BY Name ASC");
		if(mysql_num_rows($query)==0)
			throw new Exception("No recipes found");
		include_once("models/recipe.php");
		while($result = mysql_fetch_assoc($query)) {
			$array[] = new Recipe($result);
		}
		return $array;
	}

	function getPlayerProfessions() {
		$pid = $this->verifyIdentity();
		$query  = mysql_query("SELECT DISTINCT profession FROM wow_recipe JOIN wow_hasrecipe USING (rid) WHERE pid=$pid");
		if(mysql_num_rows($query)==0)
			throw new Exception("No professions found");
		while($result = mysql_fetch_assoc($query)) {
			$array[] = $result['profession'];
		}
		return $array;
	}

	function getKnownRecipes($pid = 0) {
		// If no pid is specified, use the logged in user
		if($pid==0)
			$pid = $this->verifyIdentity();
		$query = mysql_query("SELECT * FROM wow_recipe JOIN wow_hasrecipe USING (rid) WHERE pid=$pid ORDER BY profession ASC, name ASC");
		if(mysql_num_rows($query)==0)
			throw new Exception("No recipes known");
		include_once("models/recipe.php");
		while($result = mysql_fetch_assoc($query)) {
			$array[] = new Recipe($result);
		}
		return $array;
	}

	function getUnknownRecipes() {
		$pid = $this->verifyIdentity();
		$query = mysql_query("SELECT name, description, rid, profession FROM wow_recipe WHERE rid NOT IN(SELECT rid FROM wow_recipe JOIN wow_hasrecipe USING (rid) WHERE pid=$pid) ORDER BY profession ASC, name ASC");
		if(mysql_num_rows($query)==0)
			throw new Exception("No new recipes found");
		include_once("models/recipe.php");
		while($result = mysql_fetch_assoc($query)) {
			$array[] = new Recipe($result);
		}
		return $array;
	}

	function getMasteries() {
		$pid = $this->verifyIdentity();
		$query = mysql_query("SELECT * FROM wow_ismaster WHERE pid=$pid");
		$array = array();
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['profession']] = $result['mastery'];
		}
		return $array;
	}

	function getPossibleMasteries() {
		$query = mysql_query("SELECT profession, mastery FROM wow_mastery ORDER BY profession ASC, mastery ASC");
		if(mysql_num_rows($query)==0)
			throw new Exception("No masteries found");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['profession']][] = $result['mastery'];
		}
		return $array;
	}

	function getCrafters($profession) {
		$query = mysql_query("SELECT DISTINCT pid, mastery, playername, class FROM wow_recipe JOIN wow_hasrecipe USING (rid) LEFT JOIN wow_ismaster USING (pid) JOIN wow_player USING (pid) WHERE wow_recipe.profession = '$profession'");
		if(mysql_num_rows($query)==0)
			throw new Exception("No crafters available");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['pid']]['playername'] = $result['playername'];
			$array[$result['pid']]['pid'] = $result['pid'];
			$array[$result['pid']]['mastery'] = $result['mastery'];
			$array[$result['pid']]['class'] = $result['class'];
		}
		return $array;
	}

	function getCraftersForRecipe($rid) {
		$query = mysql_query("SELECT * FROM wow_player JOIN wow_hasrecipe USING (pid) WHERE rid=$rid");
		if(mysql_num_rows($query)==0)
			throw new Exception("No crafter available");
		include_once("models/player.php");
		while($result = mysql_fetch_assoc($query)) {
			$array[] = new Player($result);
		}
		return $array;
	}

	function getRecipe($rid) {
		$query = mysql_query("SELECT * FROM wow_recipe WHERE rid=$rid");
		if(mysql_num_rows($query)==0)
			throw new Exception("Could not find recipe");
		include_once("models/recipe.php");
		return new Recipe(mysql_fetch_assoc($query));
	}

	function getAllRecipes() {
		$query = mysql_query("SELECT * FROM wow_recipe");
		if(mysql_num_rows($query)==0)
			throw new Exception("No items in the database");
		include_once("models/recipe.php");
		while($result = mysql_fetch_assoc($query)) {
			$array[] = new Recipe($result);
		}
		return $array;
	}
	
	function addRecipeToPlayer($recipe) {
		$pid = $this->verifyIdentity();
		mysql_query("INSERT INTO wow_hasrecipe VALUES ($pid, $recipe)");
		if(mysql_error()) {
			throw new Exception("Could not add the recipe at this time");
		}
	}

	function removeRecipeFromPlayer($recipe) {
		$pid = $this->verifyIdentity();
		mysql_query("DELETE FROM wow_hasrecipe WHERE pid=$pid AND rid=$recipe");
		if(mysql_error())
			throw new Exception("Could not remove the recipe");
	}

	function changeMastery($profession, $mastery) {
		$pid = $this->verifyIdentity();
		$query = mysql_query("SELECT * FROM wow_ismaster WHERE pid=$pid AND profession='$profession'");
		if(mysql_num_rows($query)==0) {
			// We have to add it
			mysql_query("INSERT INTO wow_ismaster VALUES ($pid, '$profession', '$mastery')");
		} else {
			// We have to modify it
			mysql_query("UPDATE wow_ismaster SET mastery='$mastery' WHERE pid=$pid AND profession='$profession'");
		}
		if(mysql_error())
			throw new Exception("Unable to change mastery");
	}

	function addRecipe($name, $rid, $profession) {
		mysql_query("INSERT INTO wow_recipe VALUES ('$name', null, $rid, '$profession'");
		if(mysql_error()) {
			throw new Exception("Could not add the recipe");
		}
	}

	function editRecipe($name, $rid, $profession, $oldrid) {
		mysql_query("UPDATE wow_recipe SET name='$name', profession='$profession', rid=$rid WHERE rid=$oldrid");
		if(mysql_error())
			throw new Exception("Could not Edit the recipe");
	}
}