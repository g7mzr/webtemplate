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
    <!-- Include default docs XSL -->
    <xsl:include href="docs5.xsl"/>

    <!-- Set Chunk Specific XSL Params -->
    <xsl:param name="chunker.output.doctype-public">-//W3C//DTD HTML 4.01 Transitional//EN</xsl:param>
    <xsl:param name="chunker.output.doctype-system">http://www.w3.org/TR/html4/loose.dtd</xsl:param>
    <xsl:param name="chunk.section.depth" select="1"/>
    <xsl:param name="chunk.first.sections" select="1"/>
    <xsl:param name="chunker.output.encoding">UTF-8</xsl:param>
    <xsl:param name="chunker.output.indent">yes</xsl:param>
    <xsl:param name="chunk.quietly" select="1"/>
    <xsl:param name="navig.graphics" select="1"/>
    <xsl:param name="target.database.document">olinkchunk.xml</xsl:param>
    <xsl:param name="olink.doctitle">yes</xsl:param>
    <xsl:param name="current.docid" select="/*/@xml:id"/>
    <xsl:param name="targets.filename">olinkchunk.db</xsl:param>
    </xsl:stylesheet>
