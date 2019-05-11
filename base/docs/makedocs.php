#!/usr/bin/php
<?php
/**
 * Document Generation for Webtemplate
 *
 * PHP version  5
 *
 * LICENSE: This source file is subject to version 2.1 of the GPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html.
 *
 * @category  Webtemplate
 * @package   Documentation
 * @author    Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright 2012 Sandy McNeil
 * @license   View the license file distributed with this source code
 * @version   SVN: $Id$
 * @link      http://www.g7mzr.demon.co.uk
 */

/**********************************************************************************
 * Include WEBTEMPLATE MODULES - autoload
 **********************************************************************************/

// Include the Class Autoloader
require_once "../../includes/global.php";
require_once "../../vendor/autoload.php";

// include the Default Error handler
// require_once '../../includes/application/errorHandler.class.php';
use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Command;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Missing;

/**********************************************************************************
 * Style Sheets Locations - Can be updated to match your system
 **********************************************************************************/

$db4 = "/usr/share/xml/docbook/stylesheet/nwalsh/1.79.2/";
$db5 ="/usr/share/xml/docbook/stylesheet/nwalsh5/1.79.2/";

/**********************************************************************************
 * Programme Details  -  Do not change unless you know what you are doing
 **********************************************************************************/
$progname = "webtemplate";
$version  = "0.5.0+";
$entityfile =  $progname . ".ent";

/**********************************************************************************
 * Documentation Generation Functions.
 **********************************************************************************/


/**********************************************************************************
 * Main Programme
 **********************************************************************************/

// Switch off all error reporting for Production Environment
//error_reporting(0);

$getOpt = new GetOpt();

// Define the comon options
$getOpt->addOptions(
    [
        Option::create(null, 'with-pdf', GetOpt::NO_ARGUMENT)
            ->setDescription("Create PDF documentation"),

        Option::create(null, 'with-develop', GetOpt::NO_ARGUMENT)
            ->setDescription("Create all development documentation"),

        Option::create(null, 'clean', GetOpt::NO_ARGUMENT)
            ->setDescription("Remove all generated files and quit"),

        Option::create(null, 'verbose', GetOpt::NO_ARGUMENT)
            ->setDescription("Show xsl tool output"),

        Option::create(null, 'version', GetOpt::NO_ARGUMENT)
            ->setDescription("Show the version information and quit"),

        Option::create(null, 'help', GetOpt::NO_ARGUMENT)
            ->setDescription("Show this help and quit")

    ]
);

// process arguments and catch user errors
try {
    try {
        $getOpt->process();
    } catch (Missing $exception) {
        // catch missing exceptions if help is requested
        if (!$getOpt->getOption('help')) {
            throw $exception;
        }
    }
} catch (ArgumentException $exception) {
    file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
    echo PHP_EOL . $getOpt->getHelpText();
    exit;
}

// show version and quit
if ($getOpt->getOption('version')) {
    echo sprintf('%s - Build Documentation: %s' . PHP_EOL, $progname, $version);
    exit;
}

// show help and quit
if ($getOpt->getOption('help')) {
    echo $getOpt->getHelpText();
    exit;
}

// Set the control variables so that makedocs knows what to do
$clean = $getOpt->getOption('clean');
$withpdf = $getOpt->getOption('with-pdf');
$withdevelop = $getOpt->getOption('with-develop');
$verbose = $getOpt->getOption('verbose');


echo "\n" . $progname . " - Build Documentation Set\n\n";


// Start Configuration of makedocs.php
echo "Configuring makedocs.php to build Documentation............";

try {
    // Create a makedocs object
    $makedocs = new \webtemplate\application\CreateDocs(__DIR__, $entityfile, $verbose);
} catch (Throwable $ex) {
    echo "\n**** Makedocs Error ****\n";
    echo $ex->getMessage();
    echo "\n\n";
    exit(1);
}

