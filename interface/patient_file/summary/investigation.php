<?php
/**
 * add or edit a investigation.
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

require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/lists.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');

$file = fopen("/tmp/test.txt","w");


$pid = $_SESSION['pid'];
$encounter = $_SESSION['encounter'];
$userid = $_SESSION["authUserID"];

//Additional Functions
//-------------------------------------------------------------------------------------------------------------------------------------------------
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

function check_update($inp)
{
	if($inp > 0)
	{
		return true;
	}
	else {
		return false;
	}
}

//-------------------------------------------------------------------------------------------------------------------------------------------------
  //Form save Start
//-------------------------------------------------------------------------------------------------------------------------------------------------
if ($_POST['form_save'] && $_SESSION['encounter'] != 0)
{
		//Deleting Services

	$lst_array = array('lab','inter','phy','img');
	$lst_cnt = count($lst_array);
	for ($i=0; $i < $lst_cnt; $i++)
	{
		$del_id = $_POST[$lst_array[$i] . '_delete_id'];
		$del_arr = explode(",",$del_id);

		if ($lst_array[$i] == 'lab')
		{
			$tbl_name = "rmc_lab";
		}
		elseif ($lst_array[$i] == 'inter')
		{
			$tbl_name = "rmc_intervention";
		}
		elseif ($lst_array[$i] == "phy")
		{
			$tbl_name = "rmc_physical";
		}
		elseif ($lst_array[$i] == 'img')
		{
			$tbl_name = "rmc_imaging";
		}
		else {
			$tbl_name = "";
		}

		foreach ($del_arr as $value) {

			sqlQuery("DELETE FROM " . $tbl_name . " WHERE pid = ? AND id = ?", array($pid, $value));
		}
	}

	// Lab data
	$lab_service_code = $_POST['lab_service_code'];
	$lab_service_name = $_POST['lab_test'];
	$lab_comments = $_POST['lab_test_code'];
	$lab_pat_cat = $_POST['lab_pat_cat'];
	$lab_rate_cat = $_POST['lab_rate_cat'];
	$lab_rate = $_POST['lab_rate'];
	$lab_update = $_POST['lab_update'];

	$lab_cnt = count($lab_service_code);


	for ($i=0; $i < $lab_cnt; $i++) {

		$service_code = add_escape_custom($lab_service_code[$i]);
		$service_name = add_escape_custom($lab_service_name[$i]);
		$comments = add_escape_custom($lab_comments[$i]);
		$pat_cat = add_escape_custom($lab_pat_cat[$i]);
		$rate_cat = add_escape_custom($lab_rate_cat[$i]);
		$rate = add_escape_custom($lab_rate[$i]);
		$update_id = add_escape_custom($lab_update[$i]);

		if(check_update($update_id)){

			if(check_code($service_code)){
				 
				if($service_name != ""){
				 
		
					sqlQuery("UPDATE rmc_lab SET pid = '".$pid."' ,encounter = '".$encounter."' ,service_code = '".$service_code."' ,service_name = '".$service_name."',comments = '".$comments."',pat_cat = '".$pat_cat."',rate_cat = '".$rate_cat."',rate  = '".$rate."' WHERE id = $update_id");
				}
			}

		}else {
			if(check_code($service_code))
			{
				if($service_name != ""){
					


					sqlInsert("INSERT INTO rmc_lab (createdat,pid,encounter,service_code,service_name,comments,pat_cat,rate_cat,rate) VALUES (NOW(), '".$pid."','".$encounter."','".$service_code."','".$service_name."','".$comments."','".$pat_cat."','".$rate_cat."','".$rate."')");
				}
			}
		}
	}


	// Intervention data
	$inter_service_code = $_POST['inter_service_code'];
	$inter_service_name = $_POST['inter_test'];
	$inter_comments = $_POST['inter_test_code'];
	$inter_pat_cat = $_POST['inter_pat_cat'];
	$inter_rate_cat = $_POST['inter_rate_cat'];
	$inter_rate = $_POST['inter_rate'];
	$inter_update = $_POST['inter_update'];

	$inter_cnt = count($inter_service_code);

	for ($i=0; $i < $inter_cnt; $i++) {

		$service_code = add_escape_custom($inter_service_code[$i]);
		$service_name = add_escape_custom($inter_service_name[$i]);
		$comments = add_escape_custom($inter_comments[$i]);
		$pat_cat = add_escape_custom($inter_pat_cat[$i]);
		$rate_cat = add_escape_custom($inter_rate_cat[$i]);
		$rate = add_escape_custom($inter_rate[$i]);
		$update_id = add_escape_custom($inter_update[$i]);

		if(check_update($update_id)){
			if(check_code($service_code))
			{
				if($service_name != ""){
					sqlQuery("UPDATE rmc_intervention SET pid = '".$pid."' ,encounter = '".$encounter."' ,service_code = '".$service_code."' ,service_name = '".$service_name."',comments = '".$comments."',pat_cat = '".$pat_cat."',rate_cat = '".$rate_cat."',rate  = '".$rate."' WHERE id = $update_id");
				}
			}
		}else {
			if(check_code($service_code))
			{
				if($service_name != ""){
					sqlInsert("INSERT INTO rmc_intervention (createdat,pid,encounter,service_code,service_name,comments,pat_cat,rate_cat,rate) VALUES (NOW(), '".$pid."','".$encounter."','".$service_code."','".$service_name."','".$comments."','".$pat_cat."','".$rate_cat."','".$rate."')");
				}
			}
		}
	}

	// Physical Exam
	$phy_service_code = $_POST['phy_service_code'];
	$phy_service_name = $_POST['phy_test'];
	$phy_comments = $_POST['phy_test_code'];
	$phy_pat_cat = $_POST['phy_pat_cat'];
	$phy_rate_cat = $_POST['phy_rate_cat'];
	$phy_rate = $_POST['phy_rate'];
	$phy_update = $_POST['phy_update'];

	$phy_cnt = count($phy_service_code);

	for ($i=0; $i < $phy_cnt; $i++) {

		$service_code = add_escape_custom($phy_service_code[$i]);
		$service_name = add_escape_custom($phy_service_name[$i]);
		$comments = add_escape_custom($phy_comments[$i]);
		$pat_cat = add_escape_custom($phy_pat_cat[$i]);
		$rate_cat = add_escape_custom($phy_rate_cat[$i]);
		$rate = add_escape_custom($phy_rate[$i]);
		$update_id = add_escape_custom($phy_update[$i]);

		if(check_update($update_id)){
			if(check_code($service_code))
			{
				
				if($service_name != ""){
					sqlQuery("UPDATE rmc_physical SET pid = '".$pid."' ,encounter = '".$encounter."' ,service_code = '".$service_code."' ,service_name = '".$service_name."',comments = '".$comments."',pat_cat = '".$pat_cat."',rate_cat = '".$rate_cat."',rate  = '".$rate."' WHERE id = $update_id");
				}
			}
		}else {
			if(check_code($service_code))
			{
				if($service_name != ""){
					sqlInsert("INSERT INTO rmc_physical (createdat,pid,encounter,service_code,service_name,comments,pat_cat,rate_cat,rate) VALUES (NOW(), '".$pid."','".$encounter."','".$service_code."','".$service_name."','".$comments."','".$pat_cat."','".$rate_cat."','".$rate."')");
				}
			}
		}
	}

	// Imaging
	$img_service_code = $_POST['img_service_code'];
	$img_service_name = $_POST['img_test'];
	$img_comments = $_POST['img_test_code'];
	$img_pat_cat = $_POST['img_pat_cat'];
	$img_rate_cat = $_POST['img_rate_cat'];
	$img_rate = $_POST['img_rate'];
	$img_update = $_POST['img_update'];

	$img_cnt = count($img_service_code);

	for ($i=0; $i < $img_cnt; $i++) {

		$service_code = add_escape_custom($img_service_code[$i]);
		$service_name = add_escape_custom($img_service_name[$i]);
		$comments = add_escape_custom($img_comments[$i]);
		$pat_cat = add_escape_custom($img_pat_cat[$i]);
		$rate_cat = add_escape_custom($img_rate_cat[$i]);
		$rate = add_escape_custom($img_rate[$i]);
		$update_id = add_escape_custom($img_update[$i]);

		if(check_update($update_id)){
			if(check_code($service_code))
			{
				if($service_name != ""){
					

					
					sqlQuery("UPDATE rmc_imaging SET pid = '".$pid."' ,encounter = '".$encounter."' ,service_code = '".$service_code."' ,service_name = '".$service_name."',comments = '".$comments."',pat_cat = '".$pat_cat."',rate_cat = '".$rate_cat."',rate  = '".$rate."' WHERE id = $update_id");
				}
			}
		}else {
			if(check_code($service_code))
			{
				if($service_name != ""){
					
	
					
					sqlInsert("INSERT INTO rmc_imaging (createdat,pid,encounter,service_code,service_name,comments,pat_cat,rate_cat,rate) VALUES (NOW(), '".$pid."','".$encounter."','".$service_code."','".$service_name."','".$comments."','".$pat_cat."','".$rate_cat."','".$rate."')");
				}
			}
		}
	}
	

	?>
  <script>
  window.top.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php?opener_save=true');
  </script>
  <?php
 
}

//Form Save End
//-------------------------------------------------------------------------------------------------------------------------------------------------


  $labres = sqlStatement("SELECT * FROM rmc_lab WHERE pid = ? AND encounter = ? ", array($pid, $encounter));
  $interres = sqlStatement("SELECT * FROM rmc_intervention WHERE pid = ? AND encounter = ? ", array($pid, $encounter));
  $phyres = sqlStatement("SELECT * FROM rmc_physical WHERE pid = ? AND encounter = ? ", array($pid, $encounter));
  $imgres = sqlStatement("SELECT * FROM rmc_imaging WHERE pid = ? AND encounter = ? ", array($pid, $encounter));

?>

<html>
	<head>
		<?php html_header_show();?>
		<title><?php echo " ".xlt('Investigation'); ?></title>
		<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
		<link rel="stylesheet" href='<?php echo $GLOBALS['webroot']; ?>/library/css/jquery.dialogbox.css' type='text/css'>
		<link rel="stylesheet" href='<?php echo $GLOBALS['webroot']; ?>/library/css/tab_container.css' type='text/css'>
		<link rel="stylesheet" href='<?php echo $GLOBALS['webroot']; ?>/library/css/jquery-ui-1.12.1.css' type='text/css'>
		<style type="text/css">@import url(<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.css);</style>
		
		<style>
			td, input, select, textarea { font-family: Arial, Helvetica, sans-serif; font-size: 10pt;}
			div.section { border: solid; border-width: 1px; border-color: #0000ff; margin: 0 0 0 10pt; padding: 5pt;}
			#code_list{float:left;list-style:none;margin:0;padding:0;width:400px;}
			#code_list li{padding: 10px; background:#FAFAFA;border-bottom:#F0F0F0 1px solid;}
			#code_list li:hover{background:#F0F0F0;}
			
			.dialog-box-content{ white-space: normal;}


			#parent4,#parent5,#parent6{position:fixed;top:0;left:0; background:#000;opacity:0.3;z-index:998;height:100%;width:100%;}
			#dialogBox4{height: 178px;margin-left:-500px;margin-top:-150px !important;width: 1000px;display:none;}
			#dialogBox5,#dialogBox6{height: 178px;margin-left:-500px;margin-top:-168px !important;width: 550px;display:none;}
			
			
			.lab_tr_clone td{padding:10px;}.lab_tr_clone .test{height:30px;}
			
			.img_tr_clone  td{padding:10px;} .img_tr_clone .test{height:30px;}
			
			.inter_tr_clone td{padding:10px;}.inter_tr_clone .test{height:30px;}
			
			.phy_tr_clone td{padding:10px;}.phy_tr_clone .test{height:30px;}
	
			.container{ width: auto !important; height: auto;min-height: 300px;}
			#fav_tabel_lab #favlist option:hover { background-color: lightskyblue;}
			.fav_div,#shadow_table { box-shadow: 0 8px 6px -6px black;}
			
			.ui-menu .ui-menu-item-wrapper { text-align:left;}

		</style>
		
		
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-ui-1.12.1.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.dialogBox.js"></script>
		<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/tab_container.js"></script>

</head>

<body class="body_top" style="padding-right:0.5em;">

		<div id="dialogBox4"></div>
		<div id="dialogBox5"></div>
		<div id="dialogBox6"></div>
<div id="main_body" >
	<form method='post' name='theform' action='investigation.php' onsubmit='return validate()'>
		<ul id="tabs" style="background: #167f92 none repeat scroll 0 0;">
		      <li><a href="#tab1">Laboratory</a></li>
		      <li><a href="#tab2">Imaging</a></li>
		      <li><a href="#tab3">Intervention</a></li>
		      <li><a href="#tab4">Physical Exam</a></li>
		 </ul>
 <!-- ------------------------------------------------------------------------------------------------------------------------------------------- -->		
		<div class="container" id="tab1" style="background: #f7f7f7 none repeat scroll 0 0;clear:both;overflow:hidden;margin-top:-12px;">
			<div style="width: 70%;float:left;">	  		
    			<table  width='100%' class='labtable' style="padding-left:5px;">
	      			<input type="hidden" id="lab_delete_id" name="lab_delete_id" class="delete_id" value="0">
	     	    <tr style="background: #167f92 none repeat scroll 0 0; color:white;height:35px;" >
	    	    	<th>Test Name</th>
			    	<th>Comments</th>
				   	<th>Action</th>
	    	    </tr>  
					<?php
				if (sqlNumRows($labres) < 1) 
				{
		  			$rowid = 0;
		 				?>
		    	<tr class="lab_tr_clone">
		      	    		
		      		<td valign='top' style="padding-top: 22px;width:350px;">
		      			<input type='text' size='50' name='lab_test[]' id='lab_test_<?php echo $rowid; ?>' class='test' style='width:100%;' autocomplete='off' value="" />
		      			     			
		     		</td>
		     		<td style="width:350px;">
		     			<textarea  name='lab_test_code[]' style='width:100%;' id='lab_test_code_<?php echo $rowid; ?>' class='test_code'></textarea>
		      		</td>
		     		<td style="width: 100px;">
		     		<div  style="padding-left: 15px;" >	   
		       			<input type="button" class="del" id="del" value="-">
		       			<input type="button" class="add" id="add" value="+">
		       		</div>	
		     		</td>
		
		      		<input type="hidden" name="lab_service_code[]" class="lab_service_code" id="lab_service_code" value="0">
		      		<input type="hidden" name="lab_pat_cat[]" class="lab_pat_cat" id="lab_pat_cat" value="0">
		      		<input type="hidden" name="lab_rate_cat[]" class="lab_rate_cat" id="lab_rate_cat" value="0">
		      		<input type="hidden" name="lab_rate[]" class="lab_rate" id="lab_rate" value="0">
		      		<input type="hidden" name="lab_update[]" id="lab_update" class="update_id" value="0">
		   		</tr>
			  		<?php
		 		} else 
				 		{
				 			$rowid = 0;
				    		while ($row = sqlFetchArray($labres)) 
				    		{
					    		?>
				    		<tr class="lab_tr_clone">
				      		
				      		 	<td valign='top' style="padding-top: 22px;width: 350px;"">
				      				<input type='text' size='50' name='lab_test[]' id='lab_test_<?php echo $rowid; ?>' class='test' style='width:100%;' autocomplete='off' value="<?php echo attr($row['service_name']) ?>" />
				      			</td>
					     		<td style="width:350px;"> 
				     				<textarea  name='lab_test_code[]' style='width:100%;' id='lab_test_code_<?php echo $rowid; ?>' class='test_code'><?php echo attr($row['comments']) ?></textarea>
				      			</td>
				     		
				     			<td style="width: 100px;">
				     				<div  style="padding-left: 15px;" >	   
				       					<input type="button" class="del" id="del" value="-">
				       					<input type="button" class="add" id="add" value="+">
				       			  </div>	
				     			</td>
				
				      			<input type="hidden" name="lab_service_code[]" class="lab_service_code" id="lab_service_code" value="<?php echo attr($row['service_code']) ?>">
				      			<input type="hidden" name="lab_pat_cat[]" class="lab_pat_cat" id="lab_pat_cat" value="<?php echo attr($row['pat_cat']) ?>">
				      			<input type="hidden" name="lab_rate_cat[]" class="lab_rate_cat" id="lab_rate_cat" value="<?php echo attr($row['rate_cat']) ?>">
				      			<input type="hidden" name="lab_rate[]" class="lab_rate" id="lab_rate" value="<?php echo attr($row['rate']) ?>">
				      			<input type="hidden" name="lab_update[]" id="lab_update" class="update_id" value="<?php echo attr($row['id']) ?>">
				   		   </tr>
				  			<?php
				  				$rowid++;
				   			}
				  		}
				   	?>
    			</table>
  	 </div>  
    <div style="float:right;width:15%;margin-right: 150px;">
	     	<div id="shadow_table" style=" background: #167f92 none repeat scroll 0 0;color: white;font-family: Arial,Helvetica,sans-serif;font-size: 12pt;font-weight: bold;height: 29px;padding-top: 10px;text-align: center;width:310px;"><span>Laboratory Favourite List</span></div>	   
			 <table class='fav_tabel_lab' id="fav_tabel_lab" ></table>
		 </div> 
	</div>
	
 <!-- ------------------------------------------------------------------------------------------------------------------------------------------- -->	
  <!-- ------------------------------------------------------------------------------------------------------------------------------------------- -->		
	<div class="container" id="tab2" style="background: #f7f7f7 none repeat scroll 0 0;clear:both;overflow:hidden;margin-top:-12px;">
			<div style="width: 70%;float:left;">	  		
    			<table  width='100%' class='imgtable' style="padding-left:5px;">
	      			<input type="hidden" id="img_delete_id" name="img_delete_id" class="delete_id" value="0">
	     	    <tr style="background: #167f92 none repeat scroll 0 0; color:white;height:35px;" >
	    	    	<th>Test Name</th>
			    	<th>Comments</th>
				   	<th>Action</th>
	    	    </tr>  
					<?php
				if (sqlNumRows($imgres) < 1) 
				{
		  			$rowid = 0;
		 				?>
		    	<tr class="img_tr_clone">
		      	    		
		      		<td valign='top' style="padding-top: 22px;width:350px;">
		      			<input type='text' size='50' name='img_test[]' id='img_test_<?php echo $rowid; ?>' class='test' style='width:100%;' autocomplete='off' value="" />
		      			     			
		     		</td>
		     		<td style="width:350px;">
		     			<textarea  name='img_test_code[]' style='width:100%;' id='img_test_code_<?php echo $rowid; ?>' class='test_code'></textarea>
		      		</td>
		     		<td style="width: 100px;">
		     		<div  style="padding-left: 15px;" >	   
		       			<input type="button" class="del" id="del" value="-">
		       			<input type="button" class="add" id="add" value="+">
		       		</div>	
		     		</td>
		
		      		<input type="hidden" name="img_service_code[]" class="img_service_code" id="img_service_code" value="0">
		      		<input type="hidden" name="img_pat_cat[]" class="img_pat_cat" id="img_pat_cat" value="0">
		      		<input type="hidden" name="img_rate_cat[]" class="img_rate_cat" id="img_rate_cat" value="0">
		      		<input type="hidden" name="img_rate[]" class="img_rate" id="img_rate" value="0">
		      		<input type="hidden" name="img_update[]" id="img_update" class="update_id" value="0">
		   		</tr>
			  		<?php
		 		} else 
				 		{
				 			$rowid = 0;
				    		while ($row = sqlFetchArray($imgres)) 
				    		{
					    		?>
				    		<tr class="img_tr_clone">
				      		
				      		 	<td valign='top' style="padding-top: 22px;width: 350px;"">
				      				<input type='text' size='50' name='img_test[]' id='img_test_<?php echo $rowid; ?>' class='test' style='width:100%;' autocomplete='off' value="<?php echo attr($row['service_name']) ?>" />
				      			</td>
					     		<td style="width:350px;"> 
				     				<textarea  name='img_test_code[]' style='width:100%;' id='img_test_code_<?php echo $rowid; ?>' class='test_code'><?php echo attr($row['comments']) ?></textarea>
				      			</td>
				     		
				     			<td style="width: 100px;">
				     				<div  style="padding-left: 15px;" >	   
				       					<input type="button" class="del" id="del" value="-">
				       					<input type="button" class="add" id="add" value="+">
				       			  </div>	
				     			</td>
				
				      			<input type="hidden" name="img_service_code[]" class="img_service_code" id="img_service_code" value="<?php echo attr($row['service_code']) ?>">
				      			<input type="hidden" name="img_pat_cat[]" class="img_pat_cat" id="img_pat_cat" value="<?php echo attr($row['pat_cat']) ?>">
				      			<input type="hidden" name="img_rate_cat[]" class="img_rate_cat" id="img_rate_cat" value="<?php echo attr($row['rate_cat']) ?>">
				      			<input type="hidden" name="img_rate[]" class="img_rate" id="img_rate" value="<?php echo attr($row['rate']) ?>">
				      			<input type="hidden" name="img_update[]" id="img_update" class="update_id" value="<?php echo attr($row['id']) ?>">
				   		   </tr>
				  			<?php
				  				$rowid++;
				   			}
				  		}
				   	?>
    			</table>
  	 </div>  
    <div style="float:right;width:15%;margin-right: 150px;">
	     	<div id="shadow_table" style=" background: #167f92 none repeat scroll 0 0;color: white;font-family: Arial,Helvetica,sans-serif;font-size: 12pt;font-weight: bold;height: 29px;padding-top: 10px;text-align: center;width:310px;"><span>Imaging Favourite List</span></div>	   
			 <table class='fav_tabel_radio' id="fav_tabel_radio" ></table>
		 </div> 
	</div>
 <!-- ------------------------------------------------------------------------------------------------------------------------------------------- -->	
 
   <!-- ------------------------------------------------------------------------------------------------------------------------------------------- -->		
	<div class="container" id="tab3" style="background: #f7f7f7 none repeat scroll 0 0;clear:both;overflow:hidden;margin-top:-12px;">
			<div style="width: 100%;float:left;">	  		
    			<table  width='100%' class='intertable' style="padding-left:5px;padding-right: 550px;">
	      			<input type="hidden" id="inter_delete_id" name="inter_delete_id" class="delete_id" value="0">
	     	    <tr style="background: #167f92 none repeat scroll 0 0; color:white;height:35px;" >
	    	    	<th>Test Name</th>
			    	<th>Comments</th>
				   	<th>Action</th>
	    	    </tr>  
					<?php
				if (sqlNumRows($interres) < 1) 
				{
		  			$rowid = 0;
		 				?>
		    	<tr class="inter_tr_clone">
		      	    		
		      		<td valign='top' style="padding-top: 22px;width:350px;">
		      			<input type='text' size='50' name='inter_test[]' id='inter_test_<?php echo $rowid; ?>' class='test' style='width:100%;' autocomplete='off' value="" />
		      			     			
		     		</td>
		     		<td style="width:350px;">
		     			<textarea  name='inter_test_code[]' style='width:100%;' id='inter_test_code_<?php echo $rowid; ?>' class='test_code'></textarea>
		      		</td>
		     		<td style="width: 100px;">
		     		<div  style="padding-left: 15px;" >	   
		       			<input type="button" class="del" id="del" value="-">
		       			<input type="button" class="add" id="add" value="+">
		       		</div>	
		     		</td>
		
		      		<input type="hidden" name="inter_service_code[]" class="inter_service_code" id="inter_service_code" value="0">
		      		<input type="hidden" name="inter_pat_cat[]" class="inter_pat_cat" id="inter_pat_cat" value="0">
		      		<input type="hidden" name="inter_rate_cat[]" class="inter_rate_cat" id="inter_rate_cat" value="0">
		      		<input type="hidden" name="inter_rate[]" class="inter_rate" id="inter_rate" value="0">
		      		<input type="hidden" name="inter_update[]" id="inter_update" class="update_id" value="0">
		   		</tr>
			  		<?php
		 		} else 
				 		{
				 			$rowid = 0;
				    		while ($row = sqlFetchArray($interres)) 
				    		{
					    		?>
				    		<tr class="inter_tr_clone">
				      		
				      		 	<td valign='top' style="padding-top: 22px;width: 350px;"">
				      				<input type='text' size='50' name='inter_test[]' id='inter_test_<?php echo $rowid; ?>' class='test' style='width:100%;' autocomplete='off' value="<?php echo attr($row['service_name']) ?>" />
				      			</td>
					     		<td style="width:350px;"> 
				     				<textarea  name='inter_test_code[]' style='width:100%;' id='inter_test_code_<?php echo $rowid; ?>' class='test_code'><?php echo attr($row['comments']) ?></textarea>
				      			</td>
				     		
				     			<td style="width: 100px;">
				     				<div  style="padding-left: 15px;" >	   
				       					<input type="button" class="del" id="del" value="-">
				       					<input type="button" class="add" id="add" value="+">
				       			  </div>	
				     			</td>
				
				      			<input type="hidden" name="inter_service_code[]" class="inter_service_code" id="inter_service_code" value="<?php echo attr($row['service_code']) ?>">
				      			<input type="hidden" name="inter_pat_cat[]" class="inter_pat_cat" id="inter_pat_cat" value="<?php echo attr($row['pat_cat']) ?>">
				      			<input type="hidden" name="inter_rate_cat[]" class="inter_rate_cat" id="inter_rate_cat" value="<?php echo attr($row['rate_cat']) ?>">
				      			<input type="hidden" name="inter_rate[]" class="inter_rate" id="inter_rate" value="<?php echo attr($row['rate']) ?>">
				      			<input type="hidden" name="inter_update[]" id="inter_update" class="update_id" value="<?php echo attr($row['id']) ?>">
				   		   </tr>
				  			<?php
				  				$rowid++;
				   			}
				  		}
				   	?>
    			</table>
  	 </div>  
  	 </div>
 <!-- ------------------------------------------------------------------------------------------------------------------------------------------- -->	
 <!-- ------------------------------------------------------------------------------------------------------------------------------------------- -->		
	<div class="container" id="tab4" style="background: #f7f7f7 none repeat scroll 0 0;clear:both;overflow:hidden;margin-top:-12px;">
			<div style="width: 100%;float:left;">	  		
    			<table  width='100%' class='phytable' style="padding-left:5px;padding-right: 550px;">
	      			<input type="hidden" id="phy_delete_id" name="phy_delete_id" class="delete_id" value="0">
	     	    <tr style="background: #167f92 none repeat scroll 0 0; color:white;height:35px;" >
	    	    	<th>Test Name</th>
			    	<th>Comments</th>
				   	<th>Action</th>
	    	    </tr>  
					<?php
				if (sqlNumRows($phyres) < 1) 
				{
		  			$rowid = 0;
		 				?>
		    	<tr class="phy_tr_clone">
		      	    		
		      		<td valign='top' style="padding-top: 22px;width:350px;">
		      			<input type='text' size='50' name='phy_test[]' id='phy_test_<?php echo $rowid; ?>' class='test' style='width:100%;' autocomplete='off' value="" />
		      			     			
		     		</td>
		     		<td style="width:350px;">
		     			<textarea  name='phy_test_code[]' style='width:100%;' id='phy_test_code_<?php echo $rowid; ?>' class='test_code'></textarea>
		      		</td>
		     		<td style="width: 100px;">
		     		<div  style="padding-left: 15px;" >	   
		       			<input type="button" class="del" id="del" value="-">
		       			<input type="button" class="add" id="add" value="+">
		       		</div>	
		     		</td>
		
		      		<input type="hidden" name="phy_service_code[]" class="phy_service_code" id="phy_service_code" value="0">
		      		<input type="hidden" name="phy_pat_cat[]" class="phy_pat_cat" id="phy_pat_cat" value="0">
		      		<input type="hidden" name="phy_rate_cat[]" class="phy_rate_cat" id="phy_rate_cat" value="0">
		      		<input type="hidden" name="phy_rate[]" class="phy_rate" id="phy_rate" value="0">
		      		<input type="hidden" name="phy_update[]" id="phy_update" class="update_id" value="0">
		   		</tr>
			  		<?php
		 		} else 
				 		{
				 			$rowid = 0;
				    		while ($row = sqlFetchArray($phyres)) 
				    		{
					    		?>
				    		<tr class="phy_tr_clone">
				      		
				      		 	<td valign='top' style="padding-top: 22px;width: 350px;"">
				      				<input type='text' size='50' name='phy_test[]' id='phy_test_<?php echo $rowid; ?>' class='test' style='width:100%;' autocomplete='off' value="<?php echo attr($row['service_name']) ?>" />
				      			</td>
					     		<td style="width:350px;"> 
				     				<textarea  name='phy_test_code[]' style='width:100%;' id='phy_test_code_<?php echo $rowid; ?>' class='test_code'><?php echo attr($row['comments']) ?></textarea>
				      			</td>
				     		
				     			<td style="width: 100px;">
				     				<div  style="padding-left: 15px;" >	   
				       					<input type="button" class="del" id="del" value="-">
				       					<input type="button" class="add" id="add" value="+">
				       			  </div>	
				     			</td>
				
				      			<input type="hidden" name="phy_service_code[]" class="phy_service_code" id="phy_service_code" value="<?php echo attr($row['service_code']) ?>">
				      			<input type="hidden" name="phy_pat_cat[]" class="phy_pat_cat" id="phy_pat_cat" value="<?php echo attr($row['pat_cat']) ?>">
				      			<input type="hidden" name="phy_rate_cat[]" class="phy_rate_cat" id="phy_rate_cat" value="<?php echo attr($row['rate_cat']) ?>">
				      			<input type="hidden" name="phy_rate[]" class="phy_rate" id="phy_rate" value="<?php echo attr($row['rate']) ?>">
				      			<input type="hidden" name="phy_update[]" id="phy_update" class="update_id" value="<?php echo attr($row['id']) ?>">
				   		   </tr>
				  			<?php
				  				$rowid++;
				   			}
				  		}
				   	?>
    			</table>
  	 </div>  
  	 </div>
 <!-- ------------------------------------------------------------------------------------------------------------------------------------------- -->	
 
 
 
    <div class="btn_div" style="background: #f7f7f7 none repeat scroll 0 0;padding:10px;padding-left: 30px;">
    	<input type='submit' name='form_save' id="form_save" value='<?php echo xla('Save'); ?>' />
    	<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />
     </div> 	
   
</form>
	
</div>


<script language='JavaScript'>


//On Load Activities
//--------------------------------------------------------------------------------------------

//Remove add buttons
$(document).ready(function(){

var table_array = ['labtable','imgtable','intertable','phytable'];

	for(x in table_array){
			
	   $('.'+table_array[x]).each(function(){
	
			  var table = $(this).attr('class');

	 		  $('.'+table+' tr').not(':last').find('.add').remove();
	
		   });
	}

	
});

//Close and Refresh
function closeme() {
 window.top.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php?opener_save=true');
}


//Loading Favourite Lists
$(document).ready(function(){
	
	  $.ajax({
	  type: "POST",
	  url: "../../../library/ajax/validate_favlist.php?action=get_favlist_laboratory",
	  dataype: 'html',
	  success: function(data){
	   $('.fav_tabel_lab').empty().append(data);
	  }
	  });

	  $.ajax({
		  type: "POST",
		  url: "../../../library/ajax/validate_favlist.php?action=get_favlist_radiology",
		  dataype: 'html',
		  success: function(data){
		   $('.fav_tabel_radio').empty().append(data);
		  }
		  });
	  
	});


//--------------------------------------------------------------------------------------------



//Favourite list Activities
//--------------------------------------------------------------------------------------------
function set_text()
{
  res = confirm("Please confirm to add Laboratory");
  if(res){
    var f = document.forms[0];
    var txt = f.favlist.options[f.favlist.selectedIndex].text;
    var cls = f.favlist.options[f.favlist.selectedIndex].getAttribute('class');
    var code = f.favlist.options[f.favlist.selectedIndex].getAttribute('data-code');
    f.favlist.selectedIndex = -1;


    addText(cls,txt,code);
  }
}

function set_text_img()
{
  res = confirm("Please confirm to add Imaging");
  if(res){
    var f = document.forms[0];
    var txt = f.favlist_img.options[f.favlist_img.selectedIndex].text;
    var cls = f.favlist_img.options[f.favlist_img.selectedIndex].getAttribute('class');
    var code = f.favlist_img.options[f.favlist_img.selectedIndex].getAttribute('data-code');
    f.favlist_img.selectedIndex = -1;

    addText(cls,txt,code);
  }
}

function addText(cls, txt, code){

  if($('.' + cls + '_tr_clone:last .test').val() == '')
	{
 
	    if (cls == 'lab') {
    	  service_type = "LABORATORY";
    	}else if (cls == 'inter') {
      	  service_type = "INTERVENTION";
    	}else if (cls == "phy") {
      	  service_type = "HEALTH PACKAGE";
    	}else if (cls == 'img') {
          service_type = "RADIOLOGY";
    	}else {
      	  service_type = "";
   		 }
	    var cnt = $('.' + cls + 'table>tbody>tr:visible').length;


	    service_age_Check(cls,code,cnt-1);
	    
	    $('.' + cls + '_tr_clone:last .test').val(txt);
	    $('.'+cls+'_tr_clone:last').find('.'+cls+'_service_code').val(code);
	    inves_validate(service_type, code, cls);
				    
  	}else {

  			var cnt = $('.' + cls + 'table>tbody>tr:visible').length;
  			service_age_Check(cls,code,cnt,1,txt);

  	   }
 
}

function addNewRow(cls,code,cnt) {
  var tbl_row = $('.' + cls + '_tr_clone:last');
  var tbl_clone = tbl_row.clone(true);
  tbl_clone.find('.test,.test_code').val('');
  tbl_row.find('.add').remove();
  tbl_row.after(tbl_clone);

  
  $('.' + cls + 'table>tbody>tr:last').find(".test").attr('id',  cls + '_testing_'+cnt);
  $('.' + cls + 'table>tbody>tr:last').find('.test_code').attr('id', cls + '_test_codeing_'+cnt).css('background-color', '').removeAttr("comments_validation");
  $('.' + cls + 'table>tbody>tr:last').find('.update_id').attr('value', '0');
  
  if (cls == 'lab') 
  {
     service_type = "LABORATORY";
  }else if (cls == 'inter') 
	{
      service_type = "INTERVENTION";
    }else if (cls == "phy") 
     {
       service_type = "HEALTH PACKAGE";
     }else if (cls == 'img') 
     {
       service_type = "RADIOLOGY";
  	 }else 
  	  {
    	service_type = "";
  		}

  $('.'+cls+'_tr_clone:last').find('.'+cls+'_service_code').val(code);
  inves_validate(service_type, code, cls);

  
 
}

function inves_validate(service_type, code, cls){
  $.ajax({
    type: "POST",
    url: "../../../library/ajax/validate_favlist.php?action=validate",
    data:'service_type='+ service_type +'&service_code='+code,
    dataype: 'json',
    async: false,
    success: function(data){
       var json = $.parseJSON(data);
       //for (var i in json) {
         var rate = json.rate;
         var rate_cat = json.rate_cat;
         var pat_cat = json.pat_cat;

         $('.'+cls+'_tr_clone:last').find( '.'+ cls + '_pat_cat').val(pat_cat);
         $('.'+cls+'_tr_clone:last').find( '.'+ cls + '_rate_cat').val(rate_cat);
         $('.'+cls+'_tr_clone:last').find( '.'+ cls + '_rate').val(rate);
      //}

    }
  });
}
//--------------------------------------------------------------------------------------------


//Jquery Events
//--------------------------------------------------------------------------------------------



//Add Button
var count = 0;
$(document).on('click','.add', function() {
	count=count+1;
   var inves_type = $($(this).closest('tr')).attr('class');
   inves_type = inves_type.split("_");
   inves_type = inves_type[0];

   var table_name = $(this).closest('tr').parent().parent().attr('class');

   table_name = $('.'+table_name);

   var row = table_name.find('tr:last').clone();
      row.find('.test').attr('id',inves_type+'_'+count);
      row.find('.test').val('');
      row.find('.test_code').val('');
      table_name.append(row);
      $(this).remove();
         

   var cnt = $('.' + inves_type + 'table>tbody>tr:visible').length; 

   $('.' + inves_type + 'table>tbody>tr:last').find(".test").eq(0).attr('id',  inves_type + '_test_'+cnt);
   $('.' + inves_type + 'table>tbody>tr:last').find('.test_code').eq(0).attr('id', inves_type + '_test_code_'+cnt).css('background-color', '').removeAttr("comments_validation");
   $('.' + inves_type + 'table>tbody>tr:last').find('.update_id').eq(0).attr('value', '0'); 
});


//Remove Button
$(document).on('click','.del', function() {

	 var inves_type = $($(this).closest('tr')).attr('class');
     var table = $(this).closest('.'+inves_type).parent().parent();
     var table_length =table.find('.'+inves_type).length;
	 var count = table_length;
	   
	res = confirm("Please confirm to remove service");
  	if(res){
				
				inves_type1 = inves_type.split("_");
      			inves_type1 = inves_type1[0];
				old_id = $('#' + inves_type1 + '_delete_id').val();
			    del_id = $(this).parent().parent().parent().find('#' + inves_type1 + '_update').val();
			   
			   	$('#' + inves_type1 + '_delete_id').val(old_id+','+del_id);
            
      			if(count == 1){

      				table.find('.'+inves_type+':first .test,.test_code').val('');
      				table.find('.'+inves_type+':first .test_code').css('background-color', '').removeAttr("comments_validation");
				    $(this).parent().parent().parent().find('#' + inves_type1 + '_update').val('');
	   				
          		 }
      			else{

      				
		      			$(this).closest('tr').remove();
					 	var add_button = $('<input></input>').attr({'type': 'button','class':'add','id':'add_main'}).val("+");
					 	var bt_add =table.find('tr:last td:last div');
					 	bt_add.find('.add').remove();
						add_button.appendTo(bt_add);
					     					    
			   }

			   count=count-1;
		   
   		}

 		
});


//Auto Compelete Search
 $(document).on('keydown','.test',function(){

    var id_name = $(this).attr('id');
    id_name = $('#'+id_name); 
    autocompletion(id_name);
	
}); 



//Remove background
 
 $(document).on('click','.test_code',function(){

 	$(this).css('background-color', '');
 });


//Form save

 $(document).on('click','#form_save',function(e) {
     var check_submit=0;
 	  var error_msg = "";

 	  $('.test_code').each(function(){

 		  if(this.value == '')
 		  {
 			  
 		    if($(this).hasClass('test_code'))
             {
                 var attr= $(this).attr('comments_validation')
 		   			   
    		  		if (typeof attr !== typeof undefined && attr !== false) 
 	 	      		{
  					    error_msg += " comments : Fill Proper Reason ! <br> ";
    				  	    $(this).css('background-color', '#fb7666');
    			 	 }
 			   
             } 

 		  }    
 	});

 	   if(error_msg){

 		     $(".body_top").prepend("<div id='parent6'></div>");
 			 $('#dialogBox6').dialogBox({
 					hasClose: false,
 					hasBtn: true,
 					confirmValue: 'OK',
 					confirm: function(){
 										
 							$('#parent6').remove();
 				 	},
 					title: 'The Following Fields are Mandatory:',
 					content: error_msg
 				});
			
 			 check_submit=1;

 			
  			if($('section#dialogBox6').css('display') == 'block')
  			{
  		
  				$('.dialog-btn-confirm').focus();
  			
  			}
 				
 				
 		       }
 		 
 		    if(check_submit)
 		      	 return false; 



 });

//--------------------------------------------------------------------------------------------


//Depenedency Functions
//--------------------------------------------------------------------------------------------

function autocompletion(id){

	 var currentRequest = null;
	 if (currentRequest != null) currentRequest.abort();

	 var inves_type = $(id.closest('tr')).attr('class');
	 inves_type = inves_type.split("_");
	 inves_type = inves_type[0];

	 if (inves_type == 'lab') {
	   service_type = "LABORATORY";
	 }else if (inves_type == 'inter') {
	   service_type = "INTERVENTION";
	 }else if (inves_type == "phy") {
	   service_type = "HEALTH PACKAGE";
	 }else if (inves_type == 'img') {
	   service_type = "RADIOLOGY";
	 }else {
	   service_type = "";
	 }
		

	 id.autocomplete({

		 	min_length: 1,

		    source: function(request, response) {
		        $.ajax({
		        	type: "POST",
		            url: "../../../library/ajax/auto_procedure_search.php",
		            dataType: "json",
		            data: {
		            	search_term : request.term,
		            	service_type : service_type,
		            	service_auto : 'auto'
		            },
		            success: function(data) {

		            	  var result = $.map(data, function (value, key) {
				            				  return {
				            					  label:value.service_text,
				            					  service_code : value.service_code,
				            					  rate :value.rate,
				            					  rate_cat : value.rate_cat,
				            					  pat_cat : value.pat_cat,
				            					
					            				  };
				            				  });
        				        response(result.slice(0,20));

				            				  
			            
		                                }
		              });
		    },
		    
		    select: function( event, ui ) {
	    		event.preventDefault();

	    		$(this).val(ui.item.label);
	  			var index = id.closest('tr').index();

	  			selectPro(ui.item.label,ui.item.service_code,ui.item.rate,ui.item.rate_cat,ui.item.pat_cat,index,inves_type);
	  			
            }
		    
		});
	 
 }


function selectPro(service_name,service_code,rate,rate_cat,pat_cat,cnt,inves_type) 
{

  $('.'+inves_type+'table tr').eq(cnt).find('.'+inves_type+'_service_code').val(service_code);
  $('.'+inves_type+'table tr').eq(cnt).find('.'+inves_type+'_rate').val(rate);	 
  $('.'+inves_type+'table tr').eq(cnt).find('.'+inves_type+'_rate_cat').val(rate_cat);	 
  $('.'+inves_type+'table tr').eq(cnt).find('.'+inves_type+'_pat_cat').val(pat_cat);	 
 

  if(inves_type == 'lab' || inves_type == 'img')
  {
    // Update favlist
    $.ajax({
    type: "POST",
    url: "../../../library/ajax/validate_favlist.php?action=update_favlist",
    data:'service_type='+ inves_type +'&service_code='+service_code+'&service_name='+service_name,
    dataype: 'json',
    success: function(data){
		    }
    	});
  	}

  service_age_Check(inves_type,service_code,cnt);
 
	
}


 function service_age_Check(inves_type,service_code,cnt,fav_list= false,fav_txt = false){
	 $.ajax({

			type:"POST",
		     url: "../../../library/ajax/auto_procedure_search.php",
			data:{'service_type':inves_type ,'service_code': service_code,'service_age':'service_age'},
		  ataype: 'json',
		 success: function(data){

			 if(data.length != 0)
		    	{	   
				 		var json = $.parseJSON(data);

						var service_name = json.service_name;
						var serviced_date = json.serviced_date;
						var serviced_days = json.days;
						var msg = "This <span class=\"color_pres\">"+service_name+"</span> Investigation  has already been requested on <span class=\"color_pres\"> "+serviced_date+"</span>.<br> <span> Do you Wish to Override This Service </span> ?";
						
						$(".body_top").prepend("<div id='parent4'></div>");
			 			$('#dialogBox4').dialogBox({
													  hasClose: false,
														hasBtn: true,
												  confirmValue: 'Yes',
													   confirm: function(){

														   					var tab_name = $('.'+inves_type+'table tr');
		                                                                     if(fav_list == 1){
		                                                                    	 addNewRow(inves_type,service_code,cnt);
		                                                           	    		 $('.' + inves_type + '_tr_clone:last .test').val(fav_txt);
		                                                           	    		 $('.' + inves_type + '_tr_clone:last .test_code').attr("comments_validation", true);
		                                                           	    		 $('.' + inves_type + '_tr_clone:last .test_code').css('background-color', '#fb7666').focus();
		                                                                    	
			                                                                }
				                                                        				                                                          
										   									tab_name.eq(cnt).find('.test_code').attr("comments_validation", true);
										   									tab_name.eq(cnt).find('.test_code').css('background-color', '#fb7666').focus();
																		    $('#parent4').remove();	
																				
				 												},
													cancelValue: 'No',
														 cancel: function(){
																			   var tab_name = $('.'+inves_type+'table tr');
															 					tab_name.eq(cnt).find('.test').val('');
																				tab_name.eq(cnt).find('.test_code').removeAttr("comments_validation");
																				$('#parent4').remove();
	                         					
																			}, 
														  title: 'Confirmation To Proceed',
														content: msg
													});

			 			if($('section#dialogBox4').css('display') == 'block')
			 			{
			 		
			 				$('.dialog-btn-confirm').focus();
			 			
			 			}
		 								
						
		    	}
			 	else if(fav_list == 1){

               	     addNewRow(inves_type,service_code,cnt);
      	    		 $('.' + inves_type + '_tr_clone:last .test').val(fav_txt);
               	
               }

 
			}

	});

	
}
/*  
 	$(document).on('keydown', function(e) { 	
	 	
		var keyCode = e.keyCode || e.which; 
       if (keyCode == 9 ||keyCode == 37 || keyCode == 38 || keyCode == 39 || keyCode == 40) { 
		      e.preventDefault(); 

		       if ($(".dialog-btn-confirm").is(":focus")) {
			       
		        $(".dialog-btn-cancel").focus();
		      } else {
		        $(".dialog-btn-confirm").focus();
		      } 
		    }

		});	  */
//--------------------------------------------------------------------------------------------






</script>
</body>
</html>
