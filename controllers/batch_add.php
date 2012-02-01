<?php
session_start();
if(isset($_SESSION['admin'])) {
	if(isset($_POST['text'])) {
		include("database.php");
		$db = new database();
		
		$instances = $db->getInstances();
		$items = explode("\n", $_POST['text']);
		foreach($items as $item) {
			if(trim($item) != "") {
				$fields = explode("\t", $item);
				print_r($fields);
				$in = 0;
				foreach($instances as $instance) {
					if($instance->getName()==rtrim($fields[3])) {
						$in = $instance->getId();
					}
				}
				if($in != 0) {
					try {
						$db->addItem($fields[0], $fields[1], $fields[2], $in);
						echo "Added ".$fields[1]."<br>\n";
					} catch(Exception $e) {
						echo $e->getMessage()."<br>\n";
					}
				} else {
					echo "'".rtrim($fields[3])."' not found<br/>\n";
				}
			}
			
		}
		echo "<a href=\"../index.php?page=admin\">Back</a>";
			
	}
} else {
	$_SESSION['error'] = "You cannot do that";
	header("Location: ../index.php");
}