// Check and load thet the stlesheets for DB4 exist
if ($makedocs->setDB4StyleSheets($db4) == false) {
    // The DB4 Stylesheets dont exist
    echo "\n**** Makedocs Error ****\n";
    echo "The Docbook 4 Stylesheets do not exist";
    echo "\n\n";
    exit(1);
}

// Check and load thet the stlesheets for DB5 exist
if ($makedocs->setDB5StyleSheets($db5) == false) {
    // The DB4 Stylesheets dont exist
    echo "\n**** Makedocs Error ****\n";
    echo "The Docbook 5 Stylesheets do not exist";
    echo "\n\n";
    exit(1);
}

// Load all the required languages into the application
// If one fails to load it will throw an APPException
try {
    // Load new languages to the application
    // Just add new $makedocs->registerLanguage lines here
    $makedocs->registerLanguage('en', 'webtemplate.xml', 'xml', 'html', 'pdf', 4);
} catch (Throwable $ex) {
      // The language does not exist
    echo "\n**** Makedocs Error ****\n";
    echo $ex->getMessage();
    echo "\n\n";
    exit(1);
}

// Configuraton complete
echo "Done\n";

// Clean the Document tree by removing all HTML and PDF Directores.
// Remove all OLINK Databases and ent files
try {
    // Clean the Document Tree
    echo "Removing old build files...................................";
    $makedocs->cleanHTML();
    $makedocs->cleanPDF();
    $makedocs->cleanOLINKDB();
    $makedocs->cleanENT();
    $makedocs->cleanProc();
    echo "Done\n";
} catch (Throwable $ex) {
    // Failed to clean the document tree
    echo "\n**** Makedocs Error ****\n";
    echo $ex->getMessage();
    echo "\n\n";
    exit(1);
}

if ($clean) {
     exit(0);
}

echo "Creating the temporary working directory...................";
try {
    $makedocs->createworkingdirectory();
} catch (Throwable $ex) {
    // Failed to clean the document tree
    echo "\n**** Makedocs Error ****\n";
    echo $ex->getMessage();
    echo "\n\n";
    exit(1);
}

echo "Done\n";

// Create the USER Documentation
try {
    echo "Preparing the build environment for user documentation ....";
    $makedocs->prepareUserDocumentation();
    echo "Done\n";
    echo "Creating multiple file HTML user documentation.............";
    $makedocs->createchunkeduserdocs();
    echo "Done\n";
    echo "Creating single file HTML user documentaion................";
    $makedocs->createnochunkeduserdocs();
    echo "Done\n";

    if ($withpdf) {
        echo "Creating pdf file of user documentation....................";
        $makedocs->createpdfuserdocs();
        echo "Done\n";
    }
} catch (Throwable $ex) {
    // Failed to clean the document tree
    echo "\n**** Makedocs Error ****\n";
    echo $ex->getMessage();
    echo "\n\n";
    exit(1);
}


if ($withdevelop) {
    try {
        echo "Preparing the build environment for development docs.......";
        $makedocs->prepareDevelopmentDocumentation();
        echo "Done\n";
        echo "Creating multiple file HTML development documentation......";
        $makedocs->createchunkeddevelopmentdocs();
        echo "Done\n";
        echo "Creating single file HTML development documentation........";
        $makedocs->createnochunkeddevelopmentdocs();
        echo "Done\n";
        if ($withpdf) {
            echo "Creating pdf file of development documentation.............";
            $makedocs->createpdfdevelopmentdocs();
            echo "Done\n";
        }
    } catch (Throwable $ex) {
        // Failed to prepare the document tree
        echo "\n**** Makedocs Error ****\n";
        echo $ex->getMessage();
        echo "\n\n";
        exit(1);
    }
}

echo "Deleting the temporary working directory...................";
try {
    $makedocs->deleteworkingdirectory();
} catch (Throwable $ex) {
    // Failed to clean the document tree
    echo "\n**** Makedocs Error ****\n";
    echo $ex->getMessage();
    echo "\n\n";
    exit(1);
}

echo "Done\n";

// End of the program
echo "Build Complete\n\n";

?>
