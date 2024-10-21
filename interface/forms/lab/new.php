<!-- Form generated from formsWiz -->
<?php
require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/forms.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("../../../custom/code_types.inc.php");

formHeader("Form: Labortory");

?>
<html>
	<head>
		<?php html_header_show();?>
		
		 <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/css/bootstrap-3.3.7.min.css" type="text/css">
		 <link rel="stylesheet" href="<?php echo $GLOBALS['webroot']; ?>/library/css/mbd/mdb.min.css" type="text/css">
		 <script src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-3.2.1.min.js.js"></script>
 		 <script src="<?php echo $GLOBALS['webroot']; ?>/library/js/bootstrap-3.3.7.min.js"></script>
 		 <script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/mbd/mdb.min.js"></script>
 			
		<style>
		 
		 .section-header{height:28px;}
		
		  table.borderless{margin-left:-6px;}
		  table.borderless td,table.borderless th{font-family: sans-serif;font-size: 11pt;border: none !important;}
		  table.borderless th{text-align: center;background: #eb8914;color:white;}		
		  .circle {	border: 1px solid tomato;border-radius: 50%;box-shadow: 0 0 1px 1px coral;font: bold 15px/13px Helvetica,Verdana,Tahoma;height: 30px;padding: 7px 3px 0;text-align: center;width: 30px;}
		  .checkbox-success{width: 221px; margin-left: 15px;}
		  .checkboxs{left: 18px; margin-top: -1px !important;}
		  .col-md-3{padding: 10px;}
		  .row{ overflow: hidden;}
		  [class*="col-"]{ margin-bottom: -99999px;  padding-bottom: 99999px;}
		  
		  .waves-effect{height: 45px !important; padding-top: 4px !important;}
		  .badge{font-size:22px;}
		</style>
	</head>
	<body class="body_top">
	  <div class="container-fluid" style="border: 1px solid silver; box-sizing: border-box;">
		<form role="form" method = "post" action="<?php echo $rootdir;?>/forms/lab/save.php?mode=new" name="my_form" onsubmit="return top.restoreSession()" >
  			<div style="padding-bottom: 60px;">
  				<div style ="float: left; ">
  					<h1><span class="badge">LABORATORY  FORM</span></h1>
  				</div>	
  				<div style="float:right; padding-right: 47px;">
  					<input type='submit' class="btn btn-outline-default waves-effect"  name='form_save' id="form_save" value='<?php echo xla('Save'); ?>' />
   				 	<input type='button'  class="btn btn-outline-default waves-effect"  value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />
  				</div>
  			</div>
  			
  			<div class="row ">
  			<!-- First Column -->
    			<div class="col-md-3 " style="min-height: 100px;border: 1px white solid; background-color:#f3f3f3;">
    			 <?php 
    			      $lists = array('biochemistry','stool_urine','hiv_test');
    			       foreach ($lists as $options)
    			     {
    			?>
    			  <div class="<?php echo $options;?>">
    				      <table  class="table borderless">
    				      <?php  
    				      
    				      $check_title=sqlStatement("SELECT title FROM  list_options WHERE option_id = '".$options."' ORDER BY seq ASC limit 1");
    				      if(sqlNumRows($check_title) > 0)
    				      {
    				      	while( $row_title = sqlFetchArray($check_title) )
    				      	{
    				    	  ?>
    				      	<thead>
									<tr> <th colspan="3"><?php echo $row_title['title']; ?></th> </tr>
							</thead>
							
    			 			<tbody>
    			 			<?php
								$check_res=sqlStatement("SELECT codes,title, notes FROM  list_options WHERE list_id = '".$options."' ORDER BY seq ASC");
								$numrows=sqlNumRows($check_res);
								if($numrows >0)
								{
							?>		
									
							<?php 		
									while( $row = sqlFetchArray($check_res) )
									{
						   	?>
    		      						<tr>
    		      						 <td style="padding-top: 15px;"><?php echo $row['codes']; ?></td>
    		      			 			 <td>
    		      			 					<div class="checkbox checkbox-success">
													<label><input class="checkboxs" type="checkbox" name= "checkbox_codes[<?php echo $row_title['title']; ?>][]" value ="<?php echo $row['codes']; ?>" ><?php echo $row['title']; ?></label>
												</div>
                        	 			</td>
                        	 			<td style="padding-top: 15px;">
    		      			 	    		<?php $parts = preg_split('/\s+/', $row['notes']);
                        	 					foreach ($parts as $value) { 
                        	 					
                        	 						if($value){
                        	 						?>
                        	 						
    		      			 						<div class="circle"><?php echo $value; ?></div>
		   		      			 			<?php }
                        	 					
                        	 				}?>
    		      			 			
    		      			 			</td>
    		      			 			</tr>
    		      				<?php 
    		      	
									}
								}		
    		      		      	?>		 			
    		      				</tbody>     
       		     			 </table>	
    				</div>
    				
     				<?php }}}?>
    			</div>	
    			<!-- Second Column -->
    			<div class="col-md-3 " style="min-height: 100px;border: 1px white solid; background-color:#f3f3f3;">
    			   			   			
    			<?php 
    			  
    			      $lists = array('profiles','viral_serology','tumour_marker');
    			    
    			      foreach ($lists as $options)
    			      {
    			      
    			?>
    			 			
			 		<div class="<?php echo $options;?>">
    				      <table  class="table borderless">
    				      <?php  
    				      
    				      $check_title=sqlStatement("SELECT title FROM  list_options WHERE option_id = '".$options."' ORDER BY seq ASC limit 1");
    				      if(sqlNumRows($check_title) > 0)
    				      {
    				      	while( $row_title = sqlFetchArray($check_title) )
    				      	{
    				    	  ?>
    				      	<thead>
									<tr> <th colspan="3"><?php echo $row_title['title']; ?></th> </tr>
							</thead>
							
    			 			<tbody>
    			 			<?php
								$check_res=sqlStatement("SELECT codes,title, notes FROM  list_options WHERE list_id = '".$options."' ORDER BY seq ASC");
								$numrows=sqlNumRows($check_res);
								if($numrows >0)
								{
							?>		
									
							<?php 		
									while( $row = sqlFetchArray($check_res) )
									{
						   	?>
    		      						<tr>
    		      						 <td style="padding-top: 15px;"><?php echo $row['codes']; ?></td>
    		      			 			 <td>
    		      			 						<div class="checkbox checkbox-success">
													<label><input class="checkboxs" type="checkbox" name= "checkbox_codes[<?php echo $row_title['title']; ?>][]" value ="<?php echo $row['codes']; ?>" ><?php echo $row['title']; ?></label>
												</div>
                        	 			</td>
                        	 			<td style="padding-top: 15px;">
    		      			 	    		<?php $parts = preg_split('/\s+/', $row['notes']);
                        	 					foreach ($parts as $value) { 
                        	 					
                        	 						if($value){
                        	 						?>
                        	 						
    		      			 						<div class="circle"><?php echo $value; ?></div>
		   		      			 			<?php }
                        	 					
                        	 				}?>
    		      			 			
    		      			 			</td>
    		      			 			</tr>
    		      				<?php 
    		      	
									}
								}		
    		      		      	?>		 			
    		      				</tbody>     
       		     			 </table>	
    				</div>
    				
     				<?php }}}?>
    			</div>	
    					<!--Third Column -->
    			<div class="col-md-3 " style="min-height: 100px;border: 1px white solid; background-color:#f3f3f3;">
    			   			   			
    			<?php 
    			  
    			      $lists = array('hormones','haemotology','tropical');
    			    
    			      foreach ($lists as $options)
    			      {
    			      
    			?>
    			 			
			 		<div class="<?php echo $options;?>">
    				      <table  class="table borderless">
    				      <?php  
    				      
    				      $check_title=sqlStatement("SELECT title FROM  list_options WHERE option_id = '".$options."' ORDER BY seq ASC limit 1");
    				      if(sqlNumRows($check_title) > 0)
    				      {
    				      	while( $row_title = sqlFetchArray($check_title) )
    				      	{
    				    	  ?>
    				      	<thead>
									<tr> <th colspan="3"><?php echo $row_title['title']; ?></th> </tr>
							</thead>
							
    			 			<tbody>
    			 			<?php
								$check_res=sqlStatement("SELECT codes,title, notes FROM  list_options WHERE list_id = '".$options."' ORDER BY seq ASC");
								$numrows=sqlNumRows($check_res);
								if($numrows >0)
								{
							?>		
									
							<?php 		
									while( $row = sqlFetchArray($check_res) )
									{
						   	?>
    		      						<tr>
    		      						 <td style="padding-top: 15px;"><?php echo $row['codes']; ?></td>
    		      			 			 <td>
    		      			 					<div class="checkbox checkbox-success">
													<label><input class="checkboxs" type="checkbox" name= "checkbox_codes[<?php echo $row_title['title']; ?>][]" value ="<?php echo $row['codes']; ?>" ><?php echo $row['title']; ?></label>
												</div>
                        	 			</td>
                        	 			<td style="padding-top: 15px;">
    		      			 	    		<?php $parts = preg_split('/\s+/', $row['notes']);
                        	 					foreach ($parts as $value) { 
                        	 					
                        	 						if($value){
                        	 						?>
                        	 						
    		      			 						<div class="circle"><?php echo $value; ?></div>
		   		      			 			<?php }
                        	 					
                        	 				}?>
    		      			 			
    		      			 			</td>
    		      			 			</tr>
    		      				<?php 
    		      	
									}
								}		
    		      		      	?>		 			
    		      				</tbody>     
       		     			 </table>	
    				</div>
    				
     				<?php }}}?>
    				
    			</div>	
    					<!-- Fourth Column -->
    			<div class="col-md-3 " style="min-height: 100px;border: 1px white solid; background-color:#f3f3f3;">
    			   			   			
    			<?php 
    			  
    			      $lists = array('microbiology','csf','blood_culture','other_tests','blood_transfusion');
    			    
    			      foreach ($lists as $options)
    			      {
    			      
    			?>
    			 			
			 		<div class="<?php echo $options;?>">
    				      <table  class="table borderless">
    				      <?php  
    				      
    				      $check_title=sqlStatement("SELECT title FROM  list_options WHERE option_id = '".$options."' ORDER BY seq ASC limit 1");
    				      if(sqlNumRows($check_title) > 0)
    				      {
    				      	while( $row_title = sqlFetchArray($check_title) )
    				      	{
    				    	  ?>
    				      	<thead>
									<tr> <th colspan="3"><?php echo $row_title['title']; ?></th> </tr>
							</thead>
							
    			 			<tbody>
    			 			<?php
								$check_res=sqlStatement("SELECT codes,title, notes FROM  list_options WHERE list_id = '".$options."' ORDER BY seq ASC");
								$numrows=sqlNumRows($check_res);
								if($numrows >0)
								{
							?>		
								<?php 	if($options == 'microbiology')
    		      			 		{
    		      			 		?>		
								<tr style="text-align: center;"><td colspan='3'>MCS(Microscopy,Culture,Sensitivity)</td></tr>
								<?php }?>
							<?php 	
									$count = 0;
									while( $row = sqlFetchArray($check_res) )
									{
										$count += 1;
							  	?>			
    		      						<tr>
    		      						 <td style="padding-top: 15px;"><?php echo $row['codes']; ?></td>
    		      			 			 <td>
    		      			 					<div class="checkbox checkbox-success">
													<label><input class="checkboxs" type="checkbox" name= "checkbox_codes[<?php echo $row_title['title']; ?>][]" value ="<?php echo $row['codes']; ?>" ><?php echo $row['title']; ?></label>
												</div>
                        	 			</td>
                        	 			<td style="padding-top: 15px;">
    		      			 	    		<?php $parts = preg_split('/\s+/', $row['notes']);
                        	 					foreach ($parts as $value) { 
                        	 					
                        	 						if($value){
                        	 						?>
                        	 						
    		      			 						<div class="circle"><?php echo $value; ?></div>
		   		      			 			<?php }
                        	 					
                        	 				}?>
    		      			 			
    		      			 			</td>
    		      			 			</tr>
    		      			 			<?php 
    		      			 		
    		      			 		if($options == 'microbiology')
    		      			 		{
    		      			 		
    		      			 		if($count == 4 ){?>
    		      			 			<tr><td colspan="3" style="padding-top:0px;padding-bottom:0px;">
    		      			 					<div  style="float: left; margin-top:30px;"> 
    		      			 						<span>Source :</span>
    		      			 					</div>
												<div class="md-form" style="float: right;width: 310px;height:45px;">
    												<input id="form5" class="form-control source_1" style="margin-top: 17px; padding-top: 8px; margin-bottom: 0px;" type="text" name="checkbox_codes[source][source_1]">
    											
												</div>
    		      			 				      			 			
    		      			 			</td></tr>
    		      			 		
    		      			 		<?php } ?>	
    		      			 		
    		      			 			<?php if($count == 6 ){?>
    		      			 			<tr><td colspan="3" style="padding-top:0px;padding-bottom:0px;">
    		      			 					<div  style="float: left; margin-top:30px;"> 
    		      			 						<span> *Source :</span>
    		      			 					</div>
												<div class="md-form" style="float: right;width: 310px;height:45px;">
    												<input id="form5" class="form-control source_2" style="margin-top: 17px; padding-top: 8px; margin-bottom: 0px;" type="text" name="checkbox_codes[source][source_2]">
    											
												</div>
    		      			 				      			 			
    		      			 			</td></tr>
    		      			 		
    		      			 		<?php } }?>	
    		      			 		
    		      				<?php 
    		      					
    		      					}
								}		
    		      		      	?>					
    		      				</tbody>     
       		     			 </table>	
    				</div>
    				
     				<?php }}}?>
    		    </div>
     	</div>
   				<div style="padding-bottom: 60px;">
  				<div style="float:left;padding-top:10px; padding-right: 47px;">
  					<input type='submit' class="btn btn-outline-default waves-effect"  name='form_save' id="form_save" value='<?php echo xla('Save'); ?>' />
   				 	<input type='button'  class="btn btn-outline-default waves-effect"  value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />
  				</div>
  			</div>
    </form>	
</div>	

<script>
function closeme() {
		window.top.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php?opener_save=true');
}

var id = "<?php echo $_GET['id'];?>";

$('#'+id).css('display','block');



</script>
</body>
</html>
<?php
formFooter();
?>
