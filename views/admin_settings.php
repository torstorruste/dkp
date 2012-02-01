<?php
include_once("check_admin.php");
echo '<div id="widest" class="frame">';
echo '<span class="top"></span>';
echo '<a href="index.php">Back</a>';
echo "<h3>Add new value</h3>\n";
echo "<form method=\"post\" action=\"controllers/admin_settings.php\">";
echo "Name:<input type=\"text\" name=\"name\">Value<input type=\"text\" name=\"value\"><input type=\"submit\" value=\"Add setting\" name=\"addsetting\"></form>";

echo "<h3>The current values</h3>\n";
try {
	include_once("dao/settingDao.php");
	$settingDao = new SettingDao();
	
	$settings = $settingDao->getSettings();
	echo "<table>\n<thead>\n<tr><th>Name:</th><th>Value:</th></tr>\n</thead>\n<tbody>\n";
	foreach($settings as $setting) {
		echo "<tr><form method=\"post\" action=\"controllers/admin_settings.php\"><td><input type=\"hidden\" name=\"name\" value=\"$setting[name]\">$setting[name]</td><td><input type=\"text\" maxlength=\"10\" size=\"10\" name=\"value\" value=\"$setting[value]\"></td><td><input type=\"submit\" name=\"editsettings\" value=\"Edit\"></td></form><td><form method=\"post\" action=\"controllers/admin_settings.php\"><input type=\"hidden\" name=\"name\" value=\"$setting[name]\"><input type=\"submit\" value=\"Delete\" name=\"deletesetting\" onClick=\"return confirm('Are you sure you want to delete $setting[name]?')\"></form></td></tr>\n";
	}
	echo "</tbody>\n</table>\n";
} catch(Exception $e) {
	echo $e->getMessage();
}
?>
<br/><br/>
<a href="index.php">Back</a>
<span class="bottom"></span>
</div>