<?php
class GenericDao {

	var $link;
	var $prefix = 'alt';

	function __construct() {
			if(is_file("dao/dbinfo.php"))
				include_once("dao/dbinfo.php");
			elseif(is_file("../dao/dbinfo.php"))
				include_once("../dao/dbinfo.php");
	}

	/* Functions that handles login and user management */

	function login($name, $password, $remember) {
		$query = mysql_query("SELECT * FROM ".$this->prefix."_player WHERE username='$name' AND password='".sha1($password)."' and active=1");
		if(mysql_num_rows($query)==0) {
			return false;
		} else {
			if(is_file("models/player.php"))
				include_once("models/player.php");
			else
				include_once("../models/player.php");
			
			$player = new Player(mysql_fetch_assoc($query));
			$_SESSION['dkp'] = $player->getHash();
			
			if($remember=="true") {
				setcookie("dkp", $player->getHash(), time()+604800, "/");
			}

			// Set administrative privileges
			if($player->getRights() == 'officer')
				$_SESSION['admin'] = true;
			else
				unset($_SESSION['admin']);
			return true;
		}
	}

	function checkSession($session) {
		$query = mysql_query("SELECT rights FROM ".$this->prefix."_player WHERE SHA1(CONCAT(username, sha1(password)))='$session'");
		if(mysql_num_rows($query)==0)
			return false;
		$result = mysql_fetch_assoc($query);
		if($result['rights']=='officer') {
			$_SESSION['admin'] = true;
			return true;
		}
		return true;
	}
	
	function clearStats() {
		mysql_query("DELETE FROM ".$this->prefix."_stats");
		if(mysql_error())
			throw new Exception("Could not connect to the database");
	}
	
	function verifyIdentity() {
		// Find who is logged in
		$query = mysql_query("SELECT * FROM ".$this->prefix."_player WHERE SHA1(CONCAT(username, sha1(password))) = '$_SESSION[dkp]'");
		if(mysql_num_rows($query)==1) {
			$result = mysql_fetch_assoc($query);
			return $result['pid'];
		} else {
			throw new Exception("Cannot authenticate");
		}
	}
}
