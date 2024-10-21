<?xml version="1.0" encoding="ISO-8859-1"?>
<!-- Generated by Hand -->
<!--
Copyright (C) 2011 Julia Longtin <julia.longtin@gmail.com>

This program is free software; you can redistribute it and/or
Modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.
 -->
<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<!-- this file contains code written in php, used before the <html> tag -->
<!-- this code is generated for each date field in the form, to split the time of day from the date in question, for display purposes -->
<xsl:template match="field[@type='date']" mode="split_timeofday">
<xsl:text disable-output-escaping="yes"><![CDATA[if ($]]></xsl:text>
<xsl:value-of select="$fetchrow"/>
<xsl:text disable-output-escaping="yes"><![CDATA[[']]></xsl:text>
<xsl:value-of select="@name"/>
<xsl:text disable-output-escaping="yes"><![CDATA['] != '') {
    $dateparts = split(' ', $]]></xsl:text>
<xsl:value-of select="$fetchrow"/>
<xsl:text disable-output-escaping="yes"><![CDATA[[']]></xsl:text>
<xsl:value-of select="@name"/>
<xsl:text disable-output-escaping="yes"><![CDATA[']);
    $]]></xsl:text>
<xsl:value-of select="$fetchrow"/>
<xsl:text disable-output-escaping="yes"><![CDATA[[']]></xsl:text>
<xsl:value-of select="@name"/>
<xsl:text disable-output-escaping="yes"><![CDATA['] = $dateparts[0];
}
]]></xsl:text>
</xsl:template>
<!-- generate php code for fetching form contents, regardless of table format. -->
<xsl:template match="table[@type='form']" mode="fetch">
<xsl:text disable-output-escaping="yes"><![CDATA[/* Use the formFetch function from api.inc to load the saved record */
$]]></xsl:text>
<xsl:value-of select="$fetchrow"/>
<xsl:text disable-output-escaping="yes"><![CDATA[ = formFetch($table_name, $_GET['id']);

]]></xsl:text>
</xsl:template>
<xsl:template match="table[@type='extended']" mode="fetch">
<xsl:text disable-output-escaping="yes"><![CDATA[/* use the sqlQuery function from sql.inc to load the saved record. */
/* if we were passed an ID, pull it in specifically. otherwise, pull in the newest. */

/* use the pid from the session to find the newest version of this form. */
if ($_GET['id']=='')
{
 $sql = 'select * from '.$table_name." where pid='".$pid."'";
]]></xsl:text>
<xsl:choose>
<xsl:when test="//signature">
<xsl:text disable-output-escaping="yes"><![CDATA[ /* use sqlStatement to get multiple rows */
 $signature_fields = sqlStatement("select * from signatures where formname=']]></xsl:text>
<xsl:value-of select="/form/safename"/>
<xsl:text disable-output-escaping="yes"><![CDATA[' order by level ASC");

$current_level= '';

while ($signature_field = sqlFetchArray($signature_fields)) {
 $current_signature_field=$signature_field['fieldname'];
 $current_signature_comparison_operator=$signature_field['relationship'];
 $current_signature_comparison_value=$signature_field['constant'];
 $last_level=$current_level;
 $current_level=$signature_field['level'];
 $current_level_subordinate_relationship=$signature_field['subordinaterelationship'];
 $current_level_peer_relationship=$signature_field['peerrelationship'];
 if (strcmp($current_level, $last_level) != 0) {
  /* we changed levels */
  if (strcmp($last_level, '') == 0) {
   /* current is our first level */
   $sql .= ' AND ( (';
  }
  else
  {
   /* current is not our first level */
   $sql .= ')';
   if (strcmp($current_level_subordinate_relationship, 'AND') == 0) {
    $sql .= ' AND';
   }
   else {
    $sql .= ' OR';
   }
   $sql .= ' ( ';
  }
  $sql .= $current_signature_field.' ';
  if (strcmp($current_signature_comparison_operator,'not-equal') == 0) {
   $sql .= '<> ';
  }
  else
  {
   $sql .= '= ';
  }
  $sql .= '''.$current_signature_comparison_value.'' ';
 }
 else
 {
  /* same level */
  $sql .= $current_level_peer_relationship.' ';
  $sql .= $current_signature_field.' ';
  if (strcmp($current_signature_comparison_operator,'not-equal') == 0) {
   $sql .= '<> ';
  }
  else
  {
   $sql .= '= ';
  }
  $sql .= "'".$current_signature_comparison_value."' ";
 }
}
$sql .= ') ) order by effective_date DESC limit 0,1';

]]></xsl:text>
</xsl:when>
<xsl:otherwise>
<xsl:text disable-output-escaping="yes"><![CDATA[
$sql .= ' order by date DESC limit 0,1';
]]></xsl:text>
</xsl:otherwise>
</xsl:choose>
<xsl:text disable-output-escaping="yes"><![CDATA[$]]></xsl:text>
<xsl:value-of select="$fetchrow"/>
<xsl:text disable-output-escaping="yes"><![CDATA[ = sqlQuery($sql);
}
else
{
$]]></xsl:text>
<xsl:value-of select="$fetchrow"/>
<xsl:text disable-output-escaping="yes"><![CDATA[= sqlQuery('select * from '.$table_name." where pid='".$pid."' and id='".$_GET['id']."' order by date DESC limit 0,1");
}
]]></xsl:text>
</xsl:template>
<xsl:template match="table">
<xsl:if test="$fetchrow!=''">
<xsl:text disable-output-escaping="yes"><![CDATA[/** CHANGE THIS - name of the database table associated with this form **/
$table_name = ']]></xsl:text>
<xsl:value-of select="./text()" />
<xsl:text disable-output-escaping="yes"><![CDATA[';

]]></xsl:text>
</xsl:if>
</xsl:template>
<xsl:template match="acl">
<xsl:if test="@table='patients'">
<xsl:text disable-output-escaping="yes"><![CDATA[/* Check the access control lists to ensure permissions to this page */
if (!acl_check(']]></xsl:text>
<xsl:value-of select="@table" />
<xsl:text disable-output-escaping="yes"><![CDATA[', ']]></xsl:text>
<xsl:value-of select="./text()" />
<xsl:text disable-output-escaping="yes"><![CDATA[')) {
 die(text($form_name).': '.xlt("Access Denied"));
}
$thisauth_write_addonly=FALSE;
if ( acl_check(']]></xsl:text>
<xsl:value-of select="@table" />
<xsl:text disable-output-escaping="yes"><![CDATA[',']]></xsl:text>
<xsl:value-of select="./text()" />
<xsl:text disable-output-escaping="yes"><![CDATA[','',array('write','addonly') )) {
 $thisauth_write_addonly=TRUE;
}
]]></xsl:text>
<xsl:text disable-output-escaping="yes"><![CDATA[
/* perform a squad check for pages touching patients, if we're in 'athletic team' mode */
if ($GLOBALS['athletic_team']!='false') {
  $tmp = getPatientData($pid, 'squad');
  if ($tmp['squad'] && ! acl_check('squads', $tmp['squad']))
   die(text($form_name).': '.xlt("Access Denied"));
}
]]></xsl:text>
<xsl:if test="$page='new' or $page='view'">
<xsl:text disable-output-escaping="yes"><![CDATA[
if (!$thisauth_write_addonly)
  die(text($form_name).': '.xlt("Adding is not authorized"));
]]></xsl:text>
</xsl:if>
</xsl:if>
</xsl:template>
<!-- default layout object -->
<xsl:template match="layout">
<xsl:if test="$page='show'">
<xsl:text disable-output-escaping="yes"><![CDATA[<!-- display the form's layouts based fields -->
<table border='0' cellpadding='0' width='100%'>
<?php
display_layout_rows(']]></xsl:text>
<xsl:value-of select="@name"/>
<xsl:text disable-output-escaping="yes"><![CDATA[', $]]></xsl:text>
<xsl:value-of select="$fetchrow"/>
<xsl:text disable-output-escaping="yes"><![CDATA[);
?>
</table>
]]></xsl:text>
</xsl:if>
<xsl:if test="$page='new' or $page='view'">
<xsl:text disable-output-escaping="yes"><![CDATA[<!-- display the form's layouts based fields -->
<?php
while ($frow = sqlFetchArray($fres)) {
  $this_group = $frow['group_name'];
  $titlecols  = $frow['titlecols'];
  $datacols   = $frow['datacols'];
  $data_type  = $frow['data_type'];
  $field_id   = $frow['field_id'];
  $list_id    = $frow['list_id'];
  $currvalue  = isset($result[$field_id]) ? $result[$field_id] : '';

  // Handle a data category (group) change.
  if (strcmp($this_group, $last_group) != 0) {
    end_group();
    $group_seq  = substr($this_group, 0, 1);
    $group_name = substr($this_group, 1);
    $last_group = $this_group;
    echo "<div><span class='sectionlabel'><input type='checkbox' id='form_cb_$group_seq' value='1' " .
      "data-section=\"$field_id\"";
    if (strcmp($check_first_section, 'true')) echo " checked='checked'";

    // Modified 6-09 by BM - Translate if applicable
    echo ' />' . xl_layout_label($group_name) . '</span>' . PHP_EOL;

    echo "<div id='".$field_id."' class='section'>" . PHP_EOL;
    echo " <table border='0' cellpadding='0'>" . PHP_EOL;
    /* setting this to false here turns it off for the rest of the sections. */
    $check_first_section = 'false';
  }

  // Handle starting of a new row.
  if (($titlecols > 0 && $cell_count >= $cells_per_row) || $cell_count == 0) {
    end_row();
    echo '  <tr>';
  }

  if ($item_count == 0 && $titlecols == 0) $titlecols = 1;

  // Handle starting of a new label cell.
  if ($titlecols > 0) {
    end_cell();
    echo "<td colspan='$titlecols' valign='top'";
    echo ($frow['uor'] == 2) ? " class='required'" : " class='label'";
    if ($cell_count == 2) echo " style='padding-left:10pt'";
    echo '>';
    $cell_count += $titlecols;
  }

  ++$item_count;

  /* Modified 6-09 by BM - Translate if applicable */
  if ($frow['title']) echo (xl_layout_label($frow['title']));

  // Handle starting of a new data cell.
  if ($datacols > 0) {
    end_cell();
    echo "<td colspan='$datacols' class='text'";
    if ($cell_count > 0) echo " style='padding-left:5pt'";
    echo '>';
    $cell_count += $datacols;
  }

  ++$item_count;
  generate_form_field($frow, $currvalue);
}

end_group();

?>
]]></xsl:text>
</xsl:if>
</xsl:template>
<!-- default style object -->
<xsl:template match="style">
<xsl:if test="//layout">
<xsl:text disable-output-escaping="yes"><![CDATA[/* set some style related variables for the layouts code */
/* if this is true, check the check box on the first section, and none of the others. */
$check_first_sections='true';

/* how many cells to draw on each row. */
$cells_per_row=]]></xsl:text>
<xsl:value-of select="@cells_per_row" />
<xsl:text disable-output-escaping="yes"><![CDATA[;
]]></xsl:text>
</xsl:if>
</xsl:template>
<xsl:template match="manual" mode="head">
<xsl:if test="$page='show' or $page='view' or $page='new' or $page='report' or $page='print'">
<xsl:if test="//manual//field[@type='checkbox_list' or @type='checkbox_combo_list' or @type='exams' or @type='textbox' or @type='textarea' or @type='provider' or @type='date' or @type='textfield' or @type='dropdown_list']">
<xsl:text disable-output-escaping="yes"><![CDATA[/* in order to use the layout engine's draw functions, we need a fake table of layout data. */
$manual_layouts = array( 
]]></xsl:text>
<xsl:for-each select="//manual//field[@type='checkbox_list' or @type='checkbox_combo_list' or @type='exams' or @type='textbox' or @type='textarea' or @type='provider' or @type='date' or @type='textfield' or @type='dropdown_list']">
<xsl:text disable-output-escaping="yes"><![CDATA[ ']]></xsl:text>
<xsl:value-of select="@name" />
<xsl:text disable-output-escaping="yes"><![CDATA[' => 
   array( 'field_id' => ']]></xsl:text>
<xsl:value-of select="@name" />
<xsl:if test="@type='checkbox_list'">
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'data_type' => '21',
          'fld_length' => '0',
          'description' => ']]></xsl:text>
</xsl:if>
<xsl:if test="@type='checkbox_combo_list'">
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'data_type' => '25',
          'fld_length' => '140',
          'description' => ']]></xsl:text>
</xsl:if>
<xsl:if test="@type='exams'">
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'data_type' => '23',
          'fld_length' => '0',
          'description' => ']]></xsl:text>
</xsl:if>
<xsl:if test="@type='textbox'">
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'data_type' => '2',
          'fld_length' => '0',
          'description' => ']]></xsl:text>
</xsl:if>
<xsl:if test="@type='textarea'">
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'data_type' => '3',
          'fld_length' => ']]></xsl:text>
<xsl:choose>
<xsl:when test="@columns">
<xsl:value-of select="@columns"/>
</xsl:when>
<xsl:otherwise>
<xsl:text disable-output-escaping="yes"><![CDATA[40]]></xsl:text>
</xsl:otherwise>
</xsl:choose>
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'max_length' => ']]></xsl:text>
<xsl:choose>
<xsl:when test="@rows">
<xsl:value-of select="@rows"/>
</xsl:when>
<xsl:otherwise>
<xsl:text disable-output-escaping="yes"><![CDATA[4]]></xsl:text>
</xsl:otherwise>
</xsl:choose>
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'description' => ']]></xsl:text>
</xsl:if>
<xsl:if test="@type='provider'">
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'data_type' => '10',
          'fld_length' => '0',
          'description' => ']]></xsl:text>
</xsl:if>
<xsl:if test="@type='date' and @name!='effective_date'">
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'data_type' => '4',
          'fld_length' => '0',
          'description' => ']]></xsl:text>
</xsl:if>
<xsl:if test="@type='textfield'">
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'data_type' => '2',
          'fld_length' => ']]></xsl:text>
<xsl:choose>
<xsl:when test="@size">
<xsl:value-of select="@size"/>
</xsl:when>
<xsl:otherwise>
<xsl:text disable-output-escaping="yes"><![CDATA[10]]></xsl:text>
</xsl:otherwise>
</xsl:choose>
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'max_length' => ']]></xsl:text>
<xsl:value-of select="@maxlength" />
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'description' => ']]></xsl:text>
</xsl:if>
<xsl:if test="@type='dropdown_list'">
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'data_type' => '1',
          'fld_length' => '0',
          'description' => ']]></xsl:text>
</xsl:if>
<xsl:value-of select="@hoverover" />
<xsl:text disable-output-escaping="yes"><![CDATA[',
          'list_id' => ']]></xsl:text>
<xsl:variable name="i" select="@list"/>
<xsl:value-of select="//list[@name=$i]/@id" />
<xsl:text disable-output-escaping="yes"><![CDATA[' )]]></xsl:text>
<xsl:if test="following-sibling::field or ../following-sibling::section/field">
 <xsl:text disable-output-escaping="yes"><![CDATA[,]]></xsl:text>
</xsl:if>
<xsl:text disable-output-escaping="yes"><![CDATA[
]]></xsl:text>
</xsl:for-each>
<xsl:text disable-output-escaping="yes"><![CDATA[ );
]]></xsl:text>
</xsl:if>
</xsl:if>
</xsl:template>
<xsl:template match="layout" mode="head">
<xsl:if test="$page!='show'">
<xsl:text disable-output-escaping="yes"><![CDATA[/* Functions for generating the layout based form fields */
function end_cell() {
  global $item_count, $cell_count;
  if ($item_count > 0) {
    echo '</td>';
    $item_count = 0;
  }
}

function end_row() {
  global $cell_count, $cells_per_row;
  end_cell();
  if ($cell_count > 0) {
    for (; $cell_count < $cells_per_row; ++$cell_count) echo '<td></td>';
    echo '</tr>' . PHP_EOL;
    $cell_count = 0;
  }
}

function end_group() {
  global $last_group;
  if (strlen($last_group) > 0) {
    end_row();
    echo ' </table>' . PHP_EOL;
    echo '</div>' . PHP_EOL;
  }
}

/* global variables used by the previous functions */
$last_group = '';
$cell_count = 0;
$item_count = 0;

/* Retreive the layout engine based form information. */
$fres = sqlStatement('SELECT * FROM layout_options ' .
  "WHERE form_id = ']]></xsl:text>
<xsl:value-of select="@name" />
<xsl:text disable-output-escaping="yes"><![CDATA[' AND uor > 0 " .
  'ORDER BY group_name, seq');
]]></xsl:text>
</xsl:if>
</xsl:template>
<xsl:template match="safename">
<xsl:text disable-output-escaping="yes"><![CDATA[/** CHANGE THIS to match the folder you created for this form. **/
$form_folder = ']]></xsl:text>
<xsl:value-of select="./text()" />
<xsl:text disable-output-escaping="yes"><![CDATA[';

]]></xsl:text>
</xsl:template>
<xsl:template match="RealName">
<xsl:text disable-output-escaping="yes"><![CDATA[/** CHANGE THIS name to the name of your form. **/
$form_name = ']]></xsl:text>
<xsl:value-of select="./text()" />
<xsl:text disable-output-escaping="yes"><![CDATA[';

]]></xsl:text>
</xsl:template>
<xsl:template name="generate_chkdata">
<xsl:text disable-output-escaping="yes"><![CDATA[
/* define check field functions. used for translating from fields to html viewable strings */
]]></xsl:text>
<xsl:if test="//field[@type='date']">
<xsl:text disable-output-escaping="yes"><![CDATA[
function chkdata_Date(&$record, $var) {
        return htmlspecialchars($record{"$var"},ENT_QUOTES);
}
]]></xsl:text>
</xsl:if>
<xsl:if test="//field[@type='textarea' or @type='textfield']">
<xsl:text disable-output-escaping="yes"><![CDATA[
function chkdata_Txt(&$record, $var) {
        return htmlspecialchars($record{"$var"},ENT_QUOTES);
}
]]></xsl:text>
</xsl:if>
<xsl:if test="//checkbox">
<xsl:text disable-output-escaping="yes"><![CDATA[
function chkdata_CB(&$record, $nam, $var) {
	if (preg_match("/Negative.*$var/",$record{$nam})) {return;} else {return 'checked="checked"';}
}
]]></xsl:text>
</xsl:if>
<xsl:if test="//button">
<xsl:text disable-output-escaping="yes"><![CDATA[
function chkdata_Radio(&$record, $nam, $var) {
	if (strpos($record{$nam},$var) !== false) {return 'checked="checked"';}
}
]]></xsl:text>
</xsl:if>
<xsl:if test="//option">
<xsl:text disable-output-escaping="yes"><![CDATA[
 function chkdata_PopOrScroll(&$record, $nam, $var) {
	if (preg_match("/Negative.*$var/",$record{$nam})) {return;} else {return 'selected';}
}
]]></xsl:text>
</xsl:if>
</xsl:template>
</xsl:stylesheet>
