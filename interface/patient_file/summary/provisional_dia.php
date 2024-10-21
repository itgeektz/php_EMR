<?php
/**
 * add or edit provisional diagnosis
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
require_once($GLOBALS['fileroot'].'/custom/code_types.inc.php');
require_once($GLOBALS['srcdir'].'/csv_like_join.php');
require_once($GLOBALS['srcdir'].'/htmlspecialchars.inc.php');
require_once($GLOBALS['srcdir'].'/formdata.inc.php');


  $pid = $_SESSION['pid'];
  $enc = $_SESSION['encounter'];

if ($_POST['pro_dia_save']) {

  $cnt = count($_POST['update_id']);
  $id = 0;

  //Deleting the issues

  $del_id = $_POST['delete_id'];
  $del_arr = explode(",",$del_id);

  foreach ($del_arr as $value) {
    sqlQuery("DELETE FROM provisional_dia WHERE pid = ? AND id = ?", array($pid, $value));
  }

  for ($j=0; $j < $cnt; $j++) {
    $id = $_POST['update_id'][$j];

    if ($id > 0) {

      $form_title = addslashes($_POST['form_title'][$j] );
      $form_diagnosis = addslashes($_POST['form_diagnosis'][$j]);

      sqlQuery("UPDATE provisional_dia SET pid = '" . $pid . "', encounter = '". $enc ."', title = '". $form_title ."', diagnosis = '" . $form_diagnosis ."' WHERE id = $id");
    } else {
      $form_title = addslashes($_POST['form_title'][$j] );
      $form_diagnosis = addslashes($_POST['form_diagnosis'][$j]);

      sqlInsert("INSERT INTO provisional_dia (pid,encounter,title,diagnosis) VALUES ('". $pid ."','". $enc ."', '". $form_title."','". $form_diagnosis ."')");
    }
  }
  ?>
    <script>
    window.top.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php?opener_save=true');
    </script>
    <?php
}

  $prores = sqlStatement("SELECT * FROM provisional_dia WHERE pid = ? AND encounter = ? ", array($pid, $encounter));

?>

<html>
<head>
<?php html_header_show();?>
<title><?php echo xlt('Provisional Diagnosis'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>
<link rel="stylesheet" href='<?php echo $GLOBALS['webroot']; ?>/library/css/jquery-ui-1.12.1.css' type='text/css'>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-ui-1.12.1.js"></script>
<style>
.bold{
color:#fff !important;
padding-left:10px !important;

}
</style>
<body class="body_top" style="padding-right:0.5em">

<?php
  if(isset($_REQUEST['report']))
  { ?>
 
 <div class="report" style="background: #f7f7f7 none repeat scroll 0 0;;padding-left: 40px;padding-top: 10px;padding-bottom: 10px;"> 
  <table border='0' width='70%'>
	<thead>
    	<tr style="background: #167f92 none repeat scroll 0 0; color:#fff;" >
    		<td class=bold>Description</td>
    		<td class=bold>Code</td>
    	</tr>
	</thead>
    	<?php
      		while ($row = sqlFetchArray($prores)) 
      		{  ?>
      			<tr>
        			<td class=text><?php echo $row['title'] ?></td>
        		    <td class="text"><?php echo $row['diagnosis'] ?></td>
      			</tr>
    			<?php
      		}	?>
  	</table>
  </div>	
  	 	<?php
	} else { ?>
			
			<form method='post' name='theform' action='provisional_dia.php' onsubmit='return validate()' style="background: #f7f7f7 none repeat scroll 0 0;padding-left: 40px;padding-top: 10px;padding-bottom: 10px;">
  				<table border='0' width='70%' class='tbl_provisional'>
				<thead>
    				<tr  style="background: #167f92 none repeat scroll 0 0; color:white;">
    					<td class=bold>Description</td>
    					<td class=bold>Code</td>
    				</tr>
				</thead>
    			<?php
    			if (sqlNumRows($prores) < 1) 
    			{?>
    			
    			<tr class="tr_clone">
     				<input type="hidden" name="update_id[]" class="update_id" id="update_id" value="0">
     				<td style="width: 30%">
      				 	<input type='text' size='40' name='form_title[]' id='form_title_0' class='form_title' autocomplete='off' value='' style='width:100%' />
      				
     				</td>
      				
      				<td id='row_diagnosis' style="width: 30%">
       					<input type='text' size='50' name='form_diagnosis[]' id='form_diagnosis_0' autocomplete='off' class='form_diagnosis' value='<?php echo attr($row['diagnosis']) ?>' title='<?php echo xla('Click to select or change coding'); ?>' style='width: 100%' />
        			
      				</td>
			
				      <td style="width: 10%">
        					<input type="button" class="add" id="add" value="+">
        					<input type="button" class="del" id="del" value="-">
      				  </td>
	
			    </tr>
    		<?php } else {
      			
	    					while ($row = sqlFetchArray($prores))
	    					{    ?>
	      					<tr class="tr_clone">
	       						<input type="hidden" name="update_id[]" class="update_id" id="update_id" value="<?php echo attr($row['id']) ?>">
	       				
	       						<td style="width: 30%">
	    	    					<input type='text' size='40' name='form_title[]' id='form_title_<?php echo attr($row['id']) ?>' class='form_title' autocomplete='off' value='<?php echo attr($row['title']) ?>' style='width:100%' />
		        				
	       						</td>
						        <td id='row_diagnosis' style="width: 30%">
	         						<input type='text' size='50' name='form_diagnosis[]' id='form_diagnosis_<?php echo attr($row['id']) ?>' autocomplete='off' class='form_diagnosis' value='<?php echo attr($row['diagnosis']) ?>' title='<?php echo xla('Click to select or change coding'); ?>' style='width: 100%' />
	          					
	        					</td>
						
						        <td style="width: 10%">
	          							<input type="button" class="add" id="add" value="+">
        								<input type="button" class="del" id="del" value="-">
	        					</td>
						      </tr>
    						  <?php }
     					 }  ?>
				  	</table>
				  <div class="pro_sav_div" style="height: 53px;padding-top: 12px;">
				         <input type='submit' name='pro_dia_save' value='<?php echo xla('Save'); ?>' />
				         <input type='button' value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />
				         <input type="hidden" id="delete_id" name="delete_id" value="0">
				    </div>    
				</form>
		<?php }  ?>
		
<script language='JavaScript'>


$(document).ready(function(){
	


function closeme() {
 window.top.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php?opener_save=true');
}

//Jquery Events
//---------------------------------------------------------------------------------------------------------------

	//Adding New Rows
	//------------------------------------------------------------		

 $('.tbl_provisional').on('click','.add',function() {

	 var cnt=$('.tbl_provisional>tbody>tr:visible').length;
	 var table_name = $('.tbl_provisional');
     var tbl_clone = table_name.find('tr:last').clone();
		 tbl_clone.find(':text').val('');
	     tbl_clone.find('.form_title').removeAttr('id').attr('id', 'form_title_'+cnt);
	     tbl_clone.find('.form_diagnosis').removeAttr('id').attr('id', 'form_diagnosis_'+cnt);
   	     tbl_clone.find('.update_id').attr('value', '0');
	     table_name.append(tbl_clone);

	 $('.tbl_provisional>tbody>tr:last').find('.form_title').eq(0).attr('id', 'form_title_'+cnt);
	 $('.tbl_provisional>tbody>tr:last').find('.form_diagnosis').eq(0).attr('id', 'form_diagnosis_'+cnt);
	 $('.tbl_provisional>tbody>tr:last').find('.update_id').eq(0).attr('value', '0');

 }); 

	//Deleting Existing Rows
	//------------------------------------------------------------		
		 
 $('.del').on('click', function() {

	 res = confirm("Please confirm to remove diagnosis");
     var count = $('.tbl_provisional .tr_clone').length; 
     if(res)
 	 {
 	 	old_id = $('#delete_id').val();
      	del_id = $(this).parent().parent().find('#update_id').val();
      	$('#delete_id').val(old_id+','+del_id);
      	if(count == 1)
     	{
      	  	$(this).closest('.tr_clone').find('.form_title').val('');
    	  	$(this).closest('.tr_clone').find('.form_diagnosis').val('');
	    	$(this).parent().parent().find('#update_id').val('');
	      }else{

	            $(this).closest('tr').remove();
        	}
  			count = count-1; 
   		}
      
 });


//Auto Compelete Search
 $(document).on('keydown','.form_title',function(){

    var id_name = $(this).attr('id');
    id_name = $('#'+id_name); 
    var cls_name = $(this).attr('class').split(' ')[0];
    autocompletion(id_name,cls_name);
	
}); 

 $(document).on('keydown','.form_diagnosis',function(){

	    var id_name = $(this).attr('id');
	    id_name = $('#'+id_name);
	    var cls_name = $(this).attr('class').split(' ')[0]; 
	    autocompletion(id_name,cls_name);
		
	}); 

 
//Depenedency Functions
//--------------------------------------------------------------------------------------------

function autocompletion(id,cls){

if(cls == 'form_title')
{	
	id.autocomplete({

		 	min_length: 1,

		    source: function(request, response) {
		        $.ajax({
		        	type: "POST",
		            url: "../../../library/ajax/auto_code_search.php",
		            dataType: "json",
		            data: {
		            	search_term : request.term		            	
		            },
		            success: function(data) {

				                response($.map(data, function (value, key) {
		                
						              return {
				             
						                  label:value.code_text,
						                  code:value.code
						            										
						              };
						          }));
		     				              				           
		                           }
		              });
		    },
		    
		    select: function( event, ui ) {
	    		event.preventDefault();
	    	   	var tble_row = $(this).parent().parent();
	 			selectProDia(ui.item.label,ui.item.code,tble_row);
	  			
         }
		    
		});
	
}else if(cls == 'form_diagnosis')
	{

	id.autocomplete({

	 	min_length: 1,

	    source: function(request, response) {
	        $.ajax({
	        	type: "POST",
	            url: "../../../library/ajax/auto_code_search.php",
	            dataType: "json",
	            data: {
	            	search_term : request.term		            	
	            },
	            success: function(data) {

			                response($.map(data, function (value, key) {
	                
					              return {
						              
					            	  label:value.code,
					            	  desc:value.code_text
					                  
					                
					            										
					              };
					          }));
	     				              				           
	                           }
	              });
	    },
	    
	    select: function( event, ui ) {
    		event.preventDefault();
    	   	var tble_row = $(this).parent().parent();
 			selectProDia(ui.item.desc,ui.item.label,tble_row);
  			
     }
	    
	});

	
}	
	 
}


function selectProDia(desc,code,tbl_row) {
	
 tbl_row.find(".form_title").val(desc);
 tbl_row.find(".form_diagnosis").val(code);
}

});

</script>
