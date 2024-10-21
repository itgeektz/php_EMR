<?php
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../globals.php");

include_once("$srcdir/patient.inc");


if(isset($_POST['pid']))
{
	
	$html='<div id="header2" style="padding-left:5px" ><br><br>';

	$result = getPatientData($pid, "fname,lname,file_number,sex, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
	$uname=sqlQuery("select fname,lname from users where id=".$_SESSION['authUserID']);
	$html.='<table width=100%>';
	$html.='<tr><td style="padding:7px;font-weight: bold;font-size: 12px;">Lname : </td><td style="padding:7px;font-weight: bold;font-size: 12px;">'.addslashes($result['lname']).' </td><td style="padding-left:100px;font-weight: bold;font-size: 12px;width:30%">&nbsp;&nbsp;</td><td style="padding:7px;font-weight: bold;font-size: 12px;">Fname : </td><td style="padding:7px;font-weight: bold;font-size: 12px;">'.addslashes($result['fname']).' </td></tr>';
	$html.='<tr><td style="padding:7px;font-weight: bold;font-size: 12px;">Registration No : </td><td style="padding:7px;font-weight: bold;font-size: 12px;">'.addslashes($result['file_number']).' </td><td style="padding:7px;font-weight: bold;font-size: 12px;width:10%">&nbsp;</td><td style="padding:7px;font-weight: bold;font-size: 12px;">Sex : </td><td style="padding:7px;font-weight: bold;font-size: 12px;">'.addslashes($result['sex']).' </td></tr>';
	$html.='<tr><td style="padding:7px;font-weight: bold;font-size: 12px;">Age : </td><td style="padding:7px;font-weight: bold;font-size: 12px;">'.getPatientAgeDisplay($result['DOB_YMD']).' </td><td style="padding:7px;font-weight: bold;font-size: 12px;width:10%">&nbsp;</td><td style="padding:7px;font-weight: bold;font-size: 12px;">Date : </td><td style="padding:7px;font-weight: bold;font-size: 12px;">'.date('d-m-Y').' </td></tr>';
	$html.='<tr><td style="padding:7px;font-weight: bold;font-size: 12px;">Doctor : </td><td style="padding:7px;font-weight: bold;font-size: 12px;">'.$uname['fname'].' '.$uname['lname'].' </td></tr>';
	$html.='</table></div>';
	echo $html;
	
}
?>
