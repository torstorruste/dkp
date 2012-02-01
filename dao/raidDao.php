<?php
class RaidDao extends GenericDao{
	
	function addRaid($instance, $start, $status) {
		// Find who is logged in
		$query = mysql_query("SELECT * FROM wow_player WHERE SHA1(CONCAT(username, SHA1(password))) = '$_SESSION[dkp]'");
		//$this->verifyIdentity();
		// TODO: Use verifyIdentity instead
		if(mysql_num_rows($query)==1) {
			$result = mysql_fetch_assoc($query);
			$leader = $result['pid'];

			// If the raid does not have any instance set -- set the instance to 0
			if($instance=="")
				$instance = 0;

			if($start != 'now()')
				$start = "'".$start."'";

			mysql_query("INSERT INTO wow_raid (start, inid, status, leader) VALUES ($start, $instance, '$status', $leader)");
			
			if(mysql_error()) {
				throw new Exception("Could not add the raid");
			}
			$query = mysql_query("SELECT MAX(rid) rid FROM wow_raid");
			$result = mysql_fetch_assoc($query);
			return $result['rid'];
		} else {
			throw new Exception("Could not authenticate you");
		}
	}

	function deleteRaid($rid) {
		$query = mysql_query("SELECT * FROM wow_event WHERE rid=$rid");
		if(mysql_num_rows($query)>0)
			throw new Exception("Cannot delete a raid with attached events");

		mysql_query("DELETE FROM wow_raid WHERE rid=$rid");
		if(mysql_error())
			throw new Exception("Could not delete the raid");
	}
	
	function getPlayersInRaid($raid, $sort = 'class') {
		// If the raid is "planned", check the signups
		//$raid = $this->getRaid($id);
		
		if(is_file("dao/playerDao.php")) include_once("dao/playerDao.php");
		else include_once("../dao/playerDao.php");
		$playerDao = new PlayerDao();
		
		if($raid->getStatus()=='Planned')
			return $playerDao->getUsers("JOIN wow_event AS e USING (pid) WHERE rid=".$raid->getId()." AND type = 'Signup' AND time >= (SELECT MAX(time) FROM wow_event WHERE rid=e.rid and pid=e.pid AND (type='unsign' OR type='signup'));");
		else {
			if($sort=='class')
				return $playerDao->getUsers("JOIN wow_event USING (pid) WHERE type='Add' AND wow_event.rid=".$raid->getId()." ORDER BY class, playername ASC");
			else
				return $playerDao->getUsers("JOIN wow_event USING (pid) WHERE type='Add' AND wow_event.rid=".$raid->getId()." ORDER BY $sort ASC");
		}
	}
	
	function getRaids($status = 'Active', $number = 'all') {
		if($number =='all')
			$limit = "";
		else
			$limit = " LIMIT $number";
		$query = mysql_query("SELECT * FROM wow_raid LEFT JOIN wow_instance USING (inid) WHERE status='$status' ORDER BY start DESC $limit");
		if(mysql_num_rows($query)==0)
			throw new Exception("No raids found");

		include_once("models/raid.php");
		while($result = mysql_fetch_assoc($query)) {
			$raiders[] = new Raid($result);
		}
		return $raiders;
	}
	
	function getRaid($rid) {
		if(empty($rid) || ereg("[^0-9]", $rid))
			throw new Exception('Invalid id');

		$query = mysql_query("SELECT * FROM wow_raid LEFT JOIN wow_instance USING (inid) WHERE rid=$rid");
		if(mysql_num_rows($query)==0)
			throw new Exception("Could not find a raid with that id");

		if(is_file("models/raid.php"))
			include_once("models/raid.php");
		elseif(is_file("../models/raid.php"))
			include_once("../models/raid.php");
		$result = mysql_fetch_assoc($query);
		$raid = new Raid($result);
		try {
			$raid->setEvents($this->getEvents($raid));
		} catch(Exception $e) {
			$raid->setEvents(array());
		}
		try{
			$raid->setPlayers($this->getPlayersInRaid($raid));
		} catch(Exception $e) {
			$raid->setPlayers(array());
		}
		return $raid;
	}
	
