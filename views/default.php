<div id="left" class="frame">
<span class="top"></span>
<?php
include_once("dao/genericDao.php");
include_once("dao/playerDao.php");
include_once("dao/raidDao.php");
$playerDao = new PlayerDao();
$raidDao = new RaidDao();
try {
	$raiders = $playerDao->getRaiders();
	echo "<h3>Players</h3>";
	echo "<table class=\"sortable\">\n<thead>\n<tr><th>Name:</th><th>EP:</th><th>GP:</th><th>QP:</th><th>Priority:</th>";
	if(isset($_SESSION['dkp']))
		echo "<th>Activity:</th>";
	echo "</tr>\n</thead>\n<tbody>\n";
	foreach($raiders as $r) {
		echo "<tr><td>".$r->getFormattedName()."</td><td>".$r->getEP()."</td><td>".$r->getGP()."</td><td>".$r->getQP()."</td><td>".$r->getPriority()."</td>";
		if(isset($_SESSION['admin']) || isset($_SESSION['dkp']) && $r->identifyPlayer($_SESSION['dkp']))
			echo "<td>".$r->getActivity()."</td></tr>\n";
	}
	echo "</tbody>\n</table>\n";
} catch(Exception $e) {
	echo "No players found";
	echo $e->getMessage();
}
?><span class="bottom"></span>
</div>
<div id="middle" class="frame">
<span class="top"></span>
<?php

try {
	$planned = $raidDao->getRaids("Planned");
	echo "<h3>Planned raids</h3>\n";
	foreach($planned as $raid) {
		if(isset($_SESSION['dkp'])) {
			if($raidDao->isSigned($raid))
				echo "<a href=\"index.php?page=show_raid&id=".$raid->getId()."\"><img src=\"images/signed.png\" style=\"margin-left: 0px;\"></a> ";
			else
				echo "<a href=\"index.php?page=show_raid&id=".$raid->getId()."\"><img src=\"images/unsigned.png\" style=\"margin-left: 0px\"></a> ";
		}
		echo "<a href=\"index.php?page=show_raid&id=".$raid->getId()."\">".$raid->getTarget()."</a> - ".$raid->getStart();
		if(isset($_SESSION['admin']))
			echo " (<a href=\"index.php?page=admin_event&id=".$raid->getId()."\">A</a>)";
		echo "<br/>\n";
	}
} catch(Exception $e) {
	echo $e->getMessage();
}

try {
	$planned = $raidDao->getRaids("Active");
	echo "<h3>Active raids</h3>\n";
	foreach($planned as $raid) {
		echo "<a href=\"index.php?page=show_raid&id=".$raid->getId()."\">".$raid->getTarget()."</a> - ".$raid->getStart();
		if(isset($_SESSION['admin']))
			echo " (<a href=\"index.php?page=admin_event&id=".$raid->getId()."\">A</a>)";
		echo "<br/>\n";
	}
} catch(Exception $e) {

}
try {
	if(isset($_GET['show']))
		$number = 'all';
	else
		$number = 20;
	$planned = $raidDao->getRaids("Finished", $number);
	echo "<h3>Finished raids</h3>\n";
	echo "<table>\n<tbody>\n";
	foreach($planned as $raid) {
		echo "<a href=\"index.php?page=show_raid&id=".$raid->getId()."\">".$raid->getTarget()."</a> - ".$raid->getStart();
		if(isset($_SESSION['admin']))
			echo " (<a href=\"index.php?page=admin_event&id=".$raid->getId()."\">A</a>)";
		echo "<br/>\n";
	}
	if(count($planned)==20)
		echo "<a href=\"index.php?show\">Show all</a>\n";
	echo "</tbody>\n</table>\n";
} catch(Exception $e) {

}
?><span class="bottom"></span>
</div>
<div class="frame" id="menu">
<span class="top"></span>
<?php
if(isset($_SESSION['dkp'])) {
	if(isset($_SESSION['admin'])) {
		include("views/_admin.php");
		echo '<a href="index.php?page=statistics">Statistics</a><br>';
		echo '<a href="index.php?page=view_items">View items</a><br>';
	}
	echo '<a href="index.php?page=edit_password">Change password</a><br>';
	echo '<a href="index.php?page=logout">Log out</a><br>';
}else
	echo '<a href="index.php?page=login">Log in</a><br>';
?>


<span class="bottom"></span>
</div>