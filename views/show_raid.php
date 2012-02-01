<div id="left" class="frame">
<span class="top"></span>
<?php
include_once("dao/genericDao.php");
include_once("dao/raidDao.php");
$raidDao = new RaidDao();
if(ereg("^[0-9]*$", $_GET['id'])) {
	try {
		$raid = $raidDao->getRaid($_GET['id']);
		if(isset($_SESSION['admin'])) {
			echo "<a href=\"index.php?page=admin_event&id=$_GET[id]\">Administrate</a>";
		}
		echo "<h3>".$raid->getStatus()." - ".$raid->getTarget()."<br/>".$raid->getStart()."</h3>";
		
		// List out the players
		try {
			$playersInRaid = $raid->getPlayers();
			echo "Total players: ".count($playersInRaid);
			echo "<table class=\"sortable\">\n<thead>\n<tr><th>Name:</th><th>EP:</th><th>GP:</th><th>Priority</th></tr>\n</thead>\n<tbody>\n";
			foreach($playersInRaid as $raider) {
				// Update the EP and GP according to what the player has earned this raid
				if($raid->getStatus()=='Active') {
					$raider->addEP($raidDao->getChangedEP($raid, $raider->getID()));
					$raider->addGP($raidDao->getChangedGP($raid, $raider->getID()));
				}
			
				echo "<tr><td>".$raider->getFormattedName()."</td>";
				echo "<td>".$raider->getEP()." (".$raidDao->getChangedEP($raid, $raider->getID()).")</td>";
				echo "<td>".$raider->getGP()." (".$raidDao->getChangedGP($raid, $raider->getID()).")</td>";
				echo "<td>".$raider->getPriority()."</td>";
				echo "</tr>\n";
			}
			echo "</tbody>\n</table>\n";
			
		} catch(Exception $e) {
			echo $e->getMessage();
		}
		if($raid->getStatus()=='Planned' && isset($_SESSION['dkp'])) {
			echo "<br/><br/>\n<form method=\"post\" action=\"controllers/signup.php\"><input type=\"text\" name=\"comment\" size=\"30\" maxlength=\"100\">\n";
			echo "<input type=\"hidden\" value=\"$_GET[id]\" name=\"id\">";
			if($raidDao->isSigned($raid)) {
				echo "<input type=\"submit\" value=\"Unsign\" name=\"unsign\">\n</form>";
			} else {
				echo "<input type=\"submit\" value=\"Signup\" name=\"signup\">\n</form>";
			}
			echo mysql_error();
		}
		echo "<br/><br/><a href=\"index.php\">Back</a>\n";
		echo "<span class=\"bottom\"></span>\n</div>";
		if($raid->getStatus()!='Planned') {
			// List out the events
			echo "<div id=\"widest\" class=\"frame\">\n<span class=\"top\"></span>\n";
			try {
				$events = $raid->getEvents();
				echo "<table>\n<tbody>\n";
				foreach($events as $event) {
					echo "<tr><td>".$event->getOverview()."</td></tr>\n";
				}
				echo "</tbody>\n</table>\n";
	
			} catch(Exception $e) {
				echo $e->getMessage();
			}
			echo '<span class="bottom"></span>';
			echo "</div>";
		}
	} catch(Exception $e) {
		echo $e->getMessage();
	}
} else {
	echo "Invalid ID";
	echo '<span class="bottom"></span>';
	echo "</div>";
}
?>