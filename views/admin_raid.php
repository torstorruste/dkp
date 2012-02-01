<?php
include_once("check_admin.php");
include_once("dao/instanceDao.php");
include_once("dao/raidDao.php");
$instanceDao = new InstanceDao();
$raidDao = new RaidDao();
try {
	$instances = $instanceDao->getInstances();
?>
<div id="widest" class="frame">
<span class="top"></span>
<a href="index.php">Back</a>
<h3>Add new raid</h3>
<form method="post" action="controllers/admin_raid.php">
<table>
<thead>
<tr><th>Instance:</th><th>Start:</th><th>Status:</th></tr>
</thead>
<tbody>
<tr>
  <td><select name="instance"><option value="">Undetermined</option><?php foreach($instances as $instance) echo "<option value=\"".$instance->getId()."\">".$instance->getName()."</option>";?></select></td>
  <td><input type="text" name="start" value="<?php echo date("Y-m-d") ?> 20:00"><a onclick="displayCalendar(document.forms[0].start,'yyyy-mm-dd hh:ii',this,true)"><img src="images/calendar.gif"></a></td>
  <td><select name="status"><option value="Planned">Planned</option><option value="Active">Active</option></select></td>  
  <td><input type="submit" value="Add raid" name="addraid"></td>
</tr>
</tbody>
</table>
</form>
<h3>The current raids</h3>
<?php
	// List out the planned raids
	try {
		echo "<strong>Planned</strong><br/>";
		$planned = $raidDao->getRaids("Planned");
		echo "\n<table>\n<tbody>\n";
		foreach($planned as $raid)  {
			echo "<tr><td>".$raid->getStatus()."</td><td><a href=\"index.php?page=admin_event&id=".$raid->getId()."\">".$raid->getTarget()."</a></td><td>".$raid->getStart()."</td><td><a href=\"controllers/admin_raid.php?deleteraid&id=".$raid->getId()."\">Delete</a></td></tr>\n";
		}
		echo "</tbody>\n</table>\n";
	} catch(Exception $e) {
		echo $e->getMessage();
	}
	// List out the active raids
	try {
		echo "<br/><strong>Active</strong><br/>";
		$active = $raidDao->getRaids();
		echo "\n<table>\n<tbody>\n";
		foreach($active as $raid)  {
			echo "<tr><td>".$raid->getStatus()."</td><td><a href=\"index.php?page=admin_event&id=".$raid->getId()."\">".$raid->getTarget()."</a></td><td>".$raid->getStart()."</td><td><a href=\"controllers/admin_raid.php?deleteraid&id=".$raid->getId()."\">Delete</a></td></tr>\n";
		}
		echo "</tbody>\n</table>\n";
	} catch(Exception $e) {
		echo $e->getMessage();
	}
	// List out the finished raids
	try {
		echo "<br/><strong>Finished</strong><br/>";
		$active = $raidDao->getRaids("Finished");
		echo "\n<table>\n<tbody>\n";
		foreach($active as $raid)  {
			echo "<tr><td>".$raid->getStatus()."</td><td><a href=\"index.php?page=admin_event&id=".$raid->getId()."\">".$raid->getTarget()."</a></td><td>".$raid->getStart()."</td></tr>\n";
		}
		echo "</tbody>\n</table>\n";
	} catch(Exception $e) {
		echo $e->getMessage();
	}
} catch(Exception $e) {
	echo $e->getMessage();
}
?>
<br/><br/>
<a href="index.php">Back</a>
<span class="bottom"></span>
</div>