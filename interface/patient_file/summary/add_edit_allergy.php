<?php
/**
 * add or edit a medical problem.
 *
 * Copyright (C) 2005-2011 Rod Roark <rod@sunsetsystems.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
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

$issue = $_REQUEST['issue'];
$thispid = 0 + (empty($_REQUEST['thispid']) ? $pid : $_REQUEST['thispid']);
$info_msg = "";

// A nonempty thisenc means we are to link the issue to the encounter.
$thisenc = 0 + (empty($_REQUEST['thisenc']) ? 0 : $_REQUEST['thisenc']);

// A nonempty thistype is an issue type to be forced for a new issue.
$thistype = empty($_REQUEST['thistype']) ? '' : $_REQUEST['thistype'];

if ($issue && !acl_check('patients','med','','write') ) die(xlt("Edit is not authorized!"));
if ( !acl_check('patients','med','',array('write','addonly') )) die(xlt("Add is not authorized!"));

$tmp = getPatientData($thispid, "squad");
if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
  die(xlt("Not authorized for this squad!"));

function QuotedOrNull($fld) {
  if ($fld) return "'".add_escape_custom($fld)."'";
  return "NULL";
}

// Do not use this function since quotes are added in query escaping mechanism
// Only keeping since used in the football injury code football_injury.inc.php that is included.
// If start using this function, then incorporate the add_escape_custom() function into it
function rbvalue($rbname) {
  $tmp = $_POST[$rbname];
  if (! $tmp) $tmp = '0';
  return "'$tmp'";
}

function cbvalue($cbname) {
  return $_POST[$cbname] ? '1' : '0';
}

function invalue($inname) {
  return (int) trim($_POST[$inname]);
}

// Do not use this function since quotes are added in query escaping mechanism
// Only keeping since used in the football injury code football_injury.inc.php that is included.
// If start using this function, then incorporate the add_escape_custom() function into it
function txvalue($txname) {
  return "'" . trim($_POST[$txname]) . "'";
}

function rbinput($name, $value, $desc, $colname) {
  global $irow;
  $ret  = "<input type='radio' name='".attr($name)."' value='".attr($value)."'";
  if ($irow[$colname] == $value) $ret .= " checked";
  $ret .= " />".text($desc);
  return $ret;
}

function rbcell($name, $value, $desc, $colname) {
 return "<td width='25%' nowrap>" . rbinput($name, $value, $desc, $colname) . "</td>\n";
}

// Given an issue type as a string, compute its index.
function issueTypeIndex($tstr) {
  global $ISSUE_TYPES;
  $i = 0;
  foreach ($ISSUE_TYPES as $key => $value) {
    if ($key == $tstr) break;
    ++$i;
  }
  return $i;
}

function ActiveIssueCodeRecycleFn($thispid2, $ISSUE_TYPES2) {
///////////////////////////////////////////////////////////////////////
// Active Issue Code Recycle Function authored by epsdky (2014-2015) //
///////////////////////////////////////////////////////////////////////

  $modeIssueTypes = array();
  $issueTypeIdx2 = array();
  $idx2 = 0;

  foreach ($ISSUE_TYPES2 as $issueTypeX => $isJunk) {

    $modeIssueTypes[$idx2] = $issueTypeX;
    $issueTypeIdx2[$issueTypeX] = $idx2;
    ++$idx2;

  }

  $pe2 = array($thispid2);
  $qs2 = str_repeat('?, ', count($modeIssueTypes) - 1) . '?';
  $sqlParameters2 = array_merge($pe2, $modeIssueTypes);

  $codeList2 = array();

  $issueCodes2 = sqlStatement("SELECT diagnosis FROM lists WHERE pid = ? AND enddate is NULL AND type IN ($qs2)", $sqlParameters2);

  while ($issueCodesRow2 = sqlFetchArray($issueCodes2)) {

    if ($issueCodesRow2['diagnosis'] != "") {

      $someCodes2 = explode(";", $issueCodesRow2['diagnosis']);
      $codeList2 = array_merge($codeList2, $someCodes2);

    }

  }

  if ($codeList2) {

    $codeList2 = array_unique($codeList2);
    sort($codeList2);

  }

  $memberCodes = array();
  $memberCodes[0] = array();
  $memberCodes[1] = array();
  $memberCodes[2] = array();

  $allowedCodes2 = array();
  $allowedCodes2[0] = collect_codetypes("medical_problem");
  $allowedCodes2[1] = collect_codetypes("diagnosis");
  $allowedCodes2[2] = collect_codetypes("drug");

  // Test membership of codes to each code type set
  foreach ($allowedCodes2 as $akey1 => $allowCodes2) {

    foreach ($codeList2 as $listCode2) {

      list($codeTyX,) = explode(":", $listCode2);

      if (in_array($codeTyX, $allowCodes2)) {

        array_push($memberCodes[$akey1], $listCode2);

      }

    }

  }

  // output sets of display options
  $displayCodeSets[0] = $memberCodes[0]; // medical_problem
  $displayCodeSets[1] = array_merge($memberCodes[1], $memberCodes[2]);  // allergy
  $displayCodeSets[2] = array_merge($memberCodes[2], $memberCodes[1]);  // medication
  $displayCodeSets[3] = $memberCodes[1];  // default

  echo "var listBoxOptionSets = new Array();\n\n";

  foreach ($displayCodeSets as $akey => $displayCodeSet) {

    echo "listBoxOptionSets[" . attr($akey) . "] = new Array();\n";

    if ($displayCodeSet) {

      foreach ($displayCodeSet as $dispCode2) {

        $codeDesc2 = lookup_code_descriptions($dispCode2);
        echo "listBoxOptionSets[" . attr($akey) . "][listBoxOptionSets[" . attr($akey) . "].length] = new Option('" . attr($dispCode2) . " (" . attr(trim($codeDesc2)) . ") ' ,'" . attr($dispCode2) . "' , false, false);\n";

      }

    }

  }

  // map issues to a set of display options
  $modeIndexMapping = array();

  foreach ($modeIssueTypes as $akey2 => $isJunk) $modeIndexMapping[$akey2] = 3;

  if (array_key_exists("medical_problem", $issueTypeIdx2))
    $modeIndexMapping[$issueTypeIdx2['medical_problem']] = 0;
  if (array_key_exists("allergy", $issueTypeIdx2))
    $modeIndexMapping[$issueTypeIdx2['allergy']] = 1;
  if (array_key_exists("medication", $issueTypeIdx2))
    $modeIndexMapping[$issueTypeIdx2['medication']] = 2;

  echo "\nvar listBoxOptions2 = new Array();\n\n";

  foreach ($modeIssueTypes as $akey2 => $isJunk) {
    echo "listBoxOptions2[" . attr($akey2) . "] = listBoxOptionSets[" . attr($modeIndexMapping[$akey2]) . "];\n";
  }
///////////////////////////////////////////////////////////////////////
// End of Active Issue Code Recycle Function main code block         //
///////////////////////////////////////////////////////////////////////
}

// If we are saving, then save and close the window.
//
if ($_POST['form_save']) {

$cnt = count($_POST['update_id']);
$id = 0;

//Deleting the issues

$del_id = $_POST['delete_id'];
$del_arr = explode(",",$del_id);

foreach ($del_arr as $value) {
  sqlQuery("DELETE FROM lists WHERE pid = ? AND id = ?", array($thispid, $value));
}

for ($j=0; $j < $cnt; $j++) {
  $id = $_POST['update_id'][$j];
  $issue = "";
  $i = 0;
  $text_type = "unknown";
  foreach ($ISSUE_TYPES as $key => $value) {
   if ($i++ == $_POST['form_type']) $text_type = $key;
  }

  $form_begin = fixDate($_POST['form_begin'][$j], '');
  $form_end   = fixDate($_POST['form_end'][$j], '');

  if ($id > 0) {

   $query = "UPDATE lists SET " .
    "type = '"        . add_escape_custom($text_type)                  . "', " .
    "title = '"       . add_escape_custom($_POST['form_title'][$j])        . "', " .
    "comments = '"    . add_escape_custom($_POST['form_comments'][$j])     . "', " .
    "begdate = "      . QuotedOrNull($form_begin)   . ", "  .
    "enddate = "      . QuotedOrNull($form_end)     . ", "  .
    "diagnosis = '"   . add_escape_custom($_POST['form_diagnosis'][$j])    . "', " .
    "erx_uploaded = '0', " .
    "modifydate = NOW() " .
    "WHERE id = '" . add_escape_custom($id) . "'";
    sqlStatement($query);
    if ($text_type == "medication" && enddate != '') {
      sqlStatement('UPDATE prescriptions SET '
        . 'medication = 0 where patient_id = ? '
        . " and upper(trim(drug)) = ? "
        . ' and medication = 1', array($thispid,strtoupper($_POST['form_title'][$j])) );
    }

  } else {

    $issue = sqlInsert("INSERT INTO lists ( " .
    "date, pid, type, title, activity, comments, begdate, enddate, " .
    "diagnosis" .
    ") VALUES ( " .
    "NOW(), " .
    "'" . add_escape_custom($thispid) . "', " .
    "'" . add_escape_custom($text_type)                 . "', " .
    "'" . add_escape_custom($_POST['form_title'][$j])       . "', " .
    "1, "                            .
    "'" . add_escape_custom($_POST['form_comments'][$j])    . "', " .
    QuotedOrNull($form_begin)        . ", "  .
    QuotedOrNull($form_end)        . ", "  .
    "'" . add_escape_custom($_POST['form_diagnosis'][$j])   . "'" .
    ")");

  }

  // For record/reporting purposes, place entry in lists_touch table.
  setListTouch($thispid,$text_type);

  // If requested, link the issue to a specified encounter.
  if ($thisenc) {
    $query = "INSERT INTO issue_encounter ( " .
      "pid, list_id, encounter " .
      ") VALUES ( ?,?,? )";
    sqlStatement($query, array($thispid,$issue,$thisenc));
  }

  $tmp_title = addslashes($ISSUE_TYPES[$text_type][2] . ": $form_begin " .
    substr($_POST['form_title'][i], 0, 40));

 }
?>
<script>
window.top.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php?opener_save=true');
</script>
<?php
}

$irow = array();
if ($issue)
  $irow = sqlQuery("SELECT * FROM lists WHERE id = ?",array($issue));
else if ($thistype)
  $irow['type'] = $thistype;

$type_index = 0;

if (!empty($irow['type'])) {
  foreach ($ISSUE_TYPES as $key => $value) {
    if ($key == $irow['type']) break;
    ++$type_index;
  }
}
?>
<html>
<head>
<?php html_header_show();?>
<title><?php echo $issue ? xlt('Edit') : xlt('Add New'); ?><?php echo " ".xlt('Issue'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>

td, input, select, textarea {
 font-family: Arial, Helvetica, sans-serif;
 font-size: 10pt;
}

div.section {
 border: solid;
 border-width: 1px;
 border-color: #0000ff;
 margin: 0 0 0 10pt;
 padding: 5pt;
}

#code_list{float:left;list-style:none;margin:0;padding:0;width:400px;}
#code_list li{padding: 10px; background:#FAFAFA;border-bottom:#F0F0F0 1px solid;}
#code_list li:hover{background:#F0F0F0;}

</style>

<style type="text/css">@import url(<?php echo $GLOBALS['webroot']; ?>/library/dynarch_calendar.css);</style>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/js/jquery-1.9.1.min.js"></script>

<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>

<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 var aitypes = new Array(); // issue type attributes
 var aopts   = new Array(); // Option objects
<?php
 $i = 0;
 foreach ($ISSUE_TYPES as $key => $value) {
  echo " aitypes[$i] = " . attr($value[3]) . ";\n";
  echo " aopts[$i] = new Array();\n";
  $qry = sqlStatement("SELECT * FROM list_options WHERE list_id = ?",array($key."_issue_list"));
  while($res = sqlFetchArray($qry)){
    echo " aopts[$i][aopts[$i].length] = new Option('".attr(trim($res['option_id']))."', '".attr(xl_list_label(trim($res['title'])))."', false, false);\n";
    if ($res['codes']) {
      echo " aopts[$i][aopts[$i].length-1].setAttribute('data-code','".attr(trim($res['codes']))."');\n";
    }
  }
  ++$i;
 }

///////////
ActiveIssueCodeRecycleFn($thispid, $ISSUE_TYPES);
///////////
?>

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 ///////////////////////////
 function codeBoxFunction2() {
  var f = document.forms[0];
  var x2 = f.form_codeSelect2.options[f.form_codeSelect2.selectedIndex].value;
  f.form_codeSelect2.selectedIndex = -1;
  var x6 = f.form_diagnosis.value;
  if (x6.length > 0) x6 += ";";
  x6 += x2;
  f.form_diagnosis.value = x6;
 }
 ///////////////////////////
 //
 // React to selection of an issue type.  This loads the associated
 // shortcuts into the selection list of titles, and determines which
 // rows are displayed or hidden.
 function newtype(index) {
  var f = document.forms[0];
  var theopts = f.form_titles.options;
  theopts.length = 0;
  var i = 0;
  for (i = 0; i < aopts[index].length; ++i) {
   theopts[i] = aopts[index][i];
  }
  document.getElementById('row_titles').style.display = i ? 'none' : 'none';
  //
  ///////////////////////
  var listBoxOpts2 = f.form_codeSelect2.options;
  listBoxOpts2.length = 0;
  var ix = 0;
  for (ix = 0; ix < listBoxOptions2[index].length; ++ix) {
   listBoxOpts2[ix] = listBoxOptions2[index][ix];
   listBoxOpts2[ix].title = listBoxOptions2[index][ix].text;
  }

  //////////////////////
  //
  // Show or hide various rows depending on issue type, except do not
  // hide the comments or referred-by fields if they have data.

  var comdisp = (aitypes[index] == 1) ? 'none' : '';
  var comdisp_ed = (aitypes[index] == 1) ? 'none' : '';
  var comdisp_co = (aitypes[index] == 1) ? 'none' : '';
  var revdisp = (aitypes[index] == 1) ? '' : 'none';
  var revdisp_com = (aitypes[index] == 1) ? '' : 'none';
  var injdisp = (aitypes[index] == 2) ? '' : 'none';
  var nordisp = (aitypes[index] == 0) ? '' : 'none';

  var issue_allery = <?php echo issueTypeIndex('allergy'); ?>;
  if(index == issue_allery){
    comdisp_ed = '';
    comdisp = 'none';
    comdisp_co = ''
    injdisp = 'none';
    nordisp = 'none';
    revdisp_com = '';
  }
  // reaction row should be displayed only for medication allergy.
  var alldisp =  (index == <?php echo issueTypeIndex('allergy'); ?>) ? 'none' : 'none';
  document.getElementById('row_enddate').style.display = comdisp_ed;
  // Note that by default all the issues will not show the active row
  //  (which is desired functionality, since then use the end date
  //   to inactivate the item.)
  document.getElementById('row_diagnosis'     ).style.display = comdisp_co;

<?php
  if ($ISSUE_TYPES['football_injury']) {
    // Generate more of these for football injury fields.
    issue_football_injury_newtype();
  }
  if ($ISSUE_TYPES['ippf_gcac'] && !$_POST['form_save']) {
    // Generate more of these for gcac and contraceptive fields.
    if (empty($issue) || $irow['type'] == 'ippf_gcac'    ) issue_ippf_gcac_newtype();
    if (empty($issue) || $irow['type'] == 'contraceptive') issue_ippf_con_newtype();
  }
?>
 }

 // If a clickoption title is selected, copy it to the title field.
 // If it has a code, add that too.
 function set_text() {
  var f = document.forms[0];
  f.form_title.value = f.form_titles.options[f.form_titles.selectedIndex].text;
  f.form_diagnosis.value = f.form_titles.options[f.form_titles.selectedIndex].getAttribute('data-code');
  f.form_titles.selectedIndex = -1;
 }

 // Process click on Delete link.
 function deleteme() {
  dlgopen('../deleter.php?issue=<?php echo attr($issue) ?>', '_blank', 500, 450);
  return false;
 }

 // Called by the deleteme.php window on a successful delete.
 function imdeleted() {
  closeme();
 }

 function closeme() {
  window.top.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php?opener_save=true');
 }

/*
// This is for callback by the find-code popup.
// Appends to or erases the current list of diagnoses.
function set_related(codetype, code, selector, codedesc) {
 var f = document.forms[0];
 var s = f.form_diagnosis.value;
 var title = f.form_title.value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 f.form_diagnosis.value = s;
 if(title == '') f.form_title.value = codedesc;
}
*/

