<?php
/**
 * add or edit a presciption.
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
*
* Notes : For Custom dialog box jquery.dialogBox.js and jquery.dialogBox.css
*
*/

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;

require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/lists.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
require_once($GLOBALS['srcdir'].'/csv_like_join.php');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');
require_once($GLOBALS['srcdir'].'/forms.inc');


$pid = $_SESSION['pid'];
$encounter = $_SESSION['encounter'];



// To get drug interval
//---------------------------------------------------------------------------------
function load_drug_attributes($id = 'drug_interval') {
	$res = sqlStatement("SELECT * FROM list_options WHERE list_id = '$id' ORDER BY seq");
	while ($row = sqlFetchArray($res))
	{
		if ($row['title'] == '')
		{
			$arr[$row['option_id']] = ' ';
		}
		else
		{
			$arr[$row['option_id']] = xl_list_label($row['title']);
		}
	}
	return $arr;
}
//---------------------------------------------------------------------------------


// To get list option by list id
//---------------------------------------------------------------------------------
function load_list_option_by_list_id($id = 'drug_interval') {

	$res = sqlStatement("SELECT list_id,option_id,title,seq,option_value,activity,notes FROM list_options WHERE list_id = '$id' ORDER BY seq");

	$arr=array();
	while ($row = sqlFetchArray($res))
	{
		$arr[]=$row;
	}
	return $arr;
}

//---------------------------------------------------------------------------------


//to check update
//---------------------------------------------------------------------------------
function check_update($inp) {
	if($inp > 0)
	{
		return true;
	}else {
		return false;
	}
}
//---------------------------------------------------------------------------------

