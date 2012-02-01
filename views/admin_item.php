<?php
include("check_admin.php");
include_once("dao/itemDao.php");
include_once("dao/instanceDao.php");
$itemDao = new ItemDao();
$instanceDao = new InstanceDao();

$qualities = Array('Epic', 'Legendary', 'Rare');
try {
	$instances = $instanceDao->getInstances();

	echo '<div id="widest" class="frame">';
	echo '<span class="top"></span>';
	echo '<a href="index.php">Back</a>';
	echo "<h3>Add new item</h3>\n";
?>
<table>
<thead>
<tr><th>Name:</th><th>Id:</th><th>Heroic Id</th><th>Quality:</th><th>Instance:</th></tr>
</thead>
<tbody>
<tr><form method="post" action="controllers/admin_item.php">
  <td><input type="text" name="name" size="40" maxlength="100"></td>
  <td><input type="text" name="id" maxlength="6" size="6"></td>
  <td><input type="text" name="hid" maxlength="6" size="6"></td>
  <td><select name="quality"><?php foreach($qualities as $quality) echo "<option value=\"$quality\">$quality</option>";?></select></td>
  <td><?php
  try {

	  echo "<select name=\"instance\">";
	  foreach($instances as $instance)
	  	echo "<option value=\"".$instance->getId()."\">".$instance->getName()."</option>";
	  echo "</select>";
  } catch(Exception $e) {
  	  echo $e->getMessage();
  }?></select></td>
  <td><input type="submit" name="newitem" value="Add item"></td>
</form></tr>
</tbody>
</table>



<h3>The current items:</h3>
<?php
if(isset($_GET['instance']))
{
	try {
		$items = $itemDao->getItems($_GET['instance']);
		echo "<table>\n<thead>\n<tr><th>Name:</th><th>Id:</th><th>Heroic Id:</th><th>Quality:</th><th>Instance:</th></tr>\n</thead>\n<tbody>\n";
		foreach($items as $item) {
			echo "<tr><form method=\"post\" action=\"controllers/admin_item.php\"><td><input type=\"text\" name=\"name\" size=\"40\" maxlength=\"40\" value=\"".$item->getName()."\"></td>";
			echo "<td><input type=\"text\" maxlength=\"6\" size=\"6\" name=\"id\" value=\"".$item->getId()."\"></td>";
			echo "<td><input type=\"text\" maxlength=\"6\" size=\"6\" name=\"hid\" value=\"".$item->getHid()."\"></td>";
			echo "<td><select name=\"quality\">";
			foreach($qualities as $quality) {
				if($item->getQuality()==$quality)
					echo "<option value=\"$quality\" selected=\"selected\">$quality</option>";
				else
					echo "<option value=\"$quality\">$quality</option>";
			}
			echo "</select></td>";
			echo "<td><select name=\"instance\">";
			foreach($instances as $instance) {
				if($instance->getId() == $item->getInstance())
					echo "<option value=\"".$instance->getId()."\" selected=\"selected\">".$instance->getName()."</option>";
				else
					echo "<option value=\"".$instance->getId()."\">".$instance->getName()."</option>";
			}
			echo "</select></td>";
			echo "<td><input type=\"hidden\" name=\"oldid\" value=\"".$item->getId()."\"><input type=\"submit\" name=\"edititem\" value=\"Edit\"></form></td>";
			echo "<td><form method=\"post\" action=\"controllers/admin_item.php\"><input type=\"hidden\" name=\"instance\" value=\"".$_GET['instance']."\"/><input type=\"hidden\" name=\"id\" value=\"".$item->getId()."\"><input type=\"submit\" name=\"deleteitem\" value=\"Delete\" onClick=\"return confirm('Are you sure you want to delete ".$item->getName()."?')\"></form></td>";
			echo "</tr>\n";
		}
		echo "</tbody>\n</table>\n";
	} catch(Exception $e) {
		echo $e->getMessage()."<br/>";
	}
}
else
{
	echo "<form method=\"get\"><input type=\"hidden\" name=\"page\" value=\"admin_item\"/><select name=\"instance\">";
	foreach($instances as $instance)
	{
		echo "<option value=\"".$instance->getId()."\">".$instance->getName()."</option>";
	}
	echo "</select><input type=\"submit\" value=\"Show items\" /></form>";
}
}catch(Exception $e) {
	echo $e->getMessage();
}
?>
<br/><br/><a href="?">Back</a>
<span class="bottom"></span>
</div>