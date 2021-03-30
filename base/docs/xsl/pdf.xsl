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
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:fo="http://www.w3.org/1999/XSL/Format"
                version="1.0">


    <!-- Some layout parameters -->
    <xsl:param name="paper.type">A4</xsl:param>
    <xsl:param name="fop1.extensions" select="1"/>
    <xsl:param name="section.autolabel" select="0"/>
    <xsl:param name="section.label.include.component.label" select="1"/>
    <xsl:param name="xref.with.number.and.title" select="0"/>
    <xsl:param name="generate.toc">book title,toc,figure</xsl:param>
    <xsl:param name="generate.index" select="1"/>
    <xsl:param name="doc.collab.show" select="1"/>
    <xsl:param name="latex.output.revhistory" select="0"/>
    <xsl:param name="doc.lot.show"></xsl:param>
    <xsl:param name="latex.encoding">utf8</xsl:param>
    <xsl:param name="imagedata.default.scale">pagebound</xsl:param>
    <xsl:param name="latex.hyperparam">colorlinks,linkcolor=blue,urlcolor=blue</xsl:param>
    <xsl:param name="shade.verbatim" select="1"/>
    <xsl:param name="glossary.numbered">0</xsl:param>
    <xsl:param name="chapter.autolabel">0</xsl:param>

    <!-- Set olink parameters-->
    <xsl:param name="target.database.document">olinkpdf.xml</xsl:param>
    <xsl:param name="current.docid" select="/*/@id"/>
    <xsl:param name="targets.filename">olinknochunk.db</xsl:param>
    <xsl:param name="olink.base.uri">webtemplate.pdf</xsl:param>

    <!-- Put the dblatex logo -->
    <xsl:param name="doc.publisher.show">1</xsl:param>

    <!-- Show <ulink>s as footnotes -->
    <xsl:param name="ulink.footnotes" select="1"/>
    <xsl:param name="ulink.show" select="1"/>

    <!-- Use Graphics, specify their Path and Extension -->
    <xsl:param name="admon.graphics" select="1"/>
    <!--<xsl:param name="admon.graphics.path">../images/</xsl:param>-->
    <xsl:param name="admon.graphics.extension">.svg</xsl:param>
    <xsl:param name="admon.textlabel" select="1"/>
    <xsl:param name="admon.style">margin-left: 1em; margin-right: 1em</xsl:param>
    <xsl:param name="figure.note">note</xsl:param>
    <xsl:param name="figure.tip">tip</xsl:param>
    <xsl:param name="figure.warning">warning</xsl:param>
    <xsl:param name="figure.caution">caution</xsl:param>
    <xsl:param name="figure.important">important</xsl:param>
   
    <!-- Make pdflatex shut up about <prompt> and <command> within <programlisting>, -->
    <!-- see http://dblatex.sourceforge.net/doc/manual/sec-verbatim.html             -->
    <xsl:template match="prompt|command" mode="latex.programlisting">
        <xsl:param name="co-tagin" select="'&lt;:'"/>
        <xsl:param name="rnode" select="/"/>
        <xsl:param name="probe" select="0"/>

        <xsl:call-template name="verbatim.boldseq">
            <xsl:with-param name="co-tagin" select="$co-tagin"/>
            <xsl:with-param name="rnode" select="$rnode"/>
            <xsl:with-param name="probe" select="$probe"/>
        </xsl:call-template>
    </xsl:template>
    
    <!-- Paragraph Numbering  Level 0 --> 
    <xsl:template match="para[parent::section or parent::chapter]">
  	<fo:block xsl:use-attribute-sets="normal.para.spacing">
    		<xsl:call-template name="anchor"/>
    		<xsl:number from="chapter" count="para[parent::section or parent::chapter]" level="any" format="1"/>
    		<xsl:text>. &#009;</xsl:text>
    		<xsl:apply-templates/>
  	</fo:block>
  </xsl:template> 

    <!-- Paragraph Numbering  Level 1
    <xsl:template match="para[parent::para]">
  	<fo:block xsl:use-attribute-sets="normal.para.spacing">
 		<xsl:attribute name="margin-left">2pc</xsl:attribute>
 		<xsl:call-template name="anchor"/>
 		<xsl:number from="section/para" count="para[parent::para]"  level="any" format="a"/>
     		<xsl:text>.    </xsl:text>
     		<xsl:apply-templates/>
  	</fo:block>
  </xsl:template>
<xsl:template match="chapter/para|sect1/para">
  	<fo:block xsl:use-attribute-sets="normal.para.spacing">
    		<xsl:call-template name="anchor"/>
    		<xsl:number from="chapter" count="para[parent::chapter or parent::sect1]" level="any" format="1"/>
    		<xsl:text>.    </xsl:text>
    		<xsl:apply-templates/>
  	</fo:block>

</xsl:template>
   -->
   <!-- Remove text body indenting -->
   <xsl:param name="body.start.indent">0pt</xsl:param>

    <!-- Set xref properties -->
    <xsl:attribute-set name="xref.properties">

        <!-- Set the glosstem blue and all other cross refs blue.  The glossterm
             clor can be changed by changing the @role color -->
        <xsl:attribute name="color">
            <xsl:choose>
                <xsl:when test="@role = 'glossterm'">blue</xsl:when>
                <xsl:otherwise>blue</xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>

        <!-- Set the glossterm font-style ti italic -->
        <xsl:attribute name="font-style">
            <xsl:choose>
                <xsl:when test="@role = 'glossterm'">italic</xsl:when>
                <xsl:otherwise>inherit</xsl:otherwise>
            </xsl:choose>
        </xsl:attribute>

    </xsl:attribute-set>

    <!-- Set the TOC Hyperlinks to blue -->
    <xsl:attribute-set name="toc.line.properties">
        <xsl:attribute name="color">blue</xsl:attribute>
    </xsl:attribute-set>

<xsl:attribute-set name="list.block.properties">
  <xsl:attribute name="margin-left">
    <xsl:choose>
      <xsl:when test="count(ancestor::listitem)">inherit</xsl:when>
      <xsl:otherwise>2pc</xsl:otherwise>
    </xsl:choose>
  </xsl:attribute>
</xsl:attribute-set>

<xsl:attribute-set name="procedure.block.properties">
  <xsl:attribute name="margin-left">
    <xsl:choose>
      <xsl:when test="count(ancestor::listitem)">inherit</xsl:when>
      <xsl:otherwise>2pc</xsl:otherwise>
    </xsl:choose>
  </xsl:attribute>
</xsl:attribute-set>

</xsl:stylesheet>
