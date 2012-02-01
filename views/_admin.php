<?php
if(isset($_SESSION['admin'])) {
?>
<a href="index.php?page=admin_player">Administrate players</a><br/>
<a href="index.php?page=admin_item">Administrate items</a><br/>
<a href="index.php?page=admin_instance">Administrate instances</a><br/>
<a href="index.php?page=admin_raid">Administrate raids</a><br/>
<a href="index.php?page=admin_settings">Change settings</a><br/>
<?php
}