<?php
/**
 * Encounter list.
 *
 * Copyright (C) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
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
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @link    http://www.open-emr.org
 */

//SANITIZE ALL ESCAPES

$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once("../../globals.php");
require_once("$srcdir/forms.inc");
require_once("$srcdir/billing.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/invoice_summary.inc.php");
require_once("$srcdir/formatting.inc.php");
require_once("../../../custom/code_types.inc.php");
require_once("$srcdir/formdata.inc.php");
require_once("$srcdir/options.inc.php");

//Saved By Pharmacist
//---------------------------------------------------------------------------------
if($_POST['pharma_save']=='Save')
{

	$notifi_array=$_POST['notifi'];
	$pharma_status = [];
	for($i = 0 ;$i < count($notifi_array); $i++)
	{
		if($notifi_array[$i])
		{
			$pharma_status[$i] = 1;
	
		}	
	
	}	
	$updat_array=$_POST['up_id'];
	$cnts = count($_POST['up_id']);

	for ($j=0; $j < $cnts; $j++)
	{
		$notifiy   = add_escape_custom($notifi_array[$j]);
		$upd_id    = add_escape_custom($updat_array[$j]);
		$pharma_state = add_escape_custom($pharma_status[$j]);
		if($notifiy)
		{	
			sqlQuery("UPDATE  rmc_prescriptions set  notification ='".$notifiy."',pharma_status='".$pharma_state."' WHERE id = $upd_id");
		}
		
	}

}
   
   //---------------------------------------------------------------------------------
 
  	


// "issue" parameter exists if we are being invoked by clicking an issue title
// in the left_nav menu.  Currently that is just for athletic teams.  In this
// case we only display encounters that are linked to the specified issue.
$issue = empty($_GET['issue']) ? 0 : 0 + $_GET['issue'];


 //maximum number of encounter entries to display on this page:
 // $N = 12;

 //Get the default encounter from Globals
 $default_encounter = $GLOBALS['default_encounter_view']; //'0'=clinical, '1' = billing

 // Get relevant ACL info.
 $auth_notes_a  = acl_check('encounters', 'notes_a');
 $auth_notes    = acl_check('encounters', 'notes');
 $auth_coding_a = acl_check('encounters', 'coding_a');
 $auth_coding   = acl_check('encounters', 'coding');
 $auth_relaxed  = acl_check('encounters', 'relaxed');
 $auth_med      = acl_check('patients'  , 'med');
 $auth_demo     = acl_check('patients'  , 'demo');

 $tmp = getPatientData($pid, "squad");
 if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
  $auth_notes_a = $auth_notes = $auth_coding_a = $auth_coding = $auth_med = $auth_demo = $auth_relaxed = 0;

if (!($auth_notes_a || $auth_notes || $auth_coding_a || $auth_coding || $auth_med || $auth_relaxed)) {
  echo "<body>\n<html>\n";
  echo "<p>(".htmlspecialchars( xl('Encounters not authorized'), ENT_NOQUOTES).")</p>\n";
  echo "</body>\n</html>\n";
  exit();
}

// Perhaps the view choice should be saved as a session variable.
//
$tmp = sqlQuery("select authorized from users " .
  "where id = ?", array($_SESSION['authUserID']) );
$billing_view = ($tmp['authorized'] || $GLOBALS['athletic_team']) ? 0 : 1;
if (isset($_GET['billing']))
  {$billing_view = empty($_GET['billing']) ? 0 : 1;
}else $billing_view = ($default_encounter == 0) ? 0 : 1;

//Get Document List by Encounter ID
function getDocListByEncID($encounter,$raw_encounter_date,$pid){
	
	global $ISSUE_TYPES, $auth_med;
	$documents = getDocumentsByEncounter($pid,$encounter);
	
	 if ( count($documents) > 0 ) 
	 {
		foreach ( $documents as $documentrow) 
		{
			if ($auth_med) 
			{
				$irow = sqlQuery("SELECT type, title, begdate FROM lists WHERE id = ? LIMIT 1", array($documentrow['list_id']) );
				if ($irow) 
				{
          			$tcode = $irow['type'];
          			if ($ISSUE_TYPES[$tcode])
           				$tcode = $ISSUE_TYPES[$tcode][2];
         			echo text("$tcode: " . $irow['title']);
       			}
     		}
     		else {
      				echo "(" . xlt('No access') . ")";
    			 }

			 //Get the notes for this document and display as title for the link.
    		 $queryString = "SELECT date,note FROM notes WHERE foreign_id = ? ORDER BY date";
    		 $noteResultSet = sqlStatement($queryString,array($documentrow['id']));
    		 $note = '';
    		
    		while ( $row = sqlFetchArray($noteResultSet)) 
	   		{
      			$note .= oeFormatShortDate(date('Y-m-d', strtotime($row['date']))) . " : " . attr($row['note']) . "\n";
    		}
    		$docTitle = ( $note ) ? $note : xla("View document");
		    $docHref = $GLOBALS['webroot']."/controller.php?document&view&patient_id=".attr($pid)."&doc_id=".attr($documentrow['id']);
    		echo "<div class='text docrow' id='" . attr($documentrow['id'])."' title='". $docTitle . "'>\n";
    		echo "<a href='$docHref' onclick='top.restoreSession()' >". xlt('Document') . ": " . text(basename($documentrow['url'])) . ' (' . text(xl_document_category($documentrow['name'])) . ')(Approved by. ' .$documentrow['autho_fname'].' '.$documentrow['autho_lname'].')'."</a>";
    		echo "</div>";
 	   }
	}
	
	else{
		
		echo "No Documents uploaded !!!";
		
	}
}

// This is called to generate a line of output for a patient document.
//
function showDocument(&$drow) {
  global $ISSUE_TYPES, $auth_med;

  $docdate = $drow['docdate'];

  echo "<tr class='text docrow' id='".htmlspecialchars( $drow['id'], ENT_QUOTES)."' title='". htmlspecialchars( xl('View document'), ENT_QUOTES) . "'>\n";

  // show date
  echo "<td >" . htmlspecialchars( oeFormatShortDate($docdate), ENT_NOQUOTES) . "</td>\n";

  // show associated issue, if any
  echo "<td>";
  if ($auth_med) {
    $irow = sqlQuery("SELECT type, title, begdate " .
      "FROM lists WHERE " .
      "id = ? " .
      "LIMIT 1", array($drow['list_id']) );
    if ($irow) {
      $tcode = $irow['type'];
      if ($ISSUE_TYPES[$tcode]) $tcode = $ISSUE_TYPES[$tcode][2];
      echo htmlspecialchars("$tcode: " . $irow['title'], ENT_NOQUOTES);
    }
  } else {
    echo "(" . htmlspecialchars( xl('No access'), ENT_NOQUOTES) . ")";
  }
  echo "</td>\n";

  // show document name and category
  echo "<td colspan='3'>".
  htmlspecialchars( xl('Document') . ": " . basename($drow['url']) . ' (' . xl_document_category($drow['name']) . ')', ENT_NOQUOTES) .
  "</td>\n";

  // skip billing and insurance columns
  if (!$GLOBALS['athletic_team']) {
    echo "<td colspan=5>&nbsp;</td>\n";
  }

  echo "</tr>\n";
}

