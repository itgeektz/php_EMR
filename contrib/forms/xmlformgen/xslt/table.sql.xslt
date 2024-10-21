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
<xsl:strip-space elements="*"/>
<xsl:template match="/">
<xsl:apply-templates select="form"/>
</xsl:template>
<xsl:template match="form">
<xsl:text disable-output-escaping="yes"><![CDATA[CREATE TABLE IF NOT EXISTS `]]></xsl:text>
<xsl:value-of select="/form/table"/>
<xsl:text disable-output-escaping="yes"><![CDATA[` (
    /* both extended and encounter forms need a last modified date */
    date datetime default NULL comment 'last modified date',]]></xsl:text>
<xsl:if test="table[@type='form']">
<xsl:text disable-output-escaping="yes"><![CDATA[
    /* these fields are common to all encounter forms. */
    id bigint(20) NOT NULL auto_increment,
    pid bigint(20) NOT NULL default 0,
    user varchar(255) default NULL,
    groupname varchar(255) default NULL,
    authorized tinyint(4) default NULL,
    activity tinyint(4) default NULL,
    ]]></xsl:text>
</xsl:if>
<xsl:if test="table[@type='extended']">
<xsl:text disable-output-escaping="yes"><![CDATA[
    /* these fields are common to all tables that are extensions of the patient_data table. */
    id bigint(20) NOT NULL auto_increment,
    pid bigint(20) NOT NULL default 0,
    ]]></xsl:text>
</xsl:if>
<xsl:if test="//signature">
<!-- if the form is signed, it must have a single effective date, for bracketing forms -->
<xsl:text disable-output-escaping="yes"><![CDATA[effective_date datetime default NULL,
    ]]></xsl:text>
</xsl:if>
<xsl:for-each select="//field">
<xsl:if test="@name!='effective_date'">
<xsl:value-of select="@name" />
</xsl:if>
<xsl:if test="@type='checkbox_list'">
<xsl:text disable-output-escaping="yes"><![CDATA[ varchar(255),
    ]]></xsl:text>
</xsl:if>
<xsl:if test="@type='checkbox_combo_list'">
<xsl:text disable-output-escaping="yes"><![CDATA[ varchar(255),
    ]]></xsl:text>
</xsl:if>
<xsl:if test="@type='exams'">
<xsl:text disable-output-escaping="yes"><![CDATA[ TEXT NOT NULL,
    ]]></xsl:text>
</xsl:if>
<xsl:if test="@type='textbox'">
<xsl:text disable-output-escaping="yes"><![CDATA[ longtext default NULL,
    ]]></xsl:text>
</xsl:if>
<xsl:if test="@type='textarea'">
<xsl:text disable-output-escaping="yes"><![CDATA[ TEXT,
    ]]></xsl:text>
</xsl:if>
<xsl:if test="@type='provider'">
<xsl:text disable-output-escaping="yes"><![CDATA[ int(11) default NULL,
    ]]></xsl:text>
</xsl:if>
<xsl:if test="@type='date' and @name!='effective_date'">
<xsl:text disable-output-escaping="yes"><![CDATA[ datetime default NULL,
    ]]></xsl:text>
</xsl:if>
<xsl:if test="@type='textfield'">
<xsl:text disable-output-escaping="yes"><![CDATA[ varchar(]]></xsl:text>
<xsl:value-of select="@maxlength"/>
<xsl:text disable-output-escaping="yes"><![CDATA[),
    ]]></xsl:text>
</xsl:if>
<xsl:if test="@type='dropdown_list'">
<xsl:text disable-output-escaping="yes"><![CDATA[ varchar(]]></xsl:text>
<xsl:value-of select="@maxlength"/>
<xsl:text disable-output-escaping="yes"><![CDATA[),
    ]]></xsl:text>
</xsl:if>
</xsl:for-each>
<xsl:text disable-output-escaping="yes"><![CDATA[PRIMARY KEY (id)
) ENGINE=InnoDB;
]]></xsl:text>
<xsl:if test="//list">
<xsl:for-each select="//list">
<xsl:if test="@import='yes'">
<xsl:text disable-output-escaping="yes"><![CDATA[INSERT IGNORE INTO list_options SET list_id='lists',
    option_id=']]></xsl:text>
<xsl:value-of select="@id"/>
<xsl:text disable-output-escaping="yes"><![CDATA[',
    title=']]></xsl:text>
<xsl:value-of select="@label"/>
<xsl:text disable-output-escaping="yes"><![CDATA[';
]]></xsl:text>
<xsl:for-each select="listitem">
<xsl:text disable-output-escaping="yes"><![CDATA[INSERT IGNORE INTO list_options SET list_id=']]></xsl:text>
<xsl:value-of select="../@id"/>
<xsl:text disable-output-escaping="yes"><![CDATA[',
    option_id=']]></xsl:text>
<xsl:value-of select="@id"/>
<xsl:text disable-output-escaping="yes"><![CDATA[',
    title=']]></xsl:text>
<xsl:value-of select="@label"/>
<xsl:if test="@order">
<xsl:text disable-output-escaping="yes"><![CDATA[',
    seq=']]></xsl:text>
<xsl:value-of select="@order"/>
</xsl:if>
<xsl:text disable-output-escaping="yes"><![CDATA[';
]]></xsl:text>
</xsl:for-each>
</xsl:if>
</xsl:for-each>
</xsl:if>
<xsl:if test="//signature">
<xsl:text disable-output-escaping="yes"><![CDATA[CREATE TABLE IF NOT EXISTS `signatures` (
    /* a unique ID for each entry */
    id bigint(20) NOT NULL auto_increment,
    /* the name of the directory containing the form being signed */
    formname varchar(255) NOT NULL,
    /* the field to be checked */
    fieldname varchar(64) NOT NULL,
    /* how to check the field */
    relationship varchar(20) NOT NULL,
    /* what to check it against */
    constant varchar(255) NOT NULL,
    /* evaluation level */
    level tinyint(4) default NULL,
    /* how to compare this item to others of its level */
    peerrelationship varchar(20) default NULL,
    /* how to compare this item to items of the previous level */
    subordinaterelationship varchar(20) default NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB;
]]></xsl:text>
<xsl:for-each select="//signature">
<!-- FIXME: how to add signatures only if they're not already there? -->
<xsl:text disable-output-escaping="yes"><![CDATA[INSERT INTO `signatures` set formname=']]></xsl:text>
<xsl:value-of select="//form/safename"/>
<xsl:text disable-output-escaping="yes"><![CDATA[',
    fieldname=']]></xsl:text>
<xsl:value-of select="@field"/>
<xsl:text disable-output-escaping="yes"><![CDATA[',
    relationship=']]></xsl:text>
<xsl:value-of select="@relationship"/>
<xsl:text disable-output-escaping="yes"><![CDATA[',
    constant=']]></xsl:text>
<xsl:value-of select="@constant"/>
<xsl:text disable-output-escaping="yes"><![CDATA[',
    level=']]></xsl:text>
<xsl:choose>
<xsl:when test="@level">
<xsl:value-of select="@level"/>
</xsl:when>
<xsl:otherwise>
<xsl:text disable-output-escaping="yes"><![CDATA[0]]></xsl:text>
</xsl:otherwise>
</xsl:choose>
<xsl:text disable-output-escaping="yes"><![CDATA[',
    peerrelationship=']]></xsl:text>
<xsl:choose>
<xsl:when test="@peerrelationship">
<xsl:value-of select="@peerrelationship"/>
</xsl:when>
<xsl:otherwise>
<xsl:text disable-output-escaping="yes"><![CDATA[AND]]></xsl:text>
</xsl:otherwise>
</xsl:choose>
<xsl:text disable-output-escaping="yes"><![CDATA[',
    subordinaterelationship=']]></xsl:text>
<xsl:choose>
<xsl:when test="@subordinaterelationship">
<xsl:value-of select="@subordinaterelationship"/>
</xsl:when>
<xsl:otherwise>
<xsl:text disable-output-escaping="yes"><![CDATA[AND]]></xsl:text>
</xsl:otherwise>
</xsl:choose>
<xsl:text disable-output-escaping="yes"><![CDATA[';]]></xsl:text>
</xsl:for-each>
</xsl:if>
</xsl:template>
</xsl:stylesheet>
