<?php
session_start();
if(isset($_SESSION['admin'])) {
	try {
		include_once("../dao/instanceDao.php");
		include_once("../dao/playerDao.php");
		include_once("../dao/raidDao.php");
		$instanceDao = new InstanceDao();
		$playerDao = new PlayerDao();
		$raidDao = new RaidDao();

		// Clear the stats already here:
		$instanceDao->clearStats();

		// Get the available points
		$instances = $instanceDao->getRecentInstances();
		foreach($instances as $i)
		{
			$raids = $raidDao->getRecentRaidsToInstance($i->getId());
			foreach($raids as $raid) {
				$totPoints[$raid->getId()] = $raid->getTotalEP();
			}
			$players = $playerDao->getRaiders();
			
			foreach($players as $player) {
				$playerActivity = 0;
				$playerQueue = 0;
				$totalTime = 0;
				
				foreach($raids as $raid) {
					$playerActivity += $raid->getChangedEP($player->getID(), false);
					$playerQueue += $raid->getQueueTime($player->getID());
					$totalTime += $raid->getTotalEP(false);
				}

				$activity = floor($playerActivity * 100 / $totalTime);
				$queue = floor($playerQueue * 100 / $totalTime);
				if($activity > 0) {
					$player->updateStats($raid->getInid(), $activity, 100-$queue);
				}
			}
		}
		$_SESSION['notice'] = "Stats updated";
	} catch(Exception $e) {
		echo $e->getMessage();
	}
	header("Location: ../index.php");
} else {
	$_SESSION['error'] = "You are not authorized to use this feature";
	header("Location: ../index.php");
}