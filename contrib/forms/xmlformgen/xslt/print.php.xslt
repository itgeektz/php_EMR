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
<xsl:include href="field_objects.xslt"/>
<xsl:strip-space elements="*"/>
<xsl:template match="/">
<xsl:apply-templates select="form"/>
</xsl:template>
<!-- The variable telling field_objects.xslt what form is calling it -->
<xsl:variable name="page">print</xsl:variable>
<!-- if fetchrow has contents, a variable with that name will be created by field_objects.xslt, and all fields created by it will retreive values from it. -->
<xsl:variable name="fetchrow">xyzzy</xsl:variable>
<xsl:template match="form">
<xsl:text disable-output-escaping="yes"><![CDATA[<?php
/*
 * The page shown when the user requests to print this form. This page automatically prints itsself, and closes its parent browser window.
 */

/* for $GLOBALS[], ?? */
require_once('../../globals.php');
/* for acl_check(), ?? */
require_once($GLOBALS['srcdir'].'/api.inc');
/* for generate_form_field, ?? */
require_once($GLOBALS['srcdir'].'/options.inc.php');

]]></xsl:text>
<!-- these templates generate PHP code -->
<xsl:apply-templates select="table|RealName|safename|acl|style"/>
<xsl:apply-templates select="table" mode="fetch"/>
<xsl:apply-templates select="layout|manual" mode="head"/>
<xsl:text disable-output-escaping="yes"><![CDATA[
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';

]]></xsl:text>
<xsl:apply-templates select="//field[@type='date']" mode="split_timeofday"/>
<xsl:call-template name="generate_chkdata"/>
<xsl:text disable-output-escaping="yes"><![CDATA[
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>

<!-- declare this document as being encoded in UTF-8 -->
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" ></meta>

<!-- supporting javascript code -->
<!-- for dialog -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/dialog.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['webroot']; ?>/library/textformat.js"></script>

<!-- Global Stylesheet -->
<link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css"/>
<!-- Form Specific Stylesheet. -->
<link rel="stylesheet" href="../../forms/<?php echo $form_folder; ?>/style.css" type="text/css"/>
<title><?php echo htmlspecialchars('Print '.$form_name); ?></title>

</head>
<body class="body_top">

<div class="print_date"><?php xl('Printed on ','e'); echo date('F d, Y', time()); ?></div>

<form method="post" id="<?php echo $form_folder; ?>" action="">
<div class="title"><?php xl($form_name,'e'); ?></div>

<!-- container for the main body of the form -->
<div id="print_form_container">
<fieldset>

]]></xsl:text>
<xsl:apply-templates select="H2|H3|H4|manual|layout"/>
<xsl:text disable-output-escaping="yes"><![CDATA[

</fieldset>
</div><!-- end print_form_container -->

</form>
<script type="text/javascript">
window.print();
window.close();
</script>
</body>
</html>
]]></xsl:text>
</xsl:template>
</xsl:stylesheet>

<!-- LocalWords:  Stylesheet
-->
