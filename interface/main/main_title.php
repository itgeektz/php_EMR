<?php
/**
 * main_title.php - The main titlebar, at the top of the 'concurrent' layout.
*/

include_once('../globals.php');

$limit_access_group = ["Pharmacist","DataEntry User"];
?>
<html>
<head>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">
      .hidden {
        display:none;
      }
      .visible{
        display:block;
      }
a {
    color: #000 !important;
    font-weight: bold !important;
    text-decoration: none !important;
}

.css_button_smalls
{
  background: #ffffff  none repeat scroll 0 0;
    border-radius: 5px;
    color: white;
    display: block;
    font-weight: bold;
    height: 6px;
    padding: 10px;
    text-align: center;
    width: 100px;
    font-size: 8px;
    line-height: 8px;
}

.css_button_smallss
{
  background: #ffffff  none repeat scroll 0 0;
    border-radius: 5px;
    color: white;
    display: inline-block;
    font-weight: bold;
    height: 6px;
    padding: 10px;
    text-align: center;
    width: 45px;
    font-size: 10px;
    line-height: 8px;
}



</style>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/js/jquery-1.6.4.min.js"></script>
<script type="text/javascript" language="javascript">
function toencounter(rawdata) {
	
//This is called in the on change event of the Encounter list.
//It opens the corresponding pages.
	document.getElementById('EncounterHistory').selectedIndex=0;
	if(rawdata=='')
	 {
		 return false;
	 }
	else if(rawdata=='New Encounter')
	 {
		dlgopen('../../interface/forms/newpatient/new.php?autoloaded=1&calenc=', '_blank', 875, 375);
	 	//top.window.parent.left_nav.loadFrame2('nen1','RBot','forms/newpatient/new.php?autoloaded=1&calenc=')
		return true;
	 }
	else if(rawdata=='Past Encounter List')
	 {
	 	top.window.parent.left_nav.loadFrame2('pel1','RTop','patient_file/history/encounters.php')
		return true;
	 }
    var parts = rawdata.split("~");
    var enc = parts[0];
    var datestr = parts[1];
    var f = top.window.parent.left_nav.document.forms[0];
	frame = 'RBot';
    if (!f.cb_bot.checked) frame = 'RTop'; else if (!f.cb_top.checked) frame = 'RBot';

    top.restoreSession();

<?php if ($GLOBALS['concurrent_layout']) 
{	?>

 if(enc > 0)
	 {
    	parent.left_nav.setEncounter(datestr, enc, frame);
    	parent.left_nav.setRadio(frame, 'enc');
	    //top.frames[frame].location.href  = '../patient_file/encounter/encounter_top.php?set_encounter=' + enc;
	    $.ajax({
      			url: '../session.php',
      			async: false,
      			data:{get_pid:'pid'},
      			success : function (data){
        			pid = data;
        			}
			});
	    	
	<?php 	
		$limit_access_group = ["DataEntry User"];
		if(in_array($_SESSION['usergroup'], $limit_access_group))
			{
				
			?>	
			
			 $.ajax({
      			url: '../session.php',
      			async: false,
      			data:{put_enc:enc},
      			success : function (data){
        		
      			}
			});


			top.frames['RTop'].location= '../../controller.php?document&list&patient_id';
			
			<?php 		
			}else
				{
				?>
					
    			parent.left_nav.loadFrame2('dem1', 'RTop', 'patient_file/summary/demographics.php?opener_save=true&set_encounterid='+enc+'&set_pid='+pid);
    			
			<?php } ?>
      	}
      	
<?php } else
		{ ?>
    			top.Title.location.href = '../patient_file/encounter/encounter_title.php?set_encounter='+ enc;
    			top.Main.location.href  = '../patient_file/encounter/patient_encounter.php?set_encounter='+enc;
	<?php } ?>

}

function bpopup() {
 top.restoreSession();
 window.open('../main/about_page.php','_blank', 'width=350,height=250,resizable=1');
 return false;
}

function showhideMenu() {
	var m = parent.document.getElementById("fsbody");
	var targetWidth = '<?php echo $_SESSION['language_direction'] == 'ltr' ? '0,*' : '*,0'; ?>';
	if (m.cols == targetWidth) {
		m.cols = '<?php echo $_SESSION['language_direction'] == 'ltr' ?  $GLOBALS['gbl_nav_area_width'] .',*' : '*,' . $GLOBALS['gbl_nav_area_width'] ?>';
		document.getElementById("showMenuLink").innerHTML = '<?php echo htmlspecialchars( xl('Hide Menu'), ENT_QUOTES); ?>';
	} else {
		m.cols = targetWidth;
		document.getElementById("showMenuLink").innerHTML = '<?php echo htmlspecialchars( xl('Show Menu'), ENT_QUOTES); ?>';
	}
}
</script>
</head>
<body class="body_title">
<?php
$res = sqlQuery("select * from users where username='".$_SESSION{"authUser"}."'");
	 $sq="select count(id) as activecount from pnotes where assigned_to='".$_SESSION["authUser"]."' AND message_status!='Done' AND activity!=0";
 $activenotes = sqlQuery($sq);
//echo "<pre>";print_r($activenotes);exit;
?>