	function getRecentRaids($interval = 14) {
		$query = mysql_query("SELECT * FROM wow_raid LEFT JOIN wow_instance USING (inid) WHERE start> now() - INTERVAL $interval DAY AND status='Finished'");
		if(mysql_num_rows($query)==0)
			throw new Exception("Could not find any raids");

		if(is_file("models/raid.php"))
			include_once("models/raid.php");
		elseif(is_file("../models/raid.php"))
			include_once("../models/raid.php");
		while($result = mysql_fetch_assoc($query))
			$raids[] = new Raid($result);
		return $raids;
	}
	
	function getRecentRaidsToInstance($inid) {
		$query = mysql_query("SELECT * FROM wow_raid LEFT JOIN wow_instance USING (inid) WHERE start> now() - INTERVAL 14 DAY AND status='Finished' AND inid=$inid");
		if(mysql_num_rows($query)==0)
			throw new Exception($query);
//			throw new Exception("Could not find any raids");

		if(is_file("models/raid.php"))
			include_once("models/raid.php");
		elseif(is_file("../models/raid.php"))
			include_once("../models/raid.php");
		while($result = mysql_fetch_assoc($query))
			$raids[] = new Raid($result);
		return $raids;
	}

	function editTarget($instance, $id) {
		// If the target is undetermined:
		if($instance=="")
			$instance = 0;
		mysql_query("UPDATE wow_raid SET inid=$instance WHERE rid=$id");
		if(mysql_error()) {
			throw new Exception("Cannot update the raid");
		}
	}

	function editStart($rid, $start) {
		// If the target is undetermined:
		mysql_query("UPDATE wow_raid SET start='$start' WHERE rid=$rid");
		if(mysql_error()) {
			throw new Exception("Cannot update the raid");
		}
	}

	function startRaid($rid) {
		// Find who is logged in
		$leader = $this->verifyIdentity();
		
		$query = mysql_query("SELECT * FROM wow_raid WHERE rid=$rid AND status='Planned'");
		if(mysql_num_rows($query)==0)
			throw new Exception("Cannot start the same raid twice");

		// Add all the players that signed up to the raid
		$query = mysql_query("SELECT * FROM wow_event AS e WHERE rid=$rid AND type = 'Signup' AND time >= (SELECT MAX(time) FROM wow_event WHERE rid=$rid and pid=e.pid AND (type='unsign' OR type='signup'));");
		while($result = mysql_fetch_assoc($query)) {
			mysql_query("INSERT INTO wow_event (type, rid, pid, time, responsible) VALUES ('Add', $rid, $result[pid], now(), $leader)");
			if(mysql_error())
				throw new Exception("Could not the add player to the raid");
		}

		// Change the status of the raid to 'Active'
		mysql_query("UPDATE wow_raid SET status='Active' WHERE rid=$rid");
		if(mysql_error()) {
			throw new Exception('Could not start the raid');
		}
	}

	function addPlayerToRaid($pid, $rid) {
		// Find who is logged in
		$leader = $this->verifyIdentity();

		// If the raid is planned, "signup the player"
		$query = mysql_query("SELECT status FROM wow_raid WHERE rid=$rid");
		if(mysql_num_rows($query)==0)
			throw new Exception("Raid not found");
		$result = mysql_fetch_assoc($query);

		if($result['status'] == 'Active')
			mysql_query("INSERT INTO wow_event (type, rid, pid, time, responsible) VALUES ('Add', $rid, $pid, now(), $leader)");
		elseif($result['status']=='Planned')
			mysql_query("INSERT INTO wow_event (type, rid, pid, time, responsible) VALUES ('Signup', $rid, $pid, now(), $leader)");
		else
			throw new Exception("Cannot modify a finished raid");

		if(mysql_error()) {
			throw new Exception("Could not add player");
		}
	}

