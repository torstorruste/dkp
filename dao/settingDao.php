<?php
class SettingDao extends GenericDao{
	function getSetting($name) {
		$query = mysql_query("SELECT * FROM wow_settings WHERE name='$name'");
		if(mysql_num_rows($query)==0)
			throw new Exception("Value not found");

		$result = mysql_fetch_assoc($query);
		return $result['value'];
	}

	function getSettings() {
		$query = mysql_query("SELECT * FROM wow_settings ORDER BY NAME ASC");
		if(mysql_num_rows($query)==0)
			throw new Exception("No values found");
		$i = 0;
		while($result = mysql_fetch_assoc($query)) {
			$settings[$i]['name'] = $result['name'];
			$settings[$i]['value'] = $result['value'];
			$i++;
		}
		return $settings;
	}
	
	function updateSetting($name, $value) {
		mysql_query("UPDATE wow_settings SET value=$value WHERE name='$name'");
		if(mysql_error()) {
			throw new Exception("Could not update the value");
		}
	}

	function addSetting($name, $value) {
		mysql_query("INSERT INTO wow_settings (name, value) VALUES ('$name', $value)");
		if(mysql_error()) {
			throw new Exception("Could not add the setting");
		}
	}

	function deleteSetting($name) {
		mysql_query("DELETE FROM wow_settings WHERE name='$name'");
		if(mysql_error()) {
			throw new Exception("Could not delete the setting");
		}
	}
}