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

if ($_POST['final_dia_save']) {

  $cnt = count($_POST['update_id']);
  $id = 0;

  //Deleting the issues

  $del_id = $_POST['delete_id'];
  $del_arr = explode(",",$del_id);

  foreach ($del_arr as $value) {
    sqlQuery("DELETE FROM final_dia WHERE pid = ? AND id = ?", array($pid, $value));
  }

  for ($j=0; $j < $cnt; $j++) {
    $id = $_POST['update_id'][$j];

    if ($id > 0) {

      $final_title = addslashes($_POST['final_title'][$j] );
	  $final_diagnosis = addslashes($_POST['final_diagnosis'][$j]);
	  $final_diagnosis_list = addslashes($_POST['final_diagnosis_list'][$j]);

      sqlQuery("UPDATE final_dia SET pid = '" . $pid . "', encounter = '". $enc ."', title = '". $final_title ."', diagnosis = '" . $final_diagnosis ."',add_diagnosis_list ='".$final_diagnosis_list."' WHERE id = $id");
    } else {


      $final_title = addslashes($_POST['final_title'][$j] );
      $final_diagnosis = addslashes($_POST['final_diagnosis'][$j]);
	  $final_diagnosis_list = addslashes($_POST['final_diagnosis_list'][$j]);
      sqlInsert("INSERT INTO final_dia (pid,encounter,title,diagnosis,add_diagnosis_list) VALUES ('". $pid ."','". $enc ."', '". $final_title."','". $final_diagnosis ."','".$final_diagnosis_list."')");
    }
  }
  ?>
    <script>
        window.top.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php?opener_save=true');
    </script>
    <?php
}?>

<html>
<head>
<?php html_header_show();?>
<title>
<?php echo xlt('Final Diagnosis'); ?>
</title>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<link rel="stylesheet" href="<?php echo $GLOBALS[' webroot ']; ?>/library/css/jquery-ui-1.12.1.css" type="text/css">
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-1.9.1.min.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-ui-1.12.1.js"></script>
<style>
.bold {
	color: #fff !important;
	padding-left: 10px !important;
}
</style>

<body class="body_top" style="padding-right:0.5em">

