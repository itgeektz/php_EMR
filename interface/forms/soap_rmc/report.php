<?php
include_once(dirname(__FILE__).'/../../globals.php');
include_once($GLOBALS["srcdir"]."/api.inc");
function soap_rmc_report( $pid, $encounter, $cols, $id) {
$count = 0;
$data = formFetch("form_soap_rmc", $id);
	if ($data) {
		print "<table>";
			print "<tr><td><label class=bold>Presenting Symptom:</label></td><td class=text>" . $data{'presenting_symptom'} ."</td></tr>";
			print "<tr><td><label class=bold>History of presenting complaint:</label></td><td class=text>".$data{'presenting_complaint'}." </td></tr>";
			print "<tr><td><label class=bold>Past Medical History(Surgical):</label></td><td class=text>".$data{'smedical_history'}." </td></tr>";
			print "<tr><td><label class=bold>Examination Finding:</label></td><td class=text>".$data{'examination_fidning'}." </td></tr>";
			print "<tr><td><label class=bold>Investigation:</label></td><td class=text>".$data{'investigation'} ." </td></tr>";
			print "<tr><td><label class=bold>On going significant past medical history(Medical):</label></td><td class=text>".$data{'medical_history'} ."</td></tr>";
		print "</table>";
	}
}
?>
