<?php
/**
 * Issue list functions and data structure building.
 *
 * The data structure is the $ISSUE_TYPES array.
 * The $ISSUE_TYPES array is built from the issue_types sql table and provides
 * abstraction of issue types to allow customization.
 * <pre>Attributes of the $ISSUE_TYPES array are:
 *  key - The identifier. (Do NOT create element with token 'prescription_erx' since this is reserved by NewCropRx Module that leverages lists_touch table to support MU calculations)
 *  0   - The plural title.
 *  1   - The singular title.
 *  2   - The abbreviated title (one letter abbreviation). 
 *  3   - Style ('0 - Normal; 1 - Simplified: only title, start date, comments and an Active checkbox;no diagnosis, occurrence, end date, referred-by or sports fields.; 2 - Football Injury; 3 and 4 are IPPF specific)
 *  4   - Force show this issue category in the patient summary screen even if empty (setting to 1 will force it to show and setting it to 0 will turn this off).
 *
 *
 * Note there is a mechanism to show whether a category is explicitly set to
 * 'Nothing' via the getListTouch() and setListTouch() functions that store
 * applicable information in the lists_touch sql table.
 *
 *  </pre>
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
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady@sparmy.com>
 * @author  Teny <teny@zhservices.com>
 * @link    http://www.open-emr.org
 */

$ISSUE_TYPE_CATEGORIES = array(
  'default' => xl('Default'),             // Normal OpenEMR use
  'athletic_team' => xl('Athletic Team'), // For Sports Team use
  'ippf_specific' => xl('IPPF')           // For IPPF use
);

$ISSUE_TYPE_STYLES = array(
  0 => xl('Standard'),                    // Standard
  1 => xl('Simplified'),                  // Simplified: only title, start date, comments and an Active checkbox;no diagnosis, occurrence, end date, referred-by or sports fields.
  2 => xl('Football Injury'),             // Football Injury
  3 => xl('IPPF Abortion'),               // IPPF specific (abortions issues)
  4 => xl('IPPF Contraception')           // IPPF specific (contraceptions issues)
);

/**
 * Will return the current issue type category that is being used.
 * @return  string  The current issue type category that is being used.
 */
function collect_issue_type_category() {
  if (!empty($GLOBALS['ippf_specific'])) { // IPPF version
    return "ippf_specific";
  }
  else if (!empty($GLOBALS['athletic_team'])) { // Athletic team version
    return "athletic_team";
  }
  else { // Default version
    return "default";
  }
}

// Build the $ISSUE_TYPES array (see script header for description)
$res = sqlStatement("SELECT * FROM `issue_types` WHERE active=1 AND `category`=? ORDER BY `ordering` ASC", array( collect_issue_type_category() ));
while($row = sqlFetchArray($res)){
  $ISSUE_TYPES[$row['type']] = array(xl($row['plural']),xl($row['singular']),xl($row['abbreviation']),$row['style'],$row['force_show']);
}

$ISSUE_CLASSIFICATIONS = array(
  0   => xl('Unknown or N/A'),
  1   => xl('Trauma'),
  2   => xl('Overuse')
);

function getListById ($id, $cols = "*")
{
	return sqlQuery("select $cols from lists where id='$id' order by date DESC limit 0,1");
}

function getListByType ($pid, $type, $cols = "*", $active = "all", $limit = "all", $offset="0")
{
	if($active == "all")
		$sql = "select $cols from lists where pid='$pid' and type='$type' order by date DESC";
	else
		$sql = "select $cols from lists where pid='$pid' and type='$type' and activity='$active' order by date DESC";
	if ($limit != "all")
		$sql .= " limit $offset,$limit";
	

	$res = sqlStatement($sql);
	for($iter =0;$row = sqlFetchArray($res);$iter++)
		$all[$iter] = $row;
	return $all;

}

function addList ($pid, $type, $title, $comments, $activity = "1")
{
	return sqlInsert("insert into lists (date, pid, type, title, activity, comments, user, groupname) values (NOW(), '$pid', '$type', '$title', '$activity', '$comments', '".$_SESSION['authUser']."', '".$_SESSION['authProvider']."')");
}

function disappearList ($id)
{
	sqlStatement("update lists set activity = '0' where id='$id'");
	return true;
}

function reappearList ($id)
{
	sqlStatement("update lists set activity = '1' where id='$id'");
	return true;
}

function getListTouch ($patient_id,$type)
{
	$ret = sqlQuery("SELECT `date` FROM `lists_touch` WHERE pid=? AND type=?", array($patient_id,$type) );

	if (!empty($ret)) {
		return $ret['date'];
	}
	else {
		return false;
	}
}

function setListTouch ($patient_id,$type)
{
	$ret = sqlQuery("SELECT `date` FROM `lists_touch` WHERE pid=? AND type=?", array($patient_id,$type) );

	if (!empty($ret)) {
                // Already touched, so can exit
		return;
	}
	else {
		sqlStatement("INSERT INTO `lists_touch` ( `pid`,`type`,`date` ) VALUES ( ?, ?, NOW() )", array($patient_id,$type) );
	}
}
?>
