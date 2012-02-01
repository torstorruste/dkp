<?php
class event {
	var $eid;
	var $type;
	var $amount;
	var $rid;
	var $pid;
	var $time;
	var $item;
	var $comment;
	var $responsible;

	function __construct($result) {
		$this->eid = $result['eid'];
		$this->type = $result['type'];
		$this->rid = $result['rid'];
		$this->pid = $result['pid'];
		$this->comment = $result['comment'];
		$this->responsible = $result['responsible'];
		$this->timeAdded = $result['time'];
		$this->item = $result['iid'];
		$this->amount = $result['amount'];
	}
	
	function getType() {
		return $this->type;
	}
	
	function getTime() {
		$hms = split(" ",$this->timeAdded);
		$hm = split(":", $hms[1]);
		return "(".$hm[0].":".$hm[1].")";
	}
	
	function getDate() {
		$date = split(" ", $this->timeAdded);
		return $date[0];
	}
	
	function getAmount() {
		return $this->amount;
	}
	
	function getItem() {
		include_once("dao/itemDao.php");
		if($this->item != NULL)
		{
			$itemDao = new ItemDao();
			if($this->comment == 'heroic offspec' || $this->comment == 'heroic')
				return $itemDao->getItem($this->item, true);
			else
				return $itemDao->getItem($this->item, false);
		}
		throw new Exception("The event does not contain an items");
	}
	
	function getOverview() {
		include_once("dao/playerDao.php");		
		$playerDao = new PlayerDao();
		if($this->pid != NULL) {
			try {
				$player = $playerDao->getPlayer($this->pid);
				$player = $player->getFormattedName();
			} catch(Exception $e) {
				throw new Exception("Player not found");
			}
		}
		
		switch($this->type) {
			case 'Add':
				return $player. " was added to the raid";
			case 'Queue':
				return $player. " has joined the queue";
			case 'ReturnQueue':
				return $player. " has rejoined the raid";
			case 'AFK':
				return $player. " went AFK";
			case 'ReturnAFK':
				return $player. " has returned";
			case 'Buy':
				include_once("dao/itemDao.php");
				try {
					$itemDao = new ItemDao();
					if($this->comment == 'heroic offspec' || $this->comment == 'heroic')
						$item = $itemDao->getItem($this->item, true);
					else
						$item = $itemDao->getItem($this->item);
				} catch(Exception $e) {
					include_once("models/item.php");
					$item = new Item();
					$item->setId($this->item);
					$item->setHid($this->item);
					$item->setName("Item");
				}
				if($this->comment == 'offspec')
					return $player. " bought ". $item->getLink()." (offspec)";
				else if($this->comment == 'heroic offspec')
					return $player. " bought ". $item->getHeroicLink(). " (heroic, offspec)";
				else if($this->comment == "heroic")
					return $player. " bought ". $item->getHeroicLink(). " (heroic)";
				return $player. " bought ". $item->getLink();
			case 'Hour':
				return "For playing the last hour, every active player got ".$this->amount. " EP";
			case 'Start':
				return "All active players recieved ".$this->amount." EP startbonus";
			case 'Bonus':
				if($this->pid == NULL)
					$target = 'Everyone';
				else
					$target = $player;
				if($this->amount > 0)
					$type = 'Bonus';
				else
					$type = 'Penalty';
				return $target. " got a ". $type. " of ". $this->amount. " EP with the following comment: \"". $this->comment."\"";
			case 'Finish':
				return "The raid has been finished";
			case 'Signup':
				if(empty($this->comment))
					return $player. " has signed up for the raid";
				return $player. " has signed up for the raid with the following comment: \"".$this->comment."\"";
			case 'Unsign':
				if($this->comment == "")
					return $player. " has withdrawn his signup with no comment";
				else
					return $player. " has withdrawn his signup with the following comment: \"". $this->comment ."\"";
			default:
				return $this->type;
		}
	}
	
	function getId() {
		return $this->eid;
	}
	
	function getResponsible() {
		include_once("dao/playerDao.php");		
		$playerDao = new PlayerDao();
		
		return $playerDao->getPlayer($this->responsible);
	}
	
	function getComment() {
		return $this->comment;
	}
}
?>