<table id="main-title" cellspacing="0" cellpadding="0" width="100%" height="100%">
<tr>
<td align="left" style="width: 538px;">
<?php if ($GLOBALS['concurrent_layout']) { ?>
	<table cellspacing="0" cellpadding="1" style="margin:0px 0px 0px 3px;">

<tr>
	
	<?php 
	
	if(!in_array($_SESSION['usergroup'], $limit_access_group))
	{	?>
			
			<td style="vertical-align:text-bottom;width: 92px;">
				<a href='' class="css_button_smalls" style="margin:0px;vertical-align:top;" id='new0' onClick=" return top.window.parent.left_nav.loadFrame2('new0','RTop','new/new.php')">
				<span><?php echo htmlspecialchars( xl('NEW PATIENT'), ENT_QUOTES); ?></span></a>
		    </td>
    
    	<?php 
	 }?>
    
    <td style="vertical-align:text-bottom;">
            <a href='' class="css_button_smalls" style="margin:0px;vertical-align:top;display:none;" id='clear_active' onClick="javascript:parent.left_nav.clearactive();return false;">
            <span><?php echo htmlspecialchars( xl('CLEAR ACTIVE PATIENT'), ENT_QUOTES); ?></span></a>
    </td>
    
</tr>


	<tr>
	<td valign="baseline"><B>
		<a class="text" style='vertical-align:text-bottom;' href="main_title.php" id='showMenuLink' onclick='javascript:showhideMenu();return false;'><?php xl('Hide Menu','e'); ?></a></B>
	</td>
	<?php if (acl_check('patients','demo','',array('write','addonly') )) {?>
	<td id="pat_cat" style="display: none;">
       <span class="text" style="margin-left:5px;"><?php echo htmlspecialchars( xl('Patient Category :'), ENT_QUOTES);?></span>
       <span class='title_bar_top' id="patient_cate"></span>
    </td>
    <?php }?>
    
	</tr></table>
<?php } else { ?>
&nbsp;
<?php } ?>
</td>
<td style="margin:3px 0px 3px 0px;vertical-align:middle;">
        <div style='margin-left:10px; float:left; display:none' id="current_patient_block">
            <span class='text'><?php xl('Patient','e'); ?>:&nbsp;</span><span class='title_bar_top' id="current_patient"><b><?php xl('None','e'); ?></b></span>
        </div>
</td>
<td style="margin:3px 0px 3px 0px;vertical-align:middle;" align="left">
	<table cellspacing="0" cellpadding="1" ><tr><td>
		<div style='margin-left:5px; float:left; display:none' id="past_encounter_block">
			<span class='title_bar_top' id="past_encounter"><b><?php echo htmlspecialchars( xl('None'), ENT_QUOTES) ?></b></span>
		</div></td></tr>
	<tr><td valign="baseline" align="center">
        <div style='display:none' class='text' id="current_encounter_block" >
            <span class='text'><?php xl('Selected Encounter','e'); ?>:&nbsp;</span><span class='title_bar_top' id="current_encounter"><b><?php xl('None','e'); ?></b></span>
        </div></td></tr></table>
</td>

<td align="right">
	<table>
		<tr>
			<td>
				<?php
			$ress = sqlQuery("SELECT `name` FROM `gacl_aro_groups` WHERE `id`='".$GLOBALS['notification_group']."'");
			
			if($_SESSION['usergroup']==$ress['name'] || $_SESSION['usergroup']=='Administrators')
			{
			?>
				<a style="  position: relative;" href="" id="msg0" onClick="javascript:parent.left_nav.loadFrame2('msg0','RBot','main/messages/messages.php?form_active=1')" class="collapsed"><img src="../../sites/default/images/notification.png" class="notification-icon"  alt="<?php xl('Notification','e'); ?>" />
					
			<?php 
				if($activenotes['activecount']>0)
				{
			?>
					<span class="asd"><?php echo $activenotes["activecount"]; ?></span>
			<?php
				}
			?>

				</a>
			<?php } ?>
			</td>
			<td style="width: 276px;":>
				<table cellspacing="0" cellpadding="1" style="margin:0px 3px 0px 0px;">
		<tr>
			<td align="right" class="text" style="vertical-align:text-bottom;">
				<a href='main_title.php' class="css_button_smallss" onClick="javascript:parent.left_nav.goHome();return false;" ><?php xl('Home','e'); ?></a>			
        		<a  href=""  onclick="return bpopup()" class="css_button_smallss"><?php echo xlt('About'); ?></a>&nbsp;
			<td align="right" style="vertical-align:top;">
				<a href="../logout.php" target="_top" class="css_button_smallss" style='float:right;' id="logout_link" onClick="top.restoreSession()" >
					<span><?php echo htmlspecialchars( xl('Logout'), ENT_QUOTES) ?></span>
				</a>
			</td>
	</tr>
	<tr>
		<td colspan='2' valign="baseline" align='right'><B>
			<span class="text title_bar_top" title="<?php echo htmlspecialchars( xl('Authorization group') .': '.$_SESSION['authGroup'], ENT_QUOTES); ?>"><?php echo htmlspecialchars($res{"fname"}.' '.$res{"lname"},ENT_NOQUOTES); ?></span></span></td>
    </tr></table>
			</td>
		</tr>
	</table>

	
</td>
</tr>
</table>

<script type="text/javascript" language="javascript">
parent.loadedFrameCount += 1;
</script>

</body>
</html>
