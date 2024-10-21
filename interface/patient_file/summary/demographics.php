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
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/classes/Address.class.php");
require_once("$srcdir/classes/InsuranceCompany.class.php");
require_once("$srcdir/classes/Document.class.php");
require_once("$srcdir/options.inc.php");
require_once("../history/history.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("$srcdir/edi.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once("$srcdir/clinical_rules.php");
require_once("$srcdir/options.js.php");
require_once(dirname(__FILE__)."/../../../library/appointments.inc.php");

$pateint_id =  $_SESSION['pid'];
$patient_eid = $_SESSION['encounter'];
$limit_access_group = ["Pharmacist"];
$auth_group = $_SESSION['usergroup'];


if ($GLOBALS['concurrent_layout'] && isset($_GET['set_pid'])) {
	include_once("$srcdir/pid.inc");
	setpid($_GET['set_pid']);
}

$result = sqlQuery("SELECT provider_id FROM form_encounter WHERE encounter ='".$GLOBALS[encounter]."'  LIMIT 0,1");
if(count($result)>0)
		$_SESSION['encounter_provider']=$result['provider_id'];
else
		$_SESSION['encounter_provider']=0;

		$cur_date = "";
		$active_reminders = false;
		$all_allergy_alerts = false;
		if ($GLOBALS['enable_cdr']) {
			//CDR Engine stuff
			if ($GLOBALS['enable_allergy_check'] && $GLOBALS['enable_alert_log']) {
				//Check for new allergies conflicts and throw popup if any exist(note need alert logging to support this)
				$new_allergy_alerts = allergy_conflict($pid,'new',$_SESSION['authUser']);
				if (!empty($new_allergy_alerts)) {
					$pop_warning = '<script type="text/javascript">alert(\'' . xls('WARNING - FOLLOWING ACTIVE MEDICATIONS ARE ALLERGIES') . ':\n';
					foreach ($new_allergy_alerts as $new_allergy_alert) {
						$pop_warning .= addslashes($new_allergy_alert) . '\n';
					}
					$pop_warning .= '\')</script>';
					echo $pop_warning;
				}
			}
			if ((!isset($_SESSION['alert_notify_pid']) || ($_SESSION['alert_notify_pid'] != $pid)) && isset($_GET['set_pid']) && $GLOBALS['enable_cdr_crp']) {
				// showing a new patient, so check for active reminders and allergy conflicts, which use in active reminder popup
				$active_reminders = active_alert_summary($pid,"reminders-due",'','default',$_SESSION['authUser'],TRUE);
				if ($GLOBALS['enable_allergy_check']) {
					$all_allergy_alerts = allergy_conflict($pid,'all',$_SESSION['authUser'],TRUE);
				}
			}
		}
		


		function print_as_money($money) {
			preg_match("/(\d*)\.?(\d*)/",$money,$moneymatches);
			$tmp = wordwrap(strrev($moneymatches[1]),3,",",1);
			$ccheck = strrev($tmp);
			if ($ccheck[0] == ",") {
				$tmp = substr($ccheck,1,strlen($ccheck)-1);
			}
			if ($moneymatches[2] != "") {
				return "$ " . strrev($tmp) . "." . $moneymatches[2];
			} else {
				return "$ " . strrev($tmp);
			}
		}
		


		// get an array from Photos category
		function pic_array($pid,$picture_directory) {
			$pics = array();
			$sql_query = "select documents.id from documents join categories_to_documents " .
					"on documents.id = categories_to_documents.document_id " .
					"join categories on categories.id = categories_to_documents.category_id " .
					"where categories.name like ? and documents.foreign_id = ?";
			if ($query = sqlStatement($sql_query, array($picture_directory,$pid))) {
				while( $results = sqlFetchArray($query) ) {
					array_push($pics,$results['id']);
				}
			}
			return ($pics);
		}



		// Get the document ID of the first document in a specific catg.
		function get_document_by_catg($pid,$doc_catg) {
				$result = array();
					if ($pid and $doc_catg) {
						 $result = sqlQuery("SELECT d.id, d.date, d.url FROM " .
						"documents AS d, categories_to_documents AS cd, categories AS c " .
						"WHERE d.foreign_id = ? " .
						"AND cd.document_id = d.id " .
						"AND c.id = cd.category_id " .
						"AND c.name LIKE ? " .
						"ORDER BY d.date DESC LIMIT 1", array($pid, $doc_catg) );
					}
					return($result['id']);
			}		




		// Display image in 'widget style'
		function image_widget($doc_id,$doc_catg)
		{
			global $pid, $web_root;
			$docobj = new Document($doc_id);
			$image_file = $docobj->get_url_file();
			$extension = substr($image_file, strrpos($image_file,"."));
			$viewable_types = array('.png','.jpg','.jpeg','.png','.bmp','.PNG','.JPG','.JPEG','.PNG','.BMP'); // image ext supported by fancybox viewer
			if ( in_array($extension,$viewable_types) ) { // extention matches list
				$to_url = "<td> <a href = $web_root" .
				"/controller.php?document&retrieve&patient_id=$pid&document_id=$doc_id" .
				"/tmp$extension" .  // Force image type URL for fancybox
				" onclick=top.restoreSession(); class='image_modal'>" .
				" <img src = $web_root" .
				"/controller.php?document&retrieve&patient_id=$pid&document_id=$doc_id" .
				" width=100 alt='$doc_catg:$image_file'>  </a> </td> <td valign='center'>".
				htmlspecialchars($doc_catg) . '<br />&nbsp;' . htmlspecialchars($image_file) .
				"</td>";
			}
			else {
				$to_url = "<td> <a href='" . $web_root . "/controller.php?document&retrieve" .
						"&patient_id=$pid&document_id=$doc_id'" .
						" onclick='top.restoreSession()' class='css_button_small'>" .
						"<span>" .
						htmlspecialchars( xl("View"), ENT_QUOTES )."</a> &nbsp;" .
						htmlspecialchars( "$doc_catg - $image_file", ENT_QUOTES ) .
						"</span> </td>";
			}
			echo "<table><tr>";
			echo $to_url;
			echo "</tr></table>";
		}
		
		// Determine if the Vitals form is in use for this site.
		$tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE " .	"directory = 'vitals' AND state = 1");
		$vitals_is_registered = $tmp['count'];
		
		// Get patient/employer/insurance information.
		$result  = getPatientData($pid, "*, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD");
		$result2 = getEmployerData($pid);
		$result3 = getInsuranceData($pid, "primary", "copay, provider, DATE_FORMAT(`date`,'%Y-%m-%d') as effdate");
		$insco_name = "";
		if ($result3['provider']) {   // Use provider in case there is an ins record w/ unassigned insco
			$insco_name = getInsuranceProvider($result3['provider']);
		}
		
		$formresult = getFormByEncounter($pid, $encounter, "form_id,deleted,formdir,date");
		$enc_form_id = find_form_id($formresult,'newpatient');
		$cur_date = check_cur_date($formresult,'newpatient');
		
		?>
<html>
	<head>
		<?php html_header_show();?>
		<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
		<link rel="stylesheet" type="text/css" href="../../../library/js/fancybox/jquery.fancybox-1.2.6.css" media="screen" />
		<style type="text/css">@import url(../../../library/dynarch_calendar.css);</style>
		<script type="text/javascript" src="../../../library/textformat.js"></script>
		<script type="text/javascript" src="../../../library/dynarch_calendar.js"></script>
		<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
		<script type="text/javascript" src="../../../library/dynarch_calendar_setup.js"></script>
		<script type="text/javascript" src="../../../library/dialog.js"></script>
		<script type="text/javascript" src="../../../library/js/jquery-1.6.4.min.js"></script>
		<script type="text/javascript" src="../../../library/js/fancybox/jquery.fancybox-1.2.6.js"></script>
		<script type="text/javascript" src="../../../library/js/common.js"></script>

	<style>
		.demo_table tr {height:30px;}
		.body_top{background-color:#fff !important;}
		.circle {
		    border: 1px solid tomato;
		    border-radius: 50%;
		    box-shadow: 0 0 1px 1px coral;
		    font: bold 15px/13px Helvetica,Verdana,Tahoma;
		    height: 30px;
		    padding: 7px 3px 0;
		    text-align: center;
		    width: 30px;
		}
		.circle1 {
    		border-radius: 50%;
    		font: bold 15px/13px Helvetica,Verdana,Tahoma;
    		height: 20px;
    		padding: 7px 3px 0;
    		text-align: center;
    		width: 20px;
    		float:right;
    		background-color:red;
    		color:white;
    		padding-left: 3px; 
    		margin-left: 7px; 
    		margin-bottom: 0px; 
    		margin-top: -5px;
    		cursor:pointer;
		}

		input[type=submit],input[type=button]{
		    background-color:lightslategrey;
		    border: medium none;
		    border-radius: 4px;
		    color: #ffffff;
		    cursor: pointer;
		    font-size: 16px;
		    margin: 5px;
		    padding: 4px;
		    text-align: center;
		    transition: all 0.5s ease 0s;
		    width: 70px;
			}
		
		.add,.del{
			   border: medium none;
    		 border-radius: 4px;
    		 color: #ffffff;
    		 cursor: pointer;
         font-size: 16px;
         margin: 5px;
         padding: 4px;
         text-align: center;
         transition: all 0.5s ease 0s;
         width: 30px !important;
      }

      input[type=button]:hover{
          background-color: 	rgb(216, 103, 103);
      }
      
			input[type=button]:focus{
          background-color: 	rgb(216, 103, 103);
      }
      
			input[type=submit]:hover {
          background: rgb(122, 197, 163);
          color: #fff;
      }

      input[type=submit]:focus {
          background: rgb(122, 197, 163);
          color: #fff;
      }
    	.css_button_small span {
          background: none !important;
          display: block !important;
          font-weight: bold !important;
          line-height: 20px !important;
          padding: 4px 0 0 6px !important;
       }

      .css_button_small {
          background: darkcyan none repeat scroll 0 0 !important;
          border-radius: 45px !important; 
          color: #fff !important;
          display: block !important;
					float: left !important; 
					font: bold 10px arial,sans-serif !important;
					height: 30px !important;
					margin-right: 3px !important;
					padding-right: 10px !important;
					text-decoration: none !important;
					width: 20px !important;
			}

			.section-header,.section-header-dynamic{
					background-color: #d9edf7;
					box-shadow: 0 8px 6px -3px black;
					color: #fff;
					height: 40px;
					margin-top: 12px;
			}

			.no_tab,#allergy_ps_expand{
 					background: #f7f7f7 none repeat scroll 0 0 !important;
				}
				#pnotes_ps_expand {
  			height:auto;	
  			width:100%;
			}