// Check for errors when the form is submitted.
function validate() {
 var f = document.forms[0];
 if(f.form_begin.value > f.form_end.value && (f.form_end.value)) {
  alert("<?php echo addslashes(xl('Please Enter End Date greater than Begin Date!')); ?>");
  return false;
 }
 if (! f.form_title.value) {
  alert("<?php echo addslashes(xl('Please enter a title!')); ?>");
  return false;
 }
 top.restoreSession();
 return true;
}

// Supports customizable forms (currently just for IPPF).
function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

// Process click on diagnosis for patient education popup.
function educlick(codetype, codevalue) {
  dlgopen('../education.php?type=' + encodeURIComponent(codetype) +
    '&code=' + encodeURIComponent(codevalue) +
    '&language=<?php echo urlencode($language); ?>',
    '_blank', 1024, 750);
}
</script>

</head>

<body class="body_top" style="padding-right:0.5em">

<form method='post' name='theform'
 action='add_edit_allergy.php?issue=<?php echo attr($issue); ?>&thispid=<?php echo attr($thispid); ?>&thisenc=<?php echo attr($thisenc); ?>'
 onsubmit='return validate()'>

<table border='0' width='100%' class='tbl_allergy'>
  <tr id='row_titles' style="display: none">
   <td valign='top' nowrap>&nbsp;</td>
   <td valign='top'>
    <select name='form_titles' size='<?php echo $GLOBALS['athletic_team'] ? 10 : 4; ?>' onchange='set_text()'>
    </select> <?php echo xlt('(Select one of these, or type your own title)'); ?>
   </td>
  </tr>
  <tr id='row_codeSelect2' style="display: none">
  <td><b><?php echo xlt('Active Issue Codes'); ?>:</b>
  </td>
  <td>
   <select name='form_codeSelect2' size='4' onchange="codeBoxFunction2()" style="max-width:100%;">
   </select>
  </td>
  </tr>

  <tr class='head'>
    <th style='text-align:left;font-size: 10pt;'><?php echo xlt('Title'); ?></th>
    <th style='text-align:left;font-size: 10pt;'><?php echo xlt('Begin'); ?></th>
    <th style='text-align:left;font-size: 10pt;'><?php echo xlt('End'); ?></th>
    <th style='text-align:left;font-size: 10pt;'><?php echo xlt('Coding'); ?></th>
    <th style='text-align:left;font-size: 10pt;'><?php echo xlt('Comments'); ?></th>
    <th></th>
  </tr>