function generatePageElement($start,$pagesize,$billing,$issue,$text)
{
  if($start<0)
  {
    $start = 0;
  }
  $url="encounters.php?"."pagestart=".attr($start)."&"."pagesize=".attr($pagesize);
  $url.="&billing=".$billing;
  $url.="&issue=".$issue;

  echo "<A HREF='".$url."' onclick='top.restoreSession()'>".$text."</A>";
}

?>

<html>
<head>
  <?php html_header_show();?>
  <!-- Main style sheet comes after the page-specific stylesheet to facilitate overrides. -->
  <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/encounters.css" type="text/css">
  <link rel="stylesheet" href="<?php echo $GLOBALS['webroot'] ?>/library/css/bootstrap-3.3.7.min.css" type="text/css">
  <link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

  <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/jquery.min.1.12.4.js"></script>
  <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/bootstrap-3.3.7.min.js"></script>
  <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/js/ajtooltip.js"></script>
  <script language="JavaScript">

//function toencounter(enc, datestr) {
  function toencounter(rawdata) {
    var parts = rawdata.split("~");
    var enc = parts[0];
    var datestr = parts[1];

    top.restoreSession();
    <?php if ($GLOBALS['concurrent_layout']) { ?>
      parent.left_nav.setEncounter(datestr, enc, window.name);
      parent.left_nav.setRadio(window.name, 'enc');
    //parent.left_nav.loadFrame2('dem1', 'RTop', 'patient_file/summary/demographics.php?opener_save=true&set_encounter='+enc);
    parent.left_nav.loadFrame('enc2', window.name, 'patient_file/encounter/encounter_top.php?set_encounter=' + enc);
    <?php } else { ?>
      top.Title.location.href = '../encounter/encounter_title.php?set_encounter='   + enc;
      top.Main.location.href  = '../encounter/patient_encounter.php?set_encounter=' + enc;
      <?php } ?>
    }

    function todocument(docid) {
      h = '<?php echo $GLOBALS['webroot'] ?>/controller.php?document&view&patient_id=<?php echo $pid ?>&doc_id=' + docid;
      top.restoreSession();
      <?php if ($GLOBALS['concurrent_layout']) { ?>
        parent.left_nav.setRadio(window.name, 'doc');
        location.href = h;
        <?php } else { ?>
          top.Main.location.href = h;
          <?php } ?>
        }

 // Helper function to set the contents of a div.
 function setDivContent(id, content) {
  $("#"+id).html(content);
}

 // Called when clicking on a billing note.
 function editNote(feid) {
  top.restoreSession();
  var c = "<iframe src='edit_billnote.php?feid=" + feid +
  "' style='width:100%;height:88pt;'></iframe>";
  setDivContent('note_' + feid, c);
}

 // Called when the billing note editor closes.
 function closeNote(feid, fenote) {
  var c = "<div id='"+ feid +"' title='<?php echo htmlspecialchars( xl('Click to edit'), ENT_QUOTES); ?>' class='text billing_note_text'>" +
  fenote + "</div>";
  setDivContent('note_' + feid, c);
}

function changePageSize()
{
  billing=$(this).attr("billing");
  pagestart=$(this).attr("pagestart");
  issue=$(this).attr("issue");
  pagesize=$(this).val();
  top.restoreSession();
  window.location.href="encounters.php?billing="+billing+"&issue="+issue+"&pagestart="+pagestart+"&pagesize="+pagesize;
}
window.onload=function()
{
  $("#selPagesize").change(changePageSize);
}

// Mouseover handler for encounter form names. Brings up a custom tooltip
// to display the form's contents.
function efmouseover(elem, ptid, encid, formname, formid) {
  ttMouseOut();
   var tttUrl="encounters_ajax.php?ptid=" + ptid + "&encid=" + encid +"&formname=" + formname + "&formid=" + formid;
   var divempty=$("#"+formname+"_"+encid).children().length;
   if(divempty<=0)
   {
    $.get(tttUrl, function(data) {
      $("#"+formname+"_"+encid).html(data);
      $("#"+formname+"_"+encid).slideToggle('slow', function() {
      if ($(this).is(':visible'))
          $("#"+formname+"_expand_"+encid).html('(collapse)');
        else
          $("#"+formname+"_expand_"+encid).html('(expand)');
      });
    });
  }
  else
  {
      
      $("#"+formname+"_"+encid).slideToggle('slow', function() {
      if ($(this).is(':visible'))
          $("#"+formname+"_expand_"+encid).html('(collapse)');
        else
          $("#"+formname+"_expand_"+encid).html('(expand)');
      });

  }


 //ttMouseOver(elem, "encounters_ajax.php?ptid=" + ptid + "&encid=" + encid +"&formname=" + formname + "&formid=" + formid);
}

// Mouseover handler for encounter form names. Brings up a custom tooltip
// to display the form's contents.
function efmouseover1(elem, ptid, encid, formname, formid) {
 ttMouseOver(elem, "encounters_ajax.php?ptid=" + ptid + "&encid=" + encid +
  "&formname=" + formname + "&formid=" + formid);
}


function showonhover(elem,formname)
{

  ttMouseOverNew(elem,formname);

}


function showhide(obj,formname)
{
   ttMouseOut();
  $("#"+obj).slideToggle('slow', function() {
      if ($(this).is(':visible'))
          $("#"+formname).html('(collapse)');
        else
          $("#"+formname).html('(expand)');
      });
        
   
}


</script>

<style>

#encounters th {
     text-align: center !important;
     color:#fff !important;
     font-size: 12px !important;
     background-color: #167F92 !important;
}

.borderless td, .borderless th {
    border: none;
}
.borderless tr:nth-child(2n+1) {
    background-color: #eaf3f3;
}

.title_divs{

 	background-color: lightblue;
    box-shadow: 0 8px 6px -3px black;
    color: #fff;
    height: 40px;
    margin-top: 12px !important;

}

.titlet{
	color: red !important;
    font-size: 15px !important;
    font-weight: bold !important;
    padding-top: 8px !important;
 

}
</style>


</head>

<body class="body_bottom" style="padding: 15px; background-color: #eaf3f3;">
  <div id="encounters"> <!-- large outer DIV -->

    <?php if ($GLOBALS['concurrent_layout']) { ?>
    <!-- <a href='encounters_full.php'> -->
    <?php } else { ?>
    <!-- <a href='encounters_full.php' target='Main'> -->
    <?php } ?>
    <font class='title'>
      <?php
      if ($issue) {
        echo htmlspecialchars(xl('Past Encounters for'), ENT_NOQUOTES) . ' ';
        $tmp = sqlQuery("SELECT title FROM lists WHERE id = ?", array($issue));
        echo htmlspecialchars($tmp['title'], ENT_NOQUOTES);
      }
      else {
        echo htmlspecialchars(xl('Past Encounters and Documents'), ENT_NOQUOTES);
      }
      ?>
    </font>
    &nbsp;&nbsp;
    <?php