</style>
<script type="text/javascript" language="JavaScript">


//Encounter Dialog Box POP
 <?php 
   if(!in_array($_SESSION['usergroup'], $limit_access_group)){
  	if(!(isset($_SESSION['opener_save'])) && ($_SESSION['opener_save'] != 'true')){ 
			  if (!isset($_REQUEST['opener_save']) && $_REQUEST['opener_save'] != "true"){ 
		?>
		 	dlgopen('../../forms/newpatient/new.php?autoloaded=1&calenc=', '_blank', 875, 375);
		 	 <?php } 
		    } else{
		      unset($_SESSION['opener_save']);
		  	} 
		}
  ?>

//Technicain Notification
//------------------------------------------------------------------------------------------------

<?php 
if($auth_group == "Technician"){?>
	
	$(document).ready(function(){
		//For Investigation	
		var div = "<div class='investicate_note circle1'></div>";
		var frame = $('#main_demo',top.frames["RTop"].document);
		var tble = frame.find('.demo_table #investigation .section-header table tr');
		var count = frame.find('.demo_table #investigation .section-header table tr td').length;

		if(count == 1){
			tble.find('td:nth-child(1)').append(div);
		}else if(count == 2 ){
			tble.find('td:nth-child(2)').append(div);
			}
  
 		function edit_investigation_technician(){
			  enc = <?php echo $_SESSION['encounter'] == "" ? (EncounterId) : $_SESSION['encounter'];  ?>;
			  if(enc != 0){
	       			$.ajax({
	        				 url: "../../patient_file/summary/investigationdata_fragment_edit.php",
	        			   async: false,
	        			 success: function(response){
	          				$('#labdata_ps_expand').empty().append(response);
	        					}
  						});
	    	}else {
	     			alert('Please create encounter to proceed');
	    	}
	  		return false;
		}
 
 		$('.investicate_note').click(function(){
			 var  div = $("#labdata_ps_expand");
	 		 edit_investigation_technician();
	 			$(div).slideToggle("slow"); 
	   });

			//For Procedure	
			var div1 = "<div class='procedure_note circle1'></div>";
			var frame1 = $('#main_demo',top.frames["RTop"].document);
			var tble1 = frame1.find('.demo_table #procedure .section-header table tr');
			var count1 = frame1.find('.demo_table #procedure .section-header table tr td').length;

			if(count1 == 1){
				tble1.find('td:nth-child(1)').append(div1);
			}else if(count1 == 2 ){
				tble1.find('td:nth-child(2)').append(div1);
				}
				
			function edit_procedure_technician(form_id){
					enc = <?php echo $_SESSION['encounter'] == "" ? (EncounterId) : $_SESSION['encounter'];  ?>;
					if(enc != 0){
												$.ajax({
																url: "../../patient_file/summary/procedure_report_edit.php?frm_id="+form_id,
																async: false,
																success: function(response){
																	$('#proc_ps_expand').empty().append(response);
																	}
															});
					}else {
								alert('Please create encounter to proceed');
						}
						return false;
			}
  
  	$('.procedure_note').click(function(){<?php 	
	  		$formresult = getFormByEncounter($pid, $encounter, "form_id,deleted,formdir,date");
	  		$proc_form_id = find_form_id($formresult,'procedure');
	  	?>
 			
			  var  div = $("#proc_ps_expand");
 	 			edit_procedure_technician(<?php echo $proc_form_id;?>);
 	 			$(div).slideToggle("slow"); 
 	    });

});
<?php 			
}
?>
//------------------------------------------------------------------------------------------------

 var mypcc = '<?php echo htmlspecialchars($GLOBALS['phone_country_code'],ENT_QUOTES); ?>';
 var EncounterId = 0;

 function oldEvt(apptdate, eventid) {
  dlgopen('../../main/calendar/add_edit_event.php?date=' + apptdate + '&eid=' + eventid, '_blank', 775, 375);
 }

 function advdirconfigure() {
   dlgopen('advancedirectives.php', '_blank', 500, 450);
  }

	function refreshme() {
  	top.restoreSession();
  	location.reload();
 	}

		// Process click on Delete link.
		function deleteme() {
			dlgopen('../deleter.php?patient=<?php echo htmlspecialchars($pid,ENT_QUOTES); ?>', '_blank', 500, 450);
			return false;
		}

 // Called by the deleter.php window on a successful delete.
 function imdeleted(EncounterId) {
		<?php if ($GLOBALS['concurrent_layout']) { ?>
			top.window.parent.left_nav.removeOptionSelected(EncounterId);
			top.window.parent.left_nav.clearEncounter();
			EncounterId = 0;
			$.ajax({
				url: '../../enc_session.php',
				async: false,
				success : function (data){
					pid = data;
				}
			});
			window.top.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php?opener_save=true');
		<?php } else { ?>
			top.restoreSession();
			top.Title.location.href = '../patient_file/encounter/encounter_title.php';
			top.Main.location.href  = '../patient_file/summary/demographics.php?opener_save=true';
		<?php } ?>
 	}

/*
 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
<?php if ($GLOBALS['concurrent_layout']) { ?>
  parent.left_nav.clearPatient();
<?php } else { ?>
  top.restoreSession();
  top.location.href = '../main/main_screen.php';
<?php } ?>
 }
*/

 function validate() {
  	var f = document.forms[0];
		<?php
			if ($GLOBALS['athletic_team']) {
  			echo "  if (f.form_userdate1.value != f.form_original_userdate1.value) {\n";
  			$irow = sqlQuery("SELECT id, title FROM lists WHERE " .	"pid = ? AND enddate IS NULL ORDER BY begdate DESC LIMIT 1", array($pid));
  			if (!empty($irow)) {?>
   				if (confirm('Do you wish to also set this new return date in the issue titled "<?php echo htmlspecialchars($irow['title'],ENT_QUOTES); ?>"?')) {
    			f.form_issue_id.value = '<?php echo htmlspecialchars($irow['id'],ENT_QUOTES); ?>';
   			} else {
    				alert('OK, you will need to manually update the return date in any affected issue(s).');
   			}
				<?php } else { ?>
   				alert('You have changed the return date but there are no open issues. You probably need to create or modify one.');
				<?php
  			} // end empty $irow
  				echo "  }\n";
				} // end athletic team?>
  	return true;
 	}

 function newEvt() {
  dlgopen('../../main/calendar/add_edit_event.php?patientid=<?php echo htmlspecialchars($pid,ENT_QUOTES); ?>', '_blank', 775, 375);
  return false;
 }

function sendimage(pid, what) {
 // alert('Not yet implemented.'); return false;
 dlgopen('../upload_dialog.php?patientid=' + pid + '&file=' + what,
  '_blank', 500, 400);
 return false;
}

function edit_enc(frmid){
  dlgopen('../../patient_file/encounter/view_form.php?formname=newpatient&id='+frmid, '_blank', 875, 375);
  return false;
}

//Edit Vitals Informations
function edit_vitals(frmid){
  	enc = <?php echo $_SESSION['encounter'] == "" ? (EncounterId) : $_SESSION['encounter'];  ?>;
  	//enc = EncounterId;
  	if(enc != 0){
				toggleIndicator(this,"vitals_ps_expand");
		    $('.btn_vital').hide();
		    if(frmid != undefined && frmid > 0){
      	$.ajax({
				        url: "../../patient_file/encounter/view_form.php?formname=vitals&id="+frmid,
				        success: function(response){
          			$('#vital_con').empty().append(response);
        		}
      		});
    		}else {
     					$.ajax({
       								url: "../../forms/vitals/new.php",
       								async: false,
       								success: function(response){
         							$('#vital_con').html(response);
       								}
     								});
   							}
			}else {
  					alert('Please create encounter to proceed');
 			}
  		return false;
	}

//Edit SOAP informations
function edit_soap(frmid){
			enc = <?php echo $_SESSION['encounter'] == "" ? (EncounterId) : $_SESSION['encounter'];  ?>;
			if(enc != 0){
			toggleIndicator(this,"soap_ps_expand");
				$('.btn_soap').hide();
				if(frmid != undefined && frmid > 0){
					$.ajax({
						url: "../../patient_file/encounter/view_form.php?formname=soap&id="+frmid,
						async: false,
						success: function(response){
							$('#soap_con').empty().append(response);
						}
					});
					
			}else {
				$.ajax({
					url: "../../forms/soap/new.php",
					async: false,
					success: function(response){
						$('#soap_con').html(response);
					}
				});
			}
		}else {
			alert('Please create encounter to proceed');
		}
			return false;
}