//Save and update Rmc_prescriptions
//---------------------------------------------------------------------------------
if($_POST['form_save'] && $_SESSION['encounter'] != 0) {

	$updates_id =  implode(',', $_POST['update_id']);
	
if($_POST['delete_id']){
	
	//Deleting the provisional_diagonsis
	$del_id = $_POST['delete_id'];
	$del_arr = explode(",",$del_id);
    foreach ($del_arr  as $value) {
    	if(!empty($value)){
    		
    		sqlQuery("DELETE FROM forms WHERE pid = ? AND form_id = ?", array($pid, $value));
    		sqlQuery("DELETE FROM rmc_prescriptions WHERE pid = ? AND id = ?", array($pid, $value));
    	}
    		
    }
    
}


if($updates_id || $updates_id == '0' || $_POST['delete_id'] == ''){

    $cnt = count($_POST['update_id']);
    $id = 0;
    
	$drug_array=$_POST['form_drug'];
	$drug_code_array=$_POST['drug_code'];
	$item_name_array=$_POST['item_name'];
	$quantity_array=$_POST['form_quantity'];
	$dosage_array=$_POST['dosage'];
	$interval_array=$_POST['interval'];
	$notes_array=$_POST['notes'];
	$medication_array=$_POST['medication'];
	$bill_type_array=$_POST['bill_type'];
	$rate_array=$_POST['rate'];
	$rate_cat_array=$_POST['rate_cat'];
	$pat_cat=$_SESSION['pat_cat'];
	$update_array=$_POST['update_id'];
	$duration_array=$_POST['form_duration'];
	$duration1_array=$_POST['form_duration1'];
	//$notification_array=$_POST['notification'];

	for ($j=0; $j < $cnt; $j++)
	{
				$drug       = add_escape_custom($drug_array[$j]);
				$drug_code  = add_escape_custom($drug_code_array[$j]);
				$item_name  = add_escape_custom($item_name_array[$j]);
				$quantity   = add_escape_custom($quantity_array[$j]);
				$dosage     = add_escape_custom($dosage_array[$j]);
				$interval   = add_escape_custom($interval_array[$j]);
				$notes      = add_escape_custom($notes_array[$j]);
				$medication = add_escape_custom($medication_array[$j]);
		
				$medication_name="medication_".$j;
		
				if(isset($_POST[$medication_name]))
					$medication=1;
					else
						$medication=0;

				$bill_type  = add_escape_custom($bill_type_array[$j]);
				$rate       = add_escape_custom($rate_array[$j]);
				$rate_cat   = add_escape_custom($rate_cat_array[$j]);
				$update_id=add_escape_custom($update_array[$j]);
				$duration=add_escape_custom($duration_array[$j]);
				$duration1=add_escape_custom($duration1_array[$j]);
			//	$notification=add_escape_custom($notification_array[$j]);

				if(check_update($update_id))
				{

					//$res = sqlStatement("SELECT * FROM rmc_prescriptions");
					//while ($row = sqlFetchArray($res)) {
					//echo "<pre>";  print_r($row);
						//     }
						// exit;
						sqlQuery("UPDATE  rmc_prescriptions set pid = '".$pid."', encounter = ".$encounter.", drug = '".$drug."' , drug_code = '".$drug_code."', item_name = '".$item_name."', quantity ='".$quantity."',dosage = '".$dosage."', drug_interval ='".$interval."' , notes = '".$notes."', medication = '".$medication."', bill_type = '".$bill_type."' ,duration ='".$duration."', duration1 ='".$duration1."' WHERE id = $update_id");
				}
				else
				{
					
					if($drug_code)
					{		
						$new_id = sqlInsert("INSERT INTO rmc_prescriptions(pid,encounter,drug,drug_code,item_name,quantity,dosage,drug_interval,notes,medication,bill_type,rate,rate_cat,pat_cat,duration,duration1)VALUES ('".$pid."',".$encounter.", '".$drug."', '".$drug_code."', '".$item_name."', '".$quantity."', '".$dosage."', '".$interval."', '".$notes."','".$medication."', '".$bill_type."', '".$rate."', '".$rate_cat."', '".$pat_cat."', '".$duration."', '".$duration1."')");
						addForm($encounter, "Prescription", $new_id, "summary", $pid, $userauthorized);
					}	
				}

	}
	}
	?>

	<script type="text/javascript">
  		window.top.left_nav.loadFrame("dem1", window.name, "patient_file/summary/demographics.php?opener_save=true");
  	</script>
<?php
	
	
  }
  //---------------------------------------------------------------------------------
  
  //Saved By Pharmacist 
  //---------------------------------------------------------------------------------
  if($_POST['pharma_save'] && $_SESSION['encounter'] != 0) 
  {

  	$notifi_array=$_POST['notifi'];
  	$updat_array=$_POST['up_id'];
  	$cnts = count($_POST['up_id']);
  	for ($j=0; $j < $cnts; $j++)
  	{
  		$notifiy   = add_escape_custom($notifi_array[$j]);
  		$upd_id    = add_escape_custom($updat_array[$j]);
  		
  		sqlQuery("UPDATE  rmc_prescriptions set  notification ='".$notifiy."' WHERE id = $upd_id");
  	}
  ?>
  
  	<script type="text/javascript">
  		window.top.left_nav.loadFrame("dem1", window.name, "patient_file/summary/demographics.php?opener_save=true");
  	</script>
  
 <?php  
  }
  //---------------------------------------------------------------------------------
  
  
  //Empty Prescription Form
  //---------------------------------------------------------------------------------
   function prescription_fields_on_empty()
  {
  	$rowid = 0;
    $i=0;
  	$data="<tr class=\"tr_clone\">
 			<!--<td style=\"width: 3%; padding-left: 15px;\" class=\"add_auto\">.($i+1).</td> -->
  	        <td style=\"width: 18%\"><input type=\"text\" size=\"20\"  id=\"drug_1\" name=\"form_drug[]\"  class=\"form_drug\" autocomplete=\"off\" value='' style=\"width: 100%;\" />
 			
  	        </td>
  	        <td style=\"width: 7%\"><input type=\"text\" size=\"5\" name=\"dosage[]\" id='dosage_".$rowid."' class=\"dosage\" value='' style=\"width:100%\" />
  	        </td>
            <td style=\"width: 7%\"><select name=\"interval[]\" style=\"width: 100%;\" id='interval_".$rowid."' class=\"interval\">";
							  	            $drug_interval = load_drug_attributes();
  	           							    $sel='';
  	           								foreach ($drug_interval as $key => $value) {
  	             									$sel.="<option value='".$key."'>".$value."</option>";
  	           								}
  	           								$data.=$sel;
		
  	       							$data.=" </select>
  	        </td>
            <td style=\"width: 10%\">
								
							<div class=\"main\" style=\"width: 100%;\">
                				<div style=\"float:left;\">							
									<input type=\"text\" size=\"5\" name=\"form_duration[]\" id='form_duration_".$rowid."' class=\"form_duration\" value='' style=\"width:100%\" />
         						</div>
                 				<div style=\"float:left;\">
         							<select name='form_duration1[]' id='form_duration1_".$row['id']."' class='form_duration1'>
                      					<option value='day'>Day</option>
                      					<option value='month'>Month</option>
                      					<option value='year'>Year</option>
                 					</select>
							  </div>
							</div>	
            </td>
            <td style=\"width: 6%\"><input type=\"text\" size=\"5\" name=\"form_quantity[]\" id='form_quantity_".$rowid."' class=\"form_quantity\" value='' style=\"width:100%\" />
            </td>
  	        <td style=\"width: 19%\"><textarea name=\"notes[]\" id='notes_".$rowid."' class=\"notes\" style=\"width: 100%;\"></textarea></td>
  	        <td style=\"width: 8%; padding-left: 40px;padding-top:6px;\"><input type=\"checkbox\" name=\"medication\" class=\"medication\" id='medication_".$rowid."'  value=\"1\"></td>
  	        <td style=\"width:6%;padding-left: 35px;\"><label id='label_".$rowid."' class=\"label billtype\" name=\"label[]\" style=\"width: 100%;\"></label></td>
  	        <td  style=\"width: 7%;padding-left: 30px;\"><input type=\"button\" class=\"del\" id=\"del\" value=\"-\"></td>
  	        <input type=\"hidden\" name=\"drug_code[]\" class=\"drug_code rem\" id=\"drug_code\" value=\"0\">
  	        <input type=\"hidden\" name=\"item_name[]\" class=\"item_name rem\" id=\"item_name\" value=\"0\">
  	        <input type=\"hidden\" name=\"pat_cat[]\" class=\"pat_cat rem\" id=\"pat_cat\" value=\"0\">
  	        <input type=\"hidden\" name=\"bill_type[]\" class=\"bill_type rem\" id=\"bill_type\" value=\"0\">
  	        <input type=\"hidden\" name=\"rate_cat[]\" class=\"rate_cat rem\" id=\"rate_cat\" value=\"0\">
  	        <input type=\"hidden\" name=\"rate[]\" class=\"rate rem\" id=\"rate\" value=\"0\">
 	        <input type=\"hidden\" name=\"update_id[]\" id=\"update_id\" class=\"update_id rem\" value=\"0\">

  	      </tr>";
  	       return $data;
  }

  //---------------------------------------------------------------------------------

  // Prescription Form with Data From DB to Edit
  //---------------------------------------------------------------------------------
  function prescription_fields_on_data($res)
  {

  	$data='';
  	$i = 0;
  	while ($row = sqlFetchArray($res))
  	{
         $data.="<tr class=\"tr_clone\">
          			<!-- <td style=\"width: 3%;padding-left: 15px;\" class=\"add_auto\">".($i+1)."</td> -->
  	              	<td style=\"width: 18%\"><input type='text' size='20' name='form_drug[]' id=\"drug_1\"  class='form_drug' autocomplete='off' value='" . $row['drug'] . "' style='width: 100%;' />
	  	                 
  	                </td>
  	                <td style=\"width:7%\"><input type='text' size='5' name='dosage[]' id='dosage_".$row['id']."' class='dosage' value='".$row['dosage']."' style='width:100%' /></td>
                    <td style=\"width:7%\"><select name=\"interval[]\" id='interval_".$row['id']."' style=\"width: 100%;\" class=\"interval\">";

  	                   							$drug_interval = load_drug_attributes();
  	                   							$sel='';
  	                   							foreach ($drug_interval as $key => $value) {
	
  	                      							if($row['drug_interval']== $key)
  	                     								$sel.="<option value='".$key."' selected>". $value . "</option>";
  	                     							else
  	                     								$sel.="<option value='".$key."'>".$value."</option>";
  	                   							 }
  	                   							$data.=$sel;
						  	               $data.="</select>
  	                </td>
                    <td style=\"width: 10%;padding-left:10px;\">
										<div class=\"main\" style=\"width: 100%;\">
                							<div style=\"float:left;\">
												<input type='text' size='5' name='form_duration[]' id='form_duration_".$row['id']."' class='form_duration' value='".$row['duration']."'  style='width:100%' />
  											</div>
  											<div style=\"float:left;\">
                     							<select name='form_duration1[]' id='form_duration1_".$row['id']."' class='form_duration1'>";
                     								$sel='';
	                      							if($row['duration1']=='day') 
                        								$sel.="<option  value='day' selected>Day</option>";
                      								else
                        								$sel.="<option  value='day'>Day</option>";
                      								if($row['duration1']=='month') 
                        								$sel.="<option value='month' selected>Month</option>";
                      								else
                        								$sel.="<option value='month'>Month</option>";
                      								if($row['duration1']=='year') 
                        								$sel.="<option value='year' selected>Year</option>";
                      								else
                        								$sel.="<option value='year'>Year</option>";
                     									$data.=$sel;
                    						$data.=" </select>
            						</div>
            					</div>
                    </td>
                    <td style=\"width: 6%\"><input type='text' size='5' name='form_quantity[]' id='form_quantity_".$row['id']."' class='form_quantity' value='".$row['quantity']."'  style='width:100%' /></td>
  	                <td style=\"width: 19%\"><textarea name=\"notes[]\" id='notes_".$row['id']."' class=\"notes\" style=\"width: 100%;\">".$row['notes']."</textarea></td>
  	                <td style=\"width: 8%;padding-left: 40px;padding-top:6px;\">";
	  	                   $s='';
  		                   if($row['medication']==1)
  	    	                	$s.="<input type=\"checkbox\" name=\"medication\" class=\"medication\" id='medication_".$row['id']."'  value=\"1\" checked=\"true\">";
  		                	else
	  	                    	$s.="<input type=\"checkbox\" name=\"medication\" class=\"medication\" id='medication_".$row['id']."' value=\"1\">";
  	            	
	  	                  	$data.=$s;
  	                  $data.="</td>
  	                <td style=\"width:6%;padding-left:30px;\">";
		  	                 if($row['bill_type']==0)
  	    		             	$label="Cash";
  	                 		else
  	                 			$label='Credit';
	     	                 $data.="<label id='label_".$row['id']."' class=\"label billtype\" name=\"label[]\" style=\"width: 100%;\">".$label."</label></td>";
	     	           	       
                    $data.= "<td  style=\"width: 7%;padding-left:30px;\"><input type=\"button\" class=\"del\" id=\"del\" value=\"-\">
  	                </td>";
  	        		
  	        		 if(!empty($row['notification']))
  	        		 {
       		        	$data.="<td style=\"width: 13%;background: rgb(255, 255, 255) none repeat scroll 0% 0%; padding: 10px;\"><span id='notification_".$row['id']."' class=\"notification\" style=\"width: 100%;\">".$row['notification']."</span></td>";
  	        		 }       		        
                   $data.= "<input type=\"hidden\" name=\"drug_code[]\" class=\"drug_code rem\" id=\"drug_code\" value='".$row['drug_code']."'>
  	                <input type=\"hidden\" name=\"item_name[]\" class=\"item_name rem\" id=\"item_name\" value='".$row['item_name']."'>
  	                <input type=\"hidden\" name=\"pat_cat[]\" class=\"pat_cat rem\" id=\"pat_cat\" value='".$row['pat_cat']."'>
  	                <input type=\"hidden\" name=\"bill_type[]\" class=\"bill_type rem\" id=\"bill_type\" value='".$row['bill_type']."'>
  	                <input type=\"hidden\" name=\"rate_cat[]\" class=\"rate_cat rem\" id=\"rate_cat\" value=".$row['rate_cat'].">
  	                <input type=\"hidden\" name=\"rate[]\" class=\"rate rem\" id=\"rate\" value='".$row['rate']."'>
 	                <input type=\"hidden\" name=\"update_id[]\" id=\"update_id\" class=\"update_id rem\" value='".$row['id']."'>

  	              </tr>";
	 	              $i++;
  	              }
  	
  	              return $data;

  } 
  //---------------------------------------------------------------------------------
  ?>
  