<?php
$encount = 0;

$ares = sqlStatement("SELECT * FROM lists WHERE pid = ? AND type = ? ORDER BY id", array($thispid,'allergy') );
if (sqlNumRows($ares) < 1) {
  $rowid = 0;
?>
  <tr class="tr_clone">
    <input type="hidden" name="update_id[]" class="update_id" id="update_id" value="<?php echo $rowid; ?>">
   <td style="width: 17%">
    <input type='text' size='40' name='form_title[]' id='form_title_<?php echo $rowid; ?>' class='form_title' autocomplete='off' value='<?php echo attr($disptitle) ?>' style='width:100%' />
    <div id="suggesstion-box" class='suggesstion-box' style="position: absolute;"></div>
   </td>

   <td style="width: 18%">
     <input type='text' size='7' name='form_begin[]' class='form_begin' id='form_begin_<?php echo $rowid; ?>'
      value='<?php echo attr($row['begdate']) ?>'
      onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
      title='<?php echo xla('yyyy-mm-dd date of onset, surgery or start of medication'); ?>' />
     <img class='img_begin' src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
      id='img_begin_<?php echo $rowid; ?>' border='0' alt='[?]' style='cursor:pointer'
      title='<?php echo xla('Click here to choose a date'); ?>' />
    </td>

    <td  id='row_enddate' style="width: 18%;">
     <input type='text' size='7' name='form_end[]' id='form_end_<?php echo $rowid; ?>' class='form_end'
      value='<?php echo attr($row['enddate']) ?>'
      onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
      title='<?php echo xla('yyyy-mm-dd date of recovery or end of medication'); ?>' />
     <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
      id='img_end_<?php echo $rowid; ?>' class='img_end' border='0' alt='[?]' style='cursor:pointer'
      title='<?php echo xla('Click here to choose a date'); ?>' />
    </td>

     <td id='row_diagnosis' style="width: 15%">
      <input type='text' size='50' name='form_diagnosis[]' id='form_diagnosis_<?php echo $rowid; ?>' autocomplete='off' class='form_diagnosis'
       value='<?php echo attr($row['diagnosis']) ?>' title='<?php echo xla('Click to select or change coding'); ?>'
       style='width: 100%' />
       <div id="suggesstion-box-dia" class='suggesstion-box-dia' style="position: absolute;"></div>
     </td>

     <td style="width:25%">
      <textarea name='form_comments[]' class='form_comments' rows='4' cols='40' wrap='virtual' style='width:100%'><?php echo text($row['comments']) ?></textarea>
     </td>

     <td style="width: 10%">
       <input type="button" class="add" id="add" value="+">
       <input type="button" class="del" id="del" value="-">
     </td>

   </tr>
   <script>
   Calendar.setup({inputField:"form_begin_0", ifFormat:"%Y-%m-%d", button:"img_begin_0"});
   Calendar.setup({inputField:"form_end_0", ifFormat:"%Y-%m-%d", button:"img_end_0"});
   </script>
<?php
  }