//Edit Procedure
function edit_proc(frmid){
				enc = <?php echo $_SESSION['encounter'] == "" ? (EncounterId) : $_SESSION['encounter'];  ?>;
				if(enc != 0){
				toggleIndicator(this,"proc_ps_expand");
					$('.btn_proc').hide();
					if(frmid != undefined && frmid > 0){
						$.ajax({
							url: "../../patient_file/encounter/view_form.php?formname=procedure&id="+frmid,
							async: false,
							success: function(response){
								$('#proc_ps_expand').empty().append(response);
							}
						});
						
				}else {
					$.ajax({
						url: "../../forms/procedure/new.php",
						async: false,
						success: function(response){
							$('#proc_ps_expand').html(response);
						}
					});
				}
			}else {
				alert('Please create encounter to proceed');
			}
				return false;
	}


//Edit Investigations
function edit_investigation(){
		enc = <?php echo $_SESSION['encounter'] == "" ? (EncounterId) : $_SESSION['encounter'];  ?>;
		if(enc != 0){
			toggleIndicator(this,"labdata_ps_expand");
			$('.btn_inves').hide();
				$.ajax({
					url: "../../patient_file/summary/investigation.php",
					async: false,
					success: function(response){
						$('#labdata_ps_expand').empty().append(response);
					}
				});
			}else {
			alert('Please create encounter to proceed');
			}
		return false;
}


//Edit Provisional Diagnosis
function edit_prov_dia(){
			enc = <?php echo $_SESSION['encounter'] == "" ? (EncounterId) : $_SESSION['encounter'];  ?>;
			if(enc != 0){
				toggleIndicator(this,"prov_dia_ps_expand");
				$('.btn_prov_dia').hide();
					$.ajax({
						url: "../../patient_file/summary/provisional_dia.php",
						async: false,
						success: function(response){
							$('#prov_dia_ps_expand').empty().append(response);
						}
					});
				}else {
				alert('Please create encounter to proceed');
				}
			return false;
	}

//Edit Final Diagnosis
function edit_final_dia(){
			enc = <?php echo $_SESSION['encounter'] == "" ? (EncounterId) : $_SESSION['encounter'];  ?>;
			if(enc != 0){
				toggleIndicator(this,"final_dia_ps_expand");
				$('.btn_final_dia').hide();
					$.ajax({
						url: "../../patient_file/summary/final_dia.php",
						async: false,
						success: function(response){
							$('#final_dia_ps_expand').empty().append(response);
						}
					});
				}else {
				alert('Please create encounter to proceed');
				}
			return false;
		}


//Edit Prescriptions
function edit_prescriptions(){
				enc = <?php echo $_SESSION['encounter'] == "" ? (EncounterId) : $_SESSION['encounter'];  ?>;
				if(enc != 0){
					toggleIndicator(this,"presc_ps_expand");
					$('.btn_presc').hide();
						$.ajax({
							url: "../../patient_file/summary/rmc_prescription.php",
							async: false,
							success: function(response){
								$('#presc_ps_expand').empty().append(response);
							}
						});
					}else {
					alert('Please create encounter to proceed');
					}
				return false;
		}

//Edit Lab 
function edit_lab(frmid){
		enc = <?php  echo $_SESSION['encounter'] == "" ? (EncounterId) : $_SESSION['encounter'] ; ?>;
		if(enc != 0 )
		{
			$('.btn_lab').hide();
			if(frmid != undefined && frmid > 0){
					$.ajax({
								url: "../../patient_file/encounter/view_form.php?formname=lab&id="+frmid,
								async: false,
								success: function(response){
									$('#lab_con').empty().append(response);
								}
							});
							toggleIndicator(this,"ab_ps_expand");
			}else {
					$.ajax({
							url: "../../forms/lab/new.php?id=lab_ps_expand",
							async: false,
							success: function(response){
								$('#lab_con').html(response);
							}
						});
					}
		}
		else{
				alert('Please Create encounter to proceed');	
		}
		return false;
}


// Process click on Delete link.
function deleteme() {
 dlgopen('../deleter.php?encounterid=<?php echo $encounter; ?>', '_blank', 500, 450);
 return false;
}

//Toggle Expand and Collapse
function toggleIndicator(target,div) {
    $mode = $(target).find(".indicator").text();
    if ( $mode == "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" ) {
        	$(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('expand'),ENT_QUOTES); ?>" );
        	$("#"+div).hide();
					$.post( "../../../library/ajax/user_settings.php", { target: div, mode: 0 });
   		 } else {
        	$(target).find(".indicator").text( "<?php echo htmlspecialchars(xl('collapse'),ENT_QUOTES); ?>" );
        	$("#"+div).show();
					$.post( "../../../library/ajax/user_settings.php", { target: div, mode: 1 });
    	}
	}

$(document).ready(function(){
  	var msg_updation='';
	<?php
	if($GLOBALS['erx_enable']){
		//$soap_status=sqlQuery("select soap_import_status from patient_data where pid=?",array($pid));
		$soap_status=sqlStatement("select soap_import_status,pid from patient_data where pid=? and soap_import_status in ('1','3')",array($pid));
		while($row_soapstatus=sqlFetchArray($soap_status)){
			//if($soap_status['soap_import_status']=='1' || $soap_status['soap_import_status']=='3'){ ?>
				top.restoreSession();
				$.ajax({
					type: "POST",
					url: "../../soap_functions/soap_patientfullmedication.php",
					dataType: "html",
					data: {
						patient:<?php echo $row_soapstatus['pid']; ?>,
					},
					async: false,
					success: function(thedata){
						//alert(thedata);
						msg_updation+=thedata;
					},
					error:function(){
						alert('ajax error');
					}
				});
			<?php
			//}
			//elseif($soap_status['soap_import_status']=='3'){ ?>
							top.restoreSession();
							$.ajax({
								type: "POST",
								url: "../../soap_functions/soap_allergy.php",
								dataType: "html",
								data: {
									patient:<?php echo $row_soapstatus['pid']; ?>,
								},
								async: false,
								success: function(thedata){
									//alert(thedata);
									msg_updation+=thedata;
								},
								error:function(){
									alert('ajax error');
								}
							});
				<?php
					if($GLOBALS['erx_import_status_message']){ ?>
						if(msg_updation)
			  			alert(msg_updation);
				<?php	}
			//}
			}
		}?>
    // load divs
    var cur_date = <?php echo $cur_date != '' ? $cur_date : 0 ; ?>;
			$("#stats_div").load("stats.php", { 'embeddedScreen' : true }, function() {
				// (note need to place javascript code here also to get the dynamic link to work)
					$(".rx_modal").fancybox({
									'overlayOpacity' : 0.0,
									'showCloseButton' : true,
									'frameHeight' : 500,
									'frameWidth' : 800,
						'centerOnScroll' : false,
						'callbackOnClose' : function()  {
									refreshme();
						}
					});
			}); 
    $("#pnotes_ps_expand").load("pnotes_fragment.php");
    $("#disclosures_ps_expand").load("disc_fragment.php");

    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_crw']) { ?>
      top.restoreSession();
      $("#clinical_reminders_ps_expand").load("clinical_reminders_fragment.php", { 'embeddedScreen' : true }, function() {
          // (note need to place javascript code here also to get the dynamic link to work)
          $(".medium_modal").fancybox( {
                  'overlayOpacity' : 0.0,
                  'showCloseButton' : true,
                  'frameHeight' : 500,
                  'frameWidth' : 800,
                  'centerOnScroll' : false,
                  'callbackOnClose' : function()  {
                  refreshme();
                  }
          });
      });
    <?php } // end crw?>


    <?php if ($GLOBALS['enable_cdr'] && $GLOBALS['enable_cdr_prw']) { ?>
	      top.restoreSession();
      	$("#patient_reminders_ps_expand").load("patient_reminders_fragment.php");
    <?php } // end prw?>

		<?php if ($vitals_is_registered && acl_check('patients', 'med')) { ?>
		    // Initialize the Vitals form if it is registered and user is authorized.
		    //$("#vitals_ps_expand").load("vitals_fragment.php");
		<?php } ?>

    // Initialize track_anything
    $("#track_anything_ps_expand").load("track_anything_fragment.php");

    // Initialize investigationdata
    $("#labdata_ps_expand").load("investigationdata_fragment.php");
 		
		<?php 
    		$formresult = getFormByEncounter($pid, $encounter, "form_id,deleted,formdir,date");
	  		$proc_form_id = find_form_id($formresult,'procedure');
  	?>
	  $("#proc_ps_expand").load("../../patient_file/summary/procedure_report.php?frm_id="+<?php echo $proc_form_id;?>);
		<?php
  		// Initialize for each applicable LBF form.
  		$gfres = sqlStatement("SELECT option_id FROM list_options WHERE " .	"list_id = 'lbfnames' AND option_value > 0 ORDER BY seq, title");
  		while($gfrow = sqlFetchArray($gfres)) {?>
    		$("#<?php echo $gfrow['option_id']; ?>_ps_expand").load("lbf_fragment.php?formname=<?php echo $gfrow['option_id']; ?>");
			<?php }?>

    // fancy box
    	enable_modals();
    	tabbify();
		// modal for dialog boxes
		$(".large_modal").fancybox( {
			'overlayOpacity' : 0.0,
			'showCloseButton' : true,
			'frameHeight' : 600,
			'frameWidth' : 1000,
			'centerOnScroll' : false
		});

		// modal for image viewer
			$(".image_modal").fancybox( {
				'overlayOpacity' : 0.0,
				'showCloseButton' : true,
				'centerOnScroll' : false,
				'autoscale' : true
			});

			$(".iframe1").fancybox( {
			'left':10,
			'overlayOpacity' : 0.0,
			'showCloseButton' : true,
			'frameHeight' : 300,
			'frameWidth' : 350
			});

			// special size for patient portal
			$(".small_modal").fancybox( {
			'overlayOpacity' : 0.0,
			'showCloseButton' : true,
			'frameHeight' : 200,
			'frameWidth' : 380,
								'centerOnScroll' : false
			});

			<?php if ($active_reminders || $all_allergy_alerts) { ?>
				// show the active reminder modal
				$("#reminder_popup_link").fancybox({
					'overlayOpacity' : 0.0,
					'showCloseButton' : true,
					'frameHeight' : 500,
					'frameWidth' : 500,
					'centerOnScroll' : false
				}).trigger('click');
			<?php } ?>


  		var frm_save_soap = <?php echo isset($_REQUEST['frm_save_soap']) ? $_REQUEST['frm_save_soap'] : '0' ;  ?>;
  		if(frm_save_soap){
    			setTimeout(function(){
      			toggleIndicator(this,"allergy_ps_expand");
    			}, 1500);
			}

	  // Load Patient Category session value
	 		 $.ajax({
						type: "POST",
						url: "../../../library/session_pat_cat.php",
						async: false,
						success: function(thedata){
						},
						error:function(){
							alert('ajax error');
						}
  			});

			$.ajax({
				url: "../../patient_file/summary/provisional_dia.php?report",
				async: false,
				success: function(response){
					$('#prov_dia_ps_expand').empty().append(response);
				}
			});

			$.ajax({
				url: "../../patient_file/summary/final_dia.php?report",
				async: false,
				success: function(response){
					$('#final_dia_ps_expand').empty().append(response);
				}
			});

			$.ajax({
					url: "../../patient_file/summary/rmc_prescription.php?report",
					async: false,
					success: function(response){
						$('#presc_ps_expand').empty().append(response);
					},
				error:function(){
					alert('ajax error');
				}
				});
});



