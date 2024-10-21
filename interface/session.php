<?php
 require_once("globals.php");
if($_REQUEST['get_pid'] == 'pid')
{	
 	echo $_SESSION['pid'];
 }	
 if($_REQUEST['put_enc'])
{	
	$_SESSION['encounter'] = $_REQUEST['put_enc'];
 	echo $_SESSION['encounter'];
 }	
?>