	function deleteEvent($rid, $eid) {
		mysql_query("DELETE FROM wow_event WHERE rid=$rid AND eid=$eid");
		if(mysql_error()) {
			throw new Exception("Could not delete events");
		}
	}

	function changeStatus($rid, $pid, $type) {
		// Find who is logged in
		$responsible = $this->verifyIdentity();
		mysql_query("INSERT INTO wow_event (type, rid, pid, time, responsible) VALUES ('$type', $rid, $pid, now(), $responsible)");
		if(mysql_error()) {
			throw new Exception("Could not add the event");
		}
	}

	function checkStatus($rid, $pid, $type) {
		if($type == 'AFK') {
			$query = mysql_query("SELECT type FROM wow_event WHERE pid=$pid AND rid=$rid AND (type='AFK' OR type='ReturnAfk') ORDER BY TIME DESC LIMIT 1");
			if(mysql_num_rows($query)==0)
				return false;
			$result = mysql_fetch_assoc($query);
			if($result['type']=='AFK')
				return true;
			return false;
		}
		if($type == 'Queue') {
			$query = mysql_query("SELECT type FROM wow_event WHERE pid=$pid AND rid=$rid AND (type='Queue' OR type='ReturnQueue') ORDER BY TIME DESC LIMIT 1");
			if(mysql_num_rows($query)==0)
				return false;
			$result = mysql_fetch_assoc($query);
			if($result['type']=='Queue')
				return true;
			return false;
		}
	}

	function addStartPoint($rid) {
		$query = mysql_query("SELECT * FROM wow_event WHERE rid=$rid AND type='Start'");
		if(mysql_num_rows($query)>0) {
			throw new Exception("Start point already added");
		}
		$responsible = $this->verifyIdentity();
		mysql_query("INSERT INTO wow_event (rid, type, time, amount, responsible) VALUES ($rid, 'Start', now(), 10, $responsible)");
		if(mysql_error()) {
			throw new Exception("Could not add a start point at this time");
		}
	}
	function addHourPoint($rid, $amount) {
		$responsible = $this->verifyIdentity();
		mysql_query("INSERT INTO wow_event (rid, type, time, amount, responsible) VALUES ($rid, 'Hour', now(), $amount, $responsible)");
		if(mysql_error()) {
			throw new Exception("Cannot add hour points now");
		}
	}

	function buyItem($rid, $pid, $iid, $offspec) {
		$responsible = $this->verifyIdentity();
		mysql_query("INSERT INTO wow_event (rid, type, time, pid, iid, comment, responsible) VALUES ($rid, 'Buy', now(), $pid, $iid, '$offspec', $responsible)");
		if(mysql_error()) {
			throw new Exception("INSERT INTO wow_event (rid, type, time, pid, iid, comment, responsible) VALUES ($rid, 'Buy', now(), $pid, $iid, '$offspec', $responsible) ".mysql_error());
//			throw new Exception("Cannot sell items now");
		}
	}

	function addBonus($rid, $pid, $bonus, $comment) {
		$responsible = $this->verifyIdentity();
		mysql_query("INSERT INTO wow_event (rid, type, amount, comment, time, pid, responsible) VALUES ($rid, 'Bonus', $bonus, '$comment', now(), $pid, $responsible)");
		if(mysql_error()) {
			throw new Exception("Could not add the bonus");
		}
	}