// display allergy
while ($row = sqlFetchArray($ares)) {

  $rowid = $row['id'];

  $disptitle = trim($row['title']) ? $row['title'] : "[Missing Title]";

  // encount is used to toggle the color of the table-row output below
  ++$encount;
  $bgclass = (($encount & 1) ? "bg1" : "bg2");

  // look up the diag codes
  $codetext = "";
  if ($row['diagnosis'] != "") {
      $diags = explode(";", $row['diagnosis']);
      foreach ($diags as $diag) {
          $codedesc = lookup_code_descriptions($diag);
          list($codetype, $code) = explode(':', $diag);
          if ($codetext) $codetext .= "<br />";
          $codetext .= "<a href='javascript:educlick(\"$codetype\",\"$code\")' $colorstyle>" .
            text($diag . " (" . $codedesc . ")") . "</a>";
      }
  }

?>

 <tr class="tr_clone">
<input type="hidden" name="update_id[]" class="update_id" id="update_id" value="<?php echo $rowid; ?>">
  <td style="width: 17%">
   <input type='text' size='40' name='form_title[]' id='form_title_<?php echo $rowid; ?>' class='form_title' autocomplete='off' value='<?php echo attr($disptitle) ?>' style='width:100%' />
   <div id="suggesstion-box" class='suggesstion-box' style="position: absolute;"></div>
  </td>

  <td style="width: 18%;">
    <input type='text' size='7' name='form_begin[]' class='form_begin' id='form_begin_<?php echo $rowid; ?>'
     value='<?php echo attr($row['begdate']) ?>'
     onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
     title='<?php echo xla('yyyy-mm-dd date of onset, surgery or start of medication'); ?>' />
    <img class='img_begin' src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
     id='img_begin_<?php echo $rowid; ?>' border='0' alt='[?]' style='cursor:pointer'
     title='<?php echo xla('Click here to choose a date'); ?>' />
   </td>

   <td  id='row_enddate' style="width: 18%">
    <input type='text' size='7' name='form_end[]' id='form_end_<?php echo $rowid; ?>' class='form_end'
     value='<?php echo attr($row['enddate']) ?>'
     onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)'
     title='<?php echo xla('yyyy-mm-dd date of recovery or end of medication'); ?>' />
    <img src='../../pic/show_calendar.gif' align='absbottom' width='24' height='22'
     id='img_end_<?php echo $rowid; ?>' class='img_end' border='0' alt='[?]' style='cursor:pointer'
     title='<?php echo xla('Click here to choose a date'); ?>' />
   </td>

    <td id='row_diagnosis' style="width: 15%">
     <input type='text' size='50' name='form_diagnosis[]' id='form_diagnosis_<?php echo $rowid; ?>' autocomplete='off' class='form_diagnosis'
      value='<?php echo attr($row['diagnosis']) ?>' title='<?php echo xla('Click to select or change coding'); ?>'
      style='width: 100%' />
      <div id="suggesstion-box-dia" class='suggesstion-box-dia' style="position: absolute;"></div>
    </td>

    <td style="width: 25%;">
     <textarea name='form_comments[]' class='form_comments' rows='4' cols='40' wrap='virtual' style='width:100%'><?php echo text($row['comments']) ?></textarea>
    </td>

    <td style="width: 10%;">
      <input type="button" class="add" id="add" value="+">
      <input type="button" class="del" id="del" value="-">
    </td>

  </tr>

