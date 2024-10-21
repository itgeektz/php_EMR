<?php


/**
 * Custom Reports
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

// Disable magic quotes and fake register globals.
$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once('../globals.php');
  
    //echo "<pre>";print_r($_POST);exit();
  if(isset($_POST['notification_group']) )
  {
     sqlQuery("update globals  SET `gl_value`='".$_POST['notification_group']."' WHERE gl_name='notification_group'");

     echo "1";
  }
  else
  {
    echo "0";
  }
  exit;
?>