// JavaScript stuff to do when a new patient is set.
//
function setMyPatient() {
	<?php if ($GLOBALS['concurrent_layout']) { ?>
 	// Avoid race conditions with loading of the left_nav or Title frame.
		if (!parent.allFramesLoaded()) {
			setTimeout("setMyPatient()", 500);
			return;
		}	
		<?php if (isset($_GET['set_pid'])){?>
				parent.left_nav.setPatient(
				<?php echo "'" .htmlspecialchars(($result['fname']) . " " . ($result['lname']),ENT_QUOTES) .
					"'," . htmlspecialchars($pid,ENT_QUOTES) . ",'" . htmlspecialchars(($result['pubpid']),ENT_QUOTES) .
					"','', ' " . htmlspecialchars(xl('DOB') . ": " . oeFormatShortDate($result['DOB_YMD']) . " " . xl('Age') . ": " . getPatientAgeDisplay($result['DOB_YMD']), ENT_QUOTES) .
					" " .xl('Sex').": ".htmlspecialchars(($result['sex']),ENT_QUOTES)."'"; ?>);

					var EncounterDateArray = new Array;
					var CalendarCategoryArray = new Array;
					var EncounterIdArray = new Array;
					var Count = 0;
					<?php $p_cat = sqlStatement("select title from list_options where list_id ='Patient_Category' and option_id = ?",array($result['patient_category']));
					if(sqlNumRows($p_cat) > 0){
						while( $out = sqlFetchArray($p_cat) ) { ?>
						parent.left_nav.setPatientCategory( <?php echo "'".htmlspecialchars($out['title'])."'";?>);
					<?php 	
						}	 	
					}
						//Encounter details are stored to javacript as array.
							$result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe ".
								" left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? order by fe.date desc", array($pid));
							if(sqlNumRows($result4)>0) {
								while($rowresult4 = sqlFetchArray($result4)) {?>
										EncounterIdArray[Count] = '<?php echo htmlspecialchars($rowresult4['encounter'], ENT_QUOTES); ?>';
										EncounterDateArray[Count] = '<?php echo htmlspecialchars(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date']))), ENT_QUOTES); ?>';
										CalendarCategoryArray[Count] = '<?php echo htmlspecialchars(xl_appt_category($rowresult4['pc_catname']), ENT_QUOTES); ?>';
										Count++;
									<?php }
							}
					?>
 					parent.left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);
			<?php } // end setting new pid ?>

				parent.left_nav.setRadio(window.name, 'dem');
				parent.left_nav.syncRadios();

				<?php 
					if ( (isset($_GET['set_pid']) ) && (isset($_GET['set_encounterid'])) && ( intval($_GET['set_encounterid']) > 0 ) ) {
							$encounter = intval($_GET['set_encounterid']);
							$_SESSION['encounter'] = $encounter;
							$query_result = sqlQuery("SELECT `date` FROM `form_encounter` WHERE `encounter` = ?", array($encounter)); ?>
							//var othername = (window.name == 'RTop') ? 'RBot' : 'RTop';
							var othername = 'RTop';
							parent.left_nav.setEncounter('<?php echo oeFormatShortDate(date("Y-m-d", strtotime($query_result['date']))); ?>', '<?php echo attr($encounter); ?>', othername);
							parent.left_nav.setRadio(othername, 'dem');
							parent.frames[othername].location.href = 'demographics.php?opener_save=true';
							//parent.frames[othername].location.href = '../encounter/encounter_top.php?set_encounter=' + <?php echo attr($encounter);?> + '&pid=' + <?php echo attr($pid);?>;
							<?php 
					} // end setting new encounter id (only if new pid is also set)
			 } // end concurrent layout ?>
}

$(window).load(function() {
 	setMyPatient();
});
</script>
</head>
<body class="body_top">
	<a href='../reminder/active_reminder_popup.php' id='reminder_popup_link' style='visibility: false;' class='iframe' onclick='top.restoreSession()'></a>
	<?php
 		$thisauth = acl_check('patients', 'demo');
 		if ($thisauth) {
  		if ($result['squad'] && ! acl_check('squads', $result['squad']))
   				$thisauth = 0;
 		}
 		if (!$thisauth) {
  		echo "<p>(" . htmlspecialchars(xl('Demographics not authorized'),ENT_NOQUOTES) . ")</p>\n";
  		echo "</body>\n</html>\n";
  		exit();
 		}
 		if ($thisauth) {
					echo "<table style='width: 100%'><tr><td><span class='title'>" .
					htmlspecialchars(getPatientName($pid),ENT_NOQUOTES) .
					"</span></td><td style='float: right'><span class='title'>";
					if(acl_check('admin', 'super') && $_SESSION['encounter'] > 0)
						echo "<a href='toggledivs(this.id,this.id);' id='enc_del' class='css_button' onclick='return deleteme()'><span>". xl('Encounter Delete') ."</span></a>";
						echo "</span></td>";

					if (acl_check('admin', 'super') && $GLOBALS['allow_pat_delete']) {
						echo "<td style='padding-left:1em;'><a class='css_button iframe' href='../deleter.php?patient=" .
						htmlspecialchars($pid,ENT_QUOTES) . "' onclick='top.restoreSession()'>" .
						"<span>".htmlspecialchars(xl('Delete'),ENT_NOQUOTES).
						"</span></a></td>";
					}
					if($GLOBALS['erx_enable']){
						echo '<td style="padding-left:1em;"><a class="css_button" href="../../eRx.php?page=medentry" onclick="top.restoreSession()">';
						echo "<span>".htmlspecialchars(xl('NewCrop MedEntry'),ENT_NOQUOTES)."</span></a></td>";
						echo '<td style="padding-left:1em;"><a class="css_button iframe1" href="../../soap_functions/soap_accountStatusDetails.php" onclick="top.restoreSession()">';
						echo "<span>".htmlspecialchars(xl('NewCrop Account Status'),ENT_NOQUOTES)."</span></a></td><td id='accountstatus'></td>";
					}
					//Patient Portal
					$portalUserSetting = true; //flag to see if patient has authorized access to portal
					if($GLOBALS['portal_onsite_enable'] && $GLOBALS['portal_onsite_address']){
						$portalStatus = sqlQuery("SELECT allow_patient_portal FROM patient_data WHERE pid=?",array($pid));
						if ($portalStatus['allow_patient_portal']=='YES') {
							$portalLogin = sqlQuery("SELECT pid FROM `patient_access_onsite` WHERE `pid`=?", array($pid));
							echo "<td style='padding-left:1em;'><a class='css_button iframe small_modal' href='create_portallogin.php?portalsite=on&patient=" . htmlspecialchars($pid,ENT_QUOTES) . "' onclick='top.restoreSession()'>";
							if (empty($portalLogin)) {
								echo "<span>".htmlspecialchars(xl('Create Onsite Portal Credentials'),ENT_NOQUOTES)."</span></a></td>";
							}
							else {
								echo "<span>".htmlspecialchars(xl('Reset Onsite Portal Credentials'),ENT_NOQUOTES)."</span></a></td>";
							}
						}
						else {
							$portalUserSetting = false;
						}
					}
					if($GLOBALS['portal_offsite_enable'] && $GLOBALS['portal_offsite_address']){
						$portalStatus = sqlQuery("SELECT allow_patient_portal FROM patient_data WHERE pid=?",array($pid));
						if ($portalStatus['allow_patient_portal']=='YES') {
							$portalLogin = sqlQuery("SELECT pid FROM `patient_access_offsite` WHERE `pid`=?", array($pid));
							echo "<td style='padding-left:1em;'><a class='css_button iframe small_modal' href='create_portallogin.php?portalsite=off&patient=" . htmlspecialchars($pid,ENT_QUOTES) . "' onclick='top.restoreSession()'>";
							if (empty($portalLogin)) {
								echo "<span>".htmlspecialchars(xl('Create Offsite Portal Credentials'),ENT_NOQUOTES)."</span></a></td>";
							}
							else {
								echo "<span>".htmlspecialchars(xl('Reset Offsite Portal Credentials'),ENT_NOQUOTES)."</span></a></td>";
							}
						}
						else {
							$portalUserSetting = false;
						}
					}
					if (!($portalUserSetting)) {
						// Show that the patient has not authorized portal access
						echo "<td style='padding-left:1em;'>" . htmlspecialchars( xl('Patient has not authorized the Patient Portal.'), ENT_NOQUOTES) . "</td>";
					}
					//Patient Portal

					// If patient is deceased, then show this (along with the number of days patient has been deceased for)
					$days_deceased = is_patient_deceased($pid);
					if ($days_deceased) {
						echo "<td style='padding-left:1em;font-weight:bold;color:red'>" . htmlspecialchars( xl('DECEASED') ,ENT_NOQUOTES) . " (" . htmlspecialchars($days_deceased,ENT_NOQUOTES) . " " .  htmlspecialchars( xl('days ago') ,ENT_NOQUOTES) . ")</td>";
					}

					echo "</tr></table>";
 	}