	function finishRaid($rid, $decay) {
		// Change the status of the raid
		// Find who is logged in
		$leader = $this->verifyIdentity();

		$raid = $this->getRaid($rid);

		// Update the status of the raid
		mysql_query("UPDATE wow_raid SET decay=$decay, status='Finished' WHERE rid=$rid");

		// Get the decay-factor
		$factor = $this->getDecayFactor($raid);
		
		if(is_file("dao/playerDao.php")) include_once("dao/playerDao.php");
		else include_once("../dao/playerDao.php");
		$playerDao = new PlayerDao();

		// Add the EP and GP to each player
		$players = $playerDao->getUsers();
		foreach($players as $p) {
			$p->addEP($this->getChangedEP($raid,$p->getID()));
			$p->addGP($this->getChangedGP($raid,$p->getID()));

			// Decay
			if($factor<1)
				$p->decay($factor);

			$playerDao->saveEPGP($p);
		}

		// Finish the raid
		mysql_query("INSERT INTO wow_event (type, rid, time, responsible) VALUES ('Finish', $rid, now(), $leader)");
		if(mysql_error()) {
			throw new Exception('Could not end the raid');
		}
	}

	function reOpenRaid($rid) {
		// Change the status of the raid
		// Find who is logged in
		$leader = $this->verifyIdentity();

		$raid = $this->getRaid($rid);

		// Update the status of the raid
		mysql_query("UPDATE wow_raid SET status='Active' WHERE rid=$rid");

		// Get the decay-factor
		$factor = $this->getDecayFactor($raid);

		if(is_file("dao/playerDao.php")) include_once("dao/playerDao.php");
		else include_once("../dao/playerDao.php");
		$playerDao = new PlayerDao();

		// Remove the EP and GP to each player
		$players = $playerDao->getUsers();
		foreach($players as $p) {
			// Remove the decay first, to get the correct amounts
			if($factor<1)
				$p->unDecay($factor);

			// Subtract the amount of ep/gp added for the raid
			$p->addEP(-$this->getChangedEP($raid,$p->getID()));
			$p->addGP(-$this->getChangedGP($raid,$p->getID()));

			$playerDao->saveEPGP($p);
		}

		// Remove the finish-event
		mysql_query("DELETE FROM wow_event WHERE type= 'Finish' and rid='$rid'");

		if(mysql_error()) {
			throw new Exception('Could not end the raid');
		}
	}

	function signUp($rid, $comment) {
		$pid = $this->verifyIdentity();
		if($comment == '')
			$comment = "NULL";
		else
			$comment = "'".$comment."'";
		$raid = $this->getRaid($rid);

		$time = explode(" ", $raid->getStartSQL());
		$now = explode(" ", date("Y-m-d H:i:s"));
		include_once("../dao/settingDao.php");
		$settingDao = new SettingDao();
		try {
			$limit = $settingDao->getSetting('Signup');
		} catch(Exception $e) {
			$limit = 0;
		}

		if($now[0]==$time[0]) {
			$hourR = explode(":", $time[1]);
			$hourN = explode(":", $now[1]);
			if($hourR[0] - $hourN[0] < $limit) {
				throw new Exception("Too late to sign up, signups are frozen $limit hours before raid-start");
			}
		}

		mysql_query("INSERT INTO wow_event (type, rid, pid, time, comment, responsible) VALUES ('Signup', $rid, $pid, now(), $comment, $pid)");
		if(mysql_error()) {
			throw new Exception("Could not sign up now");
		}
	}
	function unSign($rid, $comment) {
		$pid = $this->verifyIdentity();
		if($comment == '')
			$comment = "NULL";
		else
			$comment = "'".$comment."'";

		$raid = $this->getRaid($rid);
		$time = explode(" ", $raid->getStartSQL());
		$now = explode(" ", date("Y-m-d H:i:s"));

		include_once("../dao/settingDao.php");
		$settingDao = new SettingDao();
		try {
			$limit = $settingDao->getSetting('Signup');
		} catch(Exception $e) {
			$limit = 0;
		}

		if($now[0]==$time[0]) {
			$hourR = explode(":", $time[1]);
			$hourN = explode(":", $now[1]);
			if($hourR[0] - $hourN[0] < $limit) {
				throw new Exception("Too late to sign up, signups are frozen $limit hours before raid-start");
			}
		}

		mysql_query("INSERT INTO wow_event (type, rid, pid, time, comment, responsible) VALUES ('Unsign', $rid, $pid, now(), $comment, $pid)");
		if(mysql_error()) {
			throw new Exception("Could not unsign now");
		}
	}
	
