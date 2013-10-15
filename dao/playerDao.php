<?php
class PlayerDao extends GenericDao{

	function addPlayer($player) {
		mysql_query("INSERT INTO $prefix_player (playername, class, role, username, password, rights, active) VALUES ('".$player->getName()."', '".$player->getClass()."', '".$player->getRole()."', '".$player->getName()."', sha1('".$player->getName()."'), 'Raider', true)");
		if(mysql_error()) {
			throw new Exception("Could not add the player");
		}
	}
	
	function editPlayer($player) {
		mysql_query("UPDATE $prefix_player SET playername='".$player->getName()."', username='".$player->getUsername()."', class='".$player->getClass()."', rights='".$player->getRights()."', role='".$player->getRole()."', active=".($player->isActive()?1:0)." WHERE pid=".$player->getID());
		if(mysql_error()) {
			throw new Exception("Could not edit the player");
		}
	}
	
	function savePlayer($player) {
		if(!is_a($player, 'Player'))
			throw new Exception("Cannot save a non-player object");
		mysql_query("UPDATE $prefix_player SET playername='".$player->getName()."', username='".$player->getUsername()."', class='".$player->getClass()."', rights='".$player->getRights()."', role='".$player->getRole()."' active=".$player->isActive()." WHERE pid=".$player->getID());
	}

	// Also save QP	
	function saveEPGP($player) {
		if(!is_a($player, 'Player'))
			throw new Exception("Cannot save the EPGP of a non-player object");
		mysql_query("UPDATE $prefix_player SET ep=".$player->getEP().", gp=".$player->getGP().", qp=".$player->getQP()." WHERE pid=".$player->getId());
	}
	
	function removePlayer($pid) {
		mysql_query("DELETE FROM $prefix_player WHERE pid=$pid");
		if(mysql_error()) {
			throw new Exception("Could not delete the player");
		}
	}
	
	function getRaiders($sort = 'class') {
		if($sort=='class')
			return $this->getUsers("WHERE active=1 ORDER BY class, playername ASC");
		else
			return $this->getUsers("WHERE active=1 ORDER BY playername ASC");
	}

	function getUsers($where = " ORDER BY playername ASC") {
		$query = mysql_query("SELECT * FROM $prefix_player $where");
		if(mysql_error())
			throw new Exception(mysql_error());
		if(mysql_num_rows($query)==0)
			throw new Exception("No players found");

		if(is_file("models/player.php")) {
			include_once("models/player.php");
		} else if (is_file("../models/player.php")) {
			include_once("../models/player.php");
		}
		while($result = mysql_fetch_assoc($query)) {
				$raiders[] = new Player($result);
		}

		return $raiders;
	}

	function getPlayer($pid) {
		$players = $this->getUsers("WHERE pid=$pid");
		return $players[0];
	}

	function getPlayersByClass() {
		return $this->getUsers("WHERE Active=1 ORDER BY class, playername ASC");
	}
	
	function changePassword($oldpass, $newpass) {
		$query = mysql_query("SELECT * FROM $prefix_player WHERE SHA1(CONCAT(username, SHA1(password))) = '$_SESSION[dkp]' AND password=SHA1('$oldpass')");
		if(mysql_num_rows($query)!= 1) {
			throw new Exception("Old password did not match");
		}
		mysql_query("UPDATE $prefix_player SET password=SHA1('$newpass') WHERE SHA1(CONCAT(username, SHA1(password))) = '$_SESSION[dkp]'");
		if(mysql_error()) {
			throw new Exception("Unable to update the password");
		}

		// Update the session variables
		$result = mysql_fetch_assoc($query);
		$_SESSION['dkp'] = sha1($result['playername'].sha1($newpass));
		if(isset($_COOKIE['dkp'])) {
			setcookie("dkp", sha1($result['playername'].sha1($newpass)), time()+604800, "/");
		}
	}
	
	function getItemEvents($player, $order="name") {	
		if(!is_a($player, 'Player'))
			throw new Exception("Not a player object");
		$query = mysql_query("SELECT * FROM wow_item,  $prefix_event WHERE (wow_item.iid=$prefix_event.iid or wow_item.hid=$prefix_event.iid) and pid=".$player->getID()." ORDER BY $order");
		if(mysql_num_rows($query)==0) {
			throw new Exception("No items found");
		}
		if(is_file("models/item.php"))
			include_once("models/event.php");

		else if(is_file("../models/item.php"))
			include_once("../models/event.php");
			
		while($result = mysql_fetch_assoc($query)) {
			$items[] = new Event($result);
		}
		return $items;
	}
	
	function updateActivity($player, $activity) {		
		if(!is_a($player, 'Player'))
			throw new Exception("Not a player object");
		mysql_query("UPDATE $prefix_player SET activity=$activity WHERE pid=".$player->getID());
		if(mysql_error()) {
			throw new Exception("Could not update the activity");
		}
	}
	
	function updateStats($player, $instance, $activity, $queue) {
		if(!is_a($player, 'Player'))
			throw new Exception("Not a player object");
		mysql_query("INSERT INTO $prefix_stats (pid, inid, type, value) VALUES (".$player->getID().", ".$instance.", 'activity', ".$activity.")");
		mysql_query("INSERT INTO $prefix_stats (pid, inid, type, value) VALUES (".$player->getId().", ".$instance.", 'queue', ".$queue.")");
		if(mysql_error())
			throw new Exception("Could not update the stats");
	}
	
	function getRecentRaids($player) {
		if(!is_a($player, 'Player'))
			throw new Exception("Not a player object");
		$query = mysql_query("SELECT * FROM $prefix_raid LEFT JOIN wow_instance USING (inid) JOIN $prefix_event USING (rid) WHERE status='Finished' AND Type='Add' AND pid=".$player->getId()." ORDER BY start DESC LIMIT 10");
		if(mysql_num_rows($query)==0) 
			throw new Exception("No raids found");
		
		include_once("models/raid.php");
		while($result = mysql_fetch_assoc($query)) {
			$raids[] = new Raid($result);
		}
		return $raids;
	}
}