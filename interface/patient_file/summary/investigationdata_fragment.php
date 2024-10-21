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
require_once('../../globals.php');
require_once($GLOBALS['srcdir'].'/lists.inc');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/acl.inc');
require_once($GLOBALS['srcdir'].'/options.inc.php');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');

$pid = $_SESSION['pid'];
$encounter = $_SESSION['encounter'];
?>


<html>
<head>
<?php html_header_show();?>
  <script src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-3.2.1.min.js" type="text/javascript" ></script>
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
<div id='labdata' style='margin-top: 3px;background: #f7f7f7 none repeat scroll 0px 0px; padding-left: 40px; border-top-width: 0px; padding:15px 10px;'>
<br>
	<span class='text'>
			<a href="https://secure.lancet.co.za/LancetLogin2/"  target = '_blank'> Pathology Results </a></br></br>
		</span>
	<table style="padding-bottom: 15px;">
		<thead>
			<tr style="background: #167f92 none repeat scroll 0 0; color:white;">
				<th style="width: 135px;">Type</th>
				<th style="width: 400px;">Test Name</th>
				<th style="width: 175px;">Status</th>
				<th style="width: 250px;">ServicedBy</th>
				<th style="width: 500px;">Comments</th>
			</tr>
		</thead>
		<?php
		
		$users =array();
		$res = sqlStatement("SELECT ga.name  FROM gacl_groups_aro_map gam LEFT JOIN gacl_aro ga ON gam.aro_id = ga.id LEFT JOIN gacl_aro_groups ag ON ag.id = gam.group_id where ag.name = 'Technician'");
		while ($row = sqlFetchArray($res))
		{
			 
			$users[] = $row['name'];
		}
			$lst_array = array('Laboratory','Intervention','Physical Exam','Imaging');
			$lst_cnt = count($lst_array);
		  for ($i=0; $i < $lst_cnt; $i++) 
		  {
						
						if ($lst_array[$i] == 'Laboratory')
						{
							$tbl_name = "rmc_lab";
						}elseif ($lst_array[$i] == 'Intervention') 
						{
							$tbl_name = "rmc_intervention";
						}elseif ($lst_array[$i] == "Physical Exam") 
						{
							$tbl_name = "rmc_physical";
						}elseif ($lst_array[$i] == 'Imaging') 
						{
							$tbl_name = "rmc_imaging";
						}else {
							
							$tbl_name = "";
						}
			echo "<table class=\"$tbl_name\">";
			
	    	$res = sqlStatement("SELECT service_code,service_name,comments,bill_status,service_status,serviced_by FROM $tbl_name WHERE pid = ? AND encounter = ? ", array($pid, $encounter));
			$tmp = 0;
	  		while ($row = sqlFetchArray($res)) 
	  		{
		 		$tmp = $tmp + 1;
		 		$service_code = $row{'service_code'};
				$service_name = $row{"service_name"};
				$comments = $row{"comments"};
				$bill_status = $row{"bill_status"};
				$service_status = $row{"service_status"};
				$serviced_by  =$row{"serviced_by"};

				if($bill_status == '' || $bill_status == "0")
				{
					$bill_status = "Unbilled";
					$color = "red";
				}else{
					$bill_status = "Billed";
					$color = "green";
				}
				
				if($service_name != "") 
			   {
			   	
			  ?> 	
			   	
			  <tr>
 		            <td style="width: 150px;"><label class=bold ><?php echo $lst_array[$i]. " " .$tmp; ?>  </label></td>
 		            <td class="text " style="width: 400px;"><?php echo  $service_name; ?>  <label class='bold  " <?php echo $color; ?>  "'>(<?php echo $bill_status; ?>)</label>
 		            <td class="text s_status" style="width: 175px;text-align:center;"><?php echo  $service_status;?></td>
 					<td class="text"  style="width: 250px;text-align:center;" ><?php echo $serviced_by;?></td>	   		
 					<td class=text style="width: 500px;"><?php echo "<span style=\"padding-left: 30px;\">". $comments."</span>"  ;?> </td>	  
 							
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

var table_names =['rmc_lab','rmc_imaging','rmc_intervention','rmc_physical'];
var status_count = 0; 
for(var i = 0 ;i < table_names.length;i++)
{
 	$('.'+table_names[i]+' tr').each(function(){

	var value = $(this).find('.s_status').text();

	if(value == 'Done')
	{
		$(this).find('.s_status').css('color','green');
		
	}
	else{

		status_count = status_count + 1;
		
		$(this).find('.s_status').css('color','red');
		
		}	
	 
		}); 
}
if(status_count == 0){

	$('.investicate_note').css('background-color','green').append('<span>'+status_count+'</span>');
}else{
		$('.investicate_note').append('<span>'+status_count+'</span>');
		
}
</script>

</body>
</html>
