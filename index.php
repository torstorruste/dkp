<?php
session_start();
if(isset($_COOKIE['dkp'])) {
		$_SESSION['dkp'] = $_COOKIE['dkp'];
}
date_default_timezone_set('Europe/Paris');
// Handle logout
if(isset($_GET['page']) && $_GET['page'] == "logout") {
	unset($_SESSION['dkp']);
	unset($_SESSION['admin']);
	setcookie("dkp", "", 0, "/");
	$_SESSION['notice'] = "Successfully logged out";	
}
// Check if the session is correct, and give the correct privileges.
if(isset($_SESSION['dkp'])) {
	include_once("dao/genericDao.php");
	$genericDao = new GenericDao();
	if(!$genericDao->checkSession($_SESSION['dkp'])) {
		unset($_SESSION['dkp']);
		unset($_SESSION['admin']);
		setcookie("dkp", "", 0, "/");
	}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>DKP</title>
<link href="stylesheets/wow.css" type="text/css" rel="stylesheet"/>
<link href="stylesheets/style.css" type="text/css" rel="stylesheet"/>
<link href="stylesheets/dhtmlgoodies_calendar.css?random=20051112" type="text/css" rel="stylesheet" >
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script src="scripts/sorttable.js" type="text/javascript"></script>
<script src="http://www.wowhead.com/widgets/power.js"></script>
<script type="text/javascript" src="scripts/dhtmlgoodies_calendar.js?random=20060118"></script>
<script src="scripts/checkall.js" type="text/javascript"></script>
</head>
<body>
<?php

if(isset($_SESSION['error'])) {
	echo "<div id=\"error\">$_SESSION[error]</div>";
	unset($_SESSION['error']);
}
if(isset($_SESSION['notice'])) {
	echo "<div id=\"notice\">$_SESSION[notice]</div>";
	unset($_SESSION['notice']);
}
?>
<div id="main">
<?php
if(isset($_GET['page'])) {
	$page = eregi_replace("[^a-zA-Z_]", "", $_GET['page']);
	if(is_file("views/$page.php"))
		include("views/$page.php");
	else {
		include("views/default.php");
	}
} else {
	include("views/default.php");
}
?>
</div>
</body>
</html>