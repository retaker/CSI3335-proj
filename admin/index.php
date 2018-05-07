<?php
include_once "header.php";
if(!$_SESSION["isAdmin"]){
	die("Permission denied");
}
echo "Hi," . $_SESSION["userName"] . ".<br/> You're an Admin.";
?>