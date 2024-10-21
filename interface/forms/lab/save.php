<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

if (!$encounter) { // comes from globals.php
	die(xlt("Internal error: we do not seem to be in an encounter!"));
}
$file = fopen("/tmp/test.txt","w");

$pid = $_SESSION['pid'];
$encounter = $_SESSION['encounter'];

if($_GET['mode'] == "new")
{
	
	
	
	/* $pat_cat = addslashes(json_encode($_POST['pat_cat']));
	$rate_cat = addslashes(json_encode($_POST['rate_cat']));
	$rate = json_encode($_POST['rate']); */
   $lab_codes = addslashes(json_encode($_POST['checkbox_codes']));

$new_id = sqlInsert("INSERT INTO form_lab_rmc (pid,encounter,user,groupname,authorized,activity,lab_codes,date)VALUES(".$pid.",".$encounter.",'".$_SESSION["authUser"]."','".$_SESSION["authProvider"]."','".$userauthorized."',1,'".$lab_codes."',NOW())");
addForm($encounter, "Laboratory", $new_id, "lab", $pid, $userauthorized);
}elseif($_GET['mode'] == "update") {
	
	
	fwrite($file, print_r($_POST,TRUE));
	$id = $_GET['id'];

	$lab_codes = addslashes(json_encode($_POST['checkbox_codes']));
	
	sqlInsert("update form_lab_rmc set pid = ".$pid.",encounter = ".$encounter.",user = '".$_SESSION["authUser"]."',groupname = '".$_SESSION["authProvider"]."',authorized = '".$userauthorized."',activity =1,lab_codes = '".$lab_codes."',date = NOW() where id =".$id);

}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump("1");
formFooter();
 