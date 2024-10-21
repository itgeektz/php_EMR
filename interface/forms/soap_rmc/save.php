<?php
/*
* Forms generated from formsWiz
* script to save Review of Systems Checks Form
*
* Copyright (C) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
*
* @package OpenEMR
* @author  Roberto Vasquez <robertogagliotta@gmail.com>
* @link    http://www.open-emr.org
*/
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
foreach ($_POST as $k => $var) {
$_POST[$k] = add_escape_custom($var);
echo attr($var);
echo "\n";
}
if ($encounter == "")
$encounter = date("Ymd");
if ($_GET["mode"] == "new"){
$newid = formSubmit("form_soap_rmc", $_POST, $_GET["id"], $userauthorized);
addForm($encounter, "SOAP RMC", $newid, "soap_rmc", $pid, $userauthorized);
}elseif ($_GET["mode"] == "update") {
sqlInsert("update form_soap_rmc set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), presenting_symptom='".$_POST["presenting_symptom"]."', presenting_complaint='".$_POST["presenting_complaint"]."', smedical_history='".$_POST["smedical_history"]."', examination_fidning='".$_POST["examination_fidning"]."', investigation='".$_POST["investigation"]."', medical_history='".$_POST["medical_history"]."' where id=$id");
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump("2");
formFooter();
?>
