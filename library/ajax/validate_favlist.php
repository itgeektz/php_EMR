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



$response = array();
$tmp = array();

function validate_favlist($service_code, $service_type, $pat_cat){

  $res = sqlStatement("SELECT rm.opd,rm.payor_name FROM rate_master rm  LEFT JOIN rate_pat_master rp ON rp.rate_cat = rm.payor_name WHERE rm.service_code = '$service_code' AND rp.status = 'ACTIVE' AND rp.pat_cat = '$pat_cat' AND rm.opd != -1 AND rm.service_group = '$service_type'");

  while ($row = sqlFetchArray($res)) {

    $tmp['rate'] = $row['opd'];
    $tmp['rate_cat'] = $row['payor_name'];
    $tmp['pat_cat'] = $pat_cat;
  }

  return $tmp;
}

function update_favlist($service_code, $service_type, $service_name, $userid){

    $currentqry = sqlQuery("SELECT id FROM service_favlist WHERE service_code = '$service_code' AND service_type = '$service_type' AND userid = $userid");
    $currentresid = $currentqry['id'];

    $currentcntqry = sqlQuery("SELECT count FROM service_favlist WHERE service_code = '$service_code' AND service_type = '$service_type' AND userid = $userid");
    $currentrescnt = $currentcntqry['count'];

    if($currentresid > 0) {

      $currentrescnt += 1;
      sqlQuery("UPDATE service_favlist SET count = $currentrescnt WHERE id = $currentresid");

    }else {

      $usercntqry = sqlQuery("SELECT COUNT(userid) as usercnt FROM service_favlist WHERE service_type = '$service_type'");
      $usercnt = $usercntqry['usercnt'];

      if($usercnt > 16){
        sqlQuery("DELETE FROM service_favlist WHERE id = (SELECT id FROM (SELECT id FROM service_favlist WHERE service_type = '$service_type' AND count IN (SELECT MIN(count) FROM service_favlist WHERE userid = $userid) LIMIT 1) as id )");
        sqlInsert("INSERT INTO service_favlist(service_type, service_code, service_name, userid, count) VALUES('$service_type','$service_code','$service_name','$userid', 1)");
      }else {
        $count = 1;
        sqlInsert("INSERT INTO service_favlist(service_type, service_code, service_name, userid, count) VALUES('$service_type','$service_code','$service_name','$userid', 1)");
      }

    }
  return true;
}

function get_favlist_laboratory($userid){

  $res = sqlStatement("SELECT service_code,service_name FROM service_favlist WHERE userid = $userid AND service_type = 'lab'");

  $response = '<tr><td style="width: 50%;"><div class="fav_div" style="display:inline-block; vertical-align:top; overflow:hidden;margin-left: 10px;"><select onchange="set_text()" size="15" id="favlist" style="padding:10px; margin:-5px -20px -5px -5px; ; background-color: white;width:310px;height:380px;">';
  while ($row = sqlFetchArray($res)) {
    $response .= '<option  style="height: 20px; padding-top: 10px;cursor:pointer;" class="lab" data-code="' . $row['service_code'] . '" value="'. $row['service_name'] .'">'. $row['service_name'] .'</option>';
  }

  $response .= '</select></div></td>';
 
/*   $response .='<td style="width: 50%;"><label class=bold>Radiology</label><select onchange="set_text_img()" size="15" id="favlist_img" style="width: 100%; background-color: lightgrey;">';
  $res_img = sqlStatement("SELECT service_code,service_name FROM service_favlist WHERE userid = $userid AND service_type = 'img'");

  while ($row = sqlFetchArray($res_img)) {
    $response .= '<option class="img" data-code="' . $row['service_code'] . '" value="'. $row['service_name'] .'">'. $row['service_name'] .'</option>';
  }
  $response .= '</select></td></tr>'; */

  return $response;

}

function get_favlist_radiology($userid){
	
     $res_img = sqlStatement("SELECT service_code,service_name FROM service_favlist WHERE userid = $userid AND service_type = 'img'");
     $response = '<tr><td style="width: 50%;"><div class="fav_div" style="display:inline-block; vertical-align:top; overflow:hidden;margin-left: 10px;"><select onchange="set_text_img()" size="15" id="favlist_img" style="padding:10px; margin:-5px -20px -5px -5px; ; background-color: white;width:310px;height:380px;">';
     while ($row = sqlFetchArray($res_img)) {
     	$response .= '<option  style="height: 20px; padding-top: 10px;cursor:pointer;" class="img" data-code="' . $row['service_code'] . '" value="'. $row['service_name'] .'">'. $row['service_name'] .'</option>';
     }
      
	
	return $response;
	
}



if(isset($_GET['action'])){
    switch ($_GET['action'])
    {
     case "validate":
       $service_code = $_REQUEST['service_code'];
       $service_type = $_REQUEST['service_type'];
       $pat_cat = $_SESSION['pat_cat'];
       $val = validate_favlist ($service_code, $service_type, $pat_cat);
       exit(json_encode($val));
       break;
     case "update_favlist":
       $service_code = $_REQUEST['service_code'];
       $service_type = $_REQUEST['service_type'];
       $service_name = $_REQUEST['service_name'];
       $userid = $_SESSION["authUserID"];
       $val =  update_favlist ($service_code, $service_type, $service_name, $userid);
       exit(json_encode($val));
       break;
     case "get_favlist_laboratory":
       $userid = $_SESSION["authUserID"];
       $val =  get_favlist_laboratory($userid);
       exit($val);
       break;
     case "get_favlist_radiology":
       $userid = $_SESSION["authUserID"];
       $val =  get_favlist_radiology($userid);
       exit($val);
       break;
     default:
       $val['status'] = "Invalid Request";
       exit(json_encode($val));
       break;
    }
}

?>
