<?php
include("check_admin.php");
include_once("dao/instanceDao.php");
?>
<div id="widest" class="frame">
<span class="top"></span>
<a href="index.php">Back</a>
<h3>Add new instance</h3>
<form method="post" action="controllers/admin_instance.php">
<table>
<thead>
  <tr>
    <th>Name:</th>
    <th>Multiplier:</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td><input type="text" name="name" size="40" maxlength="40"></td>
    <td><input type="text" name="multiplier" size="3" maxlength="3"></td>
	<td><input type="submit" value="Add instance" name="addinstance"></td>
  </tr>
</tbody>
</table>
</form>
<h3>The current instances</h3>
<?php 
try {
	$instanceDao = new InstanceDao();
	$instances = $instanceDao->getInstances();
	echo "<table>\n<thead>\n<tr><th>Name:</th></tr>\n</thead>\n<tbody>\n";
	foreach($instances as $instance) {
		echo "<tr><form method=\"post\" action=\"controllers/admin_instance.php\">";
		echo "<td><input type=\"text\" name=\"name\" value=\"".$instance->getName()."\" size=\"40\" maxlength=\"40\"></td>";
		echo "<td><input type=\"text\" name=\"multiplier\" value=\"".$instance->getMultiplier()."\" size=\"3\" maxlength=\"3\"></td>";
		echo "<td><input type=\"hidden\" name=\"id\" value=\"".$instance->getId()."\"><input type=\"submit\" value=\"Edit\" name=\"editinstance\"></form></td>";
		echo "<td><form method=\"post\" action=\"controllers/admin_instance.php\"><input type=\"hidden\" name=\"id\" value=\"".$instance->getId()."\"><input type=\"submit\" value=\"Delete\" name=\"deleteinstance\" onClick=\"return confirm('Are you sure you want to delete ".$instance->getName()."?')\"></form></td>";
		echo "</tr>\n";
	}
	echo "</tbody>\n</table>\n";
} catch(Exception $e) {
	echo $e->getMessage();
}
?>
<br><br>
<a href="index.php">Back</a>
<span class="bottom"></span>
</div>