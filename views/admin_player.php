<?php
// Check if a user is logged in
include("check_admin.php");

$classes = array('Deathknight','Druid','Hunter','Mage','Monk','Paladin','Priest','Shaman','Rogue','Warlock','Warrior');
$roles = array('Healer', 'Melee', 'Ranged', 'Tank');
?>
<div id="widest" class="frame">
<span class="top"></span>
<a href="index.php">Back</a>
<h3>Add new player</h3>
<form action="controllers/admin_player.php" method="post">
<table>
<thead>
<tr><th>Name:</th><th>Class:</th></tr>
</thead><tbody>
<tr>
  <td><input type="text" name="name"></td>
  <td><select name="class"><?php foreach($classes as $class) echo "<option value=\"$class\">$class</option>";?></select></td>
  <td><select name="role"><?php foreach($roles as $role) echo "<option value=\"$role\">$role</option>"; ?></select></td>
  <td><input type="submit" value="Add player" name="newplayer"></td></tr>
</tbody>
</table>
</form>
<h3>The current players:</h3>
<a href="?page=admin_player&showall">Show all</a>
<?php
include_once('dao/playerDao.php');
$playerDao = new PlayerDao();
try{
	if(isset($_GET['showall']))
		$raiders = $playerDao->getUsers();
	else
		$raiders = $playerDao->getRaiders();
	echo "<table>\n<thead>\n<tr><th>Name:</th><th>Username:</th><th>Class:</th><th>Role</th><th>Active:</th><th>Admin:</th></tr>\n</thead>\n<tbody>";
	foreach($raiders as $raider) {
		echo "<tr><form action=\"controllers/admin_player.php\" method=\"post\"><td><input type=\"text\" name=\"playername\" value=\"".$raider->getName()."\"></td><td><input type=\"text\" name=\"username\" value=\"".$raider->getUsername()."\"></td>\n<td><select name=\"class\">";
		foreach($classes as $class) {
			if($class == $raider->getClass())
				echo "<option value=\"$class\" selected=\"selected\">$class</option>";
			else
				echo "<option value=\"$class\">$class</option>";
		}
		echo "</select></td>\n";
		echo "<td><select name=\"role\">";
		foreach($roles as $role) {
			if($role == $raider->getRole())
				echo "<option value=\"$role\" selected=\"selected\">$role</option>";
			else
				echo "<option value=\"$role\">$role</option>";
		}
		echo "</select></td>";
		echo "<td><input type=\"hidden\" name=\"active\" value=\"false\">";
		if($raider->isActive())
			echo "<input type=\"checkbox\" name=\"active\" checked=\"checked\" value=\"true\">";
		else
			echo "<input type=\"checkbox\" name=\"active\" value=\"true\">";
		echo "</td><td><input type=\"hidden\" name=\"rights\" value=\"false\">";
		if($raider->isOfficer())
			echo "<input type=\"checkbox\" name=\"rights\" checked=\"checked\" value=\"true\">";
		else
			echo "<input type=\"checkbox\" name=\"rights\" value=\"true\">";
		echo "</td><td><input type=\"hidden\" value=\"".$raider->getId()."\" name=\"pid\"><input type=\"submit\" value=\"Edit\" name=\"editplayer\"></td></form>";
		echo "<td><form method=\"post\" action=\"controllers/admin_player.php\"><input type=\"hidden\" value=\"".$raider->getId()."\" name=\"pid\"><input type=\"submit\" value=\"Delete\" name=\"deleteplayer\" onClick=\"return confirm('Are you sure you want to delete ".$raider->getName()."?')\"></form></td></tr>";
		
	}
} catch(Exception $e) {
	echo "No players added to the system at this point";
}
?>
</tbody>
</table>
<a href="index.php">Back</a>
<span class="bottom"></span>
</div>