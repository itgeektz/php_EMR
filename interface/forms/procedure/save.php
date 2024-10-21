<?php
/*
*
* script to save Procedure
*
* Copyright (C) 2016 ViSolve <services@visolve.com>
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
* @author  ViSolve <services@visolve.com>
* @link    http://www.open-emr.org
*/
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

if ($encounter == "")
$encounter = date("Ymd");

function check_code($code)
{

	if ($code == '' || $code == "0")
	{
		return false;

	}
	else {

		return true;
	}

}


if ($_GET["mode"] == "new"){

    $service_name = addslashes(json_encode($_POST['service_name']));
    $pat_cat = addslashes(json_encode($_POST['pat_cat']));
    $rate_cat = addslashes(json_encode($_POST['rate_cat']));
    $comments = addslashes(json_encode($_POST['comments']));
    $service_code = json_encode($_POST['service_code']);
    $rate = json_encode($_POST['rate']);

    	if(check_code($service_code))
    	{
    		
    		if($service_name != "")
    		{
    		
    			
    	      $newid = sqlInsert("INSERT INTO form_procedure (date,pid,user,groupname,authorized,activity,service_code,service_name,comments,pat_cat,rate_cat,rate,createdat) VALUES (NOW(),{$_SESSION["pid"]},'".$_SESSION["authUser"]."','".$_SESSION["authProvider"]."','".$userauthorized."',1,'".$service_code."','".$service_name."','".$comments."','".$pat_cat."','".$rate_cat."','".$rate."',NOW())");
      		  addForm($encounter, "Procedure", $newid, "procedure", $pid, $userauthorized);
    		}
    	}	
      
}elseif ($_GET["mode"] == "update") {


    $service_name = addslashes(json_encode($_POST['service_name']));
    $pat_cat = addslashes(json_encode($_POST['pat_cat']));
    $rate_cat = addslashes(json_encode($_POST['rate_cat']));
    $comments = addslashes(json_encode($_POST['comments']));
    $service_code = json_encode($_POST['service_code']);
    $rate = json_encode($_POST['rate']);
   
    if(check_code($service_code))
    {
    
    	if($service_name != "")
    	{
    
    		sqlInsert("update form_procedure set pid = {$_SESSION["pid"]},groupname='".$_SESSION["authProvider"]."',user='".$_SESSION["authUser"]."',authorized=$userauthorized,activity=1, date = NOW(), service_code = '".$service_code."', service_name = '". $service_name ."', comments = '".$comments."',pat_cat = '".$pat_cat."', rate_cat = '".$rate_cat."', rate = '". $rate ."' where id= $id ");
    	}
    }
    
    $sql = sqlStatement("SELECT service_code FROM form_procedure WHERE pid = ? AND id = ? ", array($pid, $id));
    while ($row = sqlFetchArray($sql))
    {
    	$test =  json_decode($row['service_code']);
    	if(!$test[0])
    	{
    		sqlQuery("DELETE FROM form_procedure WHERE pid = ? AND id = ?", array($pid, $id));
    		sqlQuery("DELETE FROM forms WHERE pid = ? AND form_id = ?", array($pid, $id));
    	}
      }
  
}
$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump("1");
formFooter();
?>