// Get the document ID of the patient ID card if access to it is wanted here.
$idcard_doc_id = false;
if ($GLOBALS['patient_id_category_name']) {
  $idcard_doc_id = get_document_by_catg($pid, $GLOBALS['patient_id_category_name']);
}

if(!in_array($_SESSION['usergroup'], $limit_access_group))
{?>
		<table cellspacing='0' cellpadding='0' border='0'>
			<tr>
				<td class="small" colspan='4'>
					<a href="../history/history.php" onclick='top.restoreSession()'>
					<?php echo htmlspecialchars(xl('History'),ENT_NOQUOTES); ?></a>
					|
					<?php //note that we have temporarily removed report screen from the modal view ?>
					<a href="../report/patient_report.php" onclick='top.restoreSession()'>
					<?php echo htmlspecialchars(xl('Report'),ENT_NOQUOTES); ?></a>
					|
					<?php //note that we have temporarily removed document screen from the modal view ?>
					<a href="../../../controller.php?document&list&patient_id=<?php echo $pid;?>" onclick='top.restoreSession()'>
					<?php echo htmlspecialchars(xl('Documents'),ENT_NOQUOTES); ?></a>
					|
					<a href="../transaction/transactions.php" class='iframe large_modal' onclick='top.restoreSession()'>
					<?php echo htmlspecialchars(xl('Transactions'),ENT_NOQUOTES); ?></a>
					|
					<a href="stats_full.php?active=all" onclick='top.restoreSession()'>
					<?php echo htmlspecialchars(xl('Issues'),ENT_NOQUOTES); ?></a>
					|
					<a href="../../reports/pat_ledger.php?form=1&patient_id=<?php echo attr($pid);?>" onclick='top.restoreSession()'>
					<?php echo xlt('Ledger'); ?></a>
					|
					<a href="../../reports/external_data.php" onclick='top.restoreSession()'>
					<?php echo xlt('External Data'); ?></a>
					<!-- DISPLAYING HOOKS STARTS HERE -->
					<?php
						$module_query = sqlStatement("SELECT msh.*,ms.menu_name,ms.path,m.mod_ui_name,m.type FROM modules_hooks_settings AS msh
																					LEFT OUTER JOIN modules_settings AS ms ON obj_name=enabled_hooks AND ms.mod_id=msh.mod_id
																					LEFT OUTER JOIN modules AS m ON m.mod_id=ms.mod_id
																					WHERE fld_type=3 AND mod_active=1 AND sql_run=1 AND attached_to='demographics' ORDER BY mod_id");
						$DivId = 'mod_installer';
						if (sqlNumRows($module_query)) {
							$jid 	= 0;
							$modid 	= '';
							while ($modulerow = sqlFetchArray($module_query)) {
								$DivId 		= 'mod_'.$modulerow['mod_id'];
								$new_category 	= $modulerow['mod_ui_name'];
								$modulePath 	= "";
								$added      	= "";
								if($modulerow['type'] == 0) {
									$modulePath 	= $GLOBALS['customModDir'];
									$added		= "";
								}
								else{
									$added		= "index";
									$modulePath 	= $GLOBALS['zendModDir'];
								}
								$relative_link 	= "../../modules/".$modulePath."/".$modulerow['path'];
								$nickname 	= $modulerow['menu_name'] ? $modulerow['menu_name'] : 'Noname';
								$jid++;
								$modid = $modulerow['mod_id'];
								?>
								|
								<a href="<?php echo $relative_link; ?>" onclick='top.restoreSession()'>
								<?php echo htmlspecialchars($nickname,ENT_NOQUOTES); ?></a>
							<?php
							}
						}?>
				<!-- DISPLAYING HOOKS ENDS HERE -->
				</td>
			</tr>
		</table> <!-- end header -->
<?php }?>

<div style='margin-top:10px' id ="main_demo">
	<!-- start main content div -->
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
  		<tr>
	   		<td class="demographics-box" align="left" valign="top">
    		<!-- start left column div -->
      		  <div style='float:left; margin-right:20px;width: 98%;'>
     			<table class="demo_table" cellspacing=0 cellpadding=0 style="width: 100%;">
   			 	  <!-- Patient Encounter Tab -->
					<tr>
						<td>
								<?php
									$enc_auth = ($enc_form_id > 0) ? (($cur_date == 1) ? true : acl_check('admin', 'super')) : false;
									// Encounter expand collapse widget
									$widgetTitle = xl("Patient Encounter");
									$widgetLabel = "encounter";
									$widgetButtonLabel = xl("Edit");
									$widgetButtonLink = "return edit_enc(". $enc_form_id .");";
									$widgetButtonClass = "";
									$linkMethod = "javascript";
									$bodyClass = "summary_item small";
									$widgetAuth = $enc_auth;
									$fixedWidth = false;
									expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
									$widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
									$widgetAuth, $fixedWidth);
									?>
									<div>
									<?php include_once('../../forms/newpatient/report.php');
											call_user_func("newpatient" . "_report", $pid, $encounter, 2, $enc_form_id);
									?>
									</div>
						</td>
					</tr>

					<!-- Demo Graphics Tab -->
      				<tr>
       					<td>
							<?php
							// Demographics expand collapse widget
							$widgetTitle = xl("Demographics");
							$widgetLabel = "demographics";
							$widgetButtonLabel = xl("Edit");
							$widgetButtonLink = "demographics_full.php";
							$widgetButtonClass = "";
							$linkMethod = "html";
							$bodyClass = "";
							$widgetAuth = acl_check('patients', 'demo', '', 'write');
							$fixedWidth = true;
							expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
							?>
							<div id="DEM" >
								<ul class="tabNav">
									<?php display_layout_tabs('DEM', $result, $result2); ?>
								</ul>
								<div class="tabContainer">
									<?php display_layout_tabs_data('DEM', $result, $result2); ?>
								</div>
							</div>
						</td>
      				</tr>
		  			<!-- Provisional Diagnosis Tab -->
					<tr>
 						<td >
							<?php
							$prov_dia_btn = ($_SESSION['encounter'] > 0) ? "Edit" : "Add";
							$prov_auth = ($cur_date == 1) ? true : acl_check('admin', 'super');
							
							$widgetTitle = xl("Provisional Diagnosis");
							$widgetLabel = "prov_dia";
							$widgetButtonLabel = $prov_dia_btn;
							$widgetButtonLink = "return edit_prov_dia()";
							$widgetButtonClass = "btn_prov_dia";
							$linkMethod = "javascript";
							$bodyClass = "notab";
							// check to see if any labdata exist
							$widgetAuth = $prov_auth;
							$fixedWidth = true;
							expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,$widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,$widgetAuth, $fixedWidth);
							?>
 					  		<br/>
  							 <div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
  				    	</td>
 					</tr>
  					<!-- Final Diagnosis Tab -->
					<tr>
		 				<td>
							<?php
								$final_dia_btn = ($_SESSION['encounter'] > 0) ? "Edit" : "Add";
								$final_auth = ($cur_date == 1) ? true : acl_check('admin', 'super');
								$widgetTitle = xl("Final Diagnosis");
								$widgetLabel = "final_dia";
								$widgetButtonLabel = $final_dia_btn;
								$widgetButtonLink = "return edit_final_dia()";
								$widgetButtonClass = "btn_final_dia";
								$linkMethod = "javascript";
								$bodyClass = "notab";
								// check to see if any labdata exist
								$widgetAuth = $final_auth;
								$fixedWidth = true;
								expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,$widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,$widgetAuth, $fixedWidth);
							?><br/>
			    			<div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
				     	 </td>
	  				</tr>
			
					<!-- Investigation Tab -->
    				<tr class="investigation" id="investigation">
     					<td width='auto' class="investiagte">
     	    				<?php // labdata expand collapse widget
							  	$inves_btn = ($_SESSION['encounter'] > 0) ? "Edit" : "Add";
								$inves_auth = ($cur_date == 1) ? true : acl_check('admin', 'super');
								$widgetTitle = xl("Investigation");
								$widgetLabel = "labdata";
								$widgetButtonLabel = $inves_btn;
								$widgetButtonLink = "return edit_investigation()";
								$widgetButtonClass = "btn_inves";
								$linkMethod = "javascript";
								$bodyClass = "notab";
								// check to see if any labdata exist
								$widgetAuth = $inves_auth;
								$fixedWidth = true;
							  expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,$widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
							?>
    		   		 	   <br/>
      						<div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
						</td>
					</tr>
					<!-- Prescriptions Tab -->
  					<tr class="prescription" id="prescription">
    					<td >
							<?php
								$formresult = getFormByEncounter($pid, $encounter, "form_id,deleted,formdir,date");
								$pres_form_id = find_form_id($formresult,'summary');
								//$presc_btn = ($_SESSION['encounter'] > 0) ? "Edit" : "Add";
								$presc_auth = ($cur_date == 1) ? true : acl_check('admin', 'super');
								$presc_btn = ($pres_form_id > 0) ? "Edit" : "Add";
								$widgetTitle = xl("Prescriptions");
								$widgetLabel = "presc";
								$widgetButtonLabel = $presc_btn ;
								$widgetButtonLink = "return edit_prescriptions()";
								$widgetButtonClass = "btn_presc";
								$linkMethod = "javascript";
								$bodyClass = "notab";
								// check to see if any labdata exist
								$widgetAuth = $presc_auth;
								$fixedWidth = true;
								expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,$widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,$widgetAuth, $fixedWidth);
							?><br/>
        					<div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
     					</td>
  					</tr>
					<!-- Procedure Tab -->
					<tr id="procedure" class="procedure">
	    				<td class="proce">
							<?php
								$formresult = getFormByEncounter($pid, $encounter, "form_id,deleted,formdir,date");
								$proc_form_id = find_form_id($formresult,'procedure');
								$proc_auth = ($cur_date == 1) ? true : acl_check('admin', 'super');
								$proc_btn = ($proc_form_id > 0) ? "Edit" : "Add";
								$widgetTitle = xl("Procedure");
								$widgetLabel = "proc";
								$widgetButtonLabel = xl($proc_btn);
								$widgetButtonLink = "return edit_proc(". $proc_form_id .");";
								$widgetButtonClass = "btn_proc";
								$linkMethod = "javascript";
								$bodyClass = "summary_item small";
								$widgetAuth = $proc_auth;
								$fixedWidth = true;
								expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
								?> <br/>
		      					<div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
	      				</td>
	  				</tr>
					<!-- SOAP Note Tab -->
	  				<tr>
	     				<td >
	      					<?php
								$formresult = getFormByEncounter($pid, $encounter, "form_id,deleted,formdir,date");
								$soap_form_id = find_form_id($formresult,'soap');
								$soap_btn = ($soap_form_id > 0) ? "Edit" : "Add";
								$soap_auth = ($cur_date == 1) ? true : acl_check('admin', 'super');
								// SOAP expand collapse widget
								$widgetTitle = xl("SOAP Note");
								$widgetLabel = "soap";
								$widgetButtonLabel = xl($soap_btn);
								$widgetButtonLink = "return edit_soap(". $soap_form_id .");";
								$widgetButtonClass = "btn_soap";
								$linkMethod = "javascript";
								$bodyClass = "summary_item small";
								$widgetAuth = $soap_auth;
								$fixedWidth = true;
								expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
								$widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
								$widgetAuth, $fixedWidth);
							?>
							<div id="soap_con">
							<?php include_once('../../forms/soap/report.php');
									call_user_func("soap" . "_report", $pid, $encounter, 2, $soap_form_id);
							?>
							</div>
	     				  </td>
	    			</tr>

					<!-- Laboratory Tab -->
	     			<tr>
						<td >
							<?php
								$formresult = getFormByEncounter($pid, $encounter, "form_id,deleted,formdir,date");
								$lab_form_id = find_form_id($formresult,'lab');
								$lab_btn = ($lab_form_id > 0) ? "Edit" : "Add";
								$lab_auth = ($cur_date == 1) ? true : acl_check('admin', 'super');
								// LAB expand collapse widget
								$widgetTitle = xl("Laboratory Form");
								$widgetLabel = "lab";
								$widgetButtonLabel = xl($lab_btn);
								$widgetButtonLink = "return edit_lab(". $lab_form_id .");";
								$widgetButtonClass = "btn_lab";
								$linkMethod = "javascript";
								$bodyClass = "summary_item small";
								$widgetAuth = $lab_auth;
								$fixedWidth = true;
								expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
									$widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
									$widgetAuth, $fixedWidth);
								?>
								<div id="lab_con">
									<?php include_once('../../forms/lab/report.php');
										call_user_func("lab"."_report", $pid, $encounter, 2, $lab_form_id);
									?>
								</div>
	       				</td>
	    			</tr>
					<?php
						$formresult = getFormByEncounter($pid, $encounter, "form_id,deleted,formdir,date");
						$vitals_form_id = find_form_id($formresult,'vitals');
						$vitals_btn = ($vitals_form_id > 0) ? "Edit" : "Add";
						$vitals_auth = ($cur_date == 1) ? true : acl_check('admin', 'super');
						if ($vitals_is_registered && acl_check('patients', 'med'))
						{ ?>
							<tr>
								<td >
									<?php // vitals expand collapse widget
										$widgetTitle = xl("Vitals");
										$widgetLabel = "vitals";
										$widgetButtonLabel = xl($vitals_btn);
										$widgetButtonLink = "return edit_vitals(". $vitals_form_id . ");";
										$widgetButtonClass = "btn_vital";
										$linkMethod = "javascript";
										$bodyClass = "notab";
										$widgetAuth = $vitals_auth;
										$fixedWidth = true;
										expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
											$widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
											$widgetAuth, $fixedWidth);
										?>
										<br/>
									<!--div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
									</div-->
									<div id="vital_con">
									<?php include_once($GLOBALS['incdir'] . "/forms/vitals/report.php");
											call_user_func("vitals_report", $pid,$encounter, 2, $vitals_form_id);
									?>
									</div>
								</td>
							</tr>
						<?php } // end if ($vitals_is_registered && acl_check('patients', 'med'))
								// This generates a section similar to Vitals for each LBF form that
								// supports charting.  The form ID is used as the "widget label".
	  
							$gfres = sqlStatement("SELECT option_id, title FROM list_options WHERE " .
								"list_id = 'lbfnames' AND option_value > 0 ORDER BY seq, title");
							while($gfrow = sqlFetchArray($gfres)) 
							{
								?>
									<tr>
									<td>
										<?php // vitals expand collapse widget
												$vitals_form_id = $gfrow['option_id'];
												$widgetTitle = $gfrow['title'];
												$widgetLabel = $vitals_form_id;
												$widgetButtonLabel = xl("Trend");
												$widgetButtonLink = "../encounter/trend_form.php?formname=$vitals_form_id";
												$widgetButtonClass = "";
												$linkMethod = "html";
												$bodyClass = "notab";
												// check to see if any instances exist for this patient
												$existVitals = sqlQuery(
												"SELECT * FROM forms WHERE pid = ? AND formdir = ? AND deleted = 0",
												array($pid, $vitals_form_id));
												$widgetAuth = $existVitals ? true : false;
												$fixedWidth = true;
												expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,$widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,$widgetAuth, $fixedWidth);
											?>
										<br/>
											<div style='margin-left:10px' class='text'><image src='../../pic/ajax-loader.gif'/>
										</div>
										<br/>
										</div> <!-- This is required by expand_collapse_widget(). -->
									</td>
								</tr>
							<?php
							} // end while?>

					<tr>
						<td>
							<div id='stats_div'>
					       	 <br/>
				        		<div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
				    		</div>
						</td>
					</tr>

					<?php 
					if($_SESSION['usergroup']!='Physicians'){?>
					<tr <?php if ($GLOBALS['athletic_team']) echo " style='display:none;'"; ?> >
						<td>
							<?php
							// Billing expand collapse widget
							$widgetTitle = xl("Billing");
							$widgetLabel = "billing";
							$widgetButtonLabel = xl("Edit");
							$widgetButtonLink = "return newEvt();";
							$widgetButtonClass = "";
							$linkMethod = "javascript";
							$bodyClass = "notab ";
							$widgetAuth = false;
							$fixedWidth = true;
							if ($GLOBALS['force_billing_widget_open']) {
								$forceExpandAlways = true;
							}
							else {
									$forceExpandAlways = false;
							}
							expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth, $forceExpandAlways);
			
						?>
						<br>
						<?php
			
								//PATIENT BALANCE,INS BALANCE naina@capminds.com
									$patientbalance = get_patient_balance($pid, false);
								//Debit the patient balance from insurance balance
								$insurancebalance = get_patient_balance($pid, true) - $patientbalance;
								$totalbalance=$patientbalance + $insurancebalance;
								if ($GLOBALS['oer_config']['ws_accounting']['enabled'])
								{
									//Show current balance and billing note, if any.
									echo "<table border='0'><tr><td>" .
									"<table ><tr><td><span class='bold'><font color='red'>" .xlt('Patient Balance Due') ." : " . text(oeFormatMoney($patientbalance)) .
									"</font></span></td></tr>"."<tr><td><span class='bold'><font color='red'>" .xlt('Insurance Balance Due') ." : " . text(oeFormatMoney($insurancebalance)) .
									"</font></span></td></tr>"."<tr><td><span class='bold'><font color='red'>" . xlt('Total Balance Due').  " : " . text(oeFormatMoney($totalbalance)) .
									"</font></span></td></td></tr>";
									if (!empty($result['billing_note'])) 
									{
										echo "<tr><td><span class='bold'><font color='red'>" .xlt('Billing Note') . ":" .text($result['billing_note']) ."</font></span></td></tr>";
									}
									if ($result3['provider'])
									{ 
										// Use provider in case there is an ins record w/ unassigned insco
										echo "<tr><td><span class='bold'>" . xlt('Primary Insurance') . ': ' . text($insco_name) .  "</span>&nbsp;&nbsp;&nbsp;";
										if ($result3['copay'] > 0) 
										{
											echo "<span class='bold'>" . xlt('Copay') . ': ' .  text($result3['copay']) .   "</span>&nbsp;&nbsp;&nbsp;";
										}
										echo "<span class='bold'>" . xlt('Effective Date') . ': ' .  text(oeFormatShortDate($result3['effdate'])) . "</span></td></tr>";
									}
									echo "</table></td></tr></td></tr></table><br>";
							}
						?>
						</div>
						</td>
					</tr>
				<?php }  ?>
			
				<tr>
					<td>
						<?php
							// Billing history expand collapse widget
							$widgetTitle = xl("Bill History");
							$widgetLabel = "bill_history";
							$widgetButtonLabel = xl("Edit");
							$widgetButtonLink = "return ne wEvt();";
							$widgetButtonClass = "";
							$linkMethod = "javascript";
							$bodyClass = "notab";
							$widgetAuth = false;
							$fixedWidth = true;
							expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel, $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth, $forceExpandAlways);
						?><br>
						<?php
							$query="select bill_date, bill_number from patient_billing_history where"." pid=?". " ORDER BY bill_date DESC";
							$bill_res=sqlStatement($query,array($pid));
							$numrows=sqlNumRows($bill_res);
						?>
						<table border='0' cellpadding="1" width='80%'>
							<?php
							if($numrows >0)
							{?>
								<tr class="showborder_head" align='left' height="22">
									<th style="border-style: 1px solid #000" width="140px"><?php echo xlt('Bill Date'); ?></th>
									<th style="border-style: 1px solid #000" width="140px"><?php echo xlt('Bill Number'); ?></th>
								</tr>
								<?php
								while( $row = sqlFetchArray($bill_res) ){  ?>
									<tr>
										<td class=small><?php echo $row['bill_date']; ?></td>
										<td class=small><?php echo $row['bill_number']; ?> </td>
										</tr>
									<?php }
							} else{ ?>
									<tr>
										<td class=small colspan=2><?php echo "No history found"; ?></td>
									</tr>
						<?php  }    ?>
						</table> <br>
					</td>
				</tr>
				<tr>
					<td>
						<?php
							echo "<div>";
							// If there is an ID Card or any Photos show the widget
							$photos = pic_array($pid, $GLOBALS['patient_photo_category_name']);
							if ($photos or $idcard_doc_id )
							{
								$widgetTitle = xl("ID Card") . '/' . xl("Photos");
								$widgetLabel = "photos";
								$linkMethod = "javascript";
								$bodyClass = "notab-right";
								$widgetAuth = false;
								$fixedWidth = false;
								expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel ,
										$widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
										$widgetAuth, $fixedWidth);
								?>
								<br />
								<?php
								if ($idcard_doc_id) {
									image_widget($idcard_doc_id, $GLOBALS['patient_id_category_name']);
									}
								
									foreach ($photos as $photo_doc_id) {
										image_widget($photo_doc_id, $GLOBALS['patient_photo_category_name']);
									}
							}
						 // Advance Directives
						    echo "</div><div>";
							if ($GLOBALS['advance_directives_warning'])
							{
								// advance directives expand collapse widget
								$widgetTitle = xl("Advance Directives");
								$widgetLabel = "directives";
								$widgetButtonLabel = xl("Edit");
								$widgetButtonLink = "return advdirconfigure();";
								$widgetButtonClass = "";
								$linkMethod = "javascript";
								$bodyClass = "summary_item small";
								$widgetAuth = true;
								$fixedWidth = false;
								expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
									$counterFlag = false; //flag to record whether any categories contain ad records
									$query = "SELECT id FROM categories WHERE name='Advance Directive'";
									$myrow2 = sqlQuery($query);
									if ($myrow2) {
									$parentId = $myrow2['id'];
									$query = "SELECT id, name FROM categories WHERE parent=?";
									$resNew1 = sqlStatement($query, array($parentId) );
									while ($myrows3 = sqlFetchArray($resNew1)) {
										$categoryId = $myrows3['id'];
										$nameDoc = $myrows3['name'];
										$query = "SELECT documents.date, documents.id " .
											"FROM documents " .
											"INNER JOIN categories_to_documents " .
											"ON categories_to_documents.document_id=documents.id " .
											"WHERE categories_to_documents.category_id=? " .
											"AND documents.foreign_id=? " .
											"ORDER BY documents.date DESC";
										$resNew2 = sqlStatement($query, array($categoryId, $pid) );
										$limitCounter = 0; // limit to one entry per category
										while (($myrows4 = sqlFetchArray($resNew2)) && ($limitCounter == 0)) {
											$dateTimeDoc = $myrows4['date'];
										// remove time from datetime stamp
										$tempParse = explode(" ",$dateTimeDoc);
										$dateDoc = $tempParse[0];
										$idDoc = $myrows4['id'];
										echo "<a href='$web_root/controller.php?document&retrieve&patient_id=" .
												htmlspecialchars($pid,ENT_QUOTES) . "&document_id=" .
												htmlspecialchars($idDoc,ENT_QUOTES) . "&as_file=true' onclick='top.restoreSession()'>" .
												htmlspecialchars(xl_document_category($nameDoc),ENT_NOQUOTES) . "</a> " .
												htmlspecialchars($dateDoc,ENT_NOQUOTES);
										echo "<br>";
										$limitCounter = $limitCounter + 1;
										$counterFlag = true;
										}
									}
									}
									if (!$counterFlag) {
										echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'),ENT_NOQUOTES);
									} 
					 			 echo "</div>";
						  }  // close advanced dir block

					// This is a feature for a specific client.  -- Rod
						if ($GLOBALS['cene_specific'])
						{
						echo "   <br />\n";
						
							$imagedir  = $GLOBALS['OE_SITE_DIR'] . "/documents/$pid/demographics";
							$imagepath = "$web_root/sites/" . $_SESSION['site_id'] . "/documents/$pid/demographics";
						
						echo "   <a href='' onclick=\"return sendimage($pid, 'photo');\" " .
						"title='Click to attach patient image'>\n";
						if (is_file("$imagedir/photo.jpg")) {
						echo "   <img src='$imagepath/photo.jpg' /></a>\n";
						} else {
						echo "   Attach Patient Image</a><br />\n";
						}
						echo "   <br />&nbsp;<br />\n";
						
						echo "   <a href='' onclick=\"return sendimage($pid, 'fingerprint');\" " .
						"title='Click to attach fingerprint'>\n";
						if (is_file("$imagedir/fingerprint.jpg")) {
						echo "   <img src='$imagepath/fingerprint.jpg' /></a>\n";
						} else {
						echo "   Attach Biometric Fingerprint</a><br />\n";
						}
						echo "   <br />&nbsp;<br />\n";
						}
						
						// This stuff only applies to athletic team use of OpenEMR.  The client
						// insisted on being able to quickly change fitness and return date here:
						//
						if (false && $GLOBALS['athletic_team']) {
						//                  blue      green     yellow    red       orange
						$fitcolors = array('#6677ff','#00cc00','#ffff00','#ff3333','#ff8800','#ffeecc','#ffccaa');
						if (!empty($GLOBALS['fitness_colors'])) $fitcolors = $GLOBALS['fitness_colors'];
						$fitcolor = $fitcolors[0];
						$form_fitness   = $_POST['form_fitness'];
						$form_userdate1 = fixDate($_POST['form_userdate1'], '');
						$form_issue_id  = $_POST['form_issue_id'];
						if ($form_submit) {
						$returndate = $form_userdate1 ? "'$form_userdate1'" : "NULL";
						sqlStatement("UPDATE patient_data SET fitness = ?, " .
						"userdate1 = ? WHERE pid = ?", array($form_fitness, $returndate, $pid) );
						// Update return date in the designated issue, if requested.
						if ($form_issue_id) {
						sqlStatement("UPDATE lists SET returndate = ? WHERE " .
							"id = ?", array($returndate, $form_issue_id) );
						}
						} else {
						$form_fitness = $result['fitness'];
						if (! $form_fitness) $form_fitness = 1;
						$form_userdate1 = $result['userdate1'];
						}
						$fitcolor = $fitcolors[$form_fitness - 1];
						echo "   <form method='post' action='demographics.php' onsubmit='return validate()'>\n";
						echo "   <span class='bold'>Fitness to Play:</span><br />\n";
						echo "   <select name='form_fitness' style='background-color:$fitcolor'>\n";
						$res = sqlStatement("SELECT * FROM list_options WHERE " .
						"list_id = 'fitness' ORDER BY seq");
						while ($row = sqlFetchArray($res)) {
						$key = $row['option_id'];
						echo "    <option value='" . htmlspecialchars($key,ENT_QUOTES) . "'";
						if ($key == $form_fitness) echo " selected";
						echo ">" . htmlspecialchars($row['title'],ENT_NOQUOTES) . "</option>\n";
						}
						echo "   </select>\n";
						echo "   <br /><span class='bold'>Return to Play:</span><br>\n";
						echo "   <input type='text' size='10' name='form_userdate1' id='form_userdate1' " .
						"value='$form_userdate1' " .
						"title='" . htmlspecialchars(xl('yyyy-mm-dd Date of return to play'),ENT_QUOTES) . "' " .
						"onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />\n" .
						"   <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22' " .
						"id='img_userdate1' border='0' alt='[?]' style='cursor:pointer' " .
						"title='" . htmlspecialchars(xl('Click here to choose a date'),ENT_QUOTES) . "'>\n";
						echo "   <input type='hidden' name='form_original_userdate1' value='" . htmlspecialchars($form_userdate1,ENT_QUOTES) . "' />\n";
						echo "   <input type='hidden' name='form_issue_id' value='' />\n";
						echo "<p><input type='submit' name='form_submit' value='Change' /></p>\n";
						echo "   </form>\n";
						}
					 else
					 {
					 $showpast = $GLOBALS['num_past_appointments_to_show'];
					 }
					
					if (isset($pid) && !$GLOBALS['disable_calendar'] && $showpast > 0) {
					$query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " .
					"e.pc_startTime, e.pc_hometext, u.fname, u.lname, u.mname, " .
					"c.pc_catname, e.pc_apptstatus " .
					"FROM openemr_postcalendar_events AS e, users AS u, " .
					"openemr_postcalendar_categories AS c WHERE " .
					"e.pc_pid = ? AND e.pc_eventDate < CURRENT_DATE AND " .
					"u.id = e.pc_aid AND e.pc_catid = c.pc_catid " .
					"ORDER BY e.pc_eventDate $direction , e.pc_startTime DESC " .
					  "LIMIT " . $showpast;
					
					 $pres = sqlStatement($query, array($pid) );
					
					// appointments expand collapse widget
					    $widgetTitle = xl("Past Appoinments");
					    $widgetLabel = "past_appointments";
					    $widgetButtonLabel = '';
					    $widgetButtonLink = '';
					    $widgetButtonClass = '';
					    $linkMethod = "javascript";
					    $bodyClass = "summary_item small";
					    $widgetAuth = false; //no button
					    $fixedWidth = false;
					    expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel , $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass, $widgetAuth, $fixedWidth);
					    $count = 0;
					    while($row = sqlFetchArray($pres)) {
					        $count++;
					        $dayname = date("l", strtotime($row['pc_eventDate']));
					        $dispampm = "am";
					        $disphour = substr($row['pc_startTime'], 0, 2) + 0;
					        $dispmin  = substr($row['pc_startTime'], 3, 2);
					        if ($disphour >= 12) {
					            $dispampm = "pm";
					            if ($disphour > 12) $disphour -= 12;
					        }
					        if ($row['pc_hometext'] != "") {
					            $etitle = xl('Comments').": ".($row['pc_hometext'])."\r\n".$etitle;
					        }
					        echo "<a href='javascript:oldEvt(" . htmlspecialchars($row['pc_eid'],ENT_QUOTES) . ")' title='" . htmlspecialchars($etitle,ENT_QUOTES) . "'>";
					        echo "<b>" . htmlspecialchars(xl($dayname) . ", " . $row['pc_eventDate'],ENT_NOQUOTES) . "</b>" . xlt("Status") .  "(";
					        echo " " .  generate_display_field(array('data_type'=>'1','list_id'=>'apptstat'),$row['pc_apptstatus']) . ")<br>";   // can't use special char parser on this
					        echo htmlspecialchars("$disphour:$dispmin ") . xl($dispampm) . " ";
					        echo htmlspecialchars($row['fname'] . " " . $row['lname'],ENT_NOQUOTES) . "</a><br>\n";
					    }
					    if (isset($pres) && $res != null) {
					       if ( $count < 1 ) {
					           echo "&nbsp;&nbsp;" . htmlspecialchars(xl('None'),ENT_NOQUOTES);
					       }
					    echo "</div>";
					    }
					}
					// END of past appointments
					
					  ?>
				</td>
			</tr>

       		<?php // TRACK ANYTHING -----

			// Determine if track_anything form is in use for this site.
			$tmp = sqlQuery("SELECT count(*) AS count FROM registry WHERE "."directory = 'track_anything' AND state = 1");
			$track_is_registered = $tmp['count'];
			if($track_is_registered)
			{
				  echo "<tr> <td>";
				  // track_anything expand collapse widget
				  $widgetTitle = xl("Tracks");
				  $widgetLabel = "track_anything";
				  $widgetButtonLabel = xl("Tracks");
				  $widgetButtonLink = "../../forms/track_anything/create.php";
				  $widgetButtonClass = "";
				  $widgetAuth = "";  // don't show the button
				  $linkMethod = "html";
				  $bodyClass = "notab";
				  // check to see if any tracks exist
				  $spruch = "SELECT id " .
				    "FROM forms " .
				    "WHERE pid = ? " .
				    "AND formdir = ? ";
				  $existTracks = sqlQuery($spruch, array($pid, "track_anything") );
				
				  $fixedWidth = false;
				  expand_collapse_widget($widgetTitle, $widgetLabel, $widgetButtonLabel,
				    $widgetButtonLink, $widgetButtonClass, $linkMethod, $bodyClass,
				    $widgetAuth, $fixedWidth);
				?>
					  <br/>
			  		<div style='margin-left:10px' class='text'><img src='../../pic/ajax-loader.gif'/></div><br/>
			 	 </td>
				</tr><?php
		  		}  // end track_anything ?>
			</table>
		</div>
    <!-- end left column div -->
			</td>
			</tr>
			</table>	


     <!-- start right column div -->

