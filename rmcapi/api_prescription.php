<?php
/**
 * Procedure API.
 *
 * Copyright (C) 2006-2017 ViSolve <services@visolve.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @package OpenEMR
 * @author  ViSolve <services@visolve.com>
 * @link    http://hc.visolve.com
 */

require_once("db_request.php");
require_once("accesstoken.php");

/**
 * Get the unbilled prescriptions for the specified $pid
 *
 * @param  String  $pid	        Patient MRN id
 * @param  String  $accesstoken Access token for api authentication
 */
function load_drug_attributes($interval_id) {
	global $conn;
	
	$qr ="SELECT title FROM list_options WHERE list_id = 'drug_interval' and option_id=$interval_id";	
	$ans = $conn->query($qr);
	
	$interval=$ans->fetch_assoc();
		
	return $interval['title'];
}

function get_prescriptions($pubpid,$accesstoken) {

	global $conn;

	$status = validate_accesstoken($accesstoken);
	$res = array();
	$results = array();

	if($status){

		$pid = get_pid($pubpid);

		$query="select id, pat_cat, drug_code, drug, item_name, quantity, notes, dosage, drug_interval, bill_type from rmc_prescriptions where encounter = (select max(encounter) from rmc_prescriptions where pid=".$pid.")";

		$res1 = $conn->query($query);
		
		if($res1->num_rows > 0){
			
			while($row=$res1->fetch_assoc())
			{
				$tmp['id']=$row['id'];
				$tmp['patient_category']=$row['pat_cat'];
				$tmp['item_code']=$row['drug_code'];
				$tmp['generic_name']=$row['drug'];
				$tmp['item_name']=$row['item_name'];
				$tmp['quantity']=$row['quantity'];
				$tmp['notes']=$row['notes'];
				$tmp['take_in']=$row['dosage'];
				$tmp['interval']=load_drug_attributes($row['drug_interval']);
				$tmp['bill_type']=($row['bill_type'])? "Credit":"Cash";	
				$temp[]=$tmp;	
			}		
			$presc['mrn']  = $pubpid;
			$presc['prescription']=$temp;
			
			$res['status'] = true;
			$res['msg'] = $presc;
			return $res;
		}
		else
		{
			$res['status'] = false;
			$res['msg'] = "No prescriptions";
			return $res;
		}
		
	}
	else 
	{
		$res['status'] = false;
		$res['msg'] = "Session Expired";
		return $res;
	}

}

/**
 * Update Prescriptions bill status for the specified $id
 *
 * @param  String  $id	        prescription_id
 * @param  String  $bill	      Bill number
 * @param  String  $status	    Bill status
 * @param  String  $accesstoken Access token for api authentication
 */
function update_prescription_status($id,$bill,$bill_status,$accesstoken) {

	global $conn;

	$status = validate_accesstoken($accesstoken);
	$res = array();
	$results = array();
   
	
	if($status){
		//check bill status is numeric or not ( do not accept boolean values)
		
		if(is_numeric($bill_status) && $bill_status <=1)
		{
		

		$chk_old = $conn->query("SELECT id FROM rmc_prescriptions WHERE id = $id");
		if($chk_old->num_rows > 0){

			$upd_qry = "UPDATE rmc_prescriptions SET bill_status = '$bill_status', bill_number = '$bill' WHERE id = $id";
			$conn->query($upd_qry);

			$res['status'] = true;
			$res['msg'] = "Bill Status updated";

		}else {

			$res['status'] = false;
			$res['msg'] = "ID not found";

		}

		return $res;
		}
		else
		{
			$res['status'] = false;
			$res['msg'] = "Invalid Status";
			return $res;
		}

	}else{
		$res['status'] = false;
		$res['msg'] = "Session Expired";
		return $res;
	}
 
 
	
}

function get_pid($pubpid) {

	global $conn;

	$qry = "SELECT pid FROM patient_data WHERE pubpid = $pubpid";

	$res = $conn->query($qry);

	if($res->num_rows > 0){
		while($row = $res->fetch_assoc()) {
			$results = $row['pid'];
		}
		return $results;
	}

}

//check request variable
function check_request()
{
	if(isset($_REQUEST['accesstoken']) && !empty($_REQUEST['accesstoken']))
	{
		if(isset($_REQUEST['action']) && !empty($_REQUEST['action']))
		{

			$temp_action=explode("_",$_REQUEST['action']);

			if($temp_action[0]=='get')
			{
				if(isset($_REQUEST['pid']) && !empty($_REQUEST['pid']))
				{
					return true;
				}

				else
				{
					return false;

				}

			}

			else if($temp_action[0]=='update')
			{

				if(isset($_REQUEST['id']) && !empty($_REQUEST['id']) &&  isset($_REQUEST['bill']) && !empty($_REQUEST['bill']) && isset($_REQUEST['status']) && !empty($_REQUEST['status']))
				{

					return true;

				}

				else
				{
					return false;

				}


			}
			else
			{
				return false;
					
			}

		}
		else
		{
			return false;

		}

	}
	else
	{
		return false;

	}
}

$flag=0;
$flag=check_request(); //function call  to check request


if($flag){

	switch ($_GET['action'])
	{
		   case "get_prescriptions":
			  	$pid = $conn->real_escape_string($_REQUEST['pid']);
				$val = get_prescriptions($pid,$_REQUEST['accesstoken']);
				break;
           case "update_prescriptions":
				$id = $conn->real_escape_string($_REQUEST['id']);	
				$bill = $conn->real_escape_string($_REQUEST['bill']);
				$status = $conn->real_escape_string($_REQUEST['status']);
				$val = update_prescription_status($id,$bill,$status,$_REQUEST['accesstoken']);
				break;
		   default:
			      $val['status'] = false;
	              $val['msg'] = "Invalid Request";
			break;
	}
}

else
{
	$val['status'] = false;
	$val['msg'] = "Invalid Request";

}

exit(json_encode($val));


?>