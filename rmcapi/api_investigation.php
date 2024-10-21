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
 * Get the investigation for the specified $pid
 *
 * @param  String  $pid	        Patient MRN id
 * @param  String  $tbl_name	  Table name
 * @param  String  $accesstoken Access token for api authentication
 */
function get_investigation($pubpid,$tbl_name,$accesstoken) {

  global $conn;

  $status = validate_accesstoken($accesstoken);
  $res = array();
  $results = array();

  if($status){

      $pid = get_pid($pubpid);

      $inve_qry = "SELECT tbl.id,tbl.service_code,tbl.service_name,tbl.comments,tbl.bill_status,tbl.pat_cat,tbl.rate_cat,pd.pubpid FROM $tbl_name  tbl LEFT JOIN patient_data pd ON pd.pid = tbl.pid WHERE tbl.pid = $pid AND encounter = (SELECT MAX(encounter) FROM $tbl_name WHERE $tbl_name.pid = $pid) ORDER BY tbl.encounter DESC";

      $inve_res = $conn->query($inve_qry);

      if($inve_res->num_rows > 0){
         while($row = $inve_res->fetch_assoc()) {

           $tmp['id'] = $row['id'];
           $tmp['pubpid'] = $row['pubpid'];
           $tmp['service_code'] = $row['service_code'];
           $tmp['service_name'] = $row['service_name'];
           $tmp['comments'] = $row['comments'];
           $tmp['pat_cat'] = $row['pat_cat'];
           $tmp['rate_cat'] = $row['rate_cat'];
           $tmp['bill_status'] = ($tmp['bill_status'] != '' ? $tmp['bill_status'] : '0');

           $tmp_arr[] = $tmp;

        }

        $cnt = count($tmp_arr);

        $results['mrn']  = $tmp_arr[0]['pubpid'];

        for ($i=0; $i < $cnt; $i++) {

          $tmpp['investigation_id'] = $tmp_arr[$i]['id'];
          $tmpp['service_code'] = $tmp_arr[$i]['service_code'];
          $tmpp['service_name'] = $tmp_arr[$i]['service_name'];
          $tmpp['comments'] = $tmp_arr[$i]['comments'];
          $tmpp['patient_category'] = $tmp_arr[$i]['pat_cat'];
          $tmpp['rate_category'] = $tmp_arr[$i]['rate_cat'];
          $tmpp['bill_status'] = ($tmp_arr[$i]['bill_status'] != '' ? $tmp_arr[$i]['bill_status'] : '0');

          $services[] = $tmpp;
        }

        $results['service'] = $services;

        $res['status'] = true;
        $res['msg'] = $results;

        return $res;

      }else {
        $res['status'] = false;
        $res['msg'] = "No Investigations";
        return $res;
      }
  }else{
     $res['status'] = false;
     $res['msg'] = "Session Expired";
     return $res;
  }
}

/**
 * Update Investigations bill status for the specified $id
 *
 * @param  String  $id	        procedure_id
 * @param  String  $tbl_name	  table name
 * @param  String  $bill	      Bill number
 * @param  String  $status	    Bill status
 * @param  String  $accesstoken Access token for api authentication
 */
function update_investigation_status($id,$tbl_name,$bill,$bill_status,$accesstoken) {

  global $conn;

  $status = validate_accesstoken($accesstoken);
  $res = array();
  $results = array();

  if($status){
  	//check bill status is numeric or not ( do not accept boolean values)
  	if(is_numeric($bill_status) && $bill_status <=1)
  	{
    $chk_old = $conn->query("SELECT id FROM $tbl_name WHERE id = $id");
    if($chk_old->num_rows > 0){

      $upd_qry = "UPDATE $tbl_name SET bill_status = '$bill_status', bill_no = '$bill' WHERE id = $id";
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
     case "get_lab":
       $pid = $conn->real_escape_string($_REQUEST['pid']);
       $val = get_investigation($pid,'rmc_lab',$_REQUEST['accesstoken']);
       break;
     case "get_intervention":
       $pid = $conn->real_escape_string($_REQUEST['pid']);
       $val = get_investigation($pid,'rmc_intervention',$_REQUEST['accesstoken']);
       break;
     case "get_physical_exam":
       $pid = $conn->real_escape_string($_REQUEST['pid']);
       $val = get_investigation($pid,'rmc_physical',$_REQUEST['accesstoken']);
       break;
     case "get_imaging":
       $pid = $conn->real_escape_string($_REQUEST['pid']);
       $val = get_investigation($pid,'rmc_imaging',$_REQUEST['accesstoken']);
       break;
     case "update_lab":
    	 $id = $conn->real_escape_string($_REQUEST['id']);
       $bill = $conn->real_escape_string($_REQUEST['bill']);
       $status = $conn->real_escape_string($_REQUEST['status']);
       $val = update_investigation_status($id,'rmc_lab',$bill,$status,$_REQUEST['accesstoken']);
       break;
     case "update_intervention":
       $id = $conn->real_escape_string($_REQUEST['id']);
       $bill = $conn->real_escape_string($_REQUEST['bill']);
       $status = $conn->real_escape_string($_REQUEST['status']);
       $val = update_investigation_status($id,'rmc_intervention',$bill,$status,$_REQUEST['accesstoken']);
       break;
     case "update_physical_exam":
       $id = $conn->real_escape_string($_REQUEST['id']);
       $bill = $conn->real_escape_string($_REQUEST['bill']);
       $status = $conn->real_escape_string($_REQUEST['status']);
       $val = update_investigation_status($id,'rmc_physical',$bill,$status,$_REQUEST['accesstoken']);
       break;
     case "update_imaging":
       $id = $conn->real_escape_string($_REQUEST['id']);
       $bill = $conn->real_escape_string($_REQUEST['bill']);
       $status = $conn->real_escape_string($_REQUEST['status']);
       $val = update_investigation_status($id,'rmc_imaging',$bill,$status,$_REQUEST['accesstoken']);
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
