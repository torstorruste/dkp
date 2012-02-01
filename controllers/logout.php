<?php
session_start();
unset($_SESSION['dkp']);
unset($_SESSION['name']);
setcookie('dkp', '', 0);
setcookie('name', '', 0);
$_SESSION['notice'] = "Successfully logged out";
header("Location: ../index.php");