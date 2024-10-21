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
//

require_once('../../interface/globals.php');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');

$search_term = $_REQUEST['search_term'];
$form_code_type = array("ICD10");
$response = array();

$res = main_code_set_search($form_code_type,$search_term,20,NULL,true,NULL,false,0,10);
while ($row = sqlFetchArray($res)) {

  //$tmp['code'] = $row['code_type_name'] . ":" . $row['code'];
  $tmp['code'] = $row['code'];
  $tmp['code_text'] = $row['code_text'];

  array_push($response, $tmp);
}

echo json_encode($response);

?>
