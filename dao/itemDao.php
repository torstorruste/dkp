<?php
class ItemDao extends GenericDao {
	function addItem($id, $hid, $name, $quality, $instance) {
		mysql_query("INSERT INTO wow_item (iid, hid, name, quality, instance) VALUES ($id, $hid, '$name', '$quality', $instance)");
		if(mysql_error()) {
			throw new Exception("Could not add the item");
		}
	}

	function editItem($id, $hid, $name, $quality, $instance, $oldid) {
		mysql_query("UPDATE wow_item SET iid=$id, hid=$hid, name='$name', quality='$quality', instance='$instance' WHERE iid=$oldid");
		if(mysql_error()) {
			throw new Exception("Could not edit the item");
		}
	}

	function deleteItem($id) {
		mysql_query("DELETE FROM wow_item WHERE iid=$id");
		if(mysql_error()) {
			throw new Exception("Could not delete the item");
		}
	}
	
	function getItems($instance = "") {
		if($instance == "")
			$where = "";
		else
			$where = " WHERE instance=$instance";
		$query = mysql_query("SELECT * FROM wow_item $where ORDER BY name ASC");
		if(mysql_num_rows($query)==0)
			throw new Exception("No items found");

		include_once("models/item.php");
		while($result = mysql_fetch_assoc($query)) {
			$items[] = new Item($result);
		}
		return $items;
	}

	function getItem($iid, $heroic=false) {
		if($heroic)
			$query = mysql_query("SELECT * FROM wow_item WHERE hid=$iid");
		else
			$query = mysql_query("SELECT * FROM wow_item WHERE iid=$iid");
		if(mysql_num_rows($query)==0)
			throw new Exception("Invalid item");

		include_once("models/item.php");
		return new Item(mysql_fetch_assoc($query));
	}
	
	function getItemsAndPlayers($itemname = "") {
		$itemname = mysql_escape_string($itemname);
		$query = mysql_query("SELECT * FROM wow_player JOIN wow_event USING (pid) JOIN wow_item USING (iid) WHERE name LIKE '%$itemname%' ORDER BY name, class, playername");
		if(mysql_num_rows($query)==0)
			throw new Exception("No items found. You searched for '$itemname'");
		include_once("models/item.php");
		include_once("models/player.php");
		while($result = mysql_fetch_assoc($query)) {
			$items[$result['name']]['item'] = new Item($result);
			$items[$result['name']]['player'][$result['pid']] = new Player($result);
		}
		return $items;
	}
}