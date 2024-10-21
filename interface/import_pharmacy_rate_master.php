<?php
/**
 * Import rate master csv
 *
 * Copyright (C) 2016-2017 ViSolve <services@visolve.com>
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

require_once('globals.php');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');

if (!acl_check('admin', 'super')) die(htmlspecialchars(xl('Not authorized')));

$rate_master = sqlStatement("SELECT * FROM pharmacy_rate_master");

if(isset($_REQUEST['func']) && $_REQUEST['func'] == 'array_to_csv_download'){
    array_to_csv_download();
}

function array_to_csv_download() {
  // output headers so that the file is downloaded rather than displayed
  header('Content-type: text/csv');
  header('Content-Disposition: attachment; filename="sample_pharmacy.csv"');

  // do not cache the file
  header('Pragma: no-cache');
  header('Expires: 0');

  // create a file pointer connected to the output stream
  $file = fopen('php://output', 'w');

  // send the column headers
  fputcsv($file, array('RateCategory', 'ItemCode', 'GenericName', 'ItemName', 'Price'));

  // Sample data. This can be fetched from mysql too
  $data = array(
      array('00000', '12344', 'Sample Name','Sample Test', '1000'),
  );

  // output each row of the data
  foreach ($data as $row)
  {
      fputcsv($file, $row);
  }

  exit();
}

?>
<html>

<head>
<title><?php echo xlt('Pharmacy Rate Master'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<script type="text/javascript" src="../library/js/jquery-1.6.4.min.js"></script>
<script type="text/javascript">
  $(document).ready(function(){
    $('.loader_container').css('display','none');
  });
  function load_div(){
    $('.loader_container').css('display','block');
    $('#rate_master_form').submit();
  }
</script>
<style>
.loader {
    border: 16px solid #f3f3f3; /* Light grey */
    border-top: 16px solid #3498db; /* Blue */
    border-radius: 50%;
    width: 120px;
    height: 120px;
    animation: spin 2s linear infinite;
    position: absolute;
    top: 45%;
    left: 45%;
    z-index: 100;
}

.loader_container{
    width: 100vw;
    height: 100vh;
    background: white;
    opacity: 0.3;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
</head>

<body class="body_top">
<div class="loader_container"><div class="loader"></div></div>
  <div>
  <span class="title">
    Import Pharmacy Rate Master
  </span>
</div>
  <a href="import_pharmacy_rate_master.php?func=array_to_csv_download" style="float: right;" class="css_button"><span>Sample CSV</span></a>
</br>
  <div id="report_parameters">
  </br>
<form method='post' id='rate_master_form' action='import_pharmacy_rate_master.php' enctype='multipart/form-data' onsubmit='return top.restoreSession()'>
  <?php echo xlt('Source File'); ?>:
  <input type="file" accept=".csv,.xlsx" name="form_upload" size="40" />&nbsp;

<input type='submit' onclick="load_div()" name='bn_save' value='<?php echo htmlspecialchars(xl('Upload')) ?>' />
</form>
</br>
</div>
<div id="report_results">
<table id="rate_master">
  <thead>
    <tr>
      <th>Rate Category</th>
      <th>Item Code</th>
      <th>Generic Name</th>
      <th>Item Name</th>
      <th>Price</th>
    </tr>
  </thead>
  <?php
  while ($row = sqlFetchArray($rate_master)) {
    echo "<r>";
    echo "<td>" . $row['rate_cat'] . "</td>";
    echo "<td>" . $row['item_code'] . "</td>";
    echo "<td>" . $row['generic_name'] . "</td>";
    echo "<td>" . $row['item_name'] . "</td>";
    echo "<td>" . $row['price'] . "</td>";
    echo "</tr>";
  }

  ?>
</table>
</div>
</body>

<?php

if (!empty($_POST['bn_save'])) {
  ?>
  <?php
    if('text/csv' == $_FILES['form_upload']['type'] || 'application/vnd.ms-excel' == $_FILES['form_upload']['type'] || 'application/x-csv' == $_FILES['form_upload']['type'] || 'application/csv' == $_FILES['form_upload']['type'] || 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' == $_FILES['form_upload']['type'] ){

        $deleterecords = "TRUNCATE TABLE pharmacy_rate_master"; //empty the table of its current records
        sqlQuery($deleterecords);

        $file = str_replace('\\','/',$_FILES['form_upload']['tmp_name']);
        $query = "LOAD DATA LOCAL INFILE '$file' IGNORE INTO TABLE pharmacy_rate_master FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' IGNORE 1 LINES  (@col1,@col2,@col3,@col4,@col5) set rate_cat=@col1,item_code=@col2,generic_name=@col3,item_name=@col4,price=@col5";
        sqlQuery($query);

        header('Location:import_pharmacy_rate_master.php');

      } else {
        echo "<script>alert('Please upload only CSV files');";
        echo "</script>";
      }


}


?>