<?php if (false && $GLOBALS['athletic_team']) 
	  { ?>
		<script language='JavaScript'>
		 Calendar.setup({inputField:"form_userdate1", ifFormat:"%Y-%m-%d", button:"img_userdate1"});
		</script>
<?php } ?>

<?php
  function find_form_id($formresult,$formname){
    if(!empty($formresult)){
     foreach ($formresult as $key) {
       if($key['formdir'] == $formname && $key['deleted'] != 1){
         $id = $key['form_id'];
       }
     }
     return ($id != '') ? $id : 0;
   } else {
     return 0;
   }
  }

  function check_cur_date($formresult,$formname){
   if(!empty($formresult)){
    foreach ($formresult as $key) {
      $date = strtotime($key['date']);
      $date = date("Y-m-d", $date);
      if($key['formdir'] == $formname){
        if ($key['deleted'] != 1 && ($date == date("Y-m-d") || $date == date("Y-m-d", strtotime("-1 days")) || $date == date("Y-m-d", strtotime("-2 days")) )){
          return true;
        }else {
          return false;
        }
      }
    }
   }else {
	    return false;
   }
  }
 ?>

<?php 

if(!empty($pateint_id) && !empty($patient_eid)){
	
	$_query = "select pc_catname from openemr_postcalendar_categories where  pc_catid = (select  pc_catid from form_encounter where pid=".$pateint_id." and encounter=".$patient_eid.")";
	$vist_cat = sqlQuery($_query);
   
	$_query_pro_dia = "select count(id) as count from provisional_dia where pid=".$pateint_id." and encounter=".$patient_eid;
	$pro_dia_count = sqlQuery($_query_pro_dia);
	
	$_query_final_dia = "select count(*) as count from (select * from provisional_dia where pid =".$pateint_id." and encounter=".$patient_eid." union select * from final_dia where pid = ".$pateint_id." and encounter=".$patient_eid.") as main";
	$final_dia_count = sqlQuery($_query_final_dia);
}else{
	
	$no_enc = true;
}
?>