	function getEvents($raid) {
		if(!is_a($raid, 'raid'))
			throw new Exception("Not a raid");
		
		if($raid->getStatus() == 'Planned')
			$query = mysql_query("SELECT * FROM wow_event WHERE rid=".$raid->getId()." ORDER BY time ASC");
		elseif(isset($_SESSION['admin']))
			$query = mysql_query("SELECT * FROM wow_event WHERE rid=".$raid->getId()." AND ((type <> 'Signup' AND type <> 'Unsign') OR (comment IS NOT NULL AND comment <> '')) ORDER BY time ASC");
		else 
			$query = mysql_query("SELECT * FROM wow_event WHERE rid=".$raid->getId()." AND type <> 'Signup' AND type <> 'Unsign' AND type <> 'Queue' AND type <> 'ReturnQueue' ORDER BY time ASC");
		if(mysql_num_rows($query)==0)
			throw new Exception("No events found");
		
		if(is_file("models/event.php")) include_once("models/event.php");
		else include_once("../models/event.php");
		
		while($result = mysql_fetch_assoc($query)) {
			$events[] = new Event($result);
		}
		return $events;
	}	
	
	function getChangedEP($raid, $player, $includeBonuses=true) {
		if(!is_a($raid, 'Raid'))
			throw new Exception('Is not a raid');

		// If the player wasn't in the raid - return 0
		try {
			$activePlayers = $this->getPlayersInRaid($raid);
		} catch(Exception $e) {
			return 0;
		}
		$inRaid = FALSE;
		
		foreach($activePlayers as $p) {
			if($p->getID() == $player)
				$inRaid = TRUE;
		}
		if(!$inRaid)
			return 0;
		
		$query = mysql_query("SELECT * FROM wow_event WHERE pid=$player and type='AFK' and rid=".$raid->getId());
	
		$sum = 0;
		// If player hasn't gone afk:
		if(mysql_num_rows($query)==0) {
			// Get all events for the entire raid after the player joined
			$add_query = mysql_query("SELECT time FROM wow_event WHERE type='Add' AND rid=".$raid->getId()." AND pid=$player");
			$add_result = mysql_fetch_assoc($add_query);
			$query = "SELECT sum(amount) as sum FROM wow_event WHERE time>'$add_result[time]' AND pid IS NULL AND rid=".$raid->getId();
			if(!$includeBonuses)
				$query .= " AND type='Hour'";
			$raid_event_query = mysql_query($query);
			$raid_result = mysql_fetch_assoc($raid_event_query);
			$sum = $raid_result['sum'];
			
		} else {
			// If player has gone AFK - Get the times the player went AFK, and check whether he returned
			$timeline_query = mysql_query("SELECT time, type FROM wow_event WHERE pid=$player AND rid='".$raid->getId()."' AND (type='AFK' OR type='ReturnAFK' OR type='Add') ORDER BY time ASC");
			
			while($timeline_result = mysql_fetch_assoc($timeline_query)) {
				$timeline[] = $timeline_result['time'];
			}
			$query = "SELECT sum(amount) AS sum FROM wow_event WHERE rid=".$raid->getId()." AND pid IS NULL AND (";
			// Takes into account everything but eventually the last return
			for($i=0;$i<floor(count($timeline)/2);$i++) {
				if($i>0)
					$query .= " OR ";
				$start = 2*$i;
				$end = 2*$i+1;
				$query .= "(time>'$timeline[$start]' AND time<'$timeline[$end]')";
			}
			if(count($timeline)%2==1) {
				$lastindex = count($timeline)-1;
				$query .= " OR time>'$timeline[$lastindex]')";
			} else
				$query .= ")";
			if(!$includeBonuses)
				$query .= " AND type='Hour'";
			$raid_result = mysql_fetch_assoc(mysql_query($query));

			
			// Fix so we always display something
			$sum = $raid_result['sum'];
		}
		if($sum == NULL)
				$sum = 0;
		
		// Add the bonuses we've received
		if($includeBonuses) {
			$query = mysql_query("SELECT sum(amount) as sum FROM wow_event WHERE rid=".$raid->getId()." AND pid=$player AND TYPE='Bonus'");
			$result = mysql_fetch_assoc($query);
			
			return $sum + $result['sum'];
		}
		else
			return $sum;
	}
	
