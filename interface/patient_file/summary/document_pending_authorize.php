<?php
/**
 *
 * Patient summary screen.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady@sparmy.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

 require_once("../../globals.php");
  $srcdir=$GLOBALS['srcdir'];
require_once("$srcdir/authentication/common_operations.php");
 ////////////
 //echo "<pre>";print_r($_POST);exit;
  $status=0;
  
  $loginusername=$_SESSION['authUser'];
  $loginpass=$_POST['authpass'];
  $rs=confirm_user_password($loginusername,$loginpass);
 if($rs)
 {
  if(isset($_POST['req_doc_id']))
  {
    if(!empty($_POST['req_doc_id']))
    {
      $qry="update documents set authorize_status='1',authorize_by='".$_SESSION['authUserID']."',authorize_date=NOW() where authorize_status=0 AND id='".$_POST['req_doc_id']."' AND foreign_id='".$_SESSION['pid']."'";
      sqlQuery($qry);
      $status=1;
    }
    else
    {
      $status=0;
    }
  }
  else
  {
      $status=0;
  }
 }
 else
 {
    $status=2;
 }
  $url="Location: document_pending_list.php?status=".$status;
  header($url);
  exit;
?>
