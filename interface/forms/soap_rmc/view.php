<!-- Forms generated from formsWiz -->
<?php
include_once("../../globals.php");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
</head>
<body class="body_top">
<?php
include_once("$srcdir/api.inc");
$obj = formFetch("form_soap_rmc", $_GET["id"]);
?>
<form method=post action="<?php echo $rootdir?>/forms/soap_rmc/save.php?mode=update&id=<?php echo $_GET["id"];?>"
 name="my_form" onsubmit="return top.restoreSession()">
<span class="title"><?php xl('SOAP Note','e'); ?></span><Br><br>

  <table>
    <tr>  <td><label>Presenting Symptom: </label></td><td>&nbsp;</td><td><input type="text" id="presenting_symptom" name="presenting_symptom" value='<?php echo $obj{"presenting_symptom"} ?>' ></td></tr>
    <tr>  <td><label>History of presenting complaint: </label></td><td>&nbsp;</td><td><textarea class="text" cols=30 id="presenting_complaint" name="presenting_complaint" ><?php echo $obj{"presenting_complaint"} ?></textarea></td></tr>
    <tr>  <td><label>Past Medical History(Surgical): </label></td><td>&nbsp;</td><td><input type="text" id="smedical_history" name="smedical_history" value='<?php echo $obj{"smedical_history"} ?>' ></td></tr>
    <tr>  <td><label>Examination Finding: </label></td><td>&nbsp;</td><td><input type="text" id="examination_fidning" name="examination_fidning" value='<?php echo $obj{"examination_fidning"} ?>'></td></tr>
    <tr>  <td><label>Investigation: </label></td><td>&nbsp;</td><td><input type="text" id="investigation" name="investigation" value='<?php echo $obj{"investigation"} ?>' ></td></tr>
    <tr>  <td><label>On going significant past medical history(Medical): </label></td><td>&nbsp;</td><td><input type="text" id="medical_history" name="medical_history" value='<?php echo $obj{"medical_history"} ?>' ></td></tr>
  </table>

<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit">[<?php xl('Save','e'); ?>]</a>
<br>
<a href="<?php echo "$rootdir/patient_file/summary/demographics.php?opener_save=true";?>" class="link"
 onclick="top.restoreSession()">[<?php xl('Don\'t Save Changes','e'); ?>]</a>
</form>
<?php
formFooter();
?>
