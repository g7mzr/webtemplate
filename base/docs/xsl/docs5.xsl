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
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"   xmlns:d="http://docbook.org/ns/docbook">
   
    
    <!-- Nicer Filenames -->
    <xsl:param name="use.id.as.filename" select="1"/>

    <!-- Label sections if they aren't automatically labeled -->
    <xsl:param name="section.autolabel" select="1"/>
    <xsl:param name="section.label.includes.component.label" select="1"/>


    <!-- Rervision History -->
    <xsl:param name="generate.revhistory.link" select="0"/>


    <!-- Table of Contents Depth -->
    <xsl:param name="toc.section.depth">0</xsl:param>
    <xsl:param name="generate.section.toc.level" select="0"/>

    <!-- Show titles of next/previous page -->
    <xsl:param name="navig.showtitles">1</xsl:param>

    <!-- Tidy up the HTML a bit... -->
    <xsl:param name="html.cleanup" select="1"/>
    <xsl:param name="make.valid.html" select="1"/>
    <xsl:param name="html.stylesheet">style.css</xsl:param>
    <xsl:param name="highlight.source" select="1"/>

    <!-- Use Graphics, specify their Path and Extension -->
    <xsl:param name="admon.graphics" select="1"/>
    <xsl:param name="admon.graphics.path">../../images/</xsl:param>
    <xsl:param name="admon.graphics.extension">.gif</xsl:param>
    <xsl:param name="admon.textlabel" select="1"/>
    <xsl:param name="admon.style">margin-left: 1em; margin-right: 1em</xsl:param>

    <xsl:template match="d:olink[@role = 'glossterm']" mode="class.value">
          <xsl:value-of select="'glossterm'"/>
    </xsl:template>

    <xsl:template match="d:olink[@role = 'crossref']" mode="class.value">
           <xsl:value-of select="'xref'"/>
    </xsl:template>

    <xsl:template match="d:filename[@class='devicefile']" mode="class.value">
    	<xsl:value-of select="'devicefile'"  />
    </xsl:template>
    
    <xsl:template match="d:filename[@class='directory']" mode="class.value">
    	<xsl:value-of select="'directory'"  />
    </xsl:template>
    
</xsl:stylesheet>
