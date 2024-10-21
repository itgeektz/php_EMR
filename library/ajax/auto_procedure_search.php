<?php
/**
 * auto search codes
 *
 * Copyright (C) ViSolve <services@visolve.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @package OpenEMR
 * @author  ViSolve <services@visolve.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once('../../interface/globals.php');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');

$file = fopen("/tmp/test.txt","w");


if($_REQUEST['service_auto'] == 'auto')
{

	$search_term = $_REQUEST['search_term'];
	$pat_cat = $_SESSION['pat_cat'];
	$service_type = $_REQUEST['service_type'];
	
	$response = array();
	$tmp = array();

 	if($service_type != "PROCEDURE"){
		
  	$res = sqlStatement("SELECT rm.opd,rm.service_code, rm.payor_name,rm.service_name FROM rate_master rm  LEFT JOIN rate_pat_master rp ON rp.rate_cat = rm.payor_name WHERE rm.service_name LIKE '%$search_term%' AND rp.status = 'ACTIVE' AND rp.pat_cat = '$pat_cat' AND rm.opd != -1 AND rm.service_group = '$service_type'");
     
	}
	else {
  			$service_type = "'OTHERS','SERVICES','WARD CHARGES','PHARMACY','HOSPITAL CHARGE','GENERAL SURGERY','ENT','CONSULTATION CHARGES','CARDIOLOGY','INVESTIGATION','REGISTRATION CHARG','DENTAL','GYNAECOLOGY','OPTHALMOLOGY'";
		
  			$res = sqlStatement("SELECT rm.opd,rm.service_code, rm.payor_name,rm.service_name FROM rate_master rm  LEFT JOIN rate_pat_master rp ON rp.rate_cat = rm.payor_name WHERE rm.service_name LIKE '%$search_term%' AND rp.status = 'ACTIVE' AND rp.pat_cat = '$pat_cat' AND rm.opd != -1 AND rm.service_group IN ($service_type)");
		}
		
		if(sqlNumRows($res) > 0)
		{
			while ($row = sqlFetchArray($res)) 
			{

  				$tmp['rate'] = $row['opd'];
  				$tmp['service_code'] = $row['service_code'];
  				$tmp['rate_cat'] = $row['payor_name'];
  				$tmp['service_text'] = $row['service_name'];
  				$tmp['pat_cat'] = $pat_cat;
		
  				array_push($response, $tmp);
  				
			}

			echo json_encode($response);
			
		}	
		
}


if($_REQUEST['service_age'] == 'service_age')
{

	$tmp = array();
	
	$pid = $_SESSION['pid'];
	$service_code = $_REQUEST['service_code'];
	$service_type = $_REQUEST['service_type'];
	
	if ($service_type == 'lab') {
		
		$service_table = "rmc_lab";
		
	}else if ($service_type == 'inter') {
		
		$service_table = "rmc_intervention";
		
	}else if ($service_type == "phy") {
		
		$service_table = "rmc_physical";
		
	}else if ($service_type == 'img') {
		
		$service_table = "rmc_imaging";
	}	
		$res = sqlStatement("select ser.service_name,en.date,timestampdiff(day,en.date,NOW()) as Days from ".$service_table." as ser inner join form_encounter as en on ser.encounter = en.encounter  where ser.pid = ".$pid." and ser.service_code = '".$service_code."' order by en.date DESC limit 1;");
		
		if(sqlNumRows($res) > 0)
		{
				while ($row = sqlFetchArray($res))
				{
					if($row['Days'] < 14 )
					{
						
						$tmp['service_name'] = $row['service_name'];
						$tmp['serviced_date'] = (new DateTime($row['date']))->format('d-m-y');
						$tmp['days']  = $row['Days'];
						
						
						echo json_encode($tmp);
			 		}
				}
		}		

}



if($_REQUEST['type'] == 'invest')
{
	$data = $_POST['data'];
	fwrite($file,print_r($data,true));
	foreach($data as $key => $value)
	{
	
		$test[] = json_decode($value,true);		
	}
	
	foreach ($test as $key => $value){
		
		$table_name = $value['table_name'];
		$service_code = $value['service_code'];
		$encounter_id = $value['encounter_id'];
		$status = $value['status'];
		$serviced = $value['serviced'];
		
		$query = "update ".$table_name." set service_status ='".$status."',serviced_by = '".$serviced."' where encounter = '".$encounter_id."' and service_code = '".$service_code."'" ;
		$output = sqlInsert($query);

	}


	echo json_encode($output);
	
	
}

if($_REQUEST['type'] == 'proc_report')
{
	$data = $_REQUEST['data'];
	$status = $data[0];
	$serviced = $data[1];
	$proc_id = $data[2];
	$p_id = $data[3];
	
	$output = sqlInsert("update form_procedure set service_status ='".$status."',serviced_by = '".$serviced."' where pid = ".$p_id." and id =".$proc_id );
	echo json_encode($output);
	
}




?>
