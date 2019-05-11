<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\application;

use webtemplate\application\exceptions\AppException;

/**
 * This module is used to create the webtemplate error class
 *
 * @category Webtemplate
 * @package  Application
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
**/

class CreateDocs
{

    /**
     * Property: docBase
     *
     * @var    string
     * @access protected
     */
    protected $DocBase = '';

    /**
     * Property: entfile
     *
     * @var    string
     * @access protected
     */
    protected $entfile = '';

    /*
     * Property: development
     *
     * @var    array
     * @access protected
     */
    protected $development =array();

    /*
     * Property: languages
     *
     * @var    array
     * @access protected
     */
    protected $languages = array();

    /*
     * Property: xlstproc
     *
     * @var    string
     * @access protected
     */
    protected $xsltproc = '';

    /*
     * Property: fop
     *
     * @var    string
     * @access protected
     */
    protected $fop = '';

    /*
     * Property: db4stylesheets
     *
     * @var    string
     * @access protected
     */
    protected $db4stylesheets;

    /*
     * Property: db5stylesheets
     *
     * @var    string
     * @access protected
     */
    protected $db5stylesheets;

    /**
     * Property: verbose
     *
     * @var boolean
     * @access protected
     */
    protected $verbose;

    /**
     * Constructor
     *
     * @param string  $docbase The base directory for all documentation
     * @param string  $entfile Docbook Entities file containing program information
     * @param boolean $verbose Suppress all command output if false;
     *
     * @access public
     */
    public function __construct($docbase, $entfile, $verbose=false)
    {
        // Load the base documentaion Directory
        if (file_exists($docbase)) {
            $this->DocBase = $docbase;
        } else {
            throw new AppException('Documentation directory does not exist');
        }

        if (file_exists($this->DocBase . '/' . $entfile)) {
            $this->entfile = $entfile;
        } else {
            throw new AppException("Entity File '". $entfile . "' does not exist");
        }

        $this->verbose = $verbose;

        // Look for xsltproc
        $xsltprocpath = $this->checkToolExists('xsltproc');
        if ($xsltprocpath  == '') {
            throw new AppException('Unable to find xsltproc');
        } else {
            $this->xsltproc = $xsltprocpath ;
        }

        // Look for dblatex
        $dblatexpath = $this->checkToolExists('fop');
        if ($dblatexpath  == '') {
            throw new AppException('Unable to find fop');
        } else {
            $this->fop = $dblatexpath ;
        }

        // Initalise development array
        $this->development['rest'] = array();
        $this->development['rest']['xml'] = 'rest_xml';
        $this->development['rest']['filename'] = "restapi.xml";
        $this->development['rest']['html'] = 'rest_html';
        $this->development['rest']['pdf'] = 'rest_pdf';
        $this->development['rest']['dbversion'] = 5;
        $this->development['develop'] = array();
        $this->development['develop']['xml'] = 'develop_xml';
        $this->development['develop']['filename'] = "developer.xml";
        $this->development['develop']['html'] = 'develop_html';
        $this->development['develop']['pdf'] = 'develop_pdf';
        $this->development['develop']['dbversion'] = 4;
    } // end constructor

    /******************************************************************************
     * PROTECTED FUNCTIONS
     ******************************************************************************/
    /**
     * This function strips the exist extension from the filename and replaces it
     * with the one specified in $new_extension
     *
     * @param string  $filename      The original filename
     * @param string  $new_extension The new extension
     * @param boolean $includepath   Include the path in the returned filename
     *
     * @return string the new filename and extension
     *
     * @access protected.
     */
    protected function replaceExtension(
        $filename,
        $new_extension,
        $includepath = false
    ) {
        $info = pathinfo($filename);
        $newfilename = '';
        if ((key_exists("dirname", $info)) and ($includepath == true)) {
            $newfilename .= $info['dirname'] . '/';
        }
        $newfilename .= $info['filename'] . '.' . $new_extension;
        return $newfilename;
    }