<html>
	<head>
		<?php html_header_show();?>
			<title><?php echo xlt('Presciption'); ?></title>
			<link rel="stylesheet" href='<?php echo $GLOBALS['webroot']; ?>/library/css/jquery.dialogbox.css' type='text/css'>
				<link rel="stylesheet" href='<?php echo $GLOBALS['webroot']; ?>/library/css/jquery-ui-1.12.1.css' type='text/css'>
			<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
			
				<style>
						td, input, select, textarea {font-family: Arial, Helvetica, sans-serif;font-size: 10pt;}
						
						div.section {border: solid; border-width: 1px; border-color: #0000ff; margin: 0 0 0 10pt; padding: 5pt;}
						#prescription_list {float:left;list-style:none;margin:0;padding:0;width:400px;}
						#prescription_list li{padding: 10px; background:#FAFAFA;border-bottom:#F0F0F0 1px solid;}
						#prescription_list li:hover{background:#F0F0F0;}
						.billtype{color: #2e6bcc !important;font-weight: bold;}
						.billstatus1{color: green !important;font-weight: bold;}
						.billstatus0{color:  red !important;font-weight: bold;}
						.normal{width:60%;}
						
						.dialog-box-content{ white-space: normal;}
		
						#dialogBox8 > .dialog-box-container .dialog-btn-confirm{float: right !important; margin-right: 100px !important;margin-top:10px;}
						#parent1, #parent2, #parent3,#parent8 {position:fixed;top:0;left:0; background:#000;opacity:0.3;z-index:998;height:100%;width:100%;}
						#dialogBox1{height: 178px;margin-left:-500px;margin-top:-168px !important;width: 550px;display:none;}
						#dialogBox3{height: 178px;margin-left:-500px;margin-top:-150px !important;width: 1000px;display:none;}
					    #dialogBox2{height: 178px;margin-left:-500px;margin-top:-168px !important;width: 600px;display:none;}
					    #dialogBox8{height: 178px;margin-left: -900px;margin-top: -200px !important;;width: 1790px;display:none;}
					    #dialogBox8 .dialog-box-container .dialog-box-content{min-height:auto !important;max-height: 350px !important; overflow-y: auto !important;}
						.color_pres {color: #f50906;}
			   </style>
		 <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-1.9.1.min.js"></script>
		 <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-ui-1.12.1.js"></script>
		 <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
		 <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
		 <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery.dialogBox.js"></script>
  </head>

	<body class="body_top" style="padding-right:0.5em;">
		<?php
		//Prescription report
		//---------------------------------------------------------------------------------
			
  			if(isset($_REQUEST['report']))
  			{
				$prec=sqlStatement("SELECT p.*, l.title FROM rmc_prescriptions p left join list_options l on p.drug_interval=l.option_id WHERE p.pid = ? AND p.encounter = ? and l.list_id='drug_interval'", array($pid, $encounter));
  					?>
  				<form method='post' id="rmc_presc_pharma" name='pharma' action='rmc_prescription.php' >	
			  		<table border='0' width='100%' style="background: #f7f7f7 none repeat scroll 0 0; padding:15px;">
			    		<tr  style="background: #167f92 none repeat scroll 0 0; color:white;height:40px;" >
			    			<th style='text-align:center;font-size: 10pt;width:400px;'><?php echo xlt('Drugs'); ?></th>
					        <th style='text-align:center;font-size: 10pt;width:193px;'><?php echo xlt('No.of Tabs/Drops/Application'); ?></th>
					        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('FREQ'); ?></th>
					        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Duration'); ?></th>
					        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Quantity'); ?></th>
					        <th style='text-align:center;font-size: 10pt;width:255px;'><?php echo xlt('Any instruction to Pharmacist'); ?></th>
					        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Bill Type'); ?></th>
					        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Bill Status'); ?></th>
					        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Notification'); ?></th>
			    		</tr>
			      <?php
				      $jj=0;
			      		while ($row1 = sqlFetchArray($prec)) {
			        		$jj++;
			    	?>
			      <tr>
			        <!--  <td><?php echo $jj;?></td> -->
			        <td class="text"  ><?php echo $row1['drug'] ?></td>
			        <td class="text" style='text-align:center;'><?php echo $row1['dosage'] ?></td>
			        <td style='text-align:center;'><?php echo $row1['title'];?></td>
			        <td style='text-align:center;'> <?php echo $row1['duration']." ".$row1['duration1'];?></td>
			        <td class="text" style='text-align:center;'><?php echo $row1['quantity'] ?></td>
			        <td class="text" style='text-align:center;'><?php if(!empty($row1['notes'])) echo $row1['notes']; else echo "-"; ?></td>
			        <?php if($row1['bill_type']==0) {?>
			        <td class="text billtype" style='text-align:center;' >Cash</td>
			        <?php }
			         else {?>
			         <td class="text billtype" style='text-align:center;' >Credit</td>
			        <?php }?>
			        <?php if($row1['bill_status']==0) {?>
			        <td class="text billstatus0" style='text-align:center;' > Unbilled </td>
			        <?php }
			        else {?> <td class="text billstatus1" style='text-align:center;' > Billed </td>
			        <?php }?>
			  			<td class="text" style='text-align:center;width: 250px;'><?php echo $row1['notification']; ?></td>
			    
			     </tr>
			    <?php
			      }
			    ?>
			  </table>
	
  		</form>
  
	 <?php
	 }
	 //---------------------------------------------------------------------------------
	 
	 //Prescription Form
	 //---------------------------------------------------------------------------------
	else { ?>
  
  		<form method='post' id="rmc_presc" name='theform' action='rmc_prescription.php' style="background: #f7f7f7 none repeat scroll 0 0; padding-top: 15px;" >
  	  		<table border='0' width='100%' class='tbl_pres' style="padding:10px;">
		      <tr class='head' style="background: #167f92 none repeat scroll 0 0; color:white;height:40px;">
		      <!--   <th style='text-align:left;font-size: 10pt; padding-left: 10px;'><?php// echo xlt('Sl No'); ?></th>  -->
		        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Drugs'); ?></th>
		        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('No.of Tabs/Drops/Application'); ?></th>
		        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('FREQ'); ?></th>
		        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Duration'); ?></th>
		        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Quantity'); ?></th>
		        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Any instruction to Pharmacist'); ?></th>
		        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Add to Medication List'); ?></th>
		        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Bill Type'); ?></th>
		        <th style='text-align:center;font-size: 10pt;'><?php echo xlt('Actions'); ?></th>
		        <th style='text-align:center;font-size: 10pt;display:none;' id="notify"><?php echo xlt('Notification'); ?></th>
      		</tr>
      
     		 <?php
     		 
     		
     		 
      		$rmc_res = sqlStatement("SELECT * FROM rmc_prescriptions WHERE pid = ? AND encounter = ? ", array($pid, $encounter));
		     if (sqlNumRows($rmc_res) < 1) {
		
		        	//last two encounters
        			$list_of_encounter='';
        			$lis_enc=sqlStatement("select distinct(encounter) from rmc_prescriptions where pid=? and encounter < ? order by encounter desc limit 2 " ,array($pid,$encounter));
        			while($r_enc=sqlFetchArray($lis_enc))
        			{
           					if(empty($list_of_encounter))
           						$list_of_encounter=$r_enc['encounter'];
           					else
           						$list_of_encounter.=", ".$r_enc['encounter'];
        			}
        
        			
        			
        			if(!empty($list_of_encounter)){
        				
        				$last_pres=sqlStatement("select pre.id,pre.pid,pre.encounter,pre.drug,pre.drug_code,pre.item_name,pre.quantity,pre.dosage,pre.drug_interval,pre.notes,pre.medication,pre.bill_status,pre.bill_type,pre.rate,pre.rate_cat,pre.pat_cat,pre.duration,pre.duration1,pre.notification,MAX(en.date)as max_date from rmc_prescriptions as pre inner join form_encounter as en on pre.encounter = en.encounter where pre.pid=$pid and pre.medication = 1 and pre.encounter in (".$list_of_encounter.") group by pre.drug_code");
        				if(sqlNumRows($last_pres) > 0)
        				{
        					$i = 0;
        					$j=0;
        				while ($row = sqlFetchArray($last_pres)) 
        				{  
        			  	?>
              				<tr class="tr_clone">
            	    			<td style="width: 18%">
                 					<input type='text' size='20'  name='form_drug[]' autocomplete="off" id="drug_"<?php echo $i+1; ?> class='form_drug'  value='<?php echo $row['drug'];?>' style='width: 100%;' />
                   					<!-- div id="suggesstion-box" class='suggesstion-box suggesstion-hide' style="position: absolute;"></div> -->
                				</td>
                   				<td style="width:7%"><input type='text' size='5' name='dosage[]' id='dosage_<?php echo $row['id'];?>' class='dosage' value='<?php echo $row['dosage'];?>' style='width:100%' /></td>
                                <td style="width:7%"><select name="interval[]" id="interval_<?php echo $row['id'];?>" style="width: 100%;" class="interval">
                                                   <?php
                   										$drug_interval = load_drug_attributes();
                   										foreach ($drug_interval as $key => $value) {
                   											if($row['drug_interval']== $key)
                     											echo "<option value='$key' selected>" . $value . "</option>";
                     										else
                     											echo "<option value='$key' >" . $value . "</option>";
                   										}
                   									?>
                									</select>
                				</td>
                				<td style="width: 10%;">
                									<div class="main" style="width: 100%;">
                										<div style="float:left;">							
                										<input type="text" size="5" name="form_duration[]" id='form_duration_<?php echo $row['id']; ?>' class="form_duration" value='<?php echo $row['duration'];?>' style="width:100%" />
                										</div>
                 										
                 									<div style="float:left;">
                 										  <select name='form_duration1[]' id='form_duration1_<?php echo $row['id']; ?>' class='form_duration1'>
                      										<option value='day' <?php if($row['duration1']=='day') echo "selected"; ?>>Day</option>
                      										<option value='month' <?php if($row['duration1']=='month') echo "selected"; ?>>Month</option>
									                        <option value='year' <?php if($row['duration1']=='year') echo "selected"; ?>>Year</option>
                 											</select>
                 										</div>
                 									</div>	
            					</td>
                				<td style="width: 6%"><input type='text' size='5' name='form_quantity[]' id='form_quantity_<?php echo $row['id']; ?>' class='form_quantity' value='<?php echo $row['quantity']; ?>' style='width:100%' />
                				</td>
                				<td style="width: 19%"><textarea name="notes[]" id="notes_<?php echo $row['id'];?>" class="notes" style="width: 100%;"><?php echo $row['notes'];?></textarea></td>
                				<td style="width: 8%; padding-left: 40px;padding-top:6px;"><input type="checkbox"  name="medication" class="medication" id='medication_<?php echo $row['id'];?>'  value="1" <?php if($row['medication']==1) echo "checked='true'";?>></td>
				                <td style="width:6%;padding-left:30px;">
						                <?php if($row['bill_type']==0)?><label id="label_<?php echo $row['id'];?>" class="label billtype" name="label[]" style="width: 100%;"><?php if($row['bill_type']==0) echo "Cash"; else echo "Credit"?></label>
                				</td>
                				
                				<td  style="width: 7%;padding-left: 30px;"><input type="button" class="del" id="del" value="-">
                				</td>
                				
                				<?php if($row['notification']){?>
                 					<td style="width: 13%;background: rgb(255, 255, 255) none repeat scroll 0% 0%; padding: 10px;"><span id='notification_".$rowid."' class="notification" style="width: 100%;"><?php echo $row['notification'];?></span></td>
                 				<?php }?>
                				
                				<input type="hidden" name="drug_code[]" class="drug_code rem" id="drug_code" value="<?php echo $row['drug_code'];?>">
                				<input type="hidden" name="item_name[]" class="item_name rem" id="item_name" value="<?php echo $row['item_name'];?>">
                				<input type="hidden" name="pat_cat[]" class="pat_cat rem" id="pat_cat" value="<?php echo $row['pat_cat'];?>">
                				<input type="hidden" name="bill_type[]" class="bill_type rem" id="bill_type" value="<?php echo $row['bill_type'];?>">
                				<input type="hidden" name="rate_cat[]" class="rate_cat rem" id="rate_cat" value="<?php echo $row['rate_cat'];?>">
                				<input type="hidden" name="rate[]" class="rate rem" id="rate" value="<?php echo $row['rate'];?>">
				                <input type="hidden" name="update_id[]" id="update_id" class="update_id rem" value="0">

              			</tr>
         		     <?php
		             $i++;
        		     }
        		     ?>
        		     <script type="text/javascript">
        		      
        		     medication_list_age_check();
        		     	
        		     </script>
        		     <?php 	 		     
        		            		     
        		     
           			}           //no prescriptions for last two encounters of patient
              		else {
			                   $empty_fields=prescription_fields_on_empty();
      	            			echo $empty_fields;
		                  }
      			}        		 //No entry for patient in prescriptions
            	else
	                {
                			$empty_fields=prescription_fields_on_empty();
      	            		echo $empty_fields;
                	}
      	}
		 else {
         		   $data_fields=prescription_fields_on_data($rmc_res);
            		echo $data_fields;
       		}
   ?>
</table>
  <div class="btn_div" style="background: #f7f7f7 none repeat scroll 0 0;padding:10px;">
    	<input type='submit' name='form_save' id="form_save" value='<?php echo xla('Save'); ?>' />
    	<input type='button' value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />
    	<input type="hidden" id="delete_id" name="delete_id" class="delete_id" value="0">
   </div> 	
   </form>

 <?php 
	
}

//---------------------------------------------------------------------------------
?>



<div id="dialogBox1"></div>
<div id="dialogBox2"></div>
<div id="dialogBox3"></div>
<div id="dialogBox8"></div>

</body>

<?php
  	$Abrevtn=load_list_option_by_list_id('drug_interval');
   	$script = "\nvar Abrevtn = new Array();\n var AbrevtnNotes = new Array();";
   	if (count($Abrevtn)>0) {
     foreach ($Abrevtn as $key => $value)
     {
          $script.= "\n Abrevtn[".$value['option_id']."] =".$value['option_value'].";";
          $script.= "\n AbrevtnNotes[".$value['option_id']."] ='".$value['notes']."';";
     }
   }
 ?>

<script language='JavaScript'>

//On Load Activities
//------------------------------------------------------------------------------------------------------------------

//Closes and Referesh
function closeme() {
		window.top.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php?opener_save=true');
}


var past_encounter_set = false;

<?php echo  $script; ?>

	//adding ids and name for medicaion list then check for notification fields not empty
	//-----------------------------------------------------------------------------------------------
	var tables =$( ".tbl_pres .medication" );
	var len=tables.length;
 	for(i=0;i<len;i++)
  	{
      	$(tables[i]).attr('id',"medication_"+i);
      	$(tables[i]).attr('name',"medication_"+i);
  	}
	
		if($('.notification').text() != '' )
   		$('#notify').show();



 	
 	//autocomplete for pastencounters with medication list added  and show add row button
 	//------------------------------------------------------------	
		var $table;
		$table=$('.tbl_pres');
		var count=$('.tbl_pres tr').length;

		
		$('.form_drug').autocomplete(autocompleteOptions);
  
		var tbl_row = $('.tbl_pres tr ').eq(count-1);
	    $('<input></input>').attr({'type': 'button','class':'add','id':'add_main'}).val("+").appendTo(tbl_row.find('td:nth-child(9)'));
	    
	 	$(".tbl_pres").on('click', '.add', function () {

		 	var obj = $(this);
			    	addRow(obj);
       });
		
	//------------------------------------------------------------	 	
	   

	var autocompleteOptions;

//AutoCompletion query build
//------------------------------------------------------------
autocompleteOptions = {

		 source: function( request, response ) {
			 
			      $.ajax({
			        url: "../../../library/ajax/auto_prescriptions_search.php",
			        dataType: "json",
			        data: {
			        	search_term: request.term,'pres_auto' :'pres_auto'
			        },
			        success: function( data ) {

			        response($.map(data, function (value, key) {
							              return {
							                  label:value.drug,
							                  drug_name:value.drug,
							                  drug_code:value.drug_code,
							          		  rate:value.rate,
		     								  rate_cat:value.rate_cat,
		   								  	  pat_cat:value.pat_cat,
		     								  bill_type:value.bill_type,
		     								  item_name:value.item_name,
		     								  item_price:value.price
		     								
							              };
							          }));

									} 

					      });
		     
			  		  },

			    minLength: 1,
			    select: function( event, ui ) {
			    		event.preventDefault();
			  			$(this).val(ui.item.label);
			  			var tble_row = $(this).parent().parent();
						if(ui.item.bill_type==1)
							{
			  				ui.item.bill_type_value=" Credit ";
			  				ui.item.bill_class="credit";
								
							}
							else
							{
								ui.item.bill_type_value=" Cash ";
								ui.item.bill_class="credit";
							
							}
					

			  			selectPres(tble_row,ui.item.drug_name,ui.item.drug_code,ui.item.rate,ui.item.rate_cat,ui.item.pat_cat,ui.item.bill_type,ui.item.bill_type_value,ui.item.item_name,ui.item.item_price)
			  			
			  		     },
			    open: function() {
			               // Do something on open event.
			    },
			    close: function() {

				    				
			             // Do something on close event
			   }

	 }   
	
	
	
	//-----------------------------------------------------------------------------------------------------------	
 	
//Jquery Events
//---------------------------------------------------------------------------------------------------------------

	//Adding New Rows
 	//------------------------------------------------------------		
		
   function addRow(obj){

				obj.remove();
				var $row = $table.find('tr:last').clone();
				$($row).each(function(){
					$(this).find(':text').val('').css('background-color', '');
					$(this).find('.notes').val('').css('background-color', '').removeAttr("notes_validation");
					$(this).find('.rem').val('');
					$(this).find('.interval').prop('selectedIndex',0).css('background-color', '');
					$(this).find('.form_duration1').prop('selectedIndex','day').css('background-color', '');
					$(this).find('.medication').prop('checked',false);
					$(this).find('.label').empty();
					$(this).find('.dosage').val('');
					$(this).find('.notification').parent().css('background','');
					$(this).find('.notification').remove();
				   });

				$row.find('.form_drug').autocomplete(autocompleteOptions);
				$table.append($row);
				var table =$( ".tbl_pres .medication" );
				var len=table.length;
					for(i=0;i<len;i++)
					{
			  		$(table[i]).attr('id',"medication_"+i);
			  		$(table[i]).attr('name',"medication_"+i);
					}

				var tbl_row = $('.tbl_pres tr');
				var count =tbl_row.length;
				var add_button = $('<input></input>').attr({'type': 'button','class':'add','id':'add_main'}).val("+");
				var bt_add = tbl_row.eq(count-1).find('td:nth-child(9)');
				bt_add.find('.add').remove();
				add_button.appendTo(bt_add);
    
	
					
	}

	//------------------------------------------------------------	
	
	//Deleting Existing Rows
 	//------------------------------------------------------------		
		
	
	var last_delete = true; 
	
    $(".tbl_pres").on('click', '.del', function () {
		
    	var count = $('.tr_clone').length;
		var b = count;
	    res = confirm("Please confirm to remove prescription");
	    if(res){
    		  if(count) 
		 	  {

    				old_id = $('#delete_id').val();
    			    del_id = $(this).parent().parent().find('#update_id').val();
		     	    $('#delete_id').val(old_id+','+del_id);

					    if(b == 1){

					    	last_delete = false;
							$('.tbl_pres tr').eq(1).find('.form_drug,.dosage,.form_quantity,.notes,.notification' ).val('');
					     	$('.tbl_pres tr').eq(1).find('.form_duration' ).val('');
							$('.tbl_pres tr').eq(1).find('.interval').prop('selectedIndex',0);
							$('.tbl_pres tr').eq(1).find('.interval').prop('selectdIndex','day');
							$('.tbl_pres tr').eq(1).find('.medication').prop('checked',false);
							$('.tbl_pres tr').eq(1).find('.notes').css('background-color', '#fff');	
							$('.tbl_pres tr').eq(1).find('.notes').removeAttr("notes_validation",true); 
							$('.tbl_pres tr').eq(1).find('td:nth-child(10)').remove();
							$('.tbl_pres tr').eq(1).find('.label').remove();

					     	$('.tbl_pres tr').eq(1).find('.drug_code' ).val('');
					     	$('.tbl_pres tr').eq(1).find('.item_name' ).val('');
					     	$('.tbl_pres tr').eq(1).find('.pat_cat' ).val('');
					     	$('.tbl_pres tr').eq(1).find('.bill_type' ).val('');
					     	$('.tbl_pres tr').eq(1).find('.billtype' ).val('');
					     	$('.tbl_pres tr').eq(1).find('.rate_cat' ).val('');
					     	$('.tbl_pres tr').eq(1).find('.rate' ).val('');
					    	$('.tbl_pres tr').eq(1).find('.update_id' ).val('0');

					    	 
						}
						else{

							$(this).closest('tr').remove();

							var tbl = $('.tbl_pres tr');
						 	var count = tbl.length;
						 	var add_button = $('<input></input>').attr({'type': 'button','class':'add','id':'add_main'}).val("+");
						 	var bt_add =tbl.eq(count-1).find('td:nth-child(9)');
						 	bt_add.find('.add').remove();
							add_button.appendTo(bt_add);
		
	  					}	

			      		var table =$( ".tbl_pres .medication" );
		        		var len=table.length;
		        		for(i=0;i<len;i++)
		        		{
		            		$(table[i]).attr('id',"medication_"+i);
		            		$(table[i]).attr('name',"medication_"+i);
		        		}
		        	b = b-1;	

	        	
	   		}
	 			 		  
	 }
	});

		//------------------------------------------------------------	
		
  	//drugs dosage calculation
  	//------------------------------------------------------------	
  $('.tbl_pres').on('change','.form_duration1, .form_duration, .dosage', function() {

      var tbl_row = $(this).closest('.tr_clone');
      var dosage=tbl_row.find('.dosage').val().trim();
      var duration1=tbl_row.find('.form_duration1').val().trim();
      var duration=tbl_row.find('.form_duration').val().trim();
      var interval=tbl_row.find('.interval').val().trim();
      if(dosage=='' || dosage=='0')
      {
        //alert('please enter No.of Tabs/Drops/Application');
        tbl_row.find('.dosage').val('');
        tbl_row.find('.dosage').focus();
        return false;
      }
      if(interval=='' || interval=='0')
      {
        //alert('please enter Abbriviation');
        tbl_row.find('.interval').val('');
        tbl_row.find('.interval').focus();
        return false;
      }
      if(duration=='' || duration=='0')
      {
        //alert('please enter duration');
        tbl_row.find('.form_duration').val('');
        tbl_row.find('.form_duration').focus();
        return false;
      }
      
      var days=0;


      if(duration1=='day')
        days=duration;
      else if(duration1=='month')
        days=parseInt(duration)*30;
      else if(duration1=='year')
        days=parseInt(duration)*365;

      
      var tqty=days*parseFloat(dosage)*parseInt(Abrevtn[interval]);
      var dosage=tbl_row.find('.form_quantity').val(Math.round(tqty));

  });

  
    $('.tbl_pres').on('change','.interval', function() {
       var tbl_row = $(this).closest('.tr_clone');
       var FREQ=$(this).val();
       var FREQ1=$(this).val();
       FREQ=parseInt(Abrevtn[FREQ]);
      /*  var Notes=AbrevtnNotes[FREQ1];
      //  tbl_row.find('.dosage').val(FREQ);
         tbl_row.find('.notes').val(Notes); */
        

        //for cal qty start
          var tbl_row = $(this).closest('.tr_clone');
      var dosage=tbl_row.find('.dosage').val().trim();
      var duration1=tbl_row.find('.form_duration1').val().trim();
      var duration=tbl_row.find('.form_duration').val().trim();
      if(dosage=='' || dosage=='0')
      {
        //alert('please enter No.of Tabs/Drops/Application');
        tbl_row.find('.dosage').val('');
        tbl_row.find('.dosage').focus();
        return false;
      }
      if(duration=='' || duration=='0')
      {

       // alert('please enter duration');
        tbl_row.find('.form_duration').val('');
        tbl_row.find('.form_duration').focus();
        return false;
      }
      

      var days=0;
      if(duration1=='day')
        days=duration;
      else if(duration1=='month')
        days=parseInt(duration)*30;
      else if(duration1=='year')
        days=parseInt(duration)*365;

   
      var tqty=days*parseFloat(dosage)*FREQ;
      var dosage=tbl_row.find('.form_quantity').val(Math.round(tqty));

        //end
        
   });
  //------------------------------------------------------------	
    
  //Only accept Numbers
  //------------------------------------------------------------	  
   $('.tbl_pres').on('keyup','.form_duration, .form_quantity, .dosage',function(event) {

	   var valu = $(this).val();
	   if(isNaN(valu)){

		valu = valu.replace(/[^0-9\.]/g,'');
		if(valu.split('.').length > 2)
			valu = valu.replace(/\.+$/,"");

		}
	    $(this).val(valu); 
	    return false;

   });
  //------------------------------------------------------------	


  //remove background color
  //------------------------------------------------------------	
   $('.tbl_pres').on('click','.form_drug,.dosage,.interval,.form_duration,.form_quantity,.notes',function(){

 	 	$(this).css('background-color', '');

 	 	if($(this).hasClass('form_duration'))
 	 	{
 			$('.form_quantity').css('background-color','');
 		 	}
 	});	
 //------------------------------------------------------------	
  
    //making mandatory Fields
    //------------------------------------------------------------	
    var error_msg = "";
  $('#form_save').on('click',function(e) {
       var check_submit=0;

       
   $('.form_drug,.dosage,.interval,.form_duration,.form_quantity,.notes').each(function(){
    			
           if(this.value=='')
          {
		
            if($(this).hasClass('form_drug'))
            {      
        		error_msg += "Drug <br> ";
            }
            if($(this).hasClass('dosage'))
            {      
        		error_msg += "No.of Tabs/Drops/Application <br>  ";
            }
            if($(this).hasClass('form_duration'))
            {      
        		error_msg += " Duration <br> ";
            }
            if($(this).hasClass('form_quantity'))
            {      
        		error_msg += " Quantity <br> ";
            }
            if(!$(this).hasClass('notes')){
                
        	$(this).css('background-color', '#fb7666');
            }

            if($(this).hasClass('notes'))
             {

                    var attr= $(this).attr('notes_validation')
 		   			   
    		  		if (typeof attr !== typeof undefined && attr !== false) 
	 	      		{
  					    error_msg += " Any instruction to Pharmacist : Fill Proper Reason ! <br> ";
    					$(this).css('background-color', '#fb7666');
    			 	 }
 			   
             }    
        	
           } 

		if(this.value == '0'){
			
			if($(this).hasClass('interval'))
            {      
        		error_msg += " FREQ <br> ";
        		$(this).css('background-color', '#fb7666');
            }
			if($(this).hasClass('form_quantity'))
	            {      
				$(this).css('background-color', '');
	            }
			
			}

		
     }); 

   if(last_delete){
	   
      if(error_msg != ""){
					
								
					     		$(".body_top").prepend("<div id='parent1' class='parent1'></div>");
						 		$('#dialogBox1').dialogBox({
																hasClose: false,
																hasBtn: true,
																confirmValue: 'OK',
																confirm: function(){
																					
																		$('#parent1,.parent1').remove();
																	 	},
																title: 'The Following Fields are Mandatory:',
																content: error_msg
															});
						 						 		check_submit=1;
						 						 		$('.dialog-btn-confirm').focus();
						  					
						
						}


   }
      error_msg ='';
    if(check_submit){
		   	return false; 
    }

  }); 
  //------------------------------------------------------------
//---------------------------------------------------------------------------------------------------------------

//Dependency functions
//Prescriptions Insurance and Duration Validation
//---------------------------------------------------------------------------------------------------------------

  function selectPres(table_row,drug_name,drug_code,rate,rate_cat,pat_cat,bill_type,bill_type_value,item_name,item_price) 
	{

				  //auto filling filleds			
				 table_row.find('.drug_code').val(drug_code);
				 table_row.find('.form_drug').val(drug_name);
				 table_row.find('.item_name').val(item_name);
				 table_row.find('.rate').val(rate);
				 table_row.find('.rate_cat').val(rate_cat);
				 table_row.find('.pat_cat').val(pat_cat);
			     table_row.find('.billtype').text(bill_type_value);
			     table_row.find('.bill_type').val(bill_type);
		
			 //Clearing Fileds whenever autocomplete uses
			  table_row.find('.dosage').val('').css('background-color', '');
			  table_row.find('.interval').prop('selectedIndex',0).css('background-color', '');
			  table_row.find('.form_duration').val('').css('background-color', '');
			  table_row.find('.form_quantity').val('').css('background-color', '');
			  table_row.find('.notes').val('').css('background-color', '');
			  table_row.find('.notes').prop('required',false);

			

			//when drug is not associated with Proper insurance company shows Error.
			
			 if(item_price == '-1' || item_price =='0'){
				 			
					itemprice_validation();
					
				}
			   else{
			
				   last_prescription_validation();
				   
				   }

			
			   //item Price Validations

				  	function itemprice_validation(){
				
											 $(".body_top").prepend("<div id='parent2' class='parent2'></div>");
											 $('#dialogBox2').dialogBox({
													hasClose: false,
													hasBtn: true,
													confirmValue: 'Yes',
													confirm: function(){
														 table_row.find('.notes' ).attr("notes_validation", true);
														 table_row.find('.notes' ).css('background-color', '#fb7666').focus();
														$('#parent2, .parent2').remove();
														last_prescription_validation();
												
												 		},
													cancelValue: 'No',
													cancel: function(){
									
														 table_row.find('.form_drug' ).val('');
														 table_row.find('.notes' ).removeAttr("notes_validation");
																$('#parent2 ,.parent2').remove();
									                         					
														}, 
													title: 'Confirmation To Proceed',
													content: 'This drug is not approved by the associated Insurance. Do you still wish to continue ?'
												});
											 
											 $('.dialog-btn-confirm').focus();
								}
		


							
							
			//When The last Prescribed drug is not meet the specified Days  shows alert
			 function last_prescription_validation()
			 {
							
							
								 $.ajax({
							
									 type : "POST",
									  url : "../../../library/ajax/auto_prescriptions_search.php",
									  data: { 'pres_age' : 'pres_age','drug_code' : drug_code , 'pid' : <?php echo $pid;?> },
									  datatype : 'json',
									  success:function(data){
										  if(data.length != 0)
									    	{
													var json = $.parseJSON(data);
							
													var drug_names = json.drug;
													var durations = json.duration;
													var duration1s=json.duration1;
												
													if(duration1s == 'years' || duration1s == 'months' || duration1s == 'days')
													{
														duration1s = duration1s . replace(/s/g,'');
													}	
													
													var dosages = json.dosage;
													var quantitys = json.quantity;
													var intervals = json.interval;
													var prescriped_on = json.prescribed_date;
													var remaining_days = json.pres_remain_days;
													var msg = "This <span class=\"color_pres\">"+drug_names +"</span> Drug  has already been Prescribed on <span class=\"color_pres\"> "+prescriped_on+"</span> for<span class=\"color_pres\"> "+ durations +" "+duration1s+"</span> ,still <span class=\"color_pres\">"+ remaining_days+"</span> more for Complete.<br> <span> Do you Wish to Override This Drug </span> ?";
													
													 $(".body_top").prepend("<div id='parent3' class='parent3'></div>");
													 $('#dialogBox3').dialogBox({
															hasClose: false,
															hasBtn: true,
															confirmValue: 'Yes',
															confirm: function(){
																 table_row.find('.dosage').val(dosages).css('background-color', 'rgb(251, 198, 115)');
																 table_row.find('.interval').prop('selectedIndex',intervals).css('background-color', 'rgb(251, 198, 115)');
																 table_row.find('.form_duration').val(durations).css('background-color','rgb(251, 198, 115)');
																 table_row.find('.form_duration1').val(duration1s).css('background-color', 'rgb(251, 198, 115)');
																 table_row.find('.form_quantity').val(quantitys).css('background-color','rgb(251, 198, 115)');;
																 table_row.find('.notes').css('background-color','#fb7666').focus();
																 table_row.find('.notes').attr("notes_validation", true);
																 $('#parent3 ,.parent3').remove();
														 },
															cancelValue: 'No',
															cancel: function(){
							
																 table_row.find('.form_drug').val('');
																 table_row.find('.notes').css('background-color', '');
																 table_row.find('.notes').removeAttr("notes_validation", true);
																 $('#parent3,.parent3').remove();
																		
																}, 
															title: 'Confirmation To Proceed',
															content: msg
														});

													 $('#dialogBox3 .dialog-btn-confirm').focus();
							
											  }
							
										 }
							
									 });
					}
							  //end

}
//---------------------------------------------------------------------------------------------------------------
//Previous Encounters Medications list added Checks  
//---------------------------------------------------------------------------------------------------------------
function medication_age_table(msg_array)
 {

	 var msg_head = "<table class= \"medic_drug \" id = \" medic_drug\"><tr class='tr-head'>";
        msg_head += "<th style= \"width: 400px;font-size:10pt;text-align:left;\">Drug Name</th><th style= \"width: 200px;font-size:10pt;\"> Prescribed Date</th> <th style= \"width:150px;font-size:10pt;\">Total Days</th><th style= \"width:150px;font-size:10pt;\">Remaining Days</th><th style= \"width: 250px;font-size:10pt;\">Insurance Approved</th></tr>";
 		 msg_head += msg_array;
 		 msg_head += "</table><br />";
    	 var msg = msg_head ;

    		$(".body_top").prepend("<div id='parent8' class='parent8'></div>");
 		 	$('#dialogBox8').dialogBox({
 											hasClose: false,
 											hasBtn: true,
 											confirmValue: 'Proceed',
 											confirm: function(){

 																	var del_chk = $('.medic_drug .tr-row');
 														  		    var count = del_chk.length;
																	var chk = $('.tbl_pres tr');
 																	var b =0;
 																	del_chk.each(function(){

 	  																	var index = $(this).index();
																		var del_sel = $(this).find('.remain_days').text();
																		var ins_approve = $(this).find('.insurance_approve').text();
																     	var dura = del_sel.split(" ",1);
																		

																		if(dura != '0' && dura != '' || ins_approve != 'Yes'){

																			$(chk).eq(index).find('.notes').css('background-color', '#fb7666');	
																			$(chk).eq(index).find('.notes').attr("notes_validation", true);
																																					
																		}
																		$(chk).eq(index).find('.medication').prop('checked',false);

 	  																});

 																	past_encounter_set = true;	
																	
																$('#parent8 ,.parent8').remove();
																	
 		 												    	},
 											title: 'Confirmation To Proceed',
 											content: msg
 										});
				 
 		 	 $('#dialogBox8 .dialog-btn-confirm').focus();

 } 	 


function insurance_approve(term){

  	
	var result = "";
		 $.ajax({
		 type: "POST",
		 url: "../../../library/ajax/auto_prescriptions_search.php",
		 data:{'search_term' : term ,'pres_auto' :'pres_auto' },
		 async:false,
		 dataype: 'json',
		 success: function(data){
			 					
								var json = $.parseJSON(data);
							    var item_price = json[0].price;

							    if(item_price == '-1' || item_price =='0'){
	  										result = "<span style = \"color:red;\">No</span>";

	  						     	 }
								  else{
										
									 result = "<span style = \"color:Green;\">Yes</span>";
									    	  								  }	 			 
		 						}
		
		           }); 
	return result;
		
}


function medication_list_age_check()
{ 

	
	 
	 $.ajax({
			  type : "POST",
			  url : "../../../library/ajax/auto_prescriptions_search.php",
			  data: { 'pres_age' : 'pres_age','pid' : <?php echo $pid; ?>,'encounter':<?php echo $encounter;?>,'medication' : true},
			  datatype : 'json',
			  success: function(data){
				  
					var count = 0;
					var temp="";
					
				if(data.length != 0)
				{
					
				 	var json = $.parseJSON(data);
				 	for(var i = 0 ;i < json.length; i++)
				 	{ 	
					 	count = count + 1; 
					var drug_names = json[i].drug;
					var durations = json[i].duration;
					var duration1s=json[i].duration1;
					var prescriped_on = json[i].prescribed_date;
					var remaining_days = json[i].pres_remain_days;
				    var insu =  insurance_approve(drug_names); 
				    
				
				    temp += "<tr class='tr-row'><td style= \"width: 400px;\">"+drug_names+"</td><td style=\"width: 200px; text-align: center;\">"+prescriped_on+"</td> <td style=\"width:150px; text-align: center;color:green; \">"+durations+" "+duration1s+"</td><td class=\"remain_days\" style=\"width:150px; text-align: center;color:red;\">"+remaining_days+"</td><td class ='insurance_approve'style=\"width:250px;text-align: center;\">"+insu+"</td></tr>";
				   }	
				 } 

				if(temp != "")
				{
	
					medication_age_table(temp);		 
			  }	

			  }									
		 });
 }
//----------------------------------------------------------------------------------------------------------

	  

</script>
