<?php
/**
 * API authentication module.
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
require_once('accesstoken.php');

if(isset($_REQUEST['username'])){ $uname = $_REQUEST['username']; }
if(isset($_REQUEST['password'])){ $pwd = $_REQUEST['password']; }

$response = array();
global $conn;

$globals_qry = "SELECT gl_name,gl_value FROM globals WHERE gl_name IN ('rmcapi_username','rmcapi_password')";

$globals_res = $conn->query($globals_qry);

if($globals_res->num_rows > 0){
   while($row = $globals_res->fetch_assoc()) {
    if($row['gl_name'] == 'rmcapi_username'){
      $api_username = $row['gl_value'];
    }elseif($row['gl_name'] == 'rmcapi_password'){
      $api_password = $row['gl_value'];
    }
  }
}

if($uname == $api_username){
  if($pwd == $api_password){

    $conn->query("TRUNCATE session");

    $accesstoken = generate_accesstoken();
    $session_qry = "INSERT INTO session (createdtime,expirytime,accesstoken) VALUES(NOW(),DATE_ADD(NOW(), INTERVAL 1 HOUR),'$accesstoken')";

    $conn->query($session_qry);

    $response['status'] = true;
    $response['accesstoken'] = $accesstoken;
    $response['msg'] = 'Success';

  }else{

    $response['status'] = false;
    $response['msg'] = 'Invalid password';

  }
}else {
  $response['status'] = false;
  $response['msg'] = 'Invalid username';
}

 exit(json_encode($response));
?>