// Setup the GET string to append when switching between billing and clinical views.


    $pagestart=0;
    if(isset($_GET['pagesize']))
    {
      $pagesize=$_GET['pagesize'];
    }
    else
    {
      if(array_key_exists('encounter_page_size',$GLOBALS))
      {
        $pagesize=$GLOBALS['encounter_page_size'];
      }
      else
      {
        $pagesize=0;
      }
    }
    if(isset($_GET['pagestart']))
    {
      $pagestart=$_GET['pagestart'];
    }
    else
    {
      $pagestart=0;
    }
   $pagesize=5;
    $getStringForPage="&pagesize=".attr($pagesize)."&pagestart=".attr($pagestart);
//  if($pagesize==0 and $pagestart == 0)
    ?>
    <?php if ($billing_view) { ?>
    <a href='encounters.php?billing=0&issue=<?php echo $issue.$getStringForPage; ?>' onclick='top.restoreSession()' style='font-size:8pt'>(<?php echo htmlspecialchars( xl('To Clinical View'), ENT_NOQUOTES); ?>)</a>
    <?php } else { ?>
    <a href='encounters.php?billing=1&issue=<?php echo $issue.$getStringForPage; ?>' onclick='top.restoreSession()' style='font-size:8pt'>(<?php echo htmlspecialchars( xl('To Billing View'), ENT_NOQUOTES); ?>)</a>
    <?php } ?>

    <span style="float:right;font-size:14px">
      <?php echo htmlspecialchars( xl('Results per page'), ENT_NOQUOTES); ?>: 5
      <select style='display:none' id="selPagesize" billing="<?php echo htmlspecialchars($billing_view,ENT_QUOTES); ?>" issue="<?php echo htmlspecialchars($issue,ENT_QUOTES); ?>" pagestart="<?php echo htmlspecialchars($pagestart,ENT_QUOTES); ?>" >
        <?php
        $pagesizes=array(5,10,15,20,25,50,0);
        for($idx=0;$idx<count($pagesizes);$idx++)
        {
          echo "<OPTION value='" . $pagesizes[$idx] . "'";
          if($pagesize==$pagesizes[$idx])
          {
            echo " SELECTED='true'>";
          }
          else
          {
            echo ">";
          }
          if($pagesizes[$idx]==0)
          {
            echo htmlspecialchars( xl('ALL'), ENT_NOQUOTES);
          }
          else
          {
            echo $pagesizes[$idx];
          }
          echo "</OPTION>";

        }
        ?>
      </select>
    </span>

    <br>

    <table class="table borderless">
    
    <thead>
     <tr class='text' style="height: 50px;background-color: #167F92 !important;">
      <th ><?php echo htmlspecialchars( xl('Date'), ENT_NOQUOTES); ?></th>

      <?php if ($billing_view) { ?>
      <th class='billing_note'><?php echo htmlspecialchars( xl('Billing Note'), ENT_NOQUOTES); ?></th>
      <?php } else { ?>
      <?php if (!$issue) { ?>
      <th style="width: 10%;"><?php echo htmlspecialchars( xl('Issue'), ENT_NOQUOTES);       ?></th>
      <?php } ?>
      <th><?php echo htmlspecialchars( xl('Reason/Form'), ENT_NOQUOTES); ?></th>
      <th colspan="8"><?php echo htmlspecialchars( xl('Provider'), ENT_NOQUOTES);    ?></th>
      <?php } ?>

      <?php if ($billing_view) { ?>
      <th><?php echo xl('Code','e'); ?></th>
      <th class='right'><?php echo htmlspecialchars( xl('Chg'), ENT_NOQUOTES); ?></th>
      <th class='right'><?php echo htmlspecialchars( xl('Paid'), ENT_NOQUOTES); ?></th>
      <th class='right'><?php echo htmlspecialchars( xl('Adj'), ENT_NOQUOTES); ?></th>
      <th class='right'><?php echo htmlspecialchars( xl('Bal'), ENT_NOQUOTES); ?></th>
      <?php } else { ?>
      <!--th colspan='5'><?php echo htmlspecialchars( (($GLOBALS['phone_country_code'] == '1') ? xl('Billing') : xl('Coding')), ENT_NOQUOTES); ?></th-->
      <?php } ?>

      <?php if (!$GLOBALS['athletic_team'] && !$GLOBALS['ippf_specific']) { ?>
      <!--th>&nbsp;<?php echo htmlspecialchars( (($GLOBALS['weight_loss_clinic']) ? xl('Payment') : xl('Insurance')), ENT_NOQUOTES); ?></th-->
      <?php } ?>

    </tr>

	</thead>
    <?php
    $drow = false;
    if (!$billing_view) {
  // Query the documents for this patient.  If this list is issue-specific
  // then also limit the query to documents that are linked to the issue.
      $queryarr = array($pid);
      $query = "SELECT d.id, d.type, d.url, d.docdate, d.list_id, c.name " .
      "FROM documents AS d, categories_to_documents AS cd, categories AS c WHERE " .
      "d.foreign_id = ? AND cd.document_id = d.id AND c.id = cd.category_id ";
      if ($issue) {
        $query .= "AND d.list_id = ? ";
        $queryarr[] = $issue;
      }
      $query .= "ORDER BY d.docdate DESC, d.id DESC";
      $dres = sqlStatement($query, $queryarr);
      $drow = sqlFetchArray($dres);
    }