<script>
  Calendar.setup({inputField:"form_begin_<?php echo $rowid ?>", ifFormat:"%Y-%m-%d", button:"img_begin_<?php echo $rowid ?>"});
  Calendar.setup({inputField:"form_end_<?php echo $rowid ?>", ifFormat:"%Y-%m-%d", button:"img_end_<?php echo $rowid ?>"});
</script>
  <?php
    }
   ?>
</table>
<table style="width: 100%">
   <tr>
       <td>
       <p style="float:right">
       <input type='submit' name='form_save' value='<?php echo xla('Save'); ?>' />
       <input type='button' value='<?php echo xla('Cancel'); ?>' onclick='closeme();' />
       <input type="hidden" id="delete_id" name="delete_id" value="0">
       </p>
     </td>
     </tr>
</table>

</form>
<div id="result">

</div>
<script language='JavaScript'>
 newtype(<?php echo $type_index ?>);

 $('.add').on('click', function() {
    var tbl_row = $(this).closest('.tr_clone');
    var tbl_clone = tbl_row.clone(true);
    tbl_clone.find(':text').val('');
    tbl_clone.find('textarea').val('');
    tbl_row.after(tbl_clone);

    var cnt=$('.tbl_allergy>tbody>tr:visible').length;
    console.log(cnt);
    $('.tbl_allergy>tbody>tr:last').find(".form_begin").eq(0).attr('id', 'form_begin_'+cnt);
    $('.tbl_allergy>tbody>tr:last').find(".img_begin").eq(0).attr('id', 'img_begin_'+cnt);
    Calendar.setup({inputField:"form_begin_"+cnt, ifFormat:"%Y-%m-%d", button:"img_begin_"+cnt});

    $('.tbl_allergy>tbody>tr:last').find('.form_end').eq(0).attr('id', 'form_end_'+cnt);
    $('.tbl_allergy>tbody>tr:last').find('.img_end').eq(0).attr('id', 'img_end_'+cnt);
    Calendar.setup({inputField:"form_end_"+cnt, ifFormat:"%Y-%m-%d", button:"img_end_"+cnt});

    $('.tbl_allergy>tbody>tr:last').find('.form_title').eq(0).attr('id', 'form_title_'+cnt);
    $('.tbl_allergy>tbody>tr:last').find('.form_diagnosis').eq(0).attr('id', 'form_diagnosis_'+cnt);
    $('.tbl_allergy>tbody>tr:last').find('.form_comments').eq(0).attr('id', 'form_comments_'+cnt);
    $('.tbl_allergy>tbody>tr:last').find('.update_id').eq(0).attr('value', '0');

 });

 $('.del').on('click', function() {
   res = confirm("Please confirm to remove allergy");
   if(res){
    if($('.tr_clone').length > 1) {
      old_id = $('#delete_id').val();
      del_id = $(this).parent().parent().find('#update_id').val();
      $('#delete_id').val(old_id+','+del_id);
      $(this).closest('tr').remove();
    }
  }
 });

