<?php


/**
 * Custom Reports
*
* Copyright (C) 2006-2017 ViSolve <services@visolve.com>
*
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
*
* @package OpenEMR
* @author  ViSolve <services@visolve.com>
* @link    http://hc.visolve.com
*/

// Disable magic quotes and fake register globals.
$sanitize_all_escapes = true;
$fake_register_globals = false;

require_once('../globals.php');

$edit = false;

if(isset($_GET['did']))
{

	$edit = true;

	$sql="select * from  documents d inner join categories_to_documents cd on document_id=d.id inner join categories c on c.id=cd.category_id where d.id=".$_GET['did'];
	$document_data=sqlQuery($sql);

	$sql="select * from  template_categories where title='".$document_data['name']."'";
	$category=sqlQuery($sql);

}
?>
<html>

<head>
 <title><?php echo xlt('Custom Reports'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style type="text/css">
 .dehead { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
 .detail { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }

</style>
     <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/custom_template/ckeditor/ckeditor.js"></script>
     <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-1.6.4.min.js" type="text/javascript"></script>
     <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery-ui-1.7.1.custom.min.js"></script>
     <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/common.js"></script>
     <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/ajax_functions_writer.js"></script>


</head>



<body class="body_top">

<div style="display:none" id='second'></div>
<br>
<div class="content">
  <form name="patient_template" action='save_template.php' method="post" id='pt'>
    <h5><?php echo xlt('View / Edit Template'); ?></h5>
    <input type='hidden' id='sel' value=''>

  <table border='1' width='95%' id="edtitor_table">
    <tr bgcolor='#dddddd' class='dehead'>
        <td valign='top' class='detail' style='padding:10pt;' nowrap colspan="2">
            <?php
              echo htmlspecialchars(xl('Template Category')).":"; 
                $sql=sqlStatement("select id,title from template_categories where status=1");
             ?>
                
            <select name="view_temp_category" id='view_category' required >
              <option value=''>--Choose Category--</option>
              <?php
                  $html='';
                  while($row=sqlFetchArray($sql))
                  {
                    $selected='';
                    if(isset($_GET['did']))
                    {
                        if($category['id']==$row['id'])
                          $selected='selected';
                    }
                    $html.='<option value="'.$row['id'].'" cap="'.$row['title'].'" '.$selected.'>'.$row['title'].'</option>';
                  }
                  
                  echo $html;
    
              ?>
            </select>
    
            <?php
            
            if($_SESSION['usergroup']=='Administrators')
            { 
               echo htmlspecialchars(xl('Notification Group')).":"; 
               $sql=sqlStatement("SELECT `id`,`name` FROM `gacl_aro_groups` WHERE `parent_id`!=0");
               
                ?>
              <select name="user_group" id='user_group' required >
                <option value='0'>--Choose group--</option>
                <?php
                    $html='';
                    while($row=sqlFetchArray($sql))
                    {
                      $selectted='';
                      if($GLOBALS['notification_group']==$row['id'])
                      $selectted=" selected ";
                      $html.='<option value="'.$row['id'].'" cap="'.$row['name'].'" '.$selectted.'>'.$row['name'].'</option>';
                      
                    }
                    echo $html;
                 ?>
              </select>
            <?php 
             }
            
    
          if($_SESSION['usergroup']=='Administrators' || $_SESSION['usergroup']=='Physicians' || $_SESSION['usergroup'] == 'DataEntry User'|| $_SESSION['usergroup'] == 'Technician')
          {
                ?>
            <a href="" id="ecus_doc2" onclick="return parent.left_nav.loadFrame2('ecus_doc2','RTop','patient_file/summary/document_pending_list.php?')"><?php echo xlt('Pending Reports'); ?></a>
     
            <?php 
           }  ?>
     </td>
  </tr>

  <tr>
    <td valign=top style="width:300px;">
           <div style="background-color:#DFEBFE">
               <div style="overflow-y:scroll;overflow-x:hidden;height:400px">
                  <span  style=" text-align: center;"><h3>List of Templates</h3></span><br> 
                   <ul id="menu5" class="example_menu" style="width:100%;">
                        <li><a href='#' class="view-template" path=''>--- None ---</a></li>
                   </ul>
                </div>
           </div>
    </td>
    
    <td>
        <textarea class="ckeditor" cols="100" id="textarea15" name="textarea15" rows="80"></textarea>
    </td>
  </tr>
  
</table>

<br>
 <center><button type="submit" class="iframe_medium "><span><?php echo htmlspecialchars(xl('Save'),ENT_QUOTES);?></span></button>
  <button type="button" id='can' class="close "><span><?php echo htmlspecialchars(xl('Cancel'),ENT_QUOTES);?></span></button></center>

  <?php 
    
    if(isset($_GET['did']))
    {
      $date=date_create($document_data['docdate']);
      $docdate=date_format($date,"d-m-Y");
      ?>
      <input type="hidden" name="doc_id" value="<?php if(isset($_REQUEST['did'])) echo $_REQUEST['did']; ?>">
      <input type="hidden" name="doc_name" value="<?php if(isset($document_data['url'])) echo $category['title'].'_'.$docdate.'_'.$_REQUEST['did'].'_report'; ?>">
      <?php 
      
    } 
  

  ?>
  </form>
 </div>

</body>

</html>

<script type="text/javascript">

$(document).ready(function(){

  $('#second').html('');
dirty = false;
loadfirst= true;
header2='';

$.ajax({
    type:'post',
    url:'formheader2.php',
    data:{'pid':'<?php echo $pid;?>'},
    success:function(data)
    {
      header2=data;
    }
  });




$('#view_category').change(function(){

  $('iframe').attr('id','ck_frame');
 
  if($(this).val() !='')
    {

	     if($('iframe')[0].contentDocument.body.innerHTML == '<br>')
             $('iframe')[0].contentDocument.body.innerHTML='';
         
       if(loadfirst)
       {
          $.ajax({
                      type:'post',
                      url:'get_templates.php',
                      data:{'id':$(this).val()},

                      success:function(data)
                      {


                        var obj=jQuery.parseJSON(data);
                        if(obj.status==1)
                         {
                          $('#menu5').empty();
                          $('#menu5').append(obj.msg);
                         }
                        else
                        {
                            $('#menu5').empty();
                              $('#menu5').append(  "<li><a href='#' class='view-template' path=''>--- None ---</a></li>");
                              $('#second').empty();
                              $('iframe')[0].contentDocument.body.innerHTML='';
                        }


                      }
                    });
                  
          loadfirst = false;
       }
       else{

                  if($('iframe')[0].contentDocument.body.innerHTML == '<br>')
                  $('iframe')[0].contentDocument.body.innerHTML='';
                  if($('#second').html() != $('iframe')[0].contentDocument.body.innerHTML)
                  {
                       dirty = true;
                   }
                  else
                     dirty=false;
      
                    
                  if(dirty)
                 {
      
                      if(confirm('Your document has UNSAVED CHANGES  If you continue those changes will be LOST'))
                       { 
                           $.ajax({
                                 type:'post',
                                 url:'get_templates.php',
                                 data:{'id':$(this).val()},
        
                                 success:function(data)
                                 {
        
        
                                   var obj=jQuery.parseJSON(data);
                                   if(obj.status==1)
                                    {
                                     $('#menu5').empty();
                                     $('#menu5').append(obj.msg);
                                    }
                                   else
                                   {
                                        $('#menu5').empty();
                                         $('#menu5').append(  "<li><a href='#' class='view-template' path=''>--- None ---</a></li>");
                                         $('#second').empty();
                                   }
        
        
                                 }
                               });
                           $('iframe')[0].contentDocument.body.innerHTML='';
                           $('#second').html('');
                           dirty=false;
      
                           }
                           else{
      
                                  $('#view_category').val($('#sel').val());
                                return false;
      
                            }   
                       }
                       else{
      
                       $.ajax({
                                   type:'post',
                                   url:'get_templates.php',
                                   data:{'id':$(this).val()},
      
                                   success:function(data)
                                   {
      
      
                                     var obj=jQuery.parseJSON(data);
                                     if(obj.status==1)
                                      {
                                       $('#menu5').empty();
                                       $('#menu5').append(obj.msg);
                                      }
                                     else
                                     {
                                       $('#second').empty();
                                         $('#menu5').empty();
                                           $('#menu5').append(  "<li><a href='#' class='view-template' path=''>--- None ---</a></li>");
                                           $('iframe')[0].contentDocument.body.innerHTML='';
                                           $('#second').empty();
                                     }
      
      
                                   }
                                 });
                           window.onbeforeunload=null;
                             
                         }

                 
                 
                 }

                 $('#sel').val($(this).val());
                 
            }else{

                  if($('#second').html() != $('iframe')[0].contentDocument.body.innerHTML)
                     {
                         dirty = true;
                     }else
                        dirty=false;
                           
                    if(dirty)
                    {
                         if(confirm('Your document has UNSAVED CHANGES  If you continue those changes will be LOST'))
                             { 
                            $('#menu5').empty();
                               $('#menu5').append(  "<li><a href='#' class='view-template' path=''>--- None ---</a></li>");
                               $('iframe')[0].contentDocument.body.innerHTML='';
                               $('#second').empty();
                               $('#view_category').val('');
                               $('#sel').val('');
                             }
                         else
                         {                      
                             return false;
                         }
                         
                     }else{
    
                             $('#menu5').empty();
                                 $('#menu5').append(  "<li><a href='#' class='view-template' path=''>--- None ---</a></li>");
                                 $('iframe')[0].contentDocument.body.innerHTML='';
                                 $('#second').empty();
                                 $('#sel').val('');
                               
                           }
                     }
    
              if($('#doc_id').length>0)
                   $('#doc_id').remove();
              if($('#doc_name').length>0)
                    $('#doc_name').remove();
          });


  $(document).delegate('.view-template','click',function() {

         if($(this).attr('path')!='')
         {
            var address='<div style="text-align:center"><p>Alykhan Road, Upanga, P.O. Box 2029, Dar es Salaam, Tanzania.</p><p>Tel.:+255-22-2150500, Fax: +255-22-2150180 Email: info@regencymedicalcentre.com</p><p>Website: www.regencymedicalcentre.com</p></div>';
            var v=$(this).attr('path');
            var name=$(this).attr('name');
            var c=$("#view_category")[0].selectedOptions;

            name = c[0].getAttribute('cap')+ ' Report';              

            if($('iframe')[0].contentDocument.body.innerHTML == '<br>')
                  $('iframe')[0].contentDocument.body.innerHTML='';

             if($('#second').html() != $('iframe')[0].contentDocument.body.innerHTML)
              {
                   dirty = true;
                   $('#view_category').val($('#sel').val());
               }
              if(dirty)
             {

                if(confirm('Your document has UNSAVED CHANGES  If you continue those changes will be LOST'))
                   { 

                  $.get(v, function(content) {
                        $('iframe')[0].contentDocument.body.innerHTML='';
                         //create header
                          var div = document.createElement("div");
                          div.id    = 'header';
                          div.class = 'headerclass';
                          div.innerHTML = '<div style="text-align:center"><img src="regency_medical_centre_logo.jpg" alt=""  height="100" /></div>'+address+'<div style="width:100%;text-align:center"><h3 >'+name+'</h3></div>';
                          $('iframe')[0].contentDocument.body.appendChild(div);
                          $('iframe')[0].contentDocument.body.innerHTML+=header2;
                        $('iframe')[0].contentDocument.body.innerHTML+=content;

                        var data=header2+content;
                           $('#second').empty();
                        $('#second').append(div);
                        $('#second').append(data);
                        dirty = false;

                        });
                      }
                     else{
                return false;                                                   
                        }

                }else{

                       $.get(v, function(content) {

                             $('iframe')[0].contentDocument.body.innerHTML='';
                          //create header
                            var div = document.createElement("div");
                              div.id    = 'header';
                              div.class = 'headerclass';
                              div.innerHTML = '<div style="text-align:center"><img src="regency_medical_centre_logo.jpg" alt=""  height="100" /></div>'+address+'<div style="width:100%;text-align:center"><h3 >'+name+'</h3></div>';
                              $('iframe')[0].contentDocument.body.appendChild(div);
                              $('iframe')[0].contentDocument.body.innerHTML+=header2;
                              $('iframe')[0].contentDocument.body.innerHTML+=content;

                              var data=header2+content;
                              $('#second').empty();
                              $('#second').append(div);
                              $('#second').append(data); 
                              dirty = false; 
                        });
                        
                       }
        } else{

              $('iframe')[0].contentDocument.body.innerHTML='';
                $('#second').empty();
               }

  });

  
$('#can').click(function(){
           location.reload();
    });


var checkChangesEnabled = true;
window.onbeforeunload = function (e) {

  var e = e || window.event;
  for (var i in CKEDITOR.instances) 
  {
       if(CKEDITOR.instances[i].checkDirty())
       {
           dirty = true;
       }
  }
  
 if($('iframe')[0].contentDocument.body.innerHTML == '<br>')
     $('iframe')[0].contentDocument.body.innerHTML='';

 if (dirty == true && checkChangesEnabled && ($('iframe')[0].contentDocument.body.innerHTML !='') )
 {
    e.returnValue = 'Your document has UNSAVED CHANGES  If you continue those changes will be LOST.';
    
 }
 
} 



var doc_data="<?php echo trim(preg_replace("/\s+/", " " , $document_data["document_data"]));?>";

var textstring=doc_data.replace(/(\r\n|\n|\r)/gm," ");
$('#textarea15').html(textstring);

$('#user_group').change(function(){
     
            if(confirm('Are you sure to Change Notify Group ?'))
            {
      
             $.ajax({
                    type:'post',
                     url:'save_notification_group.php',
                    data:{'notification_group':$(this).val()},
                   success:function(data)
                    {
                        if(data=='1')
                        {
                            alert('<?php echo xlt('Notify group updated.'); ?>');
                        }
                        else
                        {
                            alert('<?php echo xlt('Notify group update Canceled.'); ?>');
                        }
        
                        return false;
                    }
                });
            }
            else
            {
              return false;
      
            }
    });



  <?php

  
      if(isset($_GET["addsuccess"]))
      {
          if($_GET["addsuccess"]==1)
          {
            echo 'alert("'.xlt('Report added successfully.').'");' ;
          }
          else if($_GET["addsuccess"]==2)
          {
            
          echo 'alert("'.xlt('Report edited successfully.').'");' ;
          }
          else if($_GET["addsuccess"]==0)
          {
            echo 'alert("'.xlt('Report create fail.').'");' ; 
          }
      }

    ?>

});
 


$(document).ready(function(){

var check_mate = false;
var edit_check = <?php echo json_encode($edit);?>

if(edit_check){

    setTimeout(function() {
         var frame = $('.content',top.frames["RTop"].document);
         var tble = frame.find('#pt #edtitor_table tr td .cke_editor');
         tble.find('iframe').attr('id','ck_frame');

       }, 2000);


}

var dirties;

$(document).submit('form#pt',function() {

	

	 var frame = $('.content',top.frames["RTop"].document);
	 var tble = frame.find('#pt #edtitor_table tr td .cke_editor #ck_frame').contents();

     var length = tble.find('body').html().length;


       if(length > 0 || length > 4)
           {
        	   dirties = true;
            }
           else
        	   dirties =false;
	
        if(dirties)
        {
          
			var doc_name = tble.find('#template_table .doc_name');
			if(tble.find('#template_table').length)
			{
			  
					if(!edit_check)
					{ 
					
					  if(doc_name.text())
					  { 
					      window.onbeforeunload=null;
					  }
					  else{
					        
					        var doctor_name = prompt("Please enter Doctor name:", "");
					        doc_name.text(doctor_name);
					        return check_mate;
					       
					
					    }
					  
					  }else{
					
					         if(!doc_name.text()){

						      	 var doctor_name = prompt("Please enter Doctor name:", "");
							      doc_name.text(doctor_name);
					           return check_mate;
					        }
					        else{
					
					            window.onbeforeunload=null;
					          }
					            
					            
					  }
						
				}
				else{
				
					window.onbeforeunload=null;	
				}
        }
        else{

				alert("Empty Report could not Save");
				return check_mate;

            }
  });

});

</script>