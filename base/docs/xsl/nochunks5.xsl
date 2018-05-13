<?xml version="1.0" encoding="UTF-8"?>
<!-- 
 * LICENSE: This source file is subject to version 2.1 of the GPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html. 
 *
 * @category  Webtemplate
 * @package   Docs
 * @author    Sandy McNeil <amcneil@iee.org>
 * @copyright 2012 Sandy McNeil
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @version   SVN: $Id$
 * @link      http://www.g7mzr.demon.co.uk
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html" encoding="UTF-8" indent="no"
                doctype-public="-//W3C//DTD HTML 4.01 Transitional//EN"
                doctype-system="http://www.w3.org/TR/html4/loose.dtd"/>
                
                
    <!-- Include default docs XSL -->    
    <xsl:include href="docs5.xsl"/>

    <!-- Set olink parameters-->
    <xsl:param name="target.database.document">olinknochunk.xml</xsl:param>
    <xsl:param name="current.docid" select="/*/@xml:id"/>
    <xsl:param name="targets.filename">olinknochunk.db</xsl:param>

</xsl:stylesheet>
