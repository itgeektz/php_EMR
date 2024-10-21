<?php /* Smarty version 2.6.29, created on 2020-11-24 08:28:13
         compiled from /var/www/html/openemr/templates/documents/general_list.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'xl', '/var/www/html/openemr/templates/documents/general_list.html', 23, false),)), $this); ?>
<html>
<head>
<?php html_header_show();
	$_SESSION['pid']=$_GET['patient_id'];
 ?>

<link rel="stylesheet" href="<?php echo $GLOBALS['css_header'];  ?>" type="text/css">

<?php echo '
'; ?>


<script src="DocumentTreeMenu.js" language="JavaScript" type="text/javascript"></script>
</head>
<!--<body bgcolor="<?php echo $this->_tpl_vars['STYLE']['BGCOLOR2']; ?>
">-->

<!-- ViSolve - Call expandAll function on loading of the page if global value 'expand_document' is set -->
<?php  if ($GLOBALS['expand_document_tree']) {   ?>
  <body class="body_top" onload="javascript:objTreeMenu_1.expandAll();return false;">
<?php  } else {  ?>
  <body class="body_top">
<?php  }  ?>

<div class="title"><?php echo smarty_function_xl(array('t' => 'Documents'), $this);?>
</div>
<div id="documents_list">
<table>
	<tr>
		<td height="20" valign="top"><?php echo smarty_function_xl(array('t' => 'Categories'), $this);?>
 &nbsp;
            (<a href="#" onclick="javascript:objTreeMenu_1.collapseAll();return false;"><?php echo smarty_function_xl(array('t' => 'Collapse all'), $this);?>
</a>)
		</td>
	</tr>
	<tr>
		<td valign="top"><?php echo $this->_tpl_vars['tree_html']; ?>
</td>
	</tr>
</table>
</div>
<div id="documents_actions">
		<?php if ($this->_tpl_vars['message']): ?>
			<div class='text' style="margin-bottom:-10px; margin-top:-8px"><i><?php echo $this->_tpl_vars['message']; ?>
</i></div><br>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['messages']): ?>
            <div class='text' style="margin-bottom:-10px; margin-top:-8px"><i><?php echo $this->_tpl_vars['messages']; ?>
</i></div><br>
		<?php endif; ?>
		<?php echo $this->_tpl_vars['activity']; ?>

</div>



<?php 
function oeFormatShortDates_me($date='today') {
  if ($date === 'today') $date = date('Y-m-d');
  if (strlen($date) == 10) {
    // assume input is yyyy-mm-dd
    if ($GLOBALS['date_display_format'] == 1)      // mm/dd/yyyy
      $date = substr($date, 5, 2) . '/' . substr($date, 8, 2) . '/' . substr($date, 0, 4);
    else if ($GLOBALS['date_display_format'] == 2) // dd/mm/yyyy
      $date = substr($date, 8, 2) . '/' . substr($date, 5, 2) . '/' . substr($date, 0, 4);
  }
  return $date;
}

 $pid=$_GET['patient_id'];
 ?>

<script type="text/javascript">
<?php  if ($GLOBALS['concurrent_layout']) { 
	$sql = "select *, DATE_FORMAT(DOB,'%Y-%m-%d') as DOB_YMD from patient_data where pid=? order by date DESC limit 0,1";
 

	$result=sqlQuery($sql, array($pid) );
	
 if (isset($_GET['set_pid'])) {
 	$from = new DateTime($result['DOB_YMD']);
	$to   = new DateTime('today');
	$age= $from->diff($to)->y;
   ?>

parent.left_nav.setPatient(<?php  echo "'" . htmlspecialchars(($result['fname']) . " " . ($result['lname']),ENT_QUOTES) .
   "'," . htmlspecialchars($pid,ENT_QUOTES) . ",'" . htmlspecialchars(($result['pubpid']),ENT_QUOTES) .
   "','', ' " . htmlspecialchars(xl('DOB') . ": " . oeFormatShortDates_me($result['DOB_YMD']) . " " . xl('Age') . ": " .$age, ENT_QUOTES) . "'";  ?>);
 var EncounterDateArray = new Array;
 var CalendarCategoryArray = new Array;
 var EncounterIdArray = new Array;
 var Count = 0;

<?php 
  $result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe ".
    " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? order by fe.date desc", array($pid));
  if(sqlNumRows($result4)>0) {
    while($rowresult4 = sqlFetchArray($result4)) {
 ?>
 EncounterIdArray[Count] = '<?php  echo htmlspecialchars($rowresult4['encounter'], ENT_QUOTES);  ?>';
 EncounterDateArray[Count] = '<?php  echo htmlspecialchars(oeFormatShortDates_me(date("Y-m-d", strtotime($rowresult4['date']))), ENT_QUOTES);  ?>';
 CalendarCategoryArray[Count] = '<?php  echo htmlspecialchars(xl_appt_category($rowresult4['pc_catname']), ENT_QUOTES);  ?>';
 Count++;
<?php 
    }
  }
 ?>
 parent.left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);

<?php  }  ?>
 parent.left_nav.setRadio(window.name, 'dem');
 parent.left_nav.syncRadios();

<?php  
 }  ?>


</script>
</body>
</html>