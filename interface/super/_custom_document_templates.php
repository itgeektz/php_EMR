<?php

/**
 * Custom Templates.
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
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');

if (!acl_check('admin', 'super')) die(htmlspecialchars(xl('Not authorized')));

$form_filename = strip_escape_custom($_REQUEST['form_filename']);

$templatedir = "$OE_SITE_DIR/custreport_documents/doctemplates";





if (!empty($_POST['bn_upload'])) {
  // Handle uploads.
  $tmp_name = $_FILES['form_file']['tmp_name'];
  if (is_uploaded_file($tmp_name) && $_FILES['form_file']['size']) {
    // Choose the destination path/filename.
    $size=$_FILES['form_file']['size'];
    $form_dest_filename = $_POST['form_dest_filename'];
    $groupd_id=$_REQUEST['temp_category'];
    if ($form_dest_filename == '') {
      $form_dest_filename = $_FILES['form_file']['name'];
    }
    $form_dest_filename = basename($form_dest_filename);
    if ($form_dest_filename == '') {
      die(htmlspecialchars(xl('Cannot determine a destination filename')));
    }
    
    $templatepath = "$templatedir/".$_FILES['form_file']['name'];
    // If the site's template directory does not yet exist, create it.
    if (!is_dir($templatedir)) {
      mkdir($templatedir);
    }
   
    // If the target file already exists, delete it.
    if (is_file($templatepath)) unlink($templatepath);
    // Put the new file in its desired location.
   
    if (move_uploaded_file($tmp_name, $templatepath)) {
    	 $templatepath = $web_root."/sites/default/custreport_documents/doctemplates/".$_FILES['form_file']['name'];
    	
    	sqlInsert("insert into custom_template_documents(name,size,path,template_group_id) values('". $form_dest_filename."', '".$size."', '".$templatepath."', '".$groupd_id."')");
    	
    }
    else
    		
      die(htmlspecialchars(xl('Unable to create') . " '$templatepath'"));
    
  }
}

?>



<html>

<head>
<title><?php echo xlt('Custom Template Management'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style type="text/css">
 .dehead { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }
 
</style>
   <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/custom_template/ckeditor/ckeditor.js"></script>
  
</head>
<body class="body_top">
<form method='post' action='custom_document_templates.php' enctype='multipart/form-data'
 onsubmit='return top.restoreSession()'>

<center>

<h2><?php echo xlt('Custom Template Management'); ?></h2>

</center>

<p>
<table border='1' width='95%'>

 <tr bgcolor='#dddddd' class='dehead'>
  <td align='center'><?php echo xlt('Upload a Template'); ?></td>
 </tr>

 <tr>

  <td valign='top' class='detail' style='padding:10pt;' nowrap>
  <?php echo htmlspecialchars(xl('Template Category')); ?>:
  <?php $sql=sqlStatement("select id,title from template_categories where status=1");?>
  <select name="temp_category" required>
  <option value=''>--Choose Category--</option>
  <?php 
  $html='';
  while($row=sqlFetchArray($sql))
  {
  	$html.='<option value="'.$row['id'].'">'.$row['title'].'</option>';
  }
  	echo $html;
  	
  	?>
  
  </select>
    &nbsp;<?php echo htmlspecialchars(xl('Source File')); ?>:
   <input type="hidden" name="MAX_FILE_SIZE" value="250000000" />
   <input type="file" name="form_file" size="40" accept="" />&nbsp;
   <?php echo htmlspecialchars(xl('Destination Filename')) ?>:
   <input type='text' name='form_dest_filename' size='30' />
   &nbsp;
   <input type='submit' name='bn_upload' value='<?php echo xlt('Upload') ?>' />
  </td>
 </tr>

</table>

</p>
</form>

</body>
</html>