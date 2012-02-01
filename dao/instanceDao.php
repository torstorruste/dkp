<?php
class InstanceDao extends GenericDao{
	function addInstance($name, $multiplier) {
		mysql_query("INSERT INTO wow_instance (name, multiplier) VALUES ('$name', $multiplier)");
		if(mysql_error()) {
			throw new Exception("Could not add the instance");
		}
	}

	function editInstance($name, $multiplier, $id) {
		mysql_query("UPDATE wow_instance SET name='$name', multiplier=$multiplier WHERE inid=$id");
		if(mysql_error()) {
			throw new Exception("Could not edit the instance");
		}
	}

	function deleteInstance($id) {
		mysql_query("DELETE FROM wow_instance WHERE inid=$id");
		if(mysql_error()) {
			throw new Exception("Could not delete the instance");
		}
	}
	
	function getInstances() {
		$query = mysql_query("SELECT * from wow_instance ORDER BY name ASC");
		if(mysql_num_rows($query)==0)
			throw new Exception("No instances found");

		if(is_file("models/instance.php"))
			include_once("models/instance.php");
		elseif(is_file("../models/instance.php"))
			include_once("../models/instance.php");
		while($result = mysql_fetch_assoc($query)) {
			$instances[] = new Instance($result);
		}
		return $instances;
	}

	function getInstance($inid) {
		$query = mysql_query("SELECT * from wow_instance WHERE inid=$inid");
		if(mysql_num_rows($query)==0)
			throw new Exception("Instance not found");

		if(is_file("models/instance.php"))
			include_once("models/instance.php");
		elseif(is_file("../models/instance.php"))
			include_once("../models/instance.php");
		return new Instance(mysql_fetch_assoc($query));
	}
	
	function getRecentInstances() {
		$query = mysql_query("SELECT distinct inid, name, multiplier FROM wow_raid LEFT JOIN wow_instance USING (inid) WHERE start> now() - INTERVAL 14 DAY AND status='Finished'");
		if(mysql_num_rows($query)==0)
			throw new Exception("Could not find any raids");

		if(is_file("models/instance.php"))
			include_once("models/instance.php");
		elseif(is_file("../models/instance.php"))
			include_once("../models/instance.php");

		while($result = mysql_fetch_assoc($query)){
			$instances[] = new Instance($result);
		}
		return $instances;
	}
}