    /**
     * This function checks to see if the Documentation application is available
     *
     * @param string $tool The name of the application to be checked for
     *
     * @return string Fully qualified path to $tool
     *
     * @since  Method available since Release 1.0.0
     * @access protected
     */
    protected function checkToolExists($tool)
    {
        $toolpath = '';
        if (file_exists('/bin/' . $tool)) {
            $toolpath = '/bin/' .$tool;
        } elseif (file_exists('/usr/bin/' . $tool)) {
            $toolpath = '/usr/bin/' .$tool;
        } elseif (file_exists('/usr/local/bin/' .$tool)) {
            $toolpath = '/usr/local/bin/' .$tool;
        }
        return $toolpath;
    }

    /**
     * This function creates the olink database in the required directory
     *
     * @param string $olinkdb    The name of the OLINK Database
     * @param string $stylesheet The name of the Dockbook Stylesheet to use
     * @param string $filename   The name of the base XML file
     *
     * @return boolean true if db is created
     *
     * @access protected
     */
    protected function createolinkdb($olinkdb, $stylesheet, $filename)
    {
        // create olink databases
        $cmd = $this->xsltproc;
        $cmd .= " --stringparam collect.xref.targets only";
        $cmd .= " --stringparam use.id.as.filename 1";
        $cmd .= " --stringparam chunk.first.sections 1";
        $cmd .= " --xinclude";
        $cmd .= " --stringparam targets.filename " . $olinkdb;
        $cmd .= " ". $stylesheet;
        $cmd .= " ". $filename;
        if ($this->verbose != true) {
            $cmd .= "  2>&1 > /dev/null";
        }
        $out = "";
        $error = 0;
        exec($cmd, $out, $error);
        if ($error === 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function runs the XMLTO command
     *
     * @param string $chunks       The local stylesheet
     * @param string $dbstylesheet The main Docbook Style Sheets
     * @param string $output       The output directory or filename of transformed
     *                             document
     * @param string $filename     The base XML File
     *
     * @return boolean True if the command is completed successfully
     *
     * @acces protected
     */
    protected function createhtmldocs(
        $chunks,
        $dbstylesheet,
        $output,
        $filename
    ) {
        $stylesheet = $this->DocBase . '/tmp/stylesheet.xsl';
        $this->stylesheetcombiner($stylesheet, $dbstylesheet, $chunks);
        $cmd = $this->xsltproc;
        $cmd .= " --xinclude";
        $cmd .= " --nonet";
        $cmd .= " --param passivetex.extensions '1'";
        $cmd .= " -o " . $output;
        $cmd .= " " . $stylesheet;
        $cmd .= " " . $filename;
        if ($this->verbose != true) {
            $cmd .= "  2>&1 > /dev/null";
        }
        $out = "";
        $error = 0;
        exec($cmd, $out, $error);
        if ($error === 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function creates pdf files fom the docbook xml input
     *
     * @param string $template     The local xsl customisation file
     * @param string $dbstylesheet The NWALSH Docbook stylesheet to be used
     * @param string $outputfile   The location and name of the output pdf file
     * @param string $inputfile    The location and name of the input PDF file
     *
     * @return boolean
     *
     * @access protected
     */
    protected function createpdf($template, $dbstylesheet, $outputfile, $inputfile)
    {
        $fopfile = $this->DocBase;
        $fopfile .= '/tmp/';
        $fopfile .= $this->ReplaceExtension($outputfile, 'fo');

        $imagedir = $this->DocBase . '/images/';


        $stylesheet = $this->DocBase . '/tmp/stylesheet.xsl';
        $this->stylesheetcombiner($stylesheet, $dbstylesheet, $template, $imagedir);

        $cmd = $this->xsltproc;
        //$cmd .= " --stringparam target.database.document " . $olinkdb;
        $cmd .= " --xinclude";
        $cmd .= " -o " . $fopfile;
        $cmd .= " " . $stylesheet;
        $cmd .= " " . $inputfile;
        if ($this->verbose != true) {
            $cmd .= "  2>&1 > /dev/null";
        }
        $out = "";
        $error = 0;
        exec($cmd, $out, $error);
        if ($error != 0) {
            return false;
        }
        $cmd2 = $this->fop;
        $cmd2 .= " " . $fopfile;
        $cmd2 .= " " . $outputfile;
        if ($this->verbose != true) {
            $cmd2 .= "  2>&1 > /dev/null";
        }
        exec($cmd2, $out, $error);
        if ($error === 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function creates a temporary stylesheet file which is used to load
     * both the user XSL customisation fragment and NWALSH Docbook stylesheet
     *
     * @param string $filename   The name of the temporary stylesheet
     * @param string $stylesheet The name of the DocBook stylesheet to be used
     * @param string $fragment   The name of the fragment stylesheet holding user
     *                           customisations
     * @param string $imagedir   The name of the directory holding the image files.
     *                           This is used to create an absolute path when
     *                           creating PDF files
     *
     * @return boolean True if the stylesheet has been created
     *
     * @access protected
     */
    protected function stylesheetcombiner(
        $filename,
        $stylesheet,
        $fragment,
        $imagedir = null
    ) {

        $handle = @fopen($filename, "w");
        if ($handle !== false) {
            fwrite(
                $handle,
                '<?xml version="1.0" encoding="UTF-8"?>'
            );
            fwrite(
                $handle,
                "\n"
            );
            fwrite(
                $handle,
                '<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"'
            );
            fwrite(
                $handle,
                "\n"
            );
            fwrite(
                $handle,
                '                xmlns:fo="http://www.w3.org/1999/XSL/Format"'
            );
            fwrite(
                $handle,
                "\n"
            );
            fwrite(
                $handle,
                '                version="1.0">'
            );

            fwrite(
                $handle,
                "\n"
            );
            fwrite(
                $handle,
                '     <xsl:import href="' . $stylesheet . '"/>' . "\n"
            );

            fwrite(
                $handle,
                '     <xsl:import href="' . $fragment . '"/>' . "\n"
            );

            // If the image directory is not null set the xsl:param to point to
            // that directory
            if ($imagedir != null) {
                $xslparam = '     <xsl:param name="admon.graphics.path">';
                $xslparam .= $imagedir;
                $xslparam .= '</xsl:param>';
                fwrite(
                    $handle,
                    $xslparam
                );
                fwrite(
                    $handle,
                    "\n"
                );
            }
            fwrite(
                $handle,
                '</xsl:stylesheet>' . "\n"
            );



            fclose($handle);
            return true;
        } else {
            // return false as the file could not be opened
            return false;
        }
    }
    /******************************************************************************
     *  PUBLIC FUNCTIONS
     ******************************************************************************/

    /**
     * This function is used to register a language for creating documentation.
     *
     * This function is used to register a language for creating documentation.  All
     * languages are in their own directory
     *
     * @param string  $language  The language to be added to the array
     * @param string  $filename  The name of the base xml file
     * @param string  $xml       The directory containing the source documents
     * @param string  $html      The output directory for HTML Files
     * @param string  $pdf       The output directory for the PDF file
     * @param integer $dbversion The version of docbook dtd to use
     *
     * @return boolean true if language is registered false otherwise
     *
     * @access public
     */
    public function registerLanguage(
        $language,
        $filename,
        $xml,
        $html,
        $pdf,
        $dbversion
    ) {
        if (\strlen($language) != 2) {
            $errorStr = "Language '" .$language . "' is not 2 characters long";
            throw new AppException($errorStr);
        }

        if (key_exists($language, $this->languages)) {
            $errorStr = "Language '" .$language . "' is already registered";
            throw new AppException($errorStr);
        }

        if (!file_exists($this->DocBase . '/' . $language . '/' . $xml)) {
            $errorStr = "Language '" .$language . "' source files do not exist";
            throw new AppException($errorStr);
        }

        $this->languages[$language] = array();
        $this->languages[$language]['xml'] = $xml;
        $this->languages[$language]['filename'] = $filename;
        $this->languages[$language]['html'] = $html;
        $this->languages[$language]['pdf'] = $pdf;
        $this->languages[$language]['dbversion'] = $dbversion;
        return true;
    }


    /**
     * This function sets the location of the DB4 stylesheets
     *
     * @param string $db4path Path to the DB4 Stylesheets
     *
     * @return boolean Returns true if the stylesheets exist
     *
     * @since Method available since Release 1.0.0
     */
    public function setDB4StyleSheets($db4path)
    {
        // Check if the file exist by seeing if html/chunk.xsl exists
        if (file_exists($db4path . "html/chunk.xsl")) {
            // The stylesheets exist
            $this->db4stylesheets = $db4path;
            return true;
        } else {
            // The stylesheets don't exist
            return false;
        }
    }


    /**
     * This function sets the location of the DB5 stylesheets
     *
     * @param string $db5path Path to the DB4 Stylesheets
     *
     * @return boolean Returns true if the stylesheets exist
     *
     * @since Method available since Release 1.0.0
     */
    public function setDB5StyleSheets($db5path)
    {
        // Check if the file exist by seeing if html/chunk.xsl exists
        if (file_exists($db5path . "html/chunk.xsl")) {
            // The stylesheets exist
            $this->db5stylesheets = $db5path;
            return true;
        } else {
            // The stylesheets don't exist
            return false;
        }
    }

    /**
     * Remove all the output file and Directories
     *
     * @return True when all work completed
     *
     * @access public
     */
    public function cleanHTML()
    {
        // Remove the language HTML Directories
        foreach ($this->languages as $langdir => $directory) {
            $htmldir = $this->DocBase . '/' . $langdir . '/' . $directory['html'];
            if (file_exists($htmldir)) {
                $mask = $htmldir . "/*";
                array_map("unlink", glob($mask));
                if (!rmdir($htmldir)) {
                    throw new AppException('Unable to delete dir ' . $htmldir);
                }
            }
        }

        // Remove the Development HTML Directories;
        foreach ($this->development as $directory) {
            $htmldir = $this->DocBase . '/developer/' . $directory['html'];
            if (file_exists($htmldir)) {
                $mask = $htmldir . "/*";
                array_map("unlink", glob($mask));
                if (!rmdir($htmldir)) {
                    throw new AppException('Unable to delete dir ' . $htmldir);
                }
            }
        }
        return true;
    }


    /**
     * Remove all the output file and Directories
     *
     * @return True when all work completed
     *
     * @access public
     */
    public function cleanPDF()
    {

        // Remove the language PDF Directories
        foreach ($this->languages as $langdir => $directory) {
            $pdfdir = $this->DocBase . '/' . $langdir . '/' . $directory['pdf'];
            if (file_exists($pdfdir)) {
                $mask = $pdfdir . "/*";
                array_map("unlink", glob($mask));
                if (!rmdir($pdfdir)) {
                    throw new AppException('Unable to delete dir ' . $pdfdir);
                }
            }
        }

        // Remove the Development PDF Directory
        foreach ($this->development as $directory) {
            $pdfdir = $this->DocBase . '/developer/' . $directory['pdf'];
            if (file_exists($pdfdir)) {
                $mask = $pdfdir . "/*";
                array_map("unlink", glob($mask));
                if (!rmdir($pdfdir)) {
                    throw new AppException('Unable to delete dir ' . $pdfdir);
                }
            }
        }
        return true;
    }


    /**
     * Remove OLINK Databases in the xml Directories
     *
     * @return True when all work completed
     *
     * @access public
     */
    public function cleanOLINKDB()
    {
        // Remove the language olink databases
        foreach ($this->languages as $langdir => $directory) {
            $localdir = $this->DocBase . '/' . $langdir . '/' . $directory['xml'];
            if (file_exists($localdir)) {
                $mask = $localdir . "/*.db";
                array_map("unlink", glob($mask));
            }
        }

        // Remove the Developmentolink databases
        foreach ($this->development as $directory) {
            $localdir = $this->DocBase . '/developer/' . $directory['xml'];
            if (file_exists($localdir)) {
                $mask = $localdir . "/*.db";
                array_map("unlink", glob($mask));
            }
        }
        return true;
    }

    /**
     * Remove proc files in the xml Directories
     *
     * @return True when all work completed
     *
     * @access public
     */
    public function cleanProc()
    {
        // Remove the language proc files
        foreach ($this->languages as $langdir => $directory) {
            $localdir = $this->DocBase . '/' . $langdir . '/' . $directory['xml'];
            if (file_exists($localdir)) {
                $mask = $localdir . "/*.proc";
                array_map("unlink", glob($mask));
            }
        }

        // Remove the Development procfiles
        foreach ($this->development as $directory) {
            $localdir = $this->DocBase . '/developer/' . $directory['xml'];
            if (file_exists($localdir)) {
                $mask = $localdir . "/*.proc";
                array_map("unlink", glob($mask));
            }
        }
        return true;
    }


    /**
     * Remove ent files in the xml Directories
     *
     * @return True when all work completed
     *
     * @access public
     */
    public function cleanENT()
    {
        // Remove the language HTML ENT files
        foreach ($this->languages as $langdir => $directory) {
            $localdir = $this->DocBase . '/' . $langdir . '/' . $directory['xml'];
            if (file_exists($localdir)) {
                $mask = $localdir . "/*.ent";
                array_map("unlink", glob($mask));
            }
        }

        // Remove the Development ENT files
        foreach ($this->development as $directory) {
            $localdir = $this->DocBase . '/developer/' . $directory['xml'];
            if (file_exists($localdir)) {
                $mask = $localdir . "/*.ent";
                array_map("unlink", glob($mask));
            }
        }
        return true;
    }

    /**
     * Create the temporary working directory
     *
     * @return boolean true if directory created
     *
     * @access public
     */
    public function createworkingdirectory()
    {
        $workingdir = $this->DocBase . '/tmp';
        if (!file_exists($workingdir)) {
            $dircreated = mkdir($workingdir);
            if ($dircreated === false) {
                throw new AppException(
                    'Unable to create working directory ' . $workingdir
                );
            }
        }
    }

    /**
     * Delete the temporary working directory
     *
     * @return boolean true if directory created
     *
     * @access public
     */
    public function deleteworkingdirectory()
    {
        $workingdir = $this->DocBase . '/tmp';
        if (file_exists($workingdir)) {
            $mask = $workingdir . "/*";
            array_map("unlink", glob($mask));
            if (!rmdir($workingdir)) {
                throw new AppException('Unable to delete dir ' . $workingdir);
            }
        }
    }

    /**
     * Prepare to create user documentation
     *
     * @return True when all work completed
     *
     * @access public
     */
    public function prepareUserDocumentation()
    {
        // Remove the language HTML Directories
        foreach ($this->languages as $langdir => $directory) {
            $workdir = $this->DocBase . '/' . $langdir;

            // Make the HTML and PDF Directories
            $htmldir = $workdir . '/' .$directory['html'];
            if (!file_exists($htmldir)) {
                $htmlcomplete  = mkdir($htmldir);
                if ($htmlcomplete == false) {
                    throw new AppException(
                        'Unable to create html directory in ' . $langdir
                    );
                }
            }
            $pdfdir = $workdir . '/' .$directory['pdf'];
            if (!file_exists($pdfdir)) {
                $pdfcomplete = mkdir($workdir . '/' .$directory['pdf']);
                if ($pdfcomplete == false) {
                    throw new AppException(
                        'Unable to create pdf directory in ' . $langdir
                    );
                }
            }

            // Copy the ENT file to the XML Dir.
            $cpresult = copy(
                $this->DocBase . '/' . $this->entfile,
                $workdir . '/' . $directory['xml'] .'/' . $this->entfile
            );
            if ($cpresult == false) {
                 throw new AppException('Unable to copy ENT file for ' . $langdir);
            }

            // Copy the CSS file to the HRML Dir.
            $cssresult = copy(
                $this->DocBase . '/style.css',
                $workdir . '/' .  $directory['html'] . '/' . 'style.css'
            );
            if ($cssresult == false) {
                 throw new AppException('Unable to copy CSS file for ' . $langdir);
            }
        }
        return true;
    }


    /**
     * This function creates the multiple html file user documentation for all
     * languages
     *
     * This function creates the multiple html file user documentation for all
     * languages.  It will  true when it completes its task.  It will throw an
     * exception if an error  is encountered
     *
     * @return boolean True when the function completes
     *
     * @access public
     */
    public function createchunkeduserdocs()
    {
        foreach ($this->languages as $langdir => $directory) {
            // Set the base XML Directory
            $xmldir = $this->DocBase . '/' . $langdir . '/' . $directory['xml'];

            // Select the name of the olinkdb
            $olinkdb = $xmldir . '/' . 'olinkchunk.db';

            // Set the base xml filename
            $filename = $xmldir . '/' . $directory['filename'];

            // Pick the correct style sheet
            if (($directory['dbversion'] == 5)) {
                $stylesheet = $this->db5stylesheets . 'html/chunk.xsl';
                 $chunks = $this->DocBase . '/xsl/chunks5.xsl';
            } else {
                // Default to Docbook 4
                $stylesheet = $this->db4stylesheets . 'html/chunk.xsl';
                $chunks = $this->DocBase . '/xsl/chunks.xsl';
            }

            // Check if the stylesheet exists
            if (!file_exists($stylesheet)) {
                throw new AppException(
                    "stylesheet '" . $stylesheet . "' does not exist"
                );
            }

            if ($this->createolinkdb($olinkdb, $stylesheet, $filename) === false) {
                throw new AppException(
                    "Unable to create '" . $olinkdb . "'"
                );
            }

            // Set the output directory filename
            $outputdir = $this->DocBase;
            $outputdir .= '/' . $langdir;
            $outputdir .= '/' . $directory['html'] . '/';

            $nochunckresult = $this->createhtmldocs(
                $chunks,
                $stylesheet,
                $outputdir,
                $filename
            );
            if ($nochunckresult === false) {
                throw new AppException(
                    "Unable to create single HTML documentation for " . $filename
                );
            }
        }
    }


    /**
     * This function creates a single html file user documentation for all
     * languages
     *
     * This function creates a single html file user documentation for all
     * languages.  It will  true when it completes its task.  It will throw an
     * exception if an error  is encountered
     *
     * @return boolean True when the function completes
     *
     * @access public
     */
    public function createnochunkeduserdocs()
    {
        foreach ($this->languages as $langdir => $directory) {
            // Set the base XML Directory
            $xmldir = $this->DocBase . '/' . $langdir . '/' . $directory['xml'];

            // Select the name of the olinkdb
            $olinkdb = $xmldir . '/' . 'olinknochunk.db';

            // Set the base xml filename
            $filename = $xmldir . '/' . $directory['filename'];

            // Pick the correct style sheet
            if (($directory['dbversion'] == 5)) {
                $stylesheet = $this->db5stylesheets . 'html/docbook.xsl';
                $chunks = $this->DocBase . '/xsl/nochunks5.xsl';
            } else {
                // Default to Docbook 4
                $stylesheet = $this->db4stylesheets . 'html/docbook.xsl';
                $chunks = $this->DocBase . '/xsl/nochunks.xsl';
            }

            if (!file_exists($stylesheet)) {
                throw new AppException(
                    "stylesheet '" . $stylesheet . "' does not exist"
                );
            }

            if ($this->createolinkdb($olinkdb, $stylesheet, $filename) === false) {
                throw new AppException(
                    "Unable to create '" . $olinkdb . "'"
                );
            }

            // Set the xmlto template
            $template = "html-nochunks";

            // Set the output filename
            $outputfile = $this->DocBase . '/' . $langdir . '/' . $directory['html'];
            $outputfile .= '/' . $this->ReplaceExtension($filename, 'html');

            $nochunckresult = $this->createhtmldocs(
                $chunks,
                $stylesheet,
                $outputfile,
                $filename
            );
            if ($nochunckresult === false) {
                throw new AppException(
                    "Unable to create single HTML documentation for " . $filename
                );
            }
        }
    }

    /**
     * This function creates a PDF file containing user documentation for all
     * languages
     *
     * This function creates a PDF file user documentation for all
     * languages.  It will  true when it completes its task.  It will throw an
     * exception if an error  is encountered
     *
     * @return boolean True when the function completes
     *
     * @access public
     */
    public function createpdfuserdocs()
    {
        foreach ($this->languages as $langdir => $directory) {
            // Set the base XML Directory
            $xmldir = $this->DocBase . '/' . $langdir . '/' . $directory['xml'];

            // Set the base xml filename
            $inputfile = $xmldir . '/' . $directory['filename'];

            // Set the local customisation layer
            // Set the chunks xsl filename
            if (($directory['dbversion'] == 5)) {
                 $template = $this->DocBase . '/xsl/pdf5.xsl';
                 $stylesheet =  $this->db5stylesheets . "/fo/docbook.xsl";
            } else {
                 $template = $this->DocBase . '/xsl/pdf.xsl';
                 $stylesheet =  $this->db4stylesheets . "/fo/docbook.xsl";
            }


            // Set the output directory filename
            $outputdir = $this->DocBase . '/' . $langdir . '/' . $directory['pdf'];

            //set the outputfile name
            $outputfile = $outputdir;
            $outputfile .= '/';
            $outputfile .= $this->ReplaceExtension($directory['filename'], 'pdf');

            $pdfresult = $this->createpdf(
                $template,
                $stylesheet,
                $outputfile,
                $inputfile
            );
            if ($pdfresult === false) {
                throw new AppException(
                    "Unable to create PDF documentation for " . $inputfile
                );
            }
        }
    }


    /**
     * Prepare to create development documentation
     *
     * @return True when all work completed
     *
     * @access public
     */
    public function prepareDevelopmentDocumentation()
    {
        // Remove the language HTML Directories
        foreach ($this->development as $langdir => $directory) {
            $workdir = $this->DocBase . '/developer';

            // Make the HTML and PDF Directories
            $htmldir = $workdir . '/' .$directory['html'];
            if (!file_exists($htmldir)) {
                $htmlcomplete  = mkdir($htmldir);
                if ($htmlcomplete == false) {
                    throw new AppException(
                        'Unable to create html directory in ' . $langdir
                    );
                }
            }

            $pdfdir = $workdir . '/' .$directory['pdf'];
            if (!file_exists($pdfdir)) {
                $pdfcomplete = mkdir($pdfdir);
                if ($pdfcomplete == false) {
                    throw new AppException(
                        'Unable to create pdf directory in ' . $langdir
                    );
                }
            }

            // Copy the ENT file to the XML Dir.
            $cpresult = copy(
                $this->DocBase . '/' . $this->entfile,
                $workdir . '/' . $directory['xml'] .'/' . $this->entfile
            );
            if ($cpresult == false) {
                 throw new AppException('Unable to copy ENT file for ' . $langdir);
            }

            // Copy the CSS file to the HRML Dir.
            $cssresult = copy(
                $this->DocBase . '/style.css',
                $workdir . '/' .  $directory['html'] . '/' . 'style.css'
            );
            if ($cssresult == false) {
                 throw new AppException('Unable to copy CSS file for ' . $langdir);
            }
        }
        return true;
    }


    /**
     * This function creates the multiple html file developer documentation
     *
     * This function creates the multiple html file developer documentation.
     * It will  true when it completes its task.  It will throw an exception if an
     * error  is encountered
     *
     * @return boolean True when the function completes
     *
     * @access public
     */
    public function createchunkeddevelopmentdocs()
    {
        foreach ($this->development as $langdir => $directory) {
            // Set the base XML Directory
            $xmldir = $this->DocBase . '/developer/' . $directory['xml'];

            // Select the name of the olinkdb
            $olinkdb = $xmldir . '/' . 'olinkchunk.db';

            // Set the base xml filename
            $filename = $xmldir . '/' . $directory['filename'];

            // Pick the correct style sheet
            if (($directory['dbversion'] == 5)) {
                $stylesheet = $this->db5stylesheets . 'html/chunk.xsl';
                $chunks = $this->DocBase . '/xsl/chunks5.xsl';
            } else {
                // Default to Docbook 4
                $stylesheet = $this->db4stylesheets . 'html/chunk.xsl';
                $chunks = $this->DocBase . '/xsl/chunks.xsl';
            }
            if (!file_exists($stylesheet)) {
                throw new AppException(
                    "stylesheet '" . $stylesheet . "' does not exist"
                );
            }

            if ($this->createolinkdb($olinkdb, $stylesheet, $filename) === false) {
                throw new AppException(
                    "Unable to create '" . $olinkdb . "'"
                );
            }

            // Set the chunks xsl filename
            if (($directory['dbversion'] == 5)) {
            } else {
                $chunks = $this->DocBase . '/xsl/chunks.xsl';
            }

            // Set the output directory filename
            $outputdir = $this->DocBase . '/developer/' . $directory['html'] .'/';

            $nochunckresult = $this->createhtmldocs(
                $chunks,
                $stylesheet,
                $outputdir,
                $filename
            );
            if ($nochunckresult === false) {
                throw new AppException(
                    "Unable to create single HTML documentation for " . $filename
                );
            }
        }
    }


    /**
     * This function creates a single html file user documentation for all
     * languages
     *
     * This function creates a single html file user documentation for all
     * languages.  It will  true when it completes its task.  It will throw an
     * exception if an error  is encountered
     *
     * @return boolean True when the function completes
     *
     * @access public
     */
    public function createnochunkeddevelopmentdocs()
    {
        foreach ($this->development as $langdir => $directory) {
            // Set the base XML Directory
            $xmldir = $this->DocBase . '/developer/' . $directory['xml'];

            // Select the name of the olinkdb
            $olinkdb = $xmldir . '/' . 'olinknochunk.db';

            // Set the base xml filename
            $filename = $xmldir . '/' . $directory['filename'];

            // Pick the correct style sheet
            if (($directory['dbversion'] == 5)) {
                $stylesheet = $this->db5stylesheets . 'html/docbook.xsl';
                $chunks = $this->DocBase . '/xsl/nochunks5.xsl';
            } else {
                // Default to Docbook 4
                $stylesheet = $this->db4stylesheets . 'html/docbook.xsl';
                $chunks = $this->DocBase . '/xsl/nochunks.xsl';
            }
            if (!file_exists($stylesheet)) {
                throw new AppException(
                    "stylesheet '" . $stylesheet . "' does not exist"
                );
            }

            if ($this->createolinkdb($olinkdb, $stylesheet, $filename) === false) {
                throw new AppException(
                    "Unable to create '" . $olinkdb . "'"
                );
            }

            // Set the output directory filename
            $outputdir = $this->DocBase . '/developer/' . $directory['html'];
            $outputdir .= "/" . $this->ReplaceExtension($filename, 'html');

            $nochunckresult = $this->createhtmldocs(
                $chunks,
                $stylesheet,
                $outputdir,
                $filename
            );
            if ($nochunckresult === false) {
                throw new AppException(
                    "Unable to create single HTML documentation for " . $filename
                );
            }
        }
    }



    /**
     * This function creates a PDF file user documentation for all both the
     * developer and rest api manuals.
     *
     * This function creates a sPDF file user documentation for all both the
     * developer and rest api manuals.  It will  true when it completes its task.
     * It will throw an exception if an error is encountered
     *
     * @return boolean True when the function completes
     *
     * @access public
     */
    public function createpdfdevelopmentdocs()
    {
        foreach ($this->development as $langdir => $directory) {
            // Set the base XML Directory
            $xmldir = $this->DocBase . '/developer/' . $directory['xml'];

            // Select the name of the olinkdb
            $olinkdb = $xmldir . '/' . 'olinkpdf.xml';

            // Set the base xml filename
            $inputfile = $xmldir . '/' . $directory['filename'];

            // Set the local customisation layer
            // Set the chunks xsl filename
            if (($directory['dbversion'] == 5)) {
                $template = $this->DocBase . '/xsl/pdf5.xsl';
                $stylesheet =  $this->db5stylesheets . "/fo/docbook.xsl";
            } else {
                $template = $this->DocBase . '/xsl/pdf.xsl';
                $stylesheet =  $this->db4stylesheets . "/fo/docbook.xsl";
            }


            // Set the output directory filename
            $outputdir = $this->DocBase . '/developer/' . $directory['pdf'];

            //set the outputfile name
            $outputfile = $outputdir;
            $outputfile .= '/';
            $outputfile .= $this->ReplaceExtension($directory['filename'], 'pdf');

            $pdfresult = $this->createpdf(
                $template,
                $stylesheet,
                $outputfile,
                $inputfile
            );
            if ($pdfresult === false) {
                throw new AppException(
                    "Unable to create PDF documentation for " . $inputfile
                );
            }
        }
    }
}
