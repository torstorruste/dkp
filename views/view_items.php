<?php
if(isset($_GET['id'])) {
	if(!ereg("[^0-9]", $_GET['id'])) {
		try {
			include_once("dao/genericDao.php");
			include_once("dao/playerDao.php");
			$playerDao = new PlayerDao();
			
			$player = $playerDao->getPlayer($_GET['id']);
			echo "<div id=\"wide\" class=\"frame\">\n<span class=\"top\"></span>\n";
			echo "<h3>".$player->getFormattedName()."</h3>\n";
			
			try { 
				$events = $playerDao->getItemEvents($player, "time desc");
				echo "<table class=\"sortable\">\n<thead>\n<tr><th>Date</th><th>Item</th></tr>\n</thead>\n";
				echo "<tbody>\n";
				foreach($events as $event) {
					$item = $event->getItem();
					echo "<tr><td>".$event->getDate()."</td><td>".$item->getLink();
					if($event->getComment() == 'offspec')
						echo " (offspec)";
					else if($event->getComment() == 'heroic offspec')
						echo " (heroic, offspec)";
					else if($event->getComment() == "heroic")
						echo " (heroic)";
					echo "</td></tr>\n";
				}
				echo "</tbody>\n</table>\n";
			} catch(Exception $e) {
				echo $e->getMessage();
			}
			echo "<br/><br/><a href=\"index.php\">Back</a>\n<span class=\"bottom\"></span>\n</div>\n";
			if(isset($_SESSION['admin'])) {
				try {
					$raids = $playerDao->getRecentRaids($player);
					echo "<div id=\"middle\" class=\"frame\">\n<span class=\"top\"></span>\n";
					foreach($raids as $raid) {
						echo "<a href=\"index.php?page=show_raid&id=".$raid->getId()."\">".$raid->getTarget()."</a> - ".$raid->getStart()."<br/>\n";
					}
					echo "<span class=\"bottom\"></span></div>\n";
				} catch(Exception $e) {
					echo $e->getMessage();
				}
			}
		} catch(Exception $e) {
			echo $e->getMessage();
		}
	} else {
		echo "Invalid ID";
	}

} else {
	if(isset($_SESSION['admin'])) {
		echo "<div id=\"widest\" class=\"frame\">\n<span class=\"top\"></span>\n";
		echo '<a href="index.php">Back</a>';
		echo "<form method=\"post\"><input type=\"text\" name=\"Search\"";
		if(isset($_POST['SearchButton']) && isset($_POST['Search']))
			echo " value=\"".mysql_escape_string($_POST['Search'])."\"";
		echo "><input type=\"submit\" name=\"SearchButton\" value=\"Search\"></form>\n";
		try {
			include_once("dao/genericDao.php");
			include_once("dao/itemDao.php");
			$db = new ItemDao();
			$array = $db->getItemsAndPlayers(isset($_POST['Search'])?$_POST['Search']:"");
			foreach($array as $item) {
				echo $item['item']->getLink(). " - ";
				$i = 0;
				foreach($item['player'] as $player) {
					if($i>0)
						echo ", ";
					echo $player->getFormattedName();
					$i++;
				}
				echo "<br>\n";
			}
			
		} catch(Exception $e) {
			echo $e->getMessage();
		}
		echo '<a href="index.php">Back</a>';
		echo "<span class=\"bottom\"></span>\n</div>\n";
	} else {
		echo "No player specified";
	}
}