// $count = 0;

    $sqlBindArray = array();

    $from = "FROM form_encounter AS fe " .
    "JOIN forms AS f ON f.pid = fe.pid AND f.encounter = fe.encounter AND " .
    "f.formdir = 'newpatient' AND f.deleted = 0 ";
    if ($issue) {
      $from .= "JOIN issue_encounter AS ie ON ie.pid = ? AND " .
      "ie.list_id = ? AND ie.encounter = fe.encounter ";
      array_push($sqlBindArray, $pid, $issue);
    }
    $from .= "LEFT JOIN users AS u ON u.id = fe.provider_id WHERE fe.pid = ? ";
    $sqlBindArray[] = $pid;

    $query = "SELECT fe.*, f.user, u.fname, u.mname, u.lname " . $from .
    "ORDER BY fe.date DESC, fe.id DESC";
    $countQuery = "SELECT COUNT(*) as c " . $from;
    

    $countRes = sqlStatement($countQuery,$sqlBindArray);
    $count = sqlFetchArray($countRes);
    $numRes = $count['c'];

    if($pagesize>0)
    {
      $query .= " LIMIT " . escape_limit($pagestart) . "," . escape_limit($pagesize);
    }
    else
      $query.=" LIMIT 0, 5";

    $upper  = $pagestart+$pagesize;
    if(($upper>$numRes) || ($pagesize==0))
    {
      $upper=$numRes;
    }


    if(($pagesize > 0) && ($pagestart>0))
    {
      generatePageElement($pagestart-$pagesize,$pagesize,$billing_view,$issue,"&lArr;" . htmlspecialchars( xl("Prev"), ENT_NOQUOTES) . " ");
    }
    echo "<span style='font-size:14px'>".($pagestart + 1)."-".$upper." " . htmlspecialchars( xl('of'), ENT_NOQUOTES) . " " .$numRes;
    if(($pagesize>0) && ($pagestart+$pagesize <= $numRes))
    {
      generatePageElement($pagestart+$pagesize,$pagesize,$billing_view,$issue," " . htmlspecialchars( xl("Next"), ENT_NOQUOTES) . "&rArr;");
    }
   echo "</span>";

    $res4 = sqlStatement($query, $sqlBindArray);


    if($numRes<=0)
    {

      echo "<script> alert('Sorry, No info for this patient');</script>";

    }

    while ($result4 = sqlFetchArray($res4)) {

	  	// $href = "javascript:window.toencounter(" . $result4['encounter'] . ")";
      $reason_string = "";
      $auth_sensitivity = true;

      $raw_encounter_date = '';

      $raw_encounter_date = date("Y-m-d", strtotime($result4{"date"}));
      $encounter_date = date("D F jS", strtotime($result4{"date"}));

        // if ($auth_notes_a || ($auth_notes && $result4['user'] == $_SESSION['authUser']))
      $reason_string .= htmlspecialchars( $result4{"reason"}, ENT_NOQUOTES) . "<br>\n";
        // else
        //   $reason_string = "(No access)";

      if ($result4['sensitivity']) {
        $auth_sensitivity = acl_check('sensitivities', $result4['sensitivity']);
        if (!$auth_sensitivity) {
          $reason_string = "(".htmlspecialchars( xl("No access"), ENT_NOQUOTES).")";
        }
      }

        // This generates document lines as appropriate for the date order.
        //this bellow while loop commented for hide listing of document from history page
      

        // Fetch all forms for this encounter, if the user is authorized to see
        // this encounter's notes and this is the clinical view.
      $encarr = array();
      $encounter_rows = 1;
      if (!$billing_view && $auth_sensitivity &&
        ($auth_notes_a || ($auth_notes && $result4['user'] == $_SESSION['authUser'])))
      {
      	
      	//it list the forms on the Forms table by based on the encounter
        $encarr = getFormByEncounter($pid, $result4['encounter'], "formdir, user, form_name, form_id, deleted");
        $encounter_rows = count($encarr);
      }

      $rawdata = $result4['encounter'] . "~" . oeFormatShortDate($raw_encounter_date);
      echo "<tr class='text' id='" . htmlspecialchars($rawdata, ENT_QUOTES) .
      "'>\n";

        // show encounter date
      echo "<td  style=\"width: 5%;\" class='encrow' valign='top' title='" . htmlspecialchars(xl('View encounter','','',' ') .
        "$pid.{$result4['encounter']}", ENT_QUOTES) . "'>" .
     //bellow line commented for hide encounter number form date in history date columns changed by me
     // htmlspecialchars(oeFormatShortDate($raw_encounter_date). "  (" . $result4['encounter'] . ")", ENT_NOQUOTES) . "</td>\n";
       htmlspecialchars(oeFormatShortDate($raw_encounter_date). "", ENT_NOQUOTES) . "</td>\n";
      if ($billing_view) {

            // Show billing note that you can click on to edit.
            $feid = $result4['id'] ? htmlspecialchars( $result4['id'], ENT_QUOTES) : 0; // form_encounter id
            echo "<td valign='top'>";
            echo "<div id='note_$feid'>";
            //echo "<div onclick='editNote($feid)' title='Click to edit' class='text billing_note_text'>";
            echo "<div id='$feid'  title='". htmlspecialchars( xl('Click to edit'), ENT_QUOTES) . "' class='text billing_note_text'>";
            echo $result4['billing_note'] ? nl2br(htmlspecialchars( $result4['billing_note'], ENT_NOQUOTES)) : htmlspecialchars( xl('Add','','[',']'), ENT_NOQUOTES);
            echo "</div>";
            echo "</div>";
            echo "</td>\n";

        //  *************** end billing view *********************
          }
          else {

          if (!$issue) { // only if listing for multiple issues
            // show issues for this encounter
            echo "<td>";
            if ($auth_med && $auth_sensitivity) {
              $ires = sqlStatement("SELECT lists.type, lists.title, lists.begdate " .
                "FROM issue_encounter, lists WHERE " .
                "issue_encounter.pid = ? AND " .
                "issue_encounter.encounter = ? AND " .
                "lists.id = issue_encounter.list_id " .
                "ORDER BY lists.type, lists.begdate", array($pid,$result4['encounter']) );
              for ($i = 0; $irow = sqlFetchArray($ires); ++$i) {
                if ($i > 0) echo "<br>";
                $tcode = $irow['type'];
                if ($ISSUE_TYPES[$tcode]) $tcode = $ISSUE_TYPES[$tcode][2];
                echo htmlspecialchars( "$tcode: " . $irow['title'], ENT_NOQUOTES);
              }
            }
            else {
              echo "(" . htmlspecialchars( xl('No access'), ENT_NOQUOTES) . ")";
            }
            echo "</td>\n";
          } // end if (!$issue)

          ?>
          <style type="text/css">
            .titlet
            {
              color: #000;
              display: inline-block;
              font-size: 14px;
              margin: 0 0 1px;
              padding: 1px 0px;
            }
            .boxsize 
            {
              background: #fff none repeat scroll 0 0;
              border: 1px solid #ccc;
              margin: 0 8px 4px 0px ;
              padding: 10px;
            }

            .hoverin
            {
                color: red


            }


          </style>

          <?php
            // show encounter reason/title
            
              echo "<td class='part-time' style='width: 60%;'>".
              "<div class=\"title_divs\" onclick=\"showhide('Patient_Encounter_".$result4["encounter"]."','Patient_Encounter_expand_".$result4["encounter"]."');\" onmouseout='ttMouseOut();' onmouseover='showonhover(this,\"Patient_Encounter_".$result4["encounter"]."\")' style='padding-left:10px;'>".
              " <span class='titlet'>Patient Encounter</span><span id='Patient_Encounter_expand_".$result4["encounter"]."' class='expanhover'>(collapse)</span>";
              echo "</div><div d-id='" . htmlspecialchars($rawdata, ENT_QUOTES)."' id='Patient_Encounter_".$result4["encounter"]."' class='boxsize encrow' style='display:block;margin-left: 9px;'>";
              if(trim($reason_string)!='<br>')
              {  
              echo $reason_string;
              }
              else
              {

                echo "none";
              }
                
              echo "</div>";             
            
 

          //Provisional Diagnosis start
          $encounter=$result4["encounter"];
          $pid=$result4["pid"];
          $qry="SELECT count(pid) as count FROM `provisional_dia`  WHERE pid = ".$pid." AND encounter = ".$encounter;
          $Provisional = sqlQuery($qry);
          if($Provisional['count']>0)
          {

            
            $query='';
            $query = "SELECT title,diagnosis FROM `provisional_dia` WHERE `encounter`=? && `pid`=?";
            $res = sqlStatement($query, array($encounter, $pid) );

            echo "<div class=\"title_divs\" onclick=\"showhide('Provisional_Diagnosis_".$result4["encounter"]."','Provisional_Diagnosis_expand_".$result4["encounter"]."')\"  onmouseout='ttMouseOut();' onmouseover='showonhover(this,\"Provisional_Diagnosis_".$result4["encounter"]."\")' style='padding-left:10px;'>".
            "<span class='titlet'>Provisional Diagnosis</span><span class='expanhover' id='Provisional_Diagnosis_expand_".$result4["encounter"]."'>(expand)</span>";

            echo "</div><div id='Provisional_Diagnosis_".$result4["encounter"]."' class='boxsize' style='display:none;margin-left: 9px;'>";
            echo "<table><tr>".
            "<th>Description</th><th>Code</th>";
            while( $row = sqlFetchArray($res) ) {
              echo "<tr><td>".$row['title']."</td><td>".$row['diagnosis']."</td></tr>";

            }
            echo "</table></div>";
          }
          //Provisional Diagnosis

          
          ?>

          <!--Investigation start-->
          <style>
            .red{
              color: red;
            }

            .green{
              color: green;
            }
          </style>
          <?php
          
          $tabarray = ['rmc_lab','rmc_imaging','rmc_intervention','rmc_physical'];
          $count = 0;
          foreach ($tabarray as $tab)
          {	
          	$qry="SELECT count(`id`) as count FROM ".$tab." WHERE `encounter`=".$encounter." AND `pid`=".$pid;
          	$Investigation = sqlQuery($qry);
          	 $count = $count + $Investigation['count'];
          	
          }
                                  
          if($count > 0)
          {
          	
          
            echo "".
            "<div class=\"title_divs\" onclick=\"showhide('Investigation_".$result4["encounter"]."','Investigation_expand_".$result4["encounter"]."')\" onmouseout='ttMouseOut();' onmouseover='showonhover(this,\"Investigation_".$result4["encounter"]."\")'  style='padding-left:10px;'>".
            "<span class='titlet'>Investigation</span><span class='expanhover' id='Investigation_expand_".$result4["encounter"]."'>(expand)</span>";
            echo "</div><div id='Investigation_".$result4["encounter"]."' class='boxsize' style='display:none;margin-left: 9px;'>";
            echo "<div id='labdata'>";

            ?>
            
            	<table class="table borderless" style="padding-bottom: 15px;">
				<thead>
					<tr style=height:30px;">
						<th style="width: 224px;">Type</th>
						<th >Test Name</th>
						<th >Status</th>
						<th style="width: 170px;text-align:left !important;">ServicedBy</th>
						<th >Comments</th>
					</tr>
			  </thead>
            <?php 
            $lst_array = array('Laboratory','Intervention','Physical','Imaging');
            $lst_cnt = count($lst_array);
            
            for ($i=0; $i < $lst_cnt; $i++) {
              if ($lst_array[$i] == 'Laboratory') {
                $tbl_name = "rmc_lab";
              }elseif ($lst_array[$i] == 'Intervention') {
                $tbl_name = "rmc_intervention";
              }elseif ($lst_array[$i] == "Physical") {
                $tbl_name = "rmc_physical";
              }elseif ($lst_array[$i] == 'Imaging') {
                $tbl_name = "rmc_imaging";
              }else {
                $tbl_name = "";
              }
              echo "<table class=\"$tbl_name table borderless\" style=\"margin-left: 10px;\" >";
              $res = sqlStatement("SELECT service_name,comments,bill_status,service_status, serviced_by FROM $tbl_name WHERE pid = ? AND encounter = ? ", array($pid, $encounter));
              $tmp = 0;
              while ($row = sqlFetchArray($res)) {

                $tmp = $tmp + 1;

                $service_name = $row{"service_name"};
                $comments = $row{"comments"};
                $bill_status = $row{"bill_status"};
                $service_status = $row{"service_status"};
                $serviced_by = $row{"serviced_by"};

                if($bill_status == '' || $bill_status == "0"){
                  $bill_status = "Unbilled";
                  $color = "red";
                }else{
                  $bill_status = "Billed";
                  $color = "green";
                }
                if($service_name != "") {
			   	
			  ?> 	
			   	
			  <tr style="height: 40px;">
 		            <td style="width: 180px; !important;text-align: right; padding-right: 30px;"><label class=bold ><?php echo $lst_array[$i]. " " .$tmp; ?>  </label></td>
 		            <td class="text " style="width: 400px;text-align:right;"><?php echo  $service_name; ?>  <label class='bold  " <?php echo $color; ?>  "'>(<?php echo $bill_status; ?>)</label>
 		            <td class="text s_status" style="width: 175px;text-align:end;"><?php echo  $service_status;?></td>
 					<td class="text"  style="width: 350px;text-align:center;" ><?php echo $serviced_by;?></td>	   		
 					<td class=text style="width: 500px;"><?php echo "<span style=\"padding-left: 30px;\">". $comments."</span>"  ;?> </td>	  
 							
 				</tr>
 				
 				<?php 	               	 		 
			  	   
			                	
                }
              }
              print "</table>";
            }

          
          echo "</div></div>"; 
          }

          //Investigation end

          //Prescriptions start

          $qry="SELECT count(pid) as count FROM rmc_prescriptions  WHERE pid = ".$pid." AND encounter = ".$encounter;
          $Prescriptions = sqlQuery($qry);

          if($Prescriptions['count']>0)
          {

          echo "".
          "<div class=\"title_divs\" onclick=\"showhide('Prescriptions_".$result4["encounter"]."','Prescriptions_expand_".$result4["encounter"]."')\" onmouseout='ttMouseOut();' onmouseover='showonhover(this,\"Prescriptions_".$result4["encounter"]."\")' style='padding-left:10px;'>".
          "<span class='titlet'>Prescriptions</span><span class='expanhover' id='Prescriptions_expand_".$result4["encounter"]."'>(expand)</span>";
          echo "</div><div id='Prescriptions_".$result4["encounter"]."' class='boxsize' style='display:none;margin-left: 9px;'>";


          $prec=sqlStatement("SELECT p.*, l.title FROM rmc_prescriptions p left join list_options l on p.drug_interval=l.option_id WHERE p.pid = ? AND p.encounter = ? and l.list_id='drug_interval'", array($pid, $encounter));
          ?>
          	<form method='post' id="rmc_presc_pharma" name='pharma' action='encounters.php' >	
			  	<table class="table borderless">
			  			<thead>
			    		<tr  style="height: 40px;" >
			    			<th ><?php echo xlt('Drugs'); ?></th>
					        <th ><?php echo xlt('No.of Tabs/Drops/Application'); ?></th>
					        <th ><?php echo xlt('FREQ'); ?></th>
					        <th ><?php echo xlt('Duration'); ?></th>
					        <th ><?php echo xlt('Quantity'); ?></th>
					        <th ><?php echo xlt('Any instruction to Pharmacist'); ?></th>
					        <th ><?php echo xlt('Add to Medication List'); ?></th>
					        <th ><?php echo xlt('Bill Type'); ?></th>
					        <th ><?php echo xlt('Notifications'); ?></th>
					    </tr>
					   </thead> 
			      <?php
				      $jj=0;
				      $status_phram = [];
				      
			      		while ($row1 = sqlFetchArray($prec))
			      		{
			        		$jj++;
			        		
			        	    $status_phram[] = $row1['pharma_status']; 
			    	?>
			      <tr style="height: 40px;">
			        <!--  <td><?php echo $jj;?></td> -->
			        <td class=text><?php echo $row1['drug'] ?></td>
			        <td class=text style='text-align:center;'><?php echo $row1['dosage'] ?></td>
			        <td style='text-align:center;'><?php echo $row1['title'];?></td>
			        <td style='text-align:center;'> <?php echo $row1['duration']." ".$row1['duration1'];?></td>
			        <td class="text" style='text-align:center;'><?php echo $row1['quantity'] ?></td>
			        <td class=text style='text-align:center;'><?php if(!empty($row1['notes'])) echo $row1['notes']; else echo "-"; ?></td>
			        <?php if($row1['bill_type']==0) {?>
			        <td class="text billtype" style='text-align:center;' >Cash</td>
			        <?php }
			         else {?>
			         <td class="text billtype" style='text-align:center;' >Credit</td>
			        <?php }?>
			        <?php if($row1['bill_status']==0) {?>
			        <td class="text billstatus0" style='text-align:center;color:red;' > Unbilled </td>
			        <?php }
			        else {?> <td class="text billstatus1" style='text-align:center;' > Billed </td>
			        <?php }?>
			             <?php  
			             
			            
			        	$limit_access_group = ["Pharmacist","Administrators"];
						if(in_array($_SESSION['usergroup'], $limit_access_group))
					    {	
					    	if($_SESSION['usergroup'] == 'Pharmacist' )
					    	{
					    		if($row1['pharma_status'] != 1 )
					    		{	
					    	?>
						     		<td style="width: 13%" class="notificat"><textarea name="notifi[]" id='notification_<?php echo $row1['id']?>' class="notification" style="width: 100%;"><?php echo $row1['notification'];?></textarea>
						     		<input type="hidden" name="up_id[]" id="up_id" class="up_id rem" value="<?php echo $row1['id'];?>">        			
				        		<?php }else{?>
				        		
				        			     <td class="text" style='text-align:center;width: 250px;'><?php echo $row1['notification']; ?></td>
				        		<?php 
						    	}
					    	}	
					    	if($_SESSION['usergroup'] == 'Administrators')
					    	{
					    		?>
					    	 		<td style="width: 13%"><textarea name="notifi[]" id='notification_".$rowid."' class="notification" style="width: 100%;"><?php echo $row1['notification'];?></textarea></td>	
					    			<input type="hidden" name="up_id[]" id="up_id" class="up_id rem" value="<?php echo $row1['id'];?>">		    				        			
					    	<?php 
					    	}
					    				    					    	
						}else{?>
						
			       				<td class="text" style='text-align:center;width: 250px;'><?php echo $row1['notification']; ?></td>
			       		 
			       		<?php }?> 
			       
			        	
			       </tr>
			         <?php }?>
			   <tr >
			 	 
			    <td colspan="9">
			       <?php 
			  
			  if(in_array($_SESSION['usergroup'], $limit_access_group))
			  {	 
			  
			  	if($_SESSION['usergroup'] == 'Pharmacist')
			  	{
			  		
			  		
			  		if(in_array(0,$status_phram))
			  		{	
						  	?>
							   <div class="btn_div" style="padding: 6px 29px 10px 10px; float: right; height: 50px;">
				    			
				    				<input type='submit' name='pharma_save' id="pharma_save" value='<?php echo xla('Save'); ?>' />
				    			   		
				   			  </div> 
						  <?php
			  		}
			  	}	
						 	 
			   if($_SESSION['usergroup'] == 'Administrators')
			 	{
			 	
			 		?>
			 	<div class="btn_div" style="padding: 6px 29px 10px 10px; float: right; height: 50px;">
	    			
	    				<input type='submit' name='pharma_save' id="pharma_save" value='<?php echo xla('Save'); ?>' />
	    			   		
	   			  </div> 
			 <?php }
			 
			  }
			 
			
			  ?>
			  </td>
			  </tr>
			  </table>
			</form>
			 </div> 	
          </div>
          
      
      <?php
      }
      //Prescriptions end

	  

            // Now show a line for each encounter form, if the user is authorized to
            // see this encounter's notes.

              foreach ($encarr as $enc)
              {
                  if ($enc['formdir'] == 'newpatient') continue;

                // skip forms whose 'deleted' flag is set to 1 --JRM--
                if ($enc['deleted'] == 1) continue;

                // Skip forms that we are not authorized to see. --JRM--
                // pardon the wonky logic
                $formdir = $enc['formdir'];
                if (($auth_notes_a) ||
                  ($auth_notes && $enc['user'] == $_SESSION['authUser']) ||
                  ($auth_relaxed && ($formdir == 'sports_fitness' || $formdir == 'podiatry'))) ;
                  else continue;

                // Show the form name.  In addition, for the specific-issue case show
                // the data collected by the form (this used to be a huge tooltip
                // but we did away with that).
                //
                $formdir = $enc['formdir'];
                if ($issue) 
                {
                	  	echo htmlspecialchars(xl_form_title($enc['form_name']), ENT_NOQUOTES);
                  		echo "<br>";
                  		echo "<div class='encreport' style='padding-left:10px;'>";
	                  // Use the form's report.php for display.  Forms with names starting with LBF
	                  // are list-based forms sharing a single collection of code.
                  		if (substr($formdir,0,3) == 'LBF') 
                  		{
                             include_once($GLOBALS['incdir'] . "/forms/LBF/report.php");
		                     call_user_func("lbf_report", $pid, $result4['encounter'], 2, $enc['form_id'], $formdir);
                  		}
                  		else  
                  		{
                    		include_once($GLOBALS['incdir'] . "/forms/$formdir/report.php");
                    		call_user_func($formdir . "_report", $pid, $result4['encounter'], 2, $enc['form_id']);
                  		}
                  		echo "</div>";
                }
                else
                {
                  		echo "<div class = \"$formdir title_divs\" "."onclick='efmouseover(this,$pid," . $result4['encounter'].",\"$formdir\"," . $enc['form_id'] . ")' onmouseover='efmouseover1(this,$pid," . $result4['encounter'] .",\"$formdir\"," . $enc['form_id'] . ")' " ."onmouseout='ttMouseOut()' style=\"padding-left: 10px;\">";
                  		echo "<span class='titlet'>".htmlspecialchars(xl_form_title($enc['form_name']), ENT_NOQUOTES)."</span><span class='expanhover' id='".$formdir."_expand_".$result4["encounter"]."'>(expand)</span></div>";
                  		echo "<div id='".$formdir."_".$result4["encounter"]."' class='boxsize' style='display:none;'> </div>";
                     }
                
        
              } // end encounter Forms loop

               //Final Diagnosis Start

            $qry="SELECT count(pid) as count FROM `final_dia`  WHERE pid = ".$pid." AND encounter = ".$encounter;
          $Final_Diagnosiss = sqlQuery($qry);
          if($Final_Diagnosiss['count']>0)
          {
            $query = "SELECT  `title`, `diagnosis` FROM `final_dia` WHERE `encounter`=? && `pid`=?";
            $res = sqlStatement($query, array($encounter, $pid) );
            
            echo "<div class=\"title_divs\" onclick=\"showhide('Final_Diagnosiss_".$result4["encounter"]."','Final_Diagnosiss_expand_".$result4["encounter"]."') \" onmouseout='ttMouseOut();' onmouseover='showonhover(this,\"Final_Diagnosiss_".$result4["encounter"]."\")'>".
            "<span class='titlet'>Final Diagnosis</span><span class='expanhover' id='Final_Diagnosiss_expand_".$result4["encounter"]."'>(expand)</span>";

            echo "</div><div id='Final_Diagnosiss_".$result4["encounter"]."' class='boxsize' style='display:none;margin-left: 9px;'>";
            echo "<table><tr>".
            "<th>Description</th><th>Code</th>";
            while( $row = sqlFetchArray($res) ) {
              echo "<tr><td>".$row['title']."</td><td>".$row['diagnosis']."</td></tr>";

            }
            echo "</table></div>";             
            
          }
            //Final Diagnosis Stop    
            
          
          //Display the documents tagged to this encounter
          // this bellow 1 line commnented for hide document list from history
          
          echo "".
          "<div class=\"title_divs\" onclick=\"showhide('Documents_".$result4["encounter"]."','Documents_expand_".$result4["encounter"]."')\" onmouseout='ttMouseOut();' onmouseover='showonhover(this,\"Documents_".$result4["encounter"]."\")' style='padding-left:10px;'>".
          "<span class='titlet'>Documents</span><span class='expanhover' id='Documents_expand_".$result4["encounter"]."'>(expand)</span>";
          echo "</div><div id='Documents_".$result4["encounter"]."' class='boxsize' style='display:none;margin-left: 9px;'>";
          
          $test = getDocListByEncID($result4['encounter'],$raw_encounter_date,$pid);
        
          echo $test;
          echo "<div>";
          
          
          
          
          
          
          
            echo "</div>";
            echo "</td>\n";




            // show user (Provider) for the encounter
            $provname = '&nbsp;';
            if (!empty($result4['lname']) || !empty($result4['fname'])) {
              $provname = htmlspecialchars( $result4['lname'], ENT_NOQUOTES);
              if (!empty($result4['fname']) || !empty($result4['mname']))
                $provname .= htmlspecialchars( ', ' . $result4['fname'] . ' ' . $result4['mname'], ENT_NOQUOTES);
            }
            echo "<td>$provname</td>\n";

        } // end not billing view
        while ($drow && $raw_encounter_date && $drow['docdate'] > $raw_encounter_date) {
        //showDocument($drow);
        $drow = sqlFetchArray($dres);
      }
        //this is where we print out the text of the billing that occurred on this encounter
        $thisauth = $auth_coding_a;
        if (!$thisauth && $auth_coding) {
          if ($result4['user'] == $_SESSION['authUser'])
            $thisauth = $auth_coding;
        }
        $coded = "";
        $arid = 0;
        if ($thisauth && $auth_sensitivity) {
          $binfo = array('', '', '', '', '');
          if ($subresult2 = getBillingByEncounter($pid, $result4['encounter'], "code_type, code, modifier, code_text, fee"))
          {
                // Get A/R info, if available, for this encounter.
            $arinvoice = array();
            $arlinkbeg = "";
            $arlinkend = "";
            if ($billing_view) {
              $tmp = sqlQuery("SELECT id FROM form_encounter WHERE " .
                "pid = ? AND encounter = ?", array($pid,$result4['encounter']) );
              $arid = 0 + $tmp['id'];
              if ($arid) $arinvoice = ar_get_invoice_summary($pid, $result4['encounter'], true);
              if ($arid) {
                $arlinkbeg = "<a href='../../billing/sl_eob_invoice.php?id=" .
                htmlspecialchars( $arid, ENT_QUOTES)."'" .
                " target='_blank' class='text' style='color:#00cc00'>";
                $arlinkend = "</a>";
              }
            }

                // Throw in product sales.
            $query = "SELECT s.drug_id, s.fee, d.name " .
            "FROM drug_sales AS s " .
            "LEFT JOIN drugs AS d ON d.drug_id = s.drug_id " .
            "WHERE s.pid = ? AND s.encounter = ? " .
            "ORDER BY s.sale_id";
            $sres = sqlStatement($query, array($pid,$result4['encounter']) );
            while ($srow = sqlFetchArray($sres)) {
              $subresult2[] = array('code_type' => 'PROD',
                'code' => 'PROD:' . $srow['drug_id'], 'modifier' => '',
                'code_text' => $srow['name'], 'fee' => $srow['fee']);
            }

                // This creates 5 columns of billing information:
                // billing code, charges, payments, adjustments, balance.
            foreach ($subresult2 as $iter2) {
                    // Next 2 lines were to skip diagnoses, but that seems unpopular.
                    // if ($iter2['code_type'] != 'COPAY' &&
                    //   !$code_types[$iter2['code_type']]['fee']) continue;
              $title = htmlspecialchars(($iter2['code_text']), ENT_QUOTES);
              $codekey = $iter2['code'];
              $codekeydisp = $iter2['code_type']." - ".$iter2['code'];
              if ($iter2['code_type'] == 'COPAY') {
                $codekey = 'CO-PAY';
                $codekeydisp = xl('CO-PAY');
              }
              $codekeydisp = htmlspecialchars($codekeydisp, ENT_NOQUOTES);
              if ($iter2['modifier']) $codekey .= ':' . $iter2['modifier'];
              if ($binfo[0]) $binfo[0] .= '<br>';
              if ($issue && !$billing_view) {
                      // Single issue clinical view: show code description after the code.
                $binfo[0] .= "$arlinkbeg$codekeydisp $title$arlinkend";
              }
              else {
                      // Otherwise offer the description as a tooltip.
                $binfo[0] .= "<span title='$title'>$arlinkbeg$codekeydisp$arlinkend</span>";
              }
              if ($billing_view) {
                if ($binfo[1]) {
                  for ($i = 1; $i < 5; ++$i) $binfo[$i] .= '<br>';
                }
              if (empty($arinvoice[$codekey])) {
                            // If no invoice, show the fee.
                if ($arlinkbeg) $binfo[1] .= '&nbsp;';
                else $binfo[1] .= htmlspecialchars( oeFormatMoney($iter2['fee']), ENT_NOQUOTES);

                for ($i = 2; $i < 5; ++$i) $binfo[$i] .= '&nbsp;';
              }
            else {
              $binfo[1] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['chg'] + $arinvoice[$codekey]['adj']), ENT_NOQUOTES);
              $binfo[2] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['chg'] - $arinvoice[$codekey]['bal']), ENT_NOQUOTES);
              $binfo[3] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['adj']), ENT_NOQUOTES);
              $binfo[4] .= htmlspecialchars( oeFormatMoney($arinvoice[$codekey]['bal']), ENT_NOQUOTES);
              unset($arinvoice[$codekey]);
            }
          }
                } // end foreach

                // Pick up any remaining unmatched invoice items from the accounting
                // system.  Display them in red, as they should be unusual.
                if (!empty($arinvoice)) {
                  foreach ($arinvoice as $codekey => $val) {
                    if ($binfo[0]) {
                      for ($i = 0; $i < 5; ++$i) $binfo[$i] .= '<br>';
                    }
                  for ($i = 0; $i < 5; ++$i) $binfo[$i] .= "<font color='red'>";
                    $binfo[0] .= htmlspecialchars( $codekey, ENT_NOQUOTES);
                  $binfo[1] .= htmlspecialchars( oeFormatMoney($val['chg'] + $val['adj']), ENT_NOQUOTES);
                  $binfo[2] .= htmlspecialchars( oeFormatMoney($val['chg'] - $val['bal']), ENT_NOQUOTES);
                  $binfo[3] .= htmlspecialchars( oeFormatMoney($val['adj']), ENT_NOQUOTES);
                  $binfo[4] .= htmlspecialchars( oeFormatMoney($val['bal']), ENT_NOQUOTES);
                  for ($i = 0; $i < 5; ++$i) $binfo[$i] .= "</font>";
                }
            }
            } // end if there is billing

            echo "<td class='text'>".$binfo[0]."</td>\n";
            for ($i = 1; $i < 5; ++$i) {
              echo "<td class='text right'>". $binfo[$i]."</td>\n";
            }
        } // end if authorized

        else {
          echo "<td class='text' valign='top' colspan='5' rowspan='$encounter_rows'>(".htmlspecialchars( xl("No access"), ENT_NOQUOTES).")</td>\n";
        }

        // show insurance
  /*      if (!$GLOBALS['athletic_team'] && !$GLOBALS['ippf_specific']) {
            $insured = oeFormatShortDate($raw_encounter_date);
            if ($auth_demo) {
                $responsible = -1;
                if ($arid) {
                        $responsible = ar_responsible_party($pid, $result4['encounter']);
                }
                $subresult5 = getInsuranceDataByDate($pid, $raw_encounter_date, "primary");
                if ($subresult5 && $subresult5{"provider_name"}) {
                    $style = $responsible == 1 ? " style='color:red'" : "";
                    $insured = "<span class='text'$style>&nbsp;" . htmlspecialchars( xl('Primary'), ENT_NOQUOTES) . ": " .
                    htmlspecialchars( $subresult5{"provider_name"}, ENT_NOQUOTES) . "</span><br>\n";
                }
                $subresult6 = getInsuranceDataByDate($pid, $raw_encounter_date, "secondary");
                if ($subresult6 && $subresult6{"provider_name"}) {
                    $style = $responsible == 2 ? " style='color:red'" : "";
                    $insured .= "<span class='text'$style>&nbsp;" . htmlspecialchars( xl('Secondary'), ENT_NOQUOTES) . ": " .
                    htmlspecialchars( $subresult6{"provider_name"}, ENT_NOQUOTES) . "</span><br>\n";
                }
                $subresult7 = getInsuranceDataByDate($pid, $raw_encounter_date, "tertiary");
                if ($subresult6 && $subresult7{"provider_name"}) {
                    $style = $responsible == 3 ? " style='color:red'" : "";
                    $insured .= "<span class='text'$style>&nbsp;" . htmlspecialchars( xl('Tertiary'), ENT_NOQUOTES) . ": " .
                    htmlspecialchars( $subresult7{"provider_name"}, ENT_NOQUOTES) . "</span><br>\n";
                }
                if ($responsible == 0) {
                    $insured .= "<span class='text' style='color:red'>&nbsp;" . htmlspecialchars( xl('Patient'), ENT_NOQUOTES) .
                                "</span><br>\n";
                }
            }
            else {
                $insured = " (".htmlspecialchars( xl("No access"), ENT_NOQUOTES).")";
            }

            echo "<td>".$insured."</td>\n";
          } */

          echo "</tr>\n";

} // end while


// Dump remaining document lines if count not exceeded.
//this bellow  loop commented for hide the document list by me
// while ($drow /* && $count <= $N */) {
//   showDocument($drow);
//   $drow = sqlFetchArray($dres);
// }
?>

</table>

</div> <!-- end 'encounters' large outer DIV -->

<div id='tooltipdiv' style='position:absolute;width:auto;border:1px solid black;padding:2px;background-color:#ffffaa;visibility:hidden;z-index:1000;font-size:9pt;'></div>



</body>
<style>
  .aln{
    color: blue;
  }

</style>
<script language="javascript">
// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
  $(".encrow").mouseover(function() { $(this).toggleClass("aln"); });
  $(".encrow").mouseout(function() { $(this).toggleClass("aln"); });
  $(".encrow").click(function() {
    var did=$(this).attr('d-id');
    
   toencounter(did); });

  $(".docrow").mouseover(function() { $(this).toggleClass("highlight"); });
  $(".docrow").mouseout(function() { $(this).toggleClass("highlight"); });
  $(".docrow").click(function() { todocument(this.id); });

  $(".billing_note_text").mouseover(function() { $(this).toggleClass("billing_note_text_highlight"); });
  $(".billing_note_text").mouseout(function() { $(this).toggleClass("billing_note_text_highlight"); });
  $(".billing_note_text").click(function(evt) { evt.stopPropagation(); editNote(this.id); });


  $(".expanhover").mouseover(function() { $(this).toggleClass("hoverin"); });
  $(".expanhover").mouseout(function() { $(this).toggleClass("hoverin"); });

  
});


$('div .summary').remove();

var table_names =['rmc_lab','rmc_imaging','rmc_intervention','rmc_physical'];

for(var i = 0 ;i < table_names.length;i++)
{
 	$('.'+table_names[i]+' tr').each(function(){

	var value = $(this).find('.s_status').text();

	if($.trim(value).length){
		
		if($.trim(value) == 'Done')
		{
			$(this).find('.s_status').css('color','green');
			
		}
		else{
			
			$(this).find('.s_status').css('color','red');
			
			}
	}

	 
	}); 
}



</script>

</html>
