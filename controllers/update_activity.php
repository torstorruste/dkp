<?php
session_start();
if(isset($_SESSION['admin'])) {
	try {
		include_once("../dao/genericDao.php");
		include_once("../dao/raidDao.php");
		include_once("../dao/playerDao.php");
		include_once("../dao/instanceDao.php");
		$raidDao = new RaidDao();
		$playerDao = new PlayerDao();
		$instanceDao = new InstanceDao();

		$players = $playerDao->getRaiders();
		// Get the available points
		try {
			$raids = $raidDao->getRecentRaids();
			foreach($raids as $raid) {
				$totPoints[$raid->getStartDay()][$raid->getId()] = $raidDao->getTotalEP($raid);
			}

			$string = "";
			// Get the points earned by every player
			foreach($players as $player) {
				foreach($raids as $raid) 
					$playerPoints[$player->getID()][$raid->getStartDay()][$raid->getId()] = $raidDao->getChangedEP($raid,$player->getID());
			}
			
			foreach($players as $player) {
				$activity = 0;
				$numDays = 0;
				
				foreach(array_keys($playerPoints[$player->getID()]) as $day) {
					// Adds the activity for each day
					$totalPlayerForDay = 0;
					$totalMaxForDay = 0;
					foreach(array_keys($playerPoints[$player->getID()][$day]) as $rid) {
						// If points were awarded during the raid
						if($totPoints[$day][$rid] != 0) {
							$totalMaxForDay += $totPoints[$day][$rid];
							$totalPlayerForDay += $playerPoints[$player->getID()][$day][$rid];
						}
					}
					// If points were awarded, add the points to the activity.
					if($totalMaxForDay != 0) {
						$activity += min($totalPlayerForDay / $totalMaxForDay, 1);
						$numDays++;
					}
				}
				$activity = floor($activity * 100 / $numDays);
				$playerDao->updateActivity($player,$activity);
			}
			
			// Clear the stats already here:
			$raidDao->clearStats();
	
			// Get the available points
			$instances = $instanceDao->getRecentInstances();
			foreach($instances as $i)
			{
				$raids = $raidDao->getRecentRaidsToInstance($i->getId());
				foreach($raids as $raid) {
					$totPoints[$raid->getId()] = $raidDao->getTotalEP($raid);
				}
				$players = $playerDao->getRaiders();
				
				foreach($players as $player) {
					$playerActivity = 0;
					$playerQueue = 0;
					$totalTime = 0;
					
					foreach($raids as $raid) {
						$active = $raidDao->getChangedEP($raid,$player->getID(), false);
						$queue = $raidDao->getQueueTime($raid,$player->getID());
						$playerActivity += $active;
						$playerQueue += $queue;
						$totalTime += $raidDao->getTotalEP($raid,false);
					}
					if($totalTime > 0)
					{
						$activity = floor($playerActivity * 100 / $totalTime);
						$queue = floor($playerQueue * 100 / $totalTime);
					}
					if($activity > 0) {
						$playerDao->updateStats($player,$raid->getInid(), $activity, $queue);
					}
				}
			}
		} catch(Exception $e) 
		{
			// Will only happen if we do not find any raids
		}
		
		
		// Update the queuepoints
		try{
			$raids = $raidDao->getRecentRaids(30);
			foreach($players as $player) {
				$playerQueue = 0;
				$totalTime = 0;
				
				foreach($raids as $raid) {
					$playerQueue += $raidDao->getQueueTime($raid,$player->getID());
				}
				$player->setQP($playerQueue);
	
				$playerDao->saveEPGP($player);
			}
		} catch(Exception $e)
		{
			// Will only happen if we do not find any raids
		}
		
		
		$_SESSION['notice'] = "Activity updated";
	} catch(Exception $e) {
		echo $e->getMessage();
	}
//	header("Location: ../index.php");
} else {
	$_SESSION['error'] = "You are not authorized to use this feature";
//	header("Location: ../index.php");
}