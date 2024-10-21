<?php
/**
 * This report lists all the demographics allergies,problems,drugs and lab results
 *
 * Copyright (C) 2014 Ensoftek, Inc
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
 * @link    http://www.open-emr.org
 */

	//SANITIZE ALL ESCAPES
	$sanitize_all_escapes=true;
	//

	//STOP FAKE REGISTER GLOBALS
	$fake_register_globals=false;

	require_once("../globals.php");
	require_once("$srcdir/patient.inc");
	require_once("$srcdir/options.inc.php");
	require_once("../drugs/drugs.inc.php");
	require_once("$srcdir/formatting.inc.php");
	require_once("$srcdir/payment_jav.inc.php");
	
	$DateFormat=DateFormatRead();
	
// 	$_POST['form_details'] = true;
// 	function add_date($givendate,$day=0,$mth=0,$yr=0) {
// 		$cd = strtotime($givendate);
// 		$newdate = date('Y-m-d H:i:s', mktime(date('h',$cd),
// 		date('i',$cd), date('s',$cd), date('m',$cd)+$mth,
// 		date('d',$cd)+$day, date('Y',$cd)+$yr));
// 		return $newdate;
//         }
	if($_POST['date_from'] != "")
		$sql_date_from = $_POST['date_from'];
	else
		$sql_date_from = fixDate($_POST['date_from'], date('d-m-Y'));
	
	if($_POST['date_to'] != "")
		$sql_date_to = $_POST['date_to'];
	else
		$sql_date_to = fixDate($_POST['date_to']  , date('d-m-Y'));	

	
