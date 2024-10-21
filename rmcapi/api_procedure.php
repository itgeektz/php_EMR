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
 * Get the unbilled procedures for the specified $pid
 *
 * @param  String  $pid	        Patient MRN id
 * @param  String  $accesstoken Access token for api authentication
 */
function get_procedures($pubpid,$accesstoken) {

  global $conn;

  $status = validate_accesstoken($accesstoken);
  $res = array();
  $results = array();

  if($status){

      $pid = get_pid($pubpid);

      $pro_qry = " SELECT fp.id,pd.pubpid, fp.service_code, fp.service_name, fp.comments, fp.pat_cat, fp.rate_cat, fp.bill_status FROM form_procedure fp LEFT JOIN patient_data pd ON pd.pid = fp.pid WHERE fp.pid = $pid ORDER BY fp.id DESC LIMIT 1";

      $pro_res = $conn->query($pro_qry);

      if($pro_res->num_rows > 0){
         while($row = $pro_res->fetch_assoc()) {

           $tmp['id'] = $row['id'];
           $tmp['pubpid'] = $row['pubpid'];
           $tmp['service_code'] = json_decode($row['service_code']);
           $tmp['service_name'] = json_decode($row['service_name']);
           $tmp['comments'] = json_decode($row['comments']);
           $tmp['pat_cat'] = json_decode($row['pat_cat']);
           $tmp['rate_cat'] = json_decode($row['rate_cat']);
           $tmp['bill_status'] = json_decode($row['bill_status']);
        }

        $cnt = count($tmp['service_code']);

        $results['mrn']  = $tmp['pubpid'];
        $results['procedure_id'] = $tmp['id'];

        for ($i=0; $i < $cnt; $i++) {

          $tmpp['service_code'] = $tmp['service_code'][$i];
          $tmpp['service_name'] = $tmp['service_name'][$i];
          $tmpp['comments'] = $tmp['comments'][$i];
          $tmpp['patient_category'] = $tmp['pat_cat'][$i];
          $tmpp['rate_category'] = $tmp['rate_cat'][$i];
          $tmpp['bill_status'] = ($tmp['bill_status'][$i] != '' ? $tmp['bill_status'][$i] : '0');

          $services[] = $tmpp;
        }

        $results['service'] = $services;

        $res['status'] = true;
        $res['msg'] = $results;
        return $res;
      }else {
        $res['status'] = false;
        $res['msg'] = "No Procedures";
        return $res;
      }
  }else{
     $res['status'] = false;
     $res['msg'] = "Session Expired";
     return $res;
  }
}

/**
 * Update procedure bill status for the specified $id
 *
 * @param  String  $id	        procedure_id
 * @param  String  $uid	        unique_id
 * @param  String  $bill	      Bill number
 * @param  String  $status	    Bill status
 * @param  String  $accesstoken Access token for api authentication
 */
function update_procedure_status($id,$uid,$bill,$bill_status,$accesstoken) {

  global $conn;

  $status = validate_accesstoken($accesstoken);
  $res = array();
  $results = array();
 
  if($status){
  	//check bill status is numeric or not ( do not accept boolean values)
  	if(is_numeric($bill_status) && $bill_status <=1)
  	{

    $pro_qry = "SELECT service_code,comments,bill_status,bill_no FROM form_procedure WHERE id = $id";

    $pro_res = $conn->query($pro_qry);

    if($pro_res->num_rows > 0){
       while($row = $pro_res->fetch_assoc()) {

         $old['service_code'] = json_decode($row['service_code']);
         $old['comments'] = json_decode($row['comments']);
         $old['bill_status'] = json_decode($row['bill_status']);
         $old['bill_no'] = json_decode($row['bill_no']);

       }
     }

    $new = explode('|',$uid);

    $bill_status = array();
    $bill_no = array();

    for ($i=0; $i < count($old['service_code']); $i++) {
      if ( ($old['service_code'][$i] == $new[0]) && ($old['comments'][$i] == $new[1])) {
        $bill_status[] = (int)$bstatus;
        $bill_no[] = $bill;
      }else{
        $bill_status[] = $old['bill_status'][$i];
        $bill_no[] = $old['bill_no'][$i];
      }
    }
    $bill_status = json_encode($bill_status);
    $bill_no = json_encode($bill_no);

    $upd_qry = "UPDATE form_procedure SET bill_status = '$bill_status', bill_no = '$bill_no' WHERE id = $id";
    $conn->query($upd_qry);

    $res['status'] = true;
    $res['msg'] = "Bill Status updated";
    return $res;
  }
   else 
  {
  	$res['status'] = false;
  	$res['msg'] = "Invalid Status";
  	return $res;
  }

  }
  else{
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

		     	if(isset($_REQUEST['id']) && !empty($_REQUEST['id']) && isset($_REQUEST['uid']) && !empty($_REQUEST['uid']) && isset($_REQUEST['bill']) && !empty($_REQUEST['bill']) && isset($_REQUEST['status']) && !empty($_REQUEST['status']))
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
     case "get_procedures":
    	 $pid = $conn->real_escape_string($_REQUEST['pid']);
       $val = get_procedures($pid,$_REQUEST['accesstoken']);
       break;
     case "update_procedures":
    	 $id = $conn->real_escape_string($_REQUEST['id']);
       $uid = $conn->real_escape_string($_REQUEST['uid']);
       $bill = $conn->real_escape_string($_REQUEST['bill']);
       $status = $conn->real_escape_string($_REQUEST['status']);
       $val = update_procedure_status($id,$uid,$bill,$status,$_REQUEST['accesstoken']);
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
