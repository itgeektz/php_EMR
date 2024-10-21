<?php
$ignoreAuth = true; // no login required

require_once('interface/globals.php');
require_once("$srcdir/acl_upgrade_fx.php");

//Ensure that phpGACL has been installed
include_once('library/acl.inc');
if (isset ($phpgacl_location)) {
	include_once("$phpgacl_location/gacl_api.class.php");
	$gacl = new gacl_api();
}
else {
	die("You must first set up library/acl.inc to use phpGACL!");
}



// Create ARO groups.
$tech = $gacl->add_group('technician','Technician',10,'ARO');
$pharma = $gacl->add_group('pharmacist','Pharmacist',10,'ARO');

// Set permissions 


$technicain = addNewACL('Technician', 'technician', 'addonly', 'Things that technician can read and enter but not modify');
$pharmacist = addNewACL('Pharmacist', 'pharmacist', 'addonly', 'Things that pharmacist can read and enter but not modify');


//Update the ACLs
echo "<BR/><B>Updating the ACLs(Access Control Lists)</B><BR/>";


updateAcl($technicain, 'Technician', 'patients', 'Patients', 'appt', 'Appointments (addonly)', 'addonly');

updateAcl($technicain, 'Technician', 'patients', 'Patients', 'demo', 'Demographics (addonly)', 'addonly');

updateAcl($technicain, 'Technician', 'patients', 'Patients', 'med', 'Medical/History (addonly)', 'addonly');

updateAcl($technicain, 'Technician', 'patients', 'Patients', 'trans', 'Transactions (addonly)', 'addonly');

updateAcl($technicain, 'Technician', 'patients', 'Patients', 'docs', 'Documents (addonly)', 'addonly');

updateAcl($technicain, 'Technician', 'patients', 'Patients', 'notes', 'Patient Notes (addonly)', 'addonly');

updateAcl($technicain, 'Technician', 'sensitivities', 'Sensitivities', 'normal', 'Normal', 'addonly');

updateAcl($technicain, 'Technician', 'patients', 'Patients', 'sign', 'Sign Lab Results (addonly)', 'addonly');

updateAcl($technicain, 'Technician', 'calendar', 'Calendar ', 'cal_calendar', 'Calendar Menu (addonly)', 'addonly');

updateAcl($technicain, 'Technician', 'encounters ', 'Encounters ', 'auth', 'Authorize - my encounters (addonly)', 'addonly');
 
updateAcl($technicain, 'Technician', 'encounters ', 'Encounters ', 'auth_a', 'Authorize - any encounters (addonly)', 'addonly');

updateAcl($technicain, 'Technician', 'encounters', 'Encounters', 'coding_a', 'Coding - any encounters (write,wsome optional)', 'addonly');

updateAcl($technicain, 'Technician', 'encounters ', 'Encounters ', 'notes', 'Notes - my encounters (addonly)', 'addonly');

updateAcl($technicain, 'Technician', 'encounters', 'Encounters', 'notes_a', 'Notes - any encounters (write,addonly optional)', 'addonly');

updateAcl($technicain, 'Technician', 'encounters', 'Encounters', 'date_a', 'Fix encounter dates - any encounters', 'addonly');

updateAcl($technicain, 'Technician', 'patientmenu', 'Patient Menu ', 'pat_patient', ' Patient', 'addonly');

updateAcl($technicain, 'Technician', 'patientmenu', 'Patient Menu ', 'pat_sumry', 'Summary', 'addonly');

updateAcl($technicain, 'Technician', 'patientmenu', 'Patient Menu ', 'pat_vsit_creat', 'Create Visit', 'addonly');

updateAcl($technicain, 'Technician', 'patientmenu', 'Patient Menu ', 'pat_vsit_histry', 'Visit History', 'addonly');

updateAcl($technicain, 'Technician', 'patientmenu', 'Patient Menu ', 'pat_custmrep_addnew', 'Add New', 'addonly');

updateAcl($technicain, 'Technician', 'patientmenu', 'Patient Menu ', 'pat_vsitfrm', 'Visit Forms', 'addonly');

updateAcl($technicain, 'Technician', 'Miscellaneous ', 'Miscellaneous ', 'mis_pass', 'Password', 'addonly');






updateAcl($pharmacist, 'Pharmacist', 'patients', 'Patients', 'demo', 'Demographics (addonly)', 'addonly');

updateAcl($pharmacist, 'Pharmacist', 'patients', 'Patients', 'med', 'Medical/History (addonly)', 'addonly');

updateAcl($pharmacist, 'Pharmacist', 'sensitivities', 'Sensitivities', 'normal', 'Normal', 'addonly');

updateAcl($pharmacist, 'Pharmacist', 'patientmenu', 'Patient Menu ', 'pat_patient', ' Patient', 'addonly');

updateAcl($pharmacist, 'Pharmacist', 'patientmenu', 'Patient Menu ', 'pat_sumry', 'Summary', 'addonly');

updateAcl($pharmacist, 'Pharmacist', 'patientmenu', 'Patient Menu ', 'pat_vsit_histry', 'Visit History', 'addonly');

updateAcl($pharmacist, 'Pharmacist', 'patientmenu', 'Patient Menu ', 'pat_custmrep_addnew', 'Add New', 'addonly');

updateAcl($pharmacist, 'Pharmacist', 'Miscellaneous ', 'Miscellaneous ', 'mis_pass', 'Password', 'addonly');







?>

<html>
<head>
<title>OpenEMR ACL Setup</title>
<link rel=STYLESHEET href="interface/themes/style_blue.css">
</head>
<body>
<b>OpenEMR ACL Setup</b>
<br>
All done configuring and installing access controls (php-GACL)!
</body>
</html>