?>
<html>
	<head>
		<?php html_header_show();?>
		<title>
			<?php echo xlt('Final Diagnosis Report'); ?>
		</title>
		<script type="text/javascript" src="../../library/overlib_mini.js"></script>
		<script type="text/javascript" src="../../library/dialog.js"></script>
		<script type="text/javascript" src="../../library/js/jquery.1.3.2.js"></script>
		<script language="JavaScript">
		var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
		var global_date_format = '%d-%m-%Y';				
		function Form_Validate() {
			var d = document.forms[0];		 
			FromDate = d.date_from.value;
			ToDate = d.date_to.value;
			if ( (FromDate.length > 0) && (ToDate.length > 0) ) {
				if ( FromDate > ToDate ){
					alert("<?php echo xls('To date must be later than From date!'); ?>");
					return false;
				}
			}	
			$("#processing").show();
			return true;
		}
		
		</script>
		
		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

		<style type="text/css">
			/* specifically include & exclude from printing */
			@media print {
			#report_parameters {
				visibility: hidden;
				display: none;
			}
			#report_parameters_daterange {
				visibility: visible;
				display: inline;
			}
			#report_results table {
				margin-top: 0px;
			}
			#report_image {
				visibility: hidden;
				display: none;
			}
			}

			/* specifically exclude some from the screen */
			@media screen {
			#report_parameters_daterange {
				visibility: hidden;
				display: none;
			}
			}
			
			.head
			{
			text-align:center;
			font-weight:bold;
			background-color:rgb(236,236,236);
			text-transform: uppercase;
			}
			.cell
			{
			text-align:center;
			}
		</style>
		<script language="javascript" type="text/javascript">
					
			function submitForm() {
			 var fr=$('#date_from').val();
			 var to=$('#date_to').val();
			 
				fr=fr.split("-");
				to=to.split("-");
				
				fr_d=new Date(fr[2], fr[1], fr[0]);
				to_d=new Date(to[2], to[1], to[0]);

				var diff=fr_d - to_d;
				
				if(diff>0) //negative
				{
                   alert('To date must be later than From date');
					$('#date_error').css("display", "inline");
					
				}
				else
				{
					$("#form_refresh").attr("value","true");
					
                                        top.restoreSession(); 
                                    	
					$("#theform").submit();
				}
			
				
			}
			
		
			
		</script>
	</head>
	
	<body class="body_top">
		<!-- Required for the popup date selectors -->
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
		<span class='title'>
		<?php echo xlt('Report -Final Diagnosis');?>
		</span>
		<!-- Search can be done using age range, gender, and ethnicity filters.
		Search options include diagnosis, procedure, prescription, medical history, and lab results.
		-->

		
		<form name='theform' id='theform' method='post' action='final_diagnosis_report.php' >
			<div id="report_parameters">
				<input type='hidden' name='form_refresh' id='form_refresh''/>
				<table>
					  <tr>
					<td width='900px'>
                                            <div class="cancel-float" style='float:left'>
						<table class='text'>
							<tr>
								<td class='label' ><?php echo xlt('From'); ?>: </td>
								<td><input type='text' name='date_from' id="date_from" size='18' value='<?php echo attr($sql_date_from); ?>' readonly="readonly" title='<?php echo attr($title_tooltip) ?>'> <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_from_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xla('Click here to choose a date'); ?>'></td>
								<td class='label'><?php echo xlt('To{{range}}'); ?>: </td>
								<td><input type='text' name='date_to' id="date_to" size='18' value='<?php echo attr($sql_date_to); ?>' readonly="readonly" title='<?php echo  attr($title_tooltip) ?>'>	<img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' id='img_to_date' border='0' alt='[?]' style='cursor:pointer' title='<?php echo xla('Click here to choose a date'); ?>'></td>
								
								
								
								
								
							</tr>
							
							
						</table>
						
						</div></td>
						<td height="100%" valign='middle' width="175"><table style='border-left:1px solid; width:100%; height:100%'>
							<tr>
								<td width="130px"><div style='margin-left:15px'> <a href='#' class='css_button' onclick='submitForm();'> <span>
											<?php echo xlt('Generate Report'); ?>
											</span> </a>
									</div>
								</td>
								<td>
									<div id='processing' style='display:none;' ><img src='../pic/ajax-loader.gif'/></div>
								</td>
									
							</tr>
						</table></td>
					</tr>
				</table>
			</div>
		<!-- end of parameters -->
		<?php

		$sqlBindArray = array();
		if ($_POST['form_refresh']){
			
$result=sqlStatement("select CONCAT(f.title,'- ',diagnosis) as Title, 
sum( case when TIMESTAMPDIFF( YEAR, p.DOB, now()) = 0 and TIMESTAMPDIFF( MONTH, DOB, now()) %12 = 0 and p.sex = 'Male'  then  1 else 0 end ) as age_less_1m_male, 
sum( case when TIMESTAMPDIFF( YEAR, p.DOB, now()) = 0 and TIMESTAMPDIFF( MONTH, DOB, now()) %12 = 0 and p.sex=' Female'  then  1 else 0 end ) as age_less_1m_female, 
sum( case when TIMESTAMPDIFF( YEAR, p.DOB, now()) = 0 and TIMESTAMPDIFF( MONTH, DOB, now()) %12  > 1 and TIMESTAMPDIFF( MONTH, DOB, now()) %12 <= 12 and p.sex = 'Male'  then  1 else 0 end ) as age_1m_to_1yr_male, 
sum( case when TIMESTAMPDIFF( YEAR, p.DOB, now()) = 0 and TIMESTAMPDIFF( MONTH, DOB, now()) %12  > 1 and TIMESTAMPDIFF( MONTH, DOB, now()) %12 <= 12 and p.sex = 'Female' then  1 else 0 end ) as age_1m_to_1yr_female,
sum(case when TIMESTAMPDIFF( YEAR, p.DOB, now())  > 1 and TIMESTAMPDIFF( YEAR, p.DOB, now()) <= 5  and p.sex = 'Male' then 1 else 0 end ) as age_1_to_5_male, 
sum(case when TIMESTAMPDIFF( YEAR, p.DOB, now())  > 1 and TIMESTAMPDIFF( YEAR, p.DOB, now()) <= 5 and p.sex = 'Female' then 1 else 0 end ) as age_1_to_5_female,
 sum(case when TIMESTAMPDIFF( YEAR, p.DOB, now())  > 5 and TIMESTAMPDIFF( YEAR, p.DOB, now()) <= 60 and p.sex = 'Male' then  1 else 0 end ) as age_5_to_60_male, 
sum(case when TIMESTAMPDIFF( YEAR, p.DOB, now())  > 5 and TIMESTAMPDIFF( YEAR, p.DOB, now()) <= 60 and p.sex = 'Female' then  1 else 0 end ) as age_5_to_60_female, 
sum(case when TIMESTAMPDIFF(YEAR, p.DOB, now())  > 60 and p.sex = 'Male' then  1 else 0 end ) as age_above_60_male, 
sum(case when TIMESTAMPDIFF(YEAR, p.DOB, now())  > 60 and p.sex = 'Female' then  1 else 0 end ) as age_above_60_female 
from final_dia f inner join patient_data p on p.pid=f.pid left join form_encounter fe on f.encounter = fe.encounter where fe.date >= '".date('Y-m-d',strtotime($_POST['date_from']))."' and fe.date <= '".date('Y-m-d',strtotime($_POST['date_to']))."'  group by diagnosis ");


				
			if(sqlNumRows($result) > 0){
				
			$i=1;	
                                                               
			?>
			
				<br>

				<div id = "report_results">
					<table>
						<tr>
							<td class="text"><strong><?php echo xlt('Total Number of Patients')?>:</strong>&nbsp;<span id="total_patients"><?php echo sqlNumRows($result);?></span></td>
						</tr>
					</table>
					
					<table width=90% align="center" cellpadding="5" cellspacing="0" style="font-family:tahoma;color:black;" border="0">
				<tr><td class='head'></td><td class='head'></td><td colspan='2' class='head'>1 MONTH OR BELOW</td>	<td colspan='2'  class='head'>1 MONTH - 1 YEAR</td> <td colspan='2'  class='head'>I YEAR - 5 YEAR</td><td colspan='2'  class='head'>5 YEAR - 60 YEAR</td><td colspan='2'  class='head'>ABOVE 60 YEAR</td><td colspan='2'  class='head'>Total</td><td  class='head'>GRAND TOTAL</td></tr>
				<tr><td class='head' style='text-align:left !important;'>Sno</td><td width='25%' class='head' style='text-align:left !important;'>DISEASE AND ICD9 CODES</td><td class='head'> Male</td><td class='head'>Female</td><td class='head'>Male</td><td class='head'>Female</td><td class='head'>Male</td><td class='head'>Female</td><td class='head'> Male</td><td class='head'>Female</td>
				<td class='head'>Male</td><td class='head'>Female</td><td class='head'> Male</td><td class='head'>Female</td><td class='head'></td></tr>
					<?php while($res=sqlFetchArray($result)) {
						
							$row_male_sum=$res["age_less_1m_male"]+$res["age_1m_to_1yr_male"]+ $res["age_1_to_5_male"]+$res["age_5_to_60_male"]+ $res["age_above_60_male"];
							$row_female_sum=$res["age_less_1m_female"]+$res["age_1m_to_1yr_female"]+ $res["age_1_to_5_female"]+$res["age_5_to_60_female"]+ $res["age_above_60_female"];
							$row_grand_total=$row_male_sum + $row_female_sum;?>							
						<tr>
						<td><?php echo $i;?></td><td><?php echo $res['Title'];?></td>
						<td class='cell'><?php echo $res['age_less_1m_male'];?></td>
						<td class='cell'><?php echo $res['age_less_1m_female'];?></td>
						<td class='cell'><?php echo $res['age_1m_to_1yr_male'];?></td>
						<td class='cell'><?php echo $res['age_1m_to_1yr_female'];?></td>
						<td class='cell'><?php echo $res['age_1_to_5_male'];?></td>
						<td class='cell'><?php echo $res['age_1_to_5_female'];?></td>
						<td class='cell'><?php echo $res['age_5_to_60_male'];?></td>
						<td class='cell'><?php echo $res['age_5_to_60_female'];?></td>
						<td class='cell'><?php echo $res['age_above_60_male'];?></td>
						<td class='cell'><?php echo $res['age_above_60_female'];?></td>
			<td class='cell'><?php echo $row_male_sum;?></td>
			<td class='cell'><?php echo $row_female_sum; ?></td>
			<td class='cell'><?php echo $row_grand_total;?></td>
						
						</tr>											
																	<?php $i++; }?>	
					</table>
					 <!-- Main table ends -->
				<?php 
				}else{ //End if $result?>
					<br><table>
						<tr>
							<td class="text">&nbsp;&nbsp;<?php echo xlt('No records found.')?></td>
						</tr>
					</table>
				<?php
				}
				?>
				</div>
				
			<?php
			}else{//End if form_refresh
				?><div class='text'> <?php echo xlt('Please input search criteria above, and click Generate Report to view results.'); ?> </div><?php
			}
			?>
		</form>

		<!-- stuff for the popup calendar -->
		<style type="text/css">
			@import url(../../library/dynarch_calendar.css);
		</style>
		<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
		<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
		<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
		<script language="Javascript">
		Calendar.setup({inputField:"date_from", ifFormat:"%d-%m-%Y", button:"img_from_date"});
		Calendar.setup({inputField:"date_to", ifFormat:"%d-%m-%Y", button:"img_to_date"});
		</script>
	</body>
</html>
