<?php
include("check_admin.php");
include("models/statistic.php");
$statObj = new Statistic();
?>
<div id="wide" class="frame">
<span class="top"></span>
<a href="index.php">Back</a>
<?php
echo "<h3>Recent Raids</h3>\n";
echo "<table class=\"sortable\">\n";
echo "<thead>\n<tr><th>Date</th><th>Target</th><th>Items<br/>(Regular)</th><th>Items<br/>(Offspec)</th></tr>\n</thead>\n<tbody>";
$raidStat = $statObj->getRaidStatistics();
foreach($raidStat as $raid) {
	$offspec = isset($raid['Offspec']) ? $raid['Offspec'] : 0;
	$dkp = isset($raid['DKP']) ? $raid['DKP'] : 0;
	if(isset($raid['Items']) && isset($raid['Offspec']))
		$items = $raid['Items'] - $raid['Offspec'];
	elseif(isset($raid['Items']))
		$items = $raid['Items'];
	else
		$items = 0;
	$month = $raid['Month'];
	$day = $raid['Day'];

	if($month<10)
		$month = "0".$month;
	if($day < 10)
		$day = "0".$day;
	
	$array[] = "<tr><td>".$month."-".$day."</td><td><a href=\"?page=show_raid&id=".$raid['RaidID']."\">".$raid['Target']."</a></td><td>$items</td><td>$offspec</td></tr>\n";
}
// Sorting the raids before outputting
rsort($array);
foreach($array as $ting)
	echo $ting;
echo "</tbody></table>";

// Echo the class-based token-stats
echo "<h3>Tokens distributed on class</h3>\n";
echo "<table>\n";
try {
	$classStats = $statObj->getTokenStatistics();
	echo "<thead>\n<tr><th>Class</th><th>Gloves</th><th>Helm</th><th>Pauldrons</th><th>Leggings</th><th>Chestguard</th></tr>\n</thead>\n<tbody>\n";
	foreach($classStats as $class) {
		$keys = array_keys($class);
		$gloves = 0;
		$helm = 0;
		$pauldrons = 0;
		$leggings = 0;
		$chestguard = 0;
		
		foreach($keys as $key) {
			$word = split(" ", $key);
			if($word[0] == 'Gauntlets')
				$gloves = $class[$key];
			if($word[0] == 'Crown')
				$helm = $class[$key];
			if($word[0] == 'Mantle')
				$pauldrons = $class[$key];
			if($word[0] == 'Legplates')
				$leggings = $class[$key];
			if($word[0] == 'Breastplate')
				$chestguard = $class[$key];
		}
			
		echo "<tr><td class=\"$class[Class]\">$class[Class]</td><td>$gloves</td><td>$helm</td><td>$pauldrons</td><td>$leggings</td><td>$chestguard</td></tr>\n";
	}
} catch(Exception $e) {
	echo $e->getMessage();
}
echo "</tbody>\n</table>\n";
echo "<h3>Tokens distributed on players</h3>\n";
// Echo the player-based token-stats
echo "<table>\n";

try {
	$playerstats = $statObj->getPlayerStatistics();
	echo "<thead>\n<tr><th>Class</th><th>Gloves</th><th>Helm</th><th>Pauldrons</th><th>Leggings</th><th>Chestguard</th></tr>\n</thead>\n<tbody>\n";
	foreach($playerstats as $player) {
		$keys = array_keys($player);
		$gloves = " ";
		$helm = " ";
		$pauldrons = " ";
		$leggings = " ";
		$chestguard = " ";
		
		foreach($keys as $key) {
			$word = split(" ", $key);
			if($word[0] == 'Gauntlets')
				$gloves = "X";
			if($word[0] == 'Crown')
				$helm = "X";
			if($word[0] == 'Mantle')
				$pauldrons = "X";
			if($word[0] == 'Legplates')
				$leggings = "X";
			if($word[0] == 'Breastplate')
				$chestguard = "X";
		}
			
		echo "<tr><td><a href=\"index.php?page=view_items&id=$player[pid]\" class=\"$player[Class]\">$player[Playername]</a></td><td>$gloves</td><td>$helm</td><td>$pauldrons</td><td>$leggings</td><td>$chestguard</td></tr>\n";
	}
	echo "</tbody>\n</table>\n";
} catch(Exception $e) {
	echo $e->getMessage();
}

echo "<h3>Raid attendance</h3>\n";
include_once("controllers/database.php");
$db = new Database();
$instances = $db->getInstances();
echo "<table class=\"sortable\">\n<thead>\n<tr><th>Player</th>";
foreach($instances as $instance) {
	echo "<th>".$instance->getName()."</th>";
}
echo "</tr>\n</thead>\n<tbody>\n";
foreach($statObj->getInstanceStatistics() as $player) {
	echo "<tr><td><a href=\"index.php?page=view_items&id=$player[pid]\" class=\"$player[Class]\">$player[Playername]</a></td>";
	foreach($instances as $instance) {
		echo "<td>";
			if(isset($player['Instance'][$instance->getName()]['times'])) {
				echo $player['Instance'][$instance->getName()]['times'];
				if(isset($player['Instance'][$instance->getName()]['AFK'])) {
					echo " (".$player['Instance'][$instance->getName()]['AFK'];
					if(isset($player['Instance'][$instance->getName()]['ReturnAFK']))
						echo "/".$player['Instance'][$instance->getName()]['ReturnAFK'];
					echo ")";
				}
			} else
				echo "0";
		echo "</td>";
	}
	echo "</tr>\n";
}
echo "</tbody>\n</table>\n";

echo "<h3>Activity/Queue</h3>";
echo "<table>\n<thead>\n";
$stat = $statObj->getActivityStatistics();
foreach($stat as $obj) {
	echo "<tr><th>Player</th>";
	foreach(array_keys($obj['instance']) as $instance) {
		echo "<th>".$instance."</th>";
	}
	echo "</tr>\n";
	break;
}
echo "</thead>\n<tbody>\n";
foreach(array_keys($stat) as $name) {
	echo "<tr><td class=\"".$stat[$name]['class']."\">".$name."</td>";
	foreach($stat[$name]['instance'] as $instance) {
		echo "<td>".$instance['active']."% ";
		if($instance['queue']>0)
			echo "(".$instance['queue']." %)";
		echo "</td>";
	}
	echo "</tr>\n";
}
echo "</tbody></table>";
?>
<a href="index.php">Back</a>
<span class="bottom"></span>
</div>