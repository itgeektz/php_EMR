<?php
/*
* @package OpenEMR
* @author ViSolve (services@visolve.com)
* @link http://www.open-emr.org
* */


//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//
include_once(dirname(__FILE__).'/../../globals.php');
include_once($GLOBALS["srcdir"]."/api.inc");
include_once($GLOBALS['srcdir'].'/lists.inc');
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/acl.inc');
include_once($GLOBALS['srcdir'].'/options.inc.php');
include_once($GLOBALS['srcdir'].'/formdata.inc.php');

$pid = $_SESSION['pid'];
$encounter = $_SESSION['encounter'];
$form_id = $_REQUEST['frm_id'];


?>


<html>
<head>
<?php html_header_show();?>

<style>
.red{
	color: red;
}

.green{
	color: green;
}
.text{padding-left:0px !important;}
</style>
</head>
<body>
<div id='procdata' style='margin-top: 3px; background: #f7f7f7 none repeat scroll 0px 0px;  border-top-width: 0px; padding-bottom: 15px; padding-top: 15px;'>
 <table style="padding-bottom: 15px;">
		<thead>
			<tr style="background: #167f92 none repeat scroll 0 0; color:white;font-size:10pt;">
				<th style="width: 145px;">Type</th>
				<th style="width: 400px;">Test Name</th>
				<th style="width: 175px;">Status</th>
				<th style="width: 250px;">ServicedBy</th>
				<th style="width: 500px;">Comments</th>
			</tr>
		</thead>
		<?php
		
		$data = sqlQuery ( "select service_name,service_code,comments,bill_status,service_status,serviced_by from form_procedure where id=".$form_id." and pid =".$pid." and activity = 1 order by date DESC LIMIT 0,1");
		if ($data) {
			$service_name = json_decode($data{"service_name"});
			$service_code = json_decode($data{"service_code"});
			$comments = json_decode($data{"comments"});
			$bill_status = json_decode($data{"bill_status"});
			$service_status = json_decode($data{'service_status'});
			$serviced_by = json_decode($data{'serviced_by'});
		
			$cnt = count($service_name);
		
			print "<table class=\"form_procedure\">";
			$tmp = 0;
			for ($i=0; $i < $cnt; $i++)
			{
				if ($bill_status[$i] == "" || $bill_status[$i] == '0') {
					$status = "Unbilled";
					$color = "red";
				}else {
					$status = "Billed";
					$color = "green";
				}
				$tmp = $i + 1;
				if($service_name[$i] != '')
				{
			 ?> 	
			   	
		  <tr>
 		            <td style="width: 150px;"><label class=bold >Procedure <?php echo $tmp; ?> </label></td>
 		            <td class="text" style="width: 400px;"><span style="margin-left:20px;"><?php echo  $service_name[$i]; ?>  <label class='bold  " <?php echo $color; ?>  "'>(<?php echo $status; ?>)</label></span></td>
 		            <td class="text s_status" style="width: 175px;text-align:center;"><?php if (empty($service_status[$i])){echo "Not Done";}else{echo $service_status[$i];}?> </td>
 					<td class="text"  style="width: 250px;text-align:center;" > <?php echo $serviced_by[$i]; ?></td>
 		           	<td class="text" style="width: 500px;"><?php echo "<span style=\"padding-left: 30px;\">". $comments[$i]."</span>"  ;?> </td>	  
 					  		
 				</tr>
 	<?php 	               	 		 
				}  
			 } 
		   }
		  
	   
	   ?>

	  </table>
	 </table> 
</div>
<script>

var status_count = 0; 

 	$('.form_procedure tr').each(function(){

	var value = $(this).find('.s_status').text();

	if($.trim(value).length){
		
		if($.trim(value) == 'Done')
		{
			$(this).find('.s_status').css('color','green');
			
		}
		else{
			
			status_count = status_count + 1;
			
			$(this).find('.s_status').css('color','red');
			
			}
	}


	 
		}); 

if(status_count == 0){

	$('.procedure_note').css('background-color','green').append('<span>'+status_count+'</span>');
}else{
		$('.procedure_note').append('<span>'+status_count+'</span>');
		
}
</script>

</body>
</html>
