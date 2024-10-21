<!-- Form generated from formsWiz -->
<?php
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("../../../custom/code_types.inc.php");

formHeader("Form: Procedure");

?>
<html><head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href='<?php echo $GLOBALS['webroot']; ?>/library/css/jquery-ui-1.12.1.css' type='text/css'>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-ui-1.12.1.js"></script>

</head>
<body class="body_top">

	<form method="POST" action="<?php echo $rootdir;?>/forms/procedure/save.php?mode=new"   style="padding-top: 20px;" name="my_form" onsubmit="return top.restoreSession()" >
 		<div style="width: 70%;float:left;">
	        <table  width='100%' class='proctable' style="padding-left:20px; padding-bottom: 15px;">
				    <tr style="background: #167f92 none repeat scroll 0 0; color:white;height:35px;font-size:10pt;" >
		    	    		<th>Procedure Name</th>
			    		<th>Comments</th>
				      	<th>Action</th>
		     	        </tr> 
			
  				<tr class="tr_clone">
    				<td valign='top' style="padding-top: 22px;width:400px;">
    					<input type='text' size='50' name='service_name[]' id='service_name_0' class='service_name' style='width:100%;' autocomplete='off' />
    					<div id="suggesstion-box" class='suggesstion-box' style="position: absolute;"></div>
    				 </td>
    				<td style="width:400px;">
		     			<textarea  name='comments[]' style='width:100%;' id='comments_0' class='comments'></textarea>
		      		</td>
   					<td style="width:135px;">
   					  <div style="padding-left: 38px;">
     				   <input type="button" class="del" id="del" value="-"> 
     				   <input type="button" class="add" id="add" value="+">
     				  </div> 
   					</td>
				    <input type="hidden" name="service_code[]" class="service_code" id="service_code" value="0">
    				<input type="hidden" name="pat_cat[]" class="pat_cat" id="pat_cat" value="0">
    				<input type="hidden" name="rate_cat[]" class="rate_cat" id="rate_cat" value="0">
    				<input type="hidden" name="rate[]" class="rate" id="rate" value="0">
				 </tr>
			</table>
		<div style="padding-left: 25px;">		
		<span style="float:left;">
			<a href="javascript:top.restoreSession();document.my_form.submit();" class="link_submit"><input style="width: 70px !important; height: 30px !important;" type="submit" value="Save"></a>
		</span>	
		<span style="float:left;"> 
  			<a href="<?php echo "$rootdir/patient_file/summary/demographics.php?opener_save=true";?>" class="link" onclick="top.restoreSession()"><input  style="width: 70px !important; height: 30px !important;" type="button" value="Cancel"></a>
		</span>
		     	<input type="hidden" name="update_id[]" class="update_id" id="update_id" value="0">
		     	<input type="hidden" id="delete_id" name="delete_id" value="0">
		</div>     	
      </div>	
	</form>

</body>

<script language='JavaScript'>

$(document).on('click','.add', function(){

	  var cnt=$('.proctable>tbody>tr:visible').length;
 	 // var tbl_row = $(this).closest('.tr_clone');
 	 
 	 var table_name = $('.proctable');
 	  
     var tbl_clone = table_name.find('tr:last').clone();
      tbl_clone.find('.service_name').attr('id','services'+'_'+cnt);
      tbl_clone.find('.service_name').val('');
      tbl_clone.find('.comments').val('');
      table_name.append(tbl_clone);

   	  $('.proctable>tbody>tr:last').find(".service_name").eq(0).attr('id', 'service_name_'+cnt);
   	  $('.proctable>tbody>tr:last').find('.comments').eq(0).attr('id', 'comments_'+cnt);

 });

$(document).on('click','.del', function() {
	
  	res = confirm("Please confirm to remove procedure");
 	var tbl_length = $('.tr_clone').length;
 	var count = tbl_length; 
 	
  	if(res)
  	{
	   old_id = $('#delete_id').val();
       del_id = $(this).parent().parent().find('#update_id').val();
       $('#delete_id').val(old_id+','+del_id);
       if(count == 1)
      {
    	     
    	   $('.proctable>tbody>tr:first').find('.service_name').val('');
	    	$('.proctable>tbody>tr:first').find('.service_code').val('');
	    	$('.proctable>tbody>tr:first').find('.pat_cat').val('');
	    	$('.proctable>tbody>tr:first').find('.rate_cat').val('');
	    	$('.proctable>tbody>tr:first').find('.rate').val('');
	    	$('.proctable>tbody>tr:first').find('.comments').val('');
	    	$(this).parent().parent().find('#update_id').val('');
	    	
        }  
    	else{
             $(this).closest('tr').remove();
         }
   		count = count-1; 
    }
    
});


//Auto Compelete Search
 $(document).on('keydown','.service_name',function(){

    var id_name = $(this).attr('id');
    id_name = $('#'+id_name); 
    autocompletion(id_name);
	
}); 





 
//Depenedency Functions
//--------------------------------------------------------------------------------------------

function autocompletion(id){

	id.autocomplete({

		 	min_length: 1,

		    source: function(request, response) {
		        $.ajax({
		        	type: "POST",
		            url: "../../../library/ajax/auto_procedure_search.php",
		            dataType: "json",
		            data: {
		            	search_term : request.term,
		            	service_type : 'PROCEDURE',
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

	  			selectPro(ui.item.label,ui.item.service_code,ui.item.rate,ui.item.rate_cat,ui.item.pat_cat,index);
	  			
          }
		    
		});
	 
}


function selectPro(service_name,service_code,rate,rate_cat,pat_cat,cnt) {

  $('.proctable>tbody>tr').eq(cnt).find(".service_code").val(service_code);
  $('.proctable>tbody>tr').eq(cnt).find(".service_name").val(service_name);
  $('.proctable>tbody>tr').eq(cnt).find(".rate").val(rate);
  $('.proctable>tbody>tr').eq(cnt).find(".rate_cat").val(rate_cat);
  $('.proctable>tbody>tr').eq(cnt).find(".pat_cat").val(pat_cat);
  
}


</script>
<?php
formFooter();
?>
