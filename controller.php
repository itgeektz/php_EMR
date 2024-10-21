<?php
require_once("interface/globals.php");
require_once("library/classes/Controller.class.php");



if(!empty($_SESSION['pid']))
{

	$_GET['patient_id']= $_SESSION['pid'];
	$_GET['encounter_id'] = $_SESSION['encounter'];
}


$controller = new Controller();
echo $controller->act($_GET);


?>
