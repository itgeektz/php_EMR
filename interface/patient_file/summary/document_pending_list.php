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

?>
<html>

  <head>
    <title><?php echo xlt('Pending Reports'); ?></title>
    <link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

    <style type="text/css">
     .dehead { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:bold }
     .detail { color:#000000; font-family:sans-serif; font-size:10pt; font-weight:normal }

    </style>

    <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.js"></script>
  </head>
<div style="display:none" id='second'></div>
  <body class="body_top">

    <br>
    <div class="content">
      <form id="pending_document" name="pending_document" action='document_pending_authorize.php' method="post" id='pt'>
        <h5 style="float: left;
font-size: 19px;
margin: 0;"><?php echo xlt('Pending Reports'); ?></h5>
        <span class="newright"> <a href="javascript:void();" onclick="return parent.left_nav.loadFrame2('ecus_doc2','RTop','super/custom_reports.php')">Add New Report</a></span>
        <table cellspacing="0" cellpadding="0" border='0' class="reportlisting" width='100%'>
          <tr bgcolor='#dddddd' class='dehead'>
            <th><?php echo xlt('Document Id'); ?></th>
            <th><?php echo xlt('Category'); ?></th>
            <th><?php echo xlt('Report'); ?></th>
            <th><?php echo xlt('Upload Date'); ?></th>
            <th><?php echo xlt('Approval Status'); ?></th>
          </tr>
          <?php 
              $notadmin='';
              $notallow='AND 1=2';
               
                  
              if($_SESSION['usergroup']=='Administrators' || $_SESSION['usergroup']=='Physicians' || $_SESSION['usergroup'] == 'DataEntry User'|| $_SESSION['usergroup'] == 'Technician')
              {
                  $notallow='';
                  
              }
              else
              {
                 $notallow=' AND 1=2';
              }
              
              
              if($_SESSION['usergroup'] == 'DataEntry User'){
              	
              	$u_encounter = 0;
              }
              else{
              	
              	$u_encounter = 1;
              	
              }
              

               
           /* 
            //commented for allow every doctor to edit & confirm report
           $sql=sqlStatement("SELECT d.*,cd.category_id,c.name as category_name,IF(fe.provider_id=".$_SESSION['authUserID'].",'1','0') as user_encounter,(Hour(timediff(NOW(),d.date))) as hours FROM `documents` d 
              inner join categories_to_documents cd on cd.document_id=d.id 
              inner join categories c on c.id=cd.category_id 
               inner join form_encounter fe on  fe.encounter=d.encounter_id  
              WHERE  d.authorize_status=0 AND d.foreign_id='".$_SESSION['pid']."' AND cd.category_id in(SELECT id FROM `categories` WHERE name in( SELECT title  FROM `template_categories` WHERE status=1))".$notallow." ORDER By d.id DESC");
        */
            // 15-02-2017 changed by me
            // only remove if condition & set 1 of user_encounter if condition which use to check it's login user or not in bellow qry 

            $sql=sqlStatement("SELECT d.*,cd.category_id,c.name as category_name,". $u_encounter." as user_encounter,(HOUR(timediff(NOW(),d.date))) as hours FROM `documents` d 
              inner join categories_to_documents cd on cd.document_id=d.id 
              inner join categories c on c.id=cd.category_id 
               inner join form_encounter fe on  fe.encounter=d.encounter_id  
              WHERE  d.authorize_status=0 AND d.foreign_id='".$_SESSION['pid']."' AND cd.category_id in(SELECT id FROM `categories` WHERE name in( SELECT title  FROM `template_categories` WHERE status=1))".$notallow." ORDER By d.id DESC");
        
            while($row=sqlFetchArray($sql))
            {
                       	
                $path=$OE_SITE_DIR."/documents/".$row['foreign_id'];
                $file=$path."/".end(explode('/',$row['url']));
          ?>
              <tr bgcolor='#dddddd' class='dehead'>
              <td>
                <?php echo $row['id']; ?>
              </td>
              <td>
                <?php echo $row['category_name']; ?>    
              </td>
              <td>
               <?php
               if(file_exists($file))
                {
                  echo end(explode('/',$row['url']));
                }
                else
                {
                  echo end(explode('/',$row['url'])); 
               
               }  
               ?> 
              </td>
              <td>
                <?php echo $row['date']; ?>  
              </td>
              <td>
                  <?php 

                    
                      if($row['user_encounter']=='1' || $_SESSION['usergroup']=='Administrators')
                      {
                  ?>
                    <input type="button" did="<?php echo $row['id']; ?>" name="authorize_status" class="cls_authorize_status" value="<?php echo xlt('Confirm'); ?>">
                   
                  <?php } 
                    
                   // if($row['hours']< $GLOBALS['pending_document_disp_time_hour'] || $_SESSION['usergroup']=='Administrators')
                   // {  
                      if($row['user_encounter']=='1' || $row['user_encounter']=='0' || $_SESSION['usergroup']=='Administrators')
                      {
                      	
                
                      	?>
                      <input type="button"  did="<?php echo $row['id']; ?>" name="edit_doc" class="edit_doc" value="<?php echo xlt('Edit'); ?>" onclick="return parent.left_nav.loadFrame2('ecus_doc2','RTop','super/custom_reports.php?did=<?php echo $row['id']; ?>')">
                       <?php
                       
                      }
                      else
                      {
                        echo xlt('-'); 
                      }
                 //   }
                  //  else
                   // {
                   //   echo xlt('-');  
                  //  }
                   ?>
                  
              </td>
              </tr>
          <?php
            }

          ?>
          </table>
          <input type="hidden" id="req_doc_id" name="req_doc_id" value="">
          <input type="hidden" id="authpass" name="authpass" value="">
         

      </form>
    </div>
      <style type="text/css">

.pass_autho{display: table-cell;
height: 100%;
left: 0;
position: absolute;
text-align: center;
top: 0;
vertical-align: middle;
width: 100%;
z-index: 2147483647;}
.pass_autho table{background: #ffff none repeat scroll 0 0;
box-shadow: 0 0 61px #777;
margin: 189px auto 0;
padding: 16px;
width: auto;}

.pass_autho table input{margin: 5px;}



          #pending_document > div {
            height: 100%;
            margin: 111px 0 164px 346px;
            opacity: 1;
            position: absolute;
            width: 100%;
            z-index: 9999999;
          }
          .opacity_dv { background: rgba(0, 0, 0, 0.2) none repeat scroll 0 0;
    height: 100%;
    left: 0;
    opacity: 0.5;
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 999999999;}
      </style>
       <div id="dialog" class="pass_autho" title="Basic dialog" style="display: none;">
              <table cellpadding="0" cellspacing="0" border="0" width="100%">
               <tr>
                  <td align="center">
                     <label><?php echo xl('Confirm Password');?></label>
                  </td>

                </tr>
                <tr>
                  <td>
                    <input type="password" id="pass" size="25" />
                  </td>
                 
                </tr>
                <tr>
                  <td align="center">
                    <input type="button" size="25" id="confirm" name="confirm" value="Ok"/>
                <input type="button" size="25" id="cancelconf" name="cancelconf"  value="cancel"/>
                  </td>
                 
                </tr>
              </table>
            
       </div>
       <div class="opacity_dv" style="display: none;">&nbsp;</div>
  </body>
</html>

<script type="text/javascript">
    $(document).ready(function(){

        $('#dialog').css('display','none');
        $('#opacity_dv').css('display','none');

        $('.cls_authorize_status').click(function(){
          var r = confirm("<?php echo xlt('Are you sure to authorize document ?'); ?>");
          
          if(r== true)
          {
            id=$(this).attr('did');
            
                $('#req_doc_id').val(""+id);
                

            $('#dialog').css('display','block');
            $('#opacity_dv').css('display','block');
            $('#pass').focus();
          
          }
          else
          {
            return false;
          }
           
          
        });

        $('#confirm').click(function(){

            var person = $('#pass').val();
            person=person.trim();
            if(person!='')
            {
              $('#authpass').val(person);
                  
    
               $('#pending_document').submit();
               return true;
            }
            else
            {
              <?php echo 'alert("'.xlt('Please enter your password.').'");' ; ?>
              $('#pass').focus();
              return false;
            }
        });

         $('#cancelconf').click(function(){
            $('#dialog').css('display','none');
            $('#opacity_dv').css('display','none');
            return false;
          });
    <?php
     if(isset($_GET["status"])){
      if($_GET["status"]==0){
        echo 'alert("'.xlt('Report confirmation fail.').'");' ;
      }
      else if($_GET["status"]==2){
        echo 'alert("'.xlt('Invalid Password.').'");' ;
      }
      else if($_GET["status"]==1){
        echo 'alert("'.xlt('Report conformation completed.').'");' ;
      }
    }

    if(isset($_GET["addsuccess"])){
      if($_GET["addsuccess"]==1){
        echo 'alert("'.xlt('Report added successfully.').'");' ;
      }
      else if($_GET["addsuccess"]==2){
        echo 'alert("'.xlt('Report edited successfully.').'");' ;
      }
      else if($_GET["addsuccess"]==0){
         echo 'alert("'.xlt('Report create fail.').'");' ; 
      }
    }
 ?>

    });
    

</script>