<script language='JavaScript'>
$(document).ready(function(){
	$('#proc_ps_expand,#soap_ps_expand').css('background','#f7f7f7 none repeat scroll 0 0');
	<?php if($no_enc){
	?>
	 $(window).load(function()
	             {
	             //  $(".btn_"+type).trigger("click");
	               
	               var frm = parent.frames['left_nav'].document;
	               $(frm).find('.body_nav').css({'opacity':'1','pointer-events':'auto' });
	               
	               var frm = parent.frames['Title'].document;
	               $(frm).find('.body_title').css({'opacity':'1','pointer-events':'auto' });
	               
	             });  
		
	<?php 	
	}?>
	
	<?php  
	
	if($vist_cat['pc_catname'] == 'New Visit')
	{?>

		diagnosis_check(<?php echo json_encode($pro_dia_count['count']);?>,'prov_dia');
		<?php 			
		}
 	?>
	
	<?php 
		if($vist_cat['pc_catname'] == 'Existing Visit')
		{?>
			diagnosis_check(<?php echo json_encode($final_dia_count['count']);?>,'final_dia');
		<?php 			
		}
	 ?>
	
function diagnosis_check(dia_count,type){

	if(dia_count > 0)
	{
		 $(window).load(function()
	             {
	             //  $(".btn_"+type).trigger("click");
	               
	               var frm = parent.frames['left_nav'].document;
	               $(frm).find('.body_nav').css({'opacity':'1','pointer-events':'auto' });
	               
	               var frm = parent.frames['Title'].document;
	               $(frm).find('.body_title').css({'opacity':'1','pointer-events':'auto' });
	               
	             });  
		}else{
			 $(window).load(function()
		             {
     
				 	   $(".btn_"+type).trigger("click");
				 	   
		               var frm = parent.frames['left_nav'].document;
		               $(frm).find('.body_nav').css({'opacity':'0.5','pointer-events':'none' });
		               
		               var frm = parent.frames['Title'].document;
		               $(frm).find('.body_title').css({'opacity':'0.5','pointer-events':'none' });
		               
		             });  
			}	
}
});
</script>

</body>
</html>