/* $('#form_title').on('keyup',function() {
   var search = $('#form_title').val();
   var code = 'ICD9';
   $.ajax({
     url: '../encounter/find_code_popup.php?bn_search=Search&frm=allergy&search_term='+search+'&form_code_type='+code,
     async: false,
     success: function(response){
        $('#result').empty().append(response);
     }
   });
 }); */

  $('.form_title').on('keyup',function(e) {

   var currentRequest = null;
   if (currentRequest != null) currentRequest.abort();

   currentRequest = $.ajax({
   type: "POST",
   url: "../../../library/ajax/auto_code_search.php",
   data:'search_term='+$(this).val(),
   dataype: 'json',
  /* beforeSend : function(){
       if(currentRequest != null) {
           currentRequest.abort();
       }
   },*/
   success: function(data){
      var json = $.parseJSON(data);
      var response = "<ul id='code_list'>";
      for (var i in json) {
        var desc = json[i].code_text.replace(/["']/g, "\\'");
        desc = $.trim(desc);

        var trs=$('.tbl_allergy>tbody>tr:visible');
        response += '<li onclick="selectCode(\'' + desc + '\',\'' + json[i].code + '\',\'' + trs.index($(e.target).parents('tr').eq(0)) + '\')"">' + json[i].code_text + '</li>';
      }
      response += "</ul>";

     $(e.target).parents('tr').eq(0).find(".suggesstion-box").eq(0).show();
     $(e.target).parents('tr').eq(0).find(".suggesstion-box").eq(0).html(response);
     $(e.target).parents('tr').eq(0).find(".form_title").css("background","#FFF");
   }
   });
 });

 $('.form_diagnosis').on('keyup',function(e) {

  var currentRequest = null;
  if (currentRequest != null) currentRequest.abort();

  currentRequest = $.ajax({
  type: "POST",
  url: "../../../library/ajax/auto_code_search.php",
  data:'search_term='+$(this).val(),
  dataype: 'json',
  success: function(data){
     var json = $.parseJSON(data);
     var response = "<ul id='code_list'>";
     for (var i in json) {
       var desc = json[i].code_text.replace(/["']/g, "\\'");
       desc = $.trim(desc);

       var trs=$('.tbl_allergy>tbody>tr:visible');
       response += '<li onclick="selectCode(\'' + desc + '\',\'' + json[i].code + '\',\'' + trs.index($(e.target).parents('tr').eq(0)) + '\')"">' + json[i].code + '</li>';
     }
     response += "</ul>";

    $(e.target).parents('tr').eq(0).find(".suggesstion-box-dia").eq(0).show();
    $(e.target).parents('tr').eq(0).find(".suggesstion-box-dia").eq(0).html(response);
    $(e.target).parents('tr').eq(0).find(".form_diagnosis").css("background","#FFF");
  }
  });
});

function selectCode(val1,val2,cnt) {
  cnt-=1;
  $(".form_title").eq(cnt).val(val1);
  $(".form_diagnosis").eq(cnt).val(val2);
  $(".suggesstion-box").eq(cnt).hide();
  $(".suggesstion-box-dia").eq(cnt).hide();
}

// Close the auto complete div

$(document).mouseup(function (e)
{
    var container = $(".suggesstion-box-dia");

    if (!container.is(e.target) // if the target of the click isn't the container...
        && container.has(e.target).length === 0) // ... nor a descendant of the container
    {
        container.hide();
    }

    var container1 = $(".suggesstion-box");

    if (!container1.is(e.target) // if the target of the click isn't the container...
        && container1.has(e.target).length === 0) // ... nor a descendant of the container
    {
        container1.hide();
    }

});

 </script>
</body>
</html>
