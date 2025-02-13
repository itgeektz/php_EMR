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
<xsl:output method="xml" omit-xml-declaration="yes"/>
<xsl:include href="common_objects.xslt"/>
<xsl:strip-space elements="*"/>
<xsl:template match="/">
<xsl:apply-templates select="form"/>
</xsl:template>
<!-- The variable telling field_objects.xslt what form is calling it -->
<xsl:variable name="page">report</xsl:variable>
<!-- if fetchrow has contents, a variable with that name will be created by field_objects.xslt, and all fields created by it will retreive values from it. -->
<xsl:variable name="fetchrow">xyzzy</xsl:variable>
<xsl:template match="form">
<xsl:text disable-output-escaping="yes"><![CDATA[<?php
/*
 * this file's contents are included in both the encounter page as a 'quick summary' of a form, and in the medical records' reports page.
 */

/* for $GLOBALS[], ?? */
require_once(dirname(__FILE__).'/../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for generate_display_field() */
require_once($GLOBALS['srcdir'].'/options.inc.php');
/* The name of the function is significant and must match the folder name */
function ]]></xsl:text>
<xsl:value-of select="safename" />
<xsl:text disable-output-escaping="yes"><![CDATA[_report( $pid, $encounter, $cols, $id) {
    $count = 0;
]]></xsl:text>
<xsl:apply-templates select="table"/>
<xsl:text disable-output-escaping="yes"><![CDATA[
/* an array of all of the fields' names and their types. */
$field_names = array(]]></xsl:text>
<xsl:for-each select="//field">
<xsl:text disable-output-escaping="yes"><![CDATA[']]></xsl:text>
<xsl:value-of select="@name" />
<xsl:text disable-output-escaping="yes"><![CDATA[' => ']]></xsl:text>
<xsl:value-of select="@type" />
<xsl:text disable-output-escaping="yes"><![CDATA[']]></xsl:text>
<xsl:if test="position()!=last()">,</xsl:if>
</xsl:for-each>
<xsl:text disable-output-escaping="yes"><![CDATA[);]]></xsl:text>
<xsl:apply-templates select="layout|manual" mode="head"/>
<xsl:text disable-output-escaping="yes"><![CDATA[/* an array of the lists the fields may draw on. */
$lists = array(]]></xsl:text>
<xsl:for-each select="//field[@type='radio_group' or @type='checkbox_group' or @type='scrolling_list_multiples']">
<xsl:text disable-output-escaping="yes"><![CDATA[']]></xsl:text>
<xsl:value-of select="@name" />
<xsl:text disable-output-escaping="yes"><![CDATA[' => array(]]></xsl:text>
<xsl:for-each select="*">
<xsl:text disable-output-escaping="yes"><![CDATA[']]></xsl:text>
<xsl:value-of select="./text()" />
<xsl:text disable-output-escaping="yes"><![CDATA[' => ']]></xsl:text>
<xsl:value-of select="./text()" />
<xsl:text disable-output-escaping="yes"><![CDATA[']]></xsl:text>
<xsl:if test="following-sibling::*">,</xsl:if>
</xsl:for-each>
<xsl:text disable-output-escaping="yes"><![CDATA[)]]></xsl:text>
<xsl:if test="position()!=last()">,</xsl:if>
</xsl:for-each>
<xsl:if test="//table[@type='form']">
<xsl:text disable-output-escaping="yes"><![CDATA[);
    $data = formFetch($table_name, $id);
]]></xsl:text>
</xsl:if>
<xsl:if test="//table[@type='extended']">
<xsl:text disable-output-escaping="yes"><![CDATA[);
    $data = sqlQuery('select * from '.$table_name." where pid='".$pid."' and id='".$id."' order by date DESC limit 0,1");
]]></xsl:text>
</xsl:if>
<xsl:text disable-output-escaping="yes"><![CDATA[    if ($data) {

        echo '<table><tr>';

        foreach($data as $key => $value) {

            if ($key == 'id' || $key == 'pid' || $key == 'user' ||
                $key == 'groupname' || $key == 'authorized' ||
                $key == 'activity' || $key == 'date' || 
                $value == '' || $value == '0000-00-00 00:00:00' ||
                $value == 'n')
            {
                /* skip built-in fields and "blank data". */
	        continue;
            }

            /* display 'yes' instead of 'on'. */
            if ($value == 'on') {
                $value = 'yes';
            }

            /* remove the time-of-day from the 'date' fields. */
            if ($field_names[$key] == 'date')
            if ($value != '') {
              $dateparts = split(' ', $value);
              $value = $dateparts[0];
            }

	    echo "<td><span class='bold'>";
            
]]></xsl:text> 
<xsl:for-each select="//field">
<xsl:text disable-output-escaping="yes"><![CDATA[
            if ($key == ']]></xsl:text>
<xsl:value-of select="@name" /><xsl:text disable-output-escaping="yes"><![CDATA[' ) 
            { 
                echo xl_layout_label(']]></xsl:text><xsl:value-of select="@label"/><xsl:text disable-output-escaping="yes"><![CDATA[').":";
            }
]]></xsl:text>
</xsl:for-each>
<xsl:text disable-output-escaping="yes"><![CDATA[
                echo '</span><span class=text>'.generate_display_field( $manual_layouts[$key], $value ).'</span></td>';

            $count++;
            if ($count == $cols) {
                $count = 0;
                echo '</tr><tr>' . PHP_EOL;
            }
        }
    }
    echo '</tr></table><hr>';
}
?>
]]></xsl:text></xsl:template>
</xsl:stylesheet>
