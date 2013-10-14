<?php
include_once("check_admin.php");

include_once("dao/raidDao.php");
include_once("dao/playerDao.php");
include_once("dao/instanceDao.php");
include_once("dao/itemDao.php");
$raidDao = new RaidDao();
$playerDao = new PlayerDao();
$instanceDao = new InstanceDao();
$itemDao = new ItemDao();
if(empty($_GET['id'])) {
	echo "You need to specify an id";
} else {
	$raid = $raidDao->getRaid($_GET['id']);
	$events = $raid->getEvents();
	
	foreach($events as $event) {
		echo "<form method=\"post\" action=\"controllers/move_event.php\">".$event->getOverview()."<input type=\"text\" value=\"".$event->getTimeAdded()."\" name=\"time\"><input type=\"hidden\" name=\"eid\" value=\"".$event->getId()."\"/><input type=\"hidden\" name=\"rid\" value=\"".$_GET['id']."\"/><input type=\"submit\" value=\"Move\"/></form>\n";
	}
}
?>