	function getQueueTime($raid, $player) {
		if(!is_a($raid, 'raid'))
			throw new Exception('Not a raid');
			
		// If the player wasn't in the raid - return 0
		try {
			$activePlayers = $this->getPlayersInRaid($raid);
		} catch(Exception $e) {
			return 0;
		}
		$inRaid = FALSE;
		
		foreach($activePlayers as $p) {
			if($p->getID() == $player)
				$inRaid = TRUE;
		}
		if(!$inRaid)
			return 0;
		$query = mysql_query("SELECT * FROM wow_event WHERE pid=$player and type='Queue' and rid=".$raid->getId());
	
		// If player hasn't been on queue:
		if(mysql_num_rows($query)==0) {
			// Get all events for the entire raid after the player joined
			return 0;
		} else {
			// If player has gone AFK - Get the times the player went AFK, and check whether he returned
			$timeline_query = mysql_query("SELECT time, type FROM wow_event WHERE pid=$player AND rid='".$raid->getId()."' AND (type='Queue' OR type='ReturnQueue' OR type='Add') ORDER BY time ASC");
			
			while($timeline_result = mysql_fetch_assoc($timeline_query)) {
				$timeline[] = $timeline_result['time'];
			}
			$query = "SELECT count(amount)*10 AS sum FROM wow_event WHERE type='Hour' AND rid=".$raid->getId()." AND pid IS NULL AND (";
			// Takes into account everything but eventually the last return
			for($i=0;$i<floor(count($timeline)/2);$i++) {
				if($i>0)
					$query .= " OR ";
				$start = 2*$i;
				$end = 2*$i+1;
				$query .= "(time>'$timeline[$start]' AND time<'$timeline[$end]')";
			}
			if(count($timeline)%2==1) {
				$lastindex = count($timeline)-1;
				$query .= " OR time>'$timeline[$lastindex]')";
			} else
				$query .= ")";
			$raid_result = mysql_fetch_assoc(mysql_query($query));

			
			// Fix so we always display something
			$sum = $raid_result['sum'];
			
			$add_query = mysql_query("SELECT time FROM wow_event WHERE type='Add' AND rid=".$raid->getId()." AND pid=$player");
			$add_result = mysql_fetch_assoc($add_query);
			$query = "SELECT count(amount)*10 as sum FROM wow_event WHERE time>'$add_result[time]' AND pid IS NULL AND rid=".$raid->getId()." AND type='Hour'";
			$raid_event_query = mysql_query($query);
			$raid_result = mysql_fetch_assoc($raid_event_query);
			$sum = $raid_result['sum'] - $sum;
			if($sum == NULL)
				$sum = 0;
			return $sum;
		}
	}
	
