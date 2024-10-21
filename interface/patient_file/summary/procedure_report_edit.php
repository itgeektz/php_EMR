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
<div id='procdata' style='margin-top: 3px; background: #f7f7f7 none repeat scroll 0px 0px; padding-left: 40px; border-top-width: 0px; padding-bottom: 15px; padding-top: 15px;'>
	
<form  name='tech_proc' class='tech_proc'>		
	<table style="padding-bottom: 15px;">
		<thead>
			<tr style="background: #167f92  none repeat scroll 0 0; color:white;">
				<th style="width: 180px;">Type</th>
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

		$data = sqlQuery ( "select service_name,service_code,comments,bill_status,service_status,serviced_by from form_procedure where id=".$form_id." and pid =".$pid." and activity = 1 order by date DESC LIMIT 0,1");
		if ($data) {
			$service_name = json_decode($data{"service_name"});
			$service_code = json_decode($data{"service_code"});
			$comments = json_decode($data{"comments"});
			$bill_status = json_decode($data{"bill_status"});
			$service_status = json_decode($data{'service_status'});
			$serviced_by = json_decode($data{'serviced_by'});
		
			$cnt = count($service_name);
		
			print "<table class=\"form_procedure\" style=\"margin-left: 31px;\">";
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
			  ?> 	
			   	
			  <tr>
 		            <td style="width: 150px;"><label class=bold >Procedure <?php echo $tmp; ?> </label></td>
 		            <td class="text " style="width: 400px;"><span style="margin-left:20px;"><?php echo  $service_name[$i]; ?>  <label class='bold  " <?php echo $color; ?>  "'>(<?php echo $status; ?>)</label></span></td>
 		            <td class="text" style="width: 175px;text-align:center;"><select name="status" class ="s_status">
			                       <option value="Not Done" <?php if ($service_status[$i]=="Not Done") {?>selected <?php }?> >Not Done </option>
			                       <option value="Done" <?php if ($service_status[$i]=="Done") {?>selected <?php }?>>Done</option>
			                       </select> </td>
 					<td class="text"  style="width: 250px;text-align:center;" ><select name="serviced" class ="serviced_by"><option value=""></option> 
 					
 								<?php 
 										foreach ($users as $value)
 		               	 		 	{
 		               	 		 ?>		
 		               	 		 	 	
 		               	 		 	<option value="<?php echo $value; ?>" <?php if($serviced_by[$i] == $value){?>selected <?php }?> ><?php echo $value; ?></option>	
 		               	 		 	
 		               	 		 <?php 		
 		               	 		 	}
 		               	 		 	?>
 									</select></td>	   		
 					<td class="text" style="width: 500px;"><?php echo "<span style=\"padding-left: 30px;\">". $comments[$i]."</span>"  ;?> </td>	  
 							
 					<input type="hidden" name="procedure_id" class="procedure_id" id="procedure_id" value="<?php echo $form_id; ?>"> 
 					<input type="hidden" name="p_id" class="p_id" id="p_id" value="<?php echo $pid; ?>"> 		   		
 				</tr>
 	<?php 	               	 		 
			  	   
			 } 
		   }
		  
	   
	   ?>

	  </table>
	 </table> 
	<div style="width: 300px; padding-top: 20px;"> 
	 	<input type='button' name='proc_save' id ='proc_save' value='Save'/>
	 	<input type='button' value='Cancel' onclick='closeme();' />
	</div>		
</form>
</div>
<script>

$('#proc_save').on('click', function (e) {

	 var status_array = [];
	 var serviced_array =[]; 
	 var data_array = [];
	 var proc_id;
	 var p_id;

      var status;
	 	$('.form_procedure tr').each(function(){
	 		status_array.push( $(this).find('.s_status option:selected').text());
	 		serviced_array.push($(this).find('.serviced_by option:selected').text());
   	 	    proc_id = $(this).find('.procedure_id').val();
   	 	    p_id = $(this).find('.p_id').val();
	    
   	 	 
			}); 

	 	 data_array.push(JSON.stringify(status_array));
	 	 data_array.push(JSON.stringify(serviced_array));
	 	 data_array.push(proc_id);
	 	 data_array.push(p_id);
 
   $.ajax({
   		  type: 'post',
      dataType: 'json',
      	 async:true,
    	   url: '../../../library/ajax/auto_procedure_search.php',
          data:{'data': data_array,'type':'proc_report'},
       success: function (data) {

			if(data){

				alert("Status Updated Successfully");
				window.top.left_nav.loadFrame("dem1", window.name, "patient_file/summary/demographics.php?opener_save=true");
			 }
           
   }
 });   
});

</script>

</body>
</html>
