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
	try {
		// Get the raid
		$raid = $raidDao->getRaid($_GET['id']);

		// List out all the users in the system, so they can be added
		$allPlayers = $playerDao->getPlayersByClass();
		
		// Get the players in the raid, or create a new array if none are found
		$playersInRaid = $raid->getPlayers($raid);

		echo "<div id=\"wide\" class=\"frame\">\n<span class=\"top\"></span>\n";
		echo "<a href=\"index.php\">Back</a>\n";
		echo "<h3>".$raid->getTarget()." - ".$raid->getStart()."</h3>";
		if($raid->getStatus() != 'Finished') {
			// Allow the user to change the target
			$instances = $instanceDao->getInstances();
			
			echo "<form method=\"post\" action=\"controllers/admin_event.php\"><select name=\"instance\"><option value=\"\">Undetermined</option>";
			foreach($instances as $instance) {
				if($instance->getName() == $raid->getTarget())
					echo "<option value=\"".$instance->getId()."\" selected=\"selected\">".$instance->getName()."</option>";
				else
					echo "<option value=\"".$instance->getId()."\">".$instance->getName()."</option>";
			}
			echo "<input type=\"hidden\" value=\"$_GET[id]\" name=\"id\"><input type=\"submit\" value=\"Change target\" name=\"edittarget\">";
			echo "</form>";
			
			// Allow the user to change the start-time
			echo "<form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"text\" name=\"start\" value=\"".$raid->getStartSQL()."\"><a onclick=\"displayCalendar(document.forms[1].start,'yyyy-mm-dd hh:ii',this,true)\"><img src=\"images/calendar.gif\"></a><input type=\"hidden\" name=\"rid\" value=\"$_GET[id]\"><input type=\"submit\" value=\"Change start\" name=\"changestart\"></form>";
			
			
			$class = "";
			if(count($allPlayers) > count($playersInRaid)) {
				echo "<h3>Add players</h3>\n";
				echo "<form method=\"post\" action=\"controllers/admin_event.php\"><table>\n<tr>";
				$i = 0;
				foreach($allPlayers as $player) {
					$inRaid = false;
					// Check that the player isn't added to the raid already
					foreach($playersInRaid as $p) {
						if($p->getName() == $player->getName())
							$inRaid = true;
					}
					// If the player isn't in the raid - add him here
					if(!$inRaid) {
						if($player->getClass() != $class) {
							if($class != "") {
								echo "</td>";
								if($i++==4)
									echo"</tr><tr>";
							}
							$class = $player->getClass();
							echo "<td valign=\"top\"><span class=\"$class\" style=\"font-weight: bold\">$class</span><br>";
						}
						echo "<input type=\"checkbox\" value=\"".$player->getId()."\" name=\"players[]\">".$player->getName()."<br/>";
					}
				}
				echo "</tr>\n</table>\n<input type=\"hidden\" name=\"id\" value=\"$_GET[id]\"><input type=\"submit\" name=\"addplayers\" value=\"Add Players\"></form>\n";
			}
		}
		
		if($raid->getStatus()=='Active') {
			// Give the player the option to duplicate the raid
			echo "<form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" value=\"$_GET[id]\" name=\"rid\"><input type=\"submit\" value=\"Duplicate raid\" name=\"duplicate\"></form>\n";
			
			// Show the different events to add
			echo "<h3>Manage events</h3>\n";
			// Start point
			echo "<form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" value=\"$_GET[id]\" name=\"id\"><input type=\"submit\" value=\"Add Start Point\" name=\"startpoint\"></form>\n";
			
			// Hour points
			echo "<form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" value=\"$_GET[id]\" name=\"id\"><select name=\"amount\"><option value=\"10\">10</option><option value=\"20\">20</option></select><input type=\"submit\" value=\"Add Hour Points\" name=\"hourpoint\"></form>\n";
			
			// Items
			try {
				$playersInRaid = $raid->getPlayers();
				$items = $itemDao->getItems($raid->getInid());
				if(count($playersInRaid)==0) {
					echo "No players found";
				} else {
					echo "<form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" value=\"$_GET[id]\" name=\"id\">";
					
					
					echo "<select name=\"player\">";
					foreach($playersInRaid as $player)
						echo "<option value=\"".$player->getId()."\">".$player->getName()."</option>";
					echo "</select>";
					
					echo "<select name=\"item\">";
					foreach($items as $item)
						echo "<option value=\"".$item->getId().",".$item->getHid()."\">".$item->getName()."</option>";
					echo "</select>";
					
					echo "Heroic:<input type=\"checkbox\" value=\"heroic\" name=\"heroic\">";
					
					echo "Offspec:<input type=\"checkbox\" value=\"offspec\" name=\"offspec\">";
					
					echo "<input type=\"submit\" name=\"buyitem\" value=\"Register transaction\">";
					
					echo "</form>\n";
				}
			} catch(Exception $e) {
				echo $e->getMessage();
			}
			
			// Add bonuses to individual players
			echo "<form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" name=\"rid\" value=\"$_GET[id]\">";
			echo "<select name=\"pid\">";
			foreach($playersInRaid as $player)
				echo "<option value=\"".$player->getId()."\">".$player->getName()."</option>";
			echo "</select>";
			echo "<input type\"text\" name=\"amount\" maxlength=\"4\" size=\"4\"><input type=\"text\" value=\"Comment\" name=\"comment\"><input type=\"submit\" value=\"Add Bonus\" name=\"bonus\"></form>\n";
			
			// Give the posibility of ending a raid, if the raid has a valid target
			$decay = $raid->getDecay()==1?" checked=\"checked\"":"";
			if($raid->getTarget() != "Undetermined")
				echo "<form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" name=\"id\" value=\"$_GET[id]\">Decay:<input type=\"checkbox\" value=\"true\" name=\"decay\"$decay><input type=\"submit\" value=\"Finish raid\" name=\"finishraid\" onClick=\"return confirm('Are you really sure you want to end the raid?')\"></form>\n";
		} elseif($raid->getStatus()=='Planned') {
			echo "<h3>Manage events</h3>\n";
			// Give the option to start the raid
			echo "<form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" value=\"$_GET[id]\" name=\"id\"><input type=\"submit\" value=\"Start raid\" name=\"startraid\" onClick=\"return confirm('Are you sure you want to start the raid now?')\"></form>\n";
		} else {
			echo "<h3>The raid is finished</h3>\n";
			echo "<form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" value=\"$_GET[id]\" name=\"id\"><input type=\"submit\" value=\"Reopen raid\" name=\"reopenraid\" onClick=\"return confirm('Are you sure you want to reopen the raid?')\"></form>\n";
		}
		
		// List out all the players in the raid
		echo "\n\n<h3>Players in the raid (".count($playersInRaid).")</h3>\n";
		echo "<table class=\"sortable\">\n<thead>\n<tr><th>Name:</th><th>EP:</th><th>GP:</th><th>QP<th>Priority</th><th>Changed<br/>EP</th><th>Changed<br/>GP</th></tr>\n</thead>\n<tbody>\n";
		try {
			$playersInRaid = $raid->getPlayers();
			$classes = array('Deathknight','Druid','Hunter','Mage','Paladin','Priest','Shaman','Rogue','Warlock','Warrior');
			foreach($classes as $class)
			{
				foreach($playersInRaid as $raider) {
					if($raider->getClass() != $class)
						continue;
					// Update the EP and GP according to what the player has earned this raid
					if($raid->getStatus()=='Active') {
						$raider->addEP($raidDao->getChangedEP($raid,$raider->getID()));
						$raider->addGP($raidDao->getChangedGP($raid,$raider->getID()));
					}
				
					echo "<tr><td>".$raider->getFormattedName()."</td>";
					echo "<td>".$raider->getEP()."</td>";
					echo "<td>".$raider->getGP()."</td>";
					echo "<td>".($raider->getQP()+($raid->getStatus()=='Active'?$raidDao->getQueueTime($raid, $raider->getID()):0))."</td>";
					echo "<td>".$raider->getPriority()."</td>";
					echo "<td>".$raidDao->getChangedEP($raid,$raider->getID())."</td>";
					echo "<td>".$raidDao->getChangedGP($raid,$raider->getID())."</td>";
					$onQueue = $raidDao->checkStatus($raid->getId(), $raider->getId(), "Queue");
					$afk = $raidDao->checkStatus($raid->getId(), $raider->getId(), "AFK");
					if($raid->getStatus()=='Active') {
						if($afk)
							echo "<td><form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" name=\"pid\" value=\"".$raider->getID()."\"><input type=\"hidden\" name=\"rid\" value=\"".$raid->getID()."\"><input type=\"hidden\" name=\"type\" value=\"ReturnAFK\"><input type=\"submit\" value=\"Return\" name=\"status\"></form></td>";
						else
							echo "<td><form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" name=\"pid\" value=\"".$raider->getID()."\"><input type=\"hidden\" name=\"rid\" value=\"".$raid->getID()."\"><input type=\"hidden\" name=\"type\" value=\"AFK\"><input type=\"submit\" value=\"AFK\" name=\"status\"></form></td>";
						if($onQueue)
							echo "<td><form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" name=\"pid\" value=\"".$raider->getID()."\"><input type=\"hidden\" name=\"rid\" value=\"".$raid->getID()."\"><input type=\"hidden\" name=\"type\" value=\"ReturnQueue\"><input type=\"submit\" value=\"Return\" name=\"status\"></form></td>";
						else
							echo "<td><form method=\"post\" action=\"controllers/admin_event.php\"><input type=\"hidden\" name=\"pid\" value=\"".$raider->getID()."\"><input type=\"hidden\" name=\"rid\" value=\"".$raid->getID()."\"><input type=\"hidden\" name=\"type\" value=\"Queue\"><input type=\"submit\" value=\"Queue\" name=\"status\"></form></td>";
	
						if($afk)
							echo "<td>&lt;AFK&gt;</td>";
						elseif($onQueue)
							echo "<td>&lt;Queue&gt;</td>";
					}
					echo "</tr>\n";
				}
			}
		} catch(Exception $e) {
			echo $e->getMessage();
		}
		echo "</tbody>\n</table>\n";
		echo "<br/><br/><a href=\"index.php\">Back</a>\n";
		echo "<span class=\"bottom\">";
		echo "</div>\n";
		
		// List out all the events
		echo "<div id=\"widest\" class=\"frame\">\n<span class=\"top\"></span>\n";
		echo "<h3>Events</h3>\n";
		try {
			$events = $raid->getEvents();
			echo "<form method=\"post\" action=\"controllers/admin_event.php\" id=\"eventsForm\">\n<table>\n<thead>\n<tr><th>Time</th><th><input type=\"checkbox\" id=\"CheckAllBox\" onClick=\"javascript:checkAllEvents()\"></th><th>Responsible</th><th>Event</th></tr>\n</thead>\n<tbody>\n";
			foreach($events as $event) {
				echo "<tr><td>".$event->getTime()."</td><td><input type=\"checkbox\" value=\"".$event->getId()."\" name=\"events[]\"></td><td>".$event->getResponsible()->getFormattedName()."</td><td>".$event->getOverview()."</td></tr>\n";
			}
			echo "</tbody>\n</table>\n";
			if($raid->getStatus() != 'Finished')
				echo "<input type=\"hidden\" value=\"$_GET[id]\" name=\"id\"><input type=\"submit\" value=\"Delete events\" name=\"deleteevents\">";
			echo "</form>\n";
		} catch(Exception $e) {
			echo $e->getMessage();
		}
		echo "<span class=\"bottom\"></span>\n</div>\n";
		
	} catch(Exception $e) {
		echo $e->getMessage();
	}
}
?>