	function getChangedGP($raid, $player) {
		if(!is_a($raid, 'Raid'))
			throw new Exception('Not a raid');
		global $itemPrice;
		
		if(is_file('dao/settingDao.php')) {
			include_once('dao/settingDao.php');
			include_once('dao/instanceDao.php');
		}else {
			include_once('../dao/settingDao.php');
			include_once('../dao/instanceDao.php');
		}
		$settingDao = new SettingDao();
		$instanceDao = new InstanceDao();
		
		if(!isset($itemPrice)) {
			$itemPrice = $settingDao->getSetting("Item price");
		}
		
		$query = mysql_query("SELECT count(*) as number, comment FROM wow_event WHERE type='Buy' AND pid=$player AND rid=".$raid->getId()." GROUP BY comment");

		$number=0;
		while($result = mysql_fetch_assoc($query))
		{
			if($result['comment'] == '')
				$number += $result['number'];
			elseif($result['comment']=='heroic')
				$number += 2*$result['number'];
		}
		
		if($raid->getInid() != 0) {
			$instance = $instanceDao->getInstance($raid->getInid());
			$multiplier = $instance->getMultiplier();
		} else {
			$multiplier = 1;
		}
		
		return $number * $itemPrice * $multiplier;
	}
	
	function getTotalEP($raid, $includeStartPoint=true) {
		if(!is_a($raid, 'Raid'))
			throw new Exception('Not a raid');
		if($includeStartPoint) {
			$query = mysql_query("SELECT sum(amount) as sum FROM wow_event WHERE (type='Hour' OR type = 'Start') AND rid=".$raid->getId());
			$result = mysql_fetch_assoc($query);
			return $result['sum'] == NULL ? 0 : $result['sum'];
		}
		else {
			$query = mysql_query("SELECT sum(amount) as sum FROM wow_event WHERE type='Hour' AND rid=".$raid->getId());
			$result = mysql_fetch_assoc($query);
			return $result['sum'] == NULL ? 0 : $result['sum'];
		}		
	}
	
	function isSigned($raid) {
		if(!is_a($raid, 'Raid'))
			throw new Exception($raid->get_class().' is not a raid');
		$query = mysql_query("SELECT * FROM wow_event AS e JOIN wow_player USING (pid) WHERE rid=".$raid->GetId()." AND type='Signup' AND SHA1(CONCAT(username, sha1(password)))='$_SESSION[dkp]' AND time >= (SELECT MAX(time) FROM wow_event WHERE rid=e.rid and pid=e.pid AND (type='unsign' OR type='signup'))");
		if(mysql_num_rows($query)==1)
			return true;
		else
			return false;
	}
	
	function duplicate($raid) {
		if(!is_a($raid, 'Raid'))
			throw new Exception('Not a raid');

		$players = $this->getPlayersInRaid($raid);
		$this->addRaid($raid->getInid(), "now()", "active");

		$query = mysql_query("SELECT * FROM wow_raid LEFT JOIN wow_instance USING(inid) ORDER BY rid DESC LIMIT 1");
		$newRaid = new Raid(mysql_fetch_assoc($query));
		
		foreach($players as $player) {
			if(!$this->checkStatus($raid->getId(), $player->getID(), "AFK")) {
				$this->addPlayerToRaid($player->getID(), $newRaid->getId());
				if($this->checkStatus($raid->getId(), $player->getID(), "Queue"))
					$this->changeStatus($newRaid->getId(), $player->getID(), "Queue");
			}
		}
		return $newRaid->getId();
	}
	
	function getDecayFactor($raid) {
		$query = mysql_query("SELECT count(*) as num from wow_raid where start >= '".$raid->getStartSQL()."' and decay=1");
		$result = mysql_fetch_assoc($query);
		
		$numberOfDecayedRaids = $result['num'];
		
		if(is_file("dao/settingDao.php"))
			include_once("dao/settingDao.php");
		else if(is_file("../dao/settingDao.php"))
			include_once("../dao/settingDao.php");
		
		$settingDao = new SettingDao();
		
		$decay = $settingDao->getSetting("Decay");
		// Get the number saying how much a DKP awarded during the raid is worth afterwards
		$decayRate = pow(1 - ($decay/100), $numberOfDecayedRaids);
		
		return $decayRate;
	}
}