<?php
	$prores = sqlStatement("SELECT * FROM final_dia WHERE pid = ? AND encounter = ? ", array($pid, $encounter));
	if(isset($_REQUEST['report']))
	{ ?>
	<div class="report" style="background: #f7f7f7 none repeat scroll 0 0;;padding-left: 40px;padding-top: 10px;padding-bottom: 10px;">
		<table border='0' width='70%'>
			<thead>
				<tr style="background: #167f92 none repeat scroll 0 0; color:#fff;">
					<td class=bold>Description</td>
					<td class=bold>Code</td>
				</tr>
			</thead>
			<?php
				while ($row = sqlFetchArray($prores)){ ?>
				<tr>
					<td class=text>
						<?php echo $row['title'] ?>
					</td>
					<td class="text">
						<?php echo $row['diagnosis'] ?>
					</td>
				</tr>
				<?php
				}	?>
		</table>
	</div>
	<?php
	} else { ?>
	<form method='post' name='theform' action='final_dia.php' onsubmit='return validate()' style="background: #f7f7f7 none repeat scroll 0 0;;padding-left: 40px;padding-top: 10px;padding-bottom: 10px;">
		<table border='0' width='70%' class='final_dia'>
			<thead>
				<tr style="background: #167f92 none repeat scroll 0 0; color:#fff;">
					<td class="bold">Description</td>
					<td class="bold">Code</td>
					<td class="bold">Add to Diagnosis List</td>
					<td class="bold">Action </td>
				</tr>
			</thead>
			<?php
				if (sqlNumRows($prores) < 1)
					{

						//list of encounters
						$list_of_encounter='';
						$lis_enc=sqlStatement("select distinct(encounter) from final_dia where pid=?  order by encounter" ,array($pid));
						while($r_enc=sqlFetchArray($lis_enc)){
							
							if(empty($list_of_encounter))
									$list_of_encounter=$r_enc['encounter'];
								else
									$list_of_encounter.=", ".$r_enc['encounter'];
						}   
						if(!empty($list_of_encounter))
						{   

							$previ_diagnosis=sqlStatement("select fd.id,fd.encounter,fd.title,fd.diagnosis,fd.pid,fd.add_diagnosis_list from final_dia as fd inner join form_encounter as en on fd.encounter = en.encounter where fd.pid =$pid and fd.add_diagnosis_list = 1 and fd.encounter in(".$list_of_encounter.") group by fd.diagnosis");
							if(sqlNumRows($previ_diagnosis) > 0) {
								while ($row = sqlFetchArray($previ_diagnosis)){ ?>
									<tr class="tr_clone">
										<input type="hidden" name="update_id[]" class="update_id" id="update_id" value="0">
										<td style="width: 30%">
											<input type='text' size='40' name='final_title[]' id="final_title_<?php echo attr($row['id']) ?>" class="final_title" autocomplete='off' value="<?php echo attr($row['title']) ?>" style='width:100%' />

										</td>
										<td id='row_diagnosis' style="width: 30%">
											<input type='text' size='50' name='final_diagnosis[]' id="final_diagnosis_<?php echo attr($row['id']);?>" autocomplete='off' class='final_diagnosis' value="<?php echo attr($row['diagnosis'])?>" title="<?php echo xla('Click to select or change coding'); ?>" style='width: 100%' />

										</td>
										<?php 
											if($row['add_diagnosis_list'] == 1){ ?>
											<td style="width: 8%; padding-left: 40px;padding-top:6px;"><input type="checkbox" name="final_diagnosis_list[]" class='final_diagnosis_list' id="final_diagnosis_list<?php echo $row['id'];?>" value="1" checked='true'></td>
											<?php }else{?>
											<td style="width: 8%; padding-left: 40px;padding-top:6px;"><input type="checkbox" name="final_diagnosis_list[]" class='final_diagnosis_list' id="final_diagnosis_list<?php echo $row['id'];?>" value="0"></td>
											<?php }?>
											<td style="width: 10%">
											<input type="button" class="del" id="del" value="-">
										</td>
									</tr>
									<?php }
							}else{ ?>
								<tr class="tr_clone">
									<input type="hidden" name="update_id[]" class="update_id" id="update_id" value="0">
									<td style="width: 30%">
										<input type='text' size='40' name='final_title[]' id='final_title_0' class='final_title' autocomplete='off' value='' style='width:100%' />
									</td>
									<td id='row_diagnosis' style="width: 30%">
										<input type='text' size='50' name='final_diagnosis[]' id='final_diagnosis_0' autocomplete='off' class='final_diagnosis' value='' title='<?php echo xla(' Click to select or change coding '); ?>' style='width: 100%' />
									</td>
									<td style="width: 8%; padding-left: 40px;padding-top:6px;">
										<input type="checkbox" name="final_diagnosis_list[]" class='final_diagnosis_list' id='final_diagnosis_list_0' value="1">
									</td>
									<td style="width: 10%">
										<input type="button" class="del" id="del" value="-">
									</td>
								</tr>
								<?php }
						} else {
								?>
							<tr class="tr_clone">
									<input type="hidden" name="update_id[]" class="update_id" id="update_id" value="0">
									<td style="width: 30%">
										<input type='text' size='40' name='final_title[]' id='final_title_0' class='final_title' autocomplete='off' value='' style='width:100%' />
									</td>
									<td id='row_diagnosis' style="width: 30%">
										<input type='text' size='50' name='final_diagnosis[]' id='final_diagnosis_0' autocomplete='off' class='final_diagnosis' value='' title='<?php echo xla(' Click to select or change coding '); ?>' style='width: 100%' />
									</td>
									<td style="width: 8%; padding-left: 40px;padding-top:6px;">
										<input type="checkbox" name="final_diagnosis_list[]" class='final_diagnosis_list' id='final_diagnosis_list_0' value="1"></td>
									<td style="width: 10%">
										<input type="button" class="del" id="del" value="-">
									</td>
								</tr>
							<?php
								} 
					}else{
						while ($row = sqlFetchArray($prores)){?>
									<tr class="tr_clone">
										<td style="width: 30%">
											<input type="hidden" name="update_id[]" class="update_id" id="update_id" value="<?php echo attr($row['id']) ?>">
											<input type='text' size='40' name='final_title[]' id="final_title_<?php echo attr($row['id']) ?>" class='final_title' autocomplete='off' value="<?php echo attr($row['title']) ?>" style='width:100%' />
										</td>
										<td id='row_diagnosis' style="width: 30%">
											<input type='text' size='50' name='final_diagnosis[]' id="final_diagnosis_<?php echo attr($row['id']) ?>" autocomplete='off' class='final_diagnosis' value="<?php echo attr($row['diagnosis']) ?>" title="<?php echo xla(' Click to select or change coding'); ?>" style="width: 100%;" />
										</td>
										<?php if($row['add_diagnosis_list'] == 1){ ?>
										<td style="width: 8%; padding-left: 40px;padding-top:6px;"><input type="checkbox" name="final_diagnosis_list[]" class='final_diagnosis' id="final_diagnosis_list_<?php echo $row['id'];?>" value="1" checked='true'></td>
										<?php }else{ ?>
										<td style="width: 8%; padding-left: 40px;padding-top:6px;"><input type="checkbox" name="final_diagnosis_list[]" class='final_diagnosis' id="final_diagnosis_list_<?php echo $row['id'];?>" value="1"></td>
										<?php }?>
										<td style="width: 10%">
											<input type="button" class="del" id="del" value="-">
										</td>
									</tr>
									<?php }
							
					}?>
				</table>
				<div class="final_sav_div" style="height: 53px;padding-top: 12px;">
					<input type='submit' name='final_dia_save' value='<?php echo xla("Save");?>' />
					<input type='button' value='<?php echo xla("Cancel");?>' onclick='closeme();' />
					<input type="hidden" id="delete_id" name="delete_id" value="0">
				</div>
		</form>
		<?php } ?>
	<script language='JavaScript'>
		$(document).ready(function() {
				function closeme() {
					window.top.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php?opener_save=true');
				}

				//Jquery Events
				//---------------------------------------------------------------------------------------------------------------

				//Adding New Rows
				//------------------------------------------------------------		

				var $table;
				$table=$('.final_dia');
				var count=$('.final_dia tr').length;

				var tbl_row = $('.final_dia tr ').eq(count-1);
				$('<input></input>').attr({'type': 'button','class':'add','id':'add_main'}).val("+").appendTo(tbl_row.find('td:nth-child(4)'));
				
				$(".final_dia").on('click', '.add', function () {

					var obj = $(this);
					addRow(obj);
				});
				
			
				//Adding New Rows
				//------------------------------------------------------------		
				
				function addRow(obj){

						obj.remove();
						var $row = $table.find('tr:last').clone();
						$($row).each(function(){
							var cnt = $('.final_dia>tbody>tr:visible').length;
							$(this).find(':text').val('');
							$(this).find('.final_title').removeAttr('id').attr('id', 'final_title_' + cnt);
							$(this).find('.final_diagnosis').removeAttr('id').attr('id', 'final_diagnosis_' + cnt);
							$(this).find('.final_diagnosis_list').prop('checked',false);
						});
						$table.append($row);
			
						var tbl_row = $('.final_dia tr');
						var count =tbl_row.length;
						var add_button = $('<input></input>').attr({'type': 'button','class':'add','id':'add_main'}).val("+");
						var bt_add = tbl_row.eq(count-1).find('td:nth-child(4)');
						bt_add.find('.add').remove();
						add_button.appendTo(bt_add);
						//autocompletion(id_name, cls_name);
					}


				// $('.final_dia').on('click', '.add', function() {

				// 	$(this).remove();
				// 	var cnt = $('.final_dia>tbody>tr:visible').length;
				// 	var table_name = $('.final_dia');
				// 	var tbl_clone = table_name.find('tr:last').clone();
				// 	tbl_clone.find(':text').val('');
				// 	tbl_clone.find('.final_diagnosis_list').prop('checked',false);
				// 	tbl_clone.find('.final_title').removeAttr('id').attr('id', 'final_title_' + cnt);
				// 	tbl_clone.find('.final_diagnosis').removeAttr('id').attr('id', 'final_diagnosis_' + cnt);
				// 	tbl_clone.find('.final_diagnosis_list').removeAttr('id').attr('id','final_diagnosis_list_'+ cnt);
				// 	tbl_clone.find('.update_id').attr('value', '0');
				// 	table_name.append(tbl_clone);

				// 	// $('.final_dia>tbody>tr:last').find('.final_title').eq(0).attr('id', 'final_title_' + cnt);
				// 	// $('.final_dia>tbody>tr:last').find('.final_diagnosis').eq(0).attr('id', 'final_diagnosis_' + cnt);
				// 	// $('.final_dia>tbody>tr:last').find('.final_diagnosis_list').eq(0).attr('id', 'final_diagnosis_list_' + cnt);
				// 	// $('.final_dia>tbody>tr:last').find('.update_id').eq(0).attr('value', '0');
					
					


				// });

						//------------------------------------------------------------	
	
	//Deleting Existing Rows
 	//------------------------------------------------------------		
		
	
	var last_delete = true; 
	
    $(".final_dia").on('click', '.del', function () {
		
    	var count = $('.tr_clone').length;
		var b = count;
	    res = confirm("Please confirm to remove Final Diagnosis");
	    if(res){
    		  if(count) 
		 	  {
    				old_id = $('#delete_id').val();
    			    del_id = $(this).parent().parent().find('#update_id').val();
		     	    $('#delete_id').val(old_id+','+del_id);

					    if(b == 1){

					    	last_delete = false;

							$('.final_dia tr').eq(1).find('.final_title').val('');
							$('.final_dia tr').eq(1).find('.final_diagnosis').val('');
							$('.final_dia tr').eq(1).find('.final_diagnosis_list').prop('checked',false);;
							$('.final_dia tr').eq(1).find('#update_id').val(0);
												    	 
						}
						else{

							$(this).closest('tr').remove();

							var tbl = $('.final_dia tr');
						 	var count = tbl.length;
						 	var add_button = $('<input></input>').attr({'type': 'button','class':'add','id':'add_main'}).val("+");
						 	var bt_add =tbl.eq(count-1).find('td:nth-child(4)');
						 	bt_add.find('.add').remove();
							add_button.appendTo(bt_add);
		
	  					}	
		        	b = b-1;	

	        	
	   		}
	 			 		  
	 }
	});

				//Deleting Existing Rows
				//------------------------------------------------------------		

				// $('.del').on('click', function() {

				// 	res = confirm("Please confirm to remove diagnosis");
				// 	var count = $('.final_dia .tr_clone').length;
				// 	if (res) {
				// 		old_id = $('#delete_id').val();
				// 		del_id = $(this).parent().parent().find('#update_id').val();
				// 		$('#delete_id').val(old_id + ',' + del_id);
				// 		if (count == 1) {
				// 			$(this).closest('.tr_clone').find('.final_title').val('');
				// 			$(this).closest('.tr_clone').find('.final_diagnosis').val('');
				// 			$(this).closest('.tr_clone').find('.final_diagnosis_list').prop('checked',false);;
				// 			$(this).parent().parent().find('#update_id').val('');
				// 		} else {

				// 			$(this).closest('tr').remove();
				// 		}
				// 		count = count - 1;
				// 	}

				// });


				//Auto Compelete Search
				$(document).on('keydown', '.final_title', function() {

					var id_name = $(this).attr('id');
					id_name = $('#' + id_name);
					var cls_name = $(this).attr('class').split(' ')[0];
					autocompletion(id_name, cls_name);

				});

				$(document).on('keydown', '.final_diagnosis', function() {

					var id_name = $(this).attr('id');
					id_name = $('#' + id_name);
					var cls_name = $(this).attr('class').split(' ')[0];
					autocompletion(id_name, cls_name);
				});


				//Depenedency Functions
				//--------------------------------------------------------------------------------------------

				function autocompletion(id, cls) {

					if (cls == 'final_title') {
						id.autocomplete({

							min_length: 1,

							source: function(request, response) {
								$.ajax({
									type: "POST",
									url: "../../../library/ajax/auto_code_search.php",
									dataType: "json",
									data: {
										search_term: request.term
									},
									success: function(data) {

										response($.map(data, function(value, key) {

											return {

												label: value.code_text,
												code: value.code

											};
										}));

									}
								});
							},

							select: function(event, ui) {
								event.preventDefault();
								var tble_row = $(this).parent().parent();
								selectProDia(ui.item.label, ui.item.code, tble_row);

							}

						});

					} else if (cls == 'final_diagnosis') {

						id.autocomplete({

							min_length: 1,

							source: function(request, response) {
								$.ajax({
									type: "POST",
									url: "../../../library/ajax/auto_code_search.php",
									dataType: "json",
									data: {
										search_term: request.term
									},
									success: function(data) {

										response($.map(data, function(value, key) {

											return {

												label: value.code,
												desc: value.code_text



											};
										}));

									}
								});
							},

							select: function(event, ui) {
								event.preventDefault();
								var tble_row = $(this).parent().parent();
								selectProDia(ui.item.desc, ui.item.label, tble_row);

							}

						});


					}

				}
				function selectProDia(desc, code, tbl_row) {

					tbl_row.find(".final_title").val(desc);
					tbl_row.find(".final_diagnosis").val(code);
				}

			});
		</script>
