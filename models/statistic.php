<?php
class Statistic {

	/*function getClassStatistics() {
		$query = mysql_query("SELECT Class, Count(*) as Amount, Sum(Active) as Active FROM wow_player GROUP BY Class");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['Class']]['Class'] = $result['Class'];
			$array[$result['Class']]['Amount'] = $result['Amount'];
			$array[$result['Class']]['Active'] = $result['Active'];
		}
		$query = mysql_query("SELECT Class, Count(*) as Amount, Sum(Active) as Active FROM wow_player JOIN wow_event USING (Playername) WHERE Type='Buy' GROUP BY Class");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['Class']]['AmountItem'] = $result['Amount'];
			$array[$result['Class']]['ActiveItem'] = $result['Active'];
		}
		$query = mysql_query("SELECT Class, Count(*) as Amount FROM wow_player WHERE Activity > 75 GROUP BY Class");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['Class']]['Raiders'] = $result['Amount'];
		}
		$query = mysql_query("SELECT Class, Count(*) as Amount FROM wow_player JOIN wow_event USING (Playername) WHERE Type='Buy' AND Comment IS NOT NULL GROUP BY Class");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['Class']]['Offspec'] = $result['Amount'];
		}
		$query = mysql_query("SELECT Sum(T6DKP) as T6, Sum(SWDKP) as SW, Class FROM wow_player WHERE Active=1 GROUP BY Class");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['Class']]['T6'] = $result['T6'];
			$array[$result['Class']]['SW'] = $result['SW'];
		}
		$query = mysql_query("SELECT Class, Count(*) as Num FROM wow_player JOIN wow_event USING (Playername) JOIN wow_item USING (ItemID) WHERE wow_item.name LIKE '%Wayward%' AND Comment IS NULL AND Tier <> 'T5' AND Active=1 GROUP BY Playername");
		while($result = mysql_fetch_assoc($query)) {
			if(!isset($array[$result['Class']]['4T6']))
				$array[$result['Class']]['4T6'] = 0;
			if($result['Num'] >=4)
				$array[$result['Class']]['4T6']++;
		}
		return $array;
	}*/

	function getRaidStatistics() {
		$query = mysql_query("SELECT Start FROM wow_raid WHERE Status='Finished' ORDER BY START DESC LIMIT 10");
		while($result = mysql_fetch_assoc($query)) {
			$minId = $result['Start'];
		}
		$query = mysql_query("SELECT rid, Count(*) as Offspec FROM wow_raid JOIN wow_event USING (rid) WHERE Type='Buy' AND COMMENT <> '' AND Start>='$minId' GROUP BY rid");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['rid']]['Offspec'] = $result['Offspec'];

		}
		$query = mysql_query("SELECT Day(Start) as Day, Month(Start) as Month, rid, wow_instance.name as Target, Count(*) as Items FROM wow_raid JOIN wow_event USING (rid) JOIN wow_instance USING (inid) WHERE Type='Buy' AND Start>='$minId' GROUP BY rid");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['rid']]['Items'] = $result['Items'];
		}
		$query = mysql_query("SELECT rid, Day(Start) as Day, Month(Start) as Month, wow_instance.name as Target, Sum(Amount) as DKP FROM wow_raid JOIN wow_event USING (rid) JOIN wow_instance USING (inid) WHERE Start>='$minId' GROUP BY rid");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['rid']]['RaidID'] = $result['rid'];
			$array[$result['rid']]['DKP'] = $result['DKP'];
			$array[$result['rid']]['Month'] = $result['Month'];
			$array[$result['rid']]['Day'] = $result['Day'];
			$array[$result['rid']]['Target'] = $result['Target'];
		}
		return $array;
	}

	function getTokenStatistics() {
		$query = mysql_query("SELECT name, class, Count(*) as num FROM wow_player JOIN wow_event USING (pid) JOIN wow_item USING (iid) WHERE wow_item.name LIKE '% of the Wayward %' AND comment = '' AND Active=1 GROUP BY iid, class");
		if(mysql_num_rows($query)==0)
			throw new Exception("No tokens awarded");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['class']]['Class'] = $result['class'];
			$array[$result['class']][$result['name']] = $result['num'];
		}

		if(isset($array))
			return $array;
	}

	function getPlayerStatistics() {
		global $path;
		$query = mysql_query("SELECT pid, playername as Playername, wow_item.name as Name, class as Class FROM wow_player JOIN wow_event USING (pid) JOIN wow_item USING (iid) WHERE wow_item.name LIKE '% of the Wayward %' AND Comment = '' AND Active=1 ORDER BY class, playername");
		if(mysql_num_rows($query)==0)
			throw new Exception("No tokens awarded");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['Playername']]['pid'] = $result['pid'];
			$array[$result['Playername']]['Playername'] = $result['Playername'];
			$array[$result['Playername']][$result['Name']] = 1;
			$array[$result['Playername']]['Class'] = $result['Class'];
		}
		return $array;
	}

	function getInstanceStatistics() {
		$query = mysql_query("select count(*) as sum, pid, class, playername, name from wow_event join wow_raid using (rid) join wow_instance using (inid) join wow_player using (pid) where type='Add' AND active=1 group by inid, pid;");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['playername']]['Instance'][$result['name']]['times'] = $result['sum'];
			$array[$result['playername']]['Playername'] = $result['playername'];
			$array[$result['playername']]['Class'] = $result['class'];
			$array[$result['playername']]['pid'] = $result['pid'];
		}

		$query = mysql_query("select count(*) as sum, playername, name from wow_event join wow_raid using (rid) join wow_instance using (inid) join wow_player using (pid) where type='AFK' AND active=1 group by inid, pid;");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['playername']]['Instance'][$result['name']]['AFK'] = $result['sum'];
		}

		$query = mysql_query("select count(*) as sum, playername, name from wow_event join wow_raid using (rid) join wow_instance using (inid) join wow_player using (pid) where type='ReturnAFK' AND active=1 group by inid, pid;");
		while($result = mysql_fetch_assoc($query)) {
			$array[$result['playername']]['Instance'][$result['name']]['ReturnAFK'] = $result['sum'];
		}
		return $array;
	}
	
	function getActivityStatistics() {
		$activityQuery = mysql_query("SELECT * FROM wow_player JOIN wow_stats USING (pid) JOIN wow_instance USING (inid) WHERE type='activity' ORDER BY pid ASC, inid ASC");
		$queueQuery = mysql_query("SELECT * FROM wow_player JOIN wow_stats USING (pid) JOIN wow_instance USING (inid) WHERE type='queue' ORDER BY pid ASC, inid ASC");

		$db = new database();

		$instances = $db->getRecentInstances();
		$players = $db->getRaiders();

		foreach($instances as $instance)
		{
			foreach($players as $player)
			{
				$array[$player->getName()]['instance'][$instance->getName()]['active'] = 0;
				$array[$player->getName()]['instance'][$instance->getName()]['queue'] = 0;
				$array[$player->getName()]['class'] = $player->getClass();
			}
		}
		
		while($result = mysql_fetch_assoc($activityQuery))
		{
			$array[$result['playername']]['instance'][$result['name']]['active'] = $result['value'];
		}
		while($result = mysql_fetch_assoc($queueQuery))
		{
			$array[$result['playername']]['instance'][$result['name']]['queue'] = $result['value'];
		}
		return $array;
	}
}
