<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Required class files and interfaces
 */
// Include the Globals
require_once "../includes/global.php";

// Load common include files
require_once "../includes/general/help.class.php";

// Create a WEBTEMPLATE CLASS
try {
    $app = new \webtemplate\application\Application();
} catch (\Throwable $e) {
    // Create the Smart Template object
    $tpl = new \webtemplate\application\SmartyTemplate;
    $template = 'global/error.tpl';
    $msg = $e->getMessage();
    $msg .= "\n\n";
    $msg .= gettext("Please Contact your Adminstrator");
    $header =  gettext("Application Error");
    $tpl->assign('ERRORMSG', $msg);
    $tpl->assign('HEADERMSG', $header);
    $dateArray = getdate();
    $tpl->assign("YEAR", "$dateArray[year]");
    $tpl->display($template);
    exit();
}

// Set up the Language for translations
$language = $app->language();
\putenv("LANGUAGE=$language");
\setlocale(LC_ALL, $language);
\bindtextdomain('messages', '../locale');
\textdomain('messages');

// Set the Sys admin email address
$app->tpl()->assign("SYSADMINEMAIL", $app->config()->read("param.maintainer"));

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $app->config()->read('param.docbase'),
    $language
);
$app->tpl()->assign("DOCSAVAILABLE", $docsAvailable);

// Load the menu and assign it to a SMARTY Variable
$mainmenu = $app->config()->readMenu('mainmenu');
$app->tpl()->assign('MAINMENU', $mainmenu);

/* Send the HTTP Headers required by the application */
$headerResult = \webtemplate\application\Header::sendHeaders();
if ($headerResult == false) {
    // Log error is headers not sent
    $log->error(basename(__FILE__) . ":  Failed to send HTTP Headers");
}

// Check if the user is logged in.
if ($app->session()->getUserName() == '') {
    //The user is not logged in. Display a login screen
    $headerResult = \webtemplate\application\Header::sendRedirect('index.php');
    if ($headerResult == false) {
        // Log error is headers not sent
        $log->error(basename(__FILE__) . ":  Failed to send Redirect HTTP Header");
    }
    exit();
}


// Default position. Help not found.
$foundDoc = false;

if ($docsAvailable == true) {
    // Documentation is available
    if (\filter_input(INPUT_GET, 'page') !== null) {
        // A documentation page has been requested
        // Validate it
        if (preg_match("/^[a-z]{5,20}$/", \filter_input(INPUT_GET, 'page'))) {
            // // TRansfer page name to a clean variable
            $pageid = \filter_input(INPUT_GET, 'page');
            // Check if the page exists in the document map
            if (array_key_exists($pageid, $helpMap)) {
                // Get the Documentation file name
                $pagename =  $helpMap[$pageid]['url'];

                $docroot = $_SERVER["DOCUMENT_ROOT"];

                $docBase = $app->config()->read('param.docbase');
                if (is_dir($docroot . '/docs/' . $language)) {
                    $docBase = str_replace("%lang%", $language, $docBase);
                } else {
                    $docBase = str_replace("%lang%", "en", $docBase);
                }

                // Check the file actually exists
                $pagefilename = $docroot . '/' . $docBase . '/'.$pagename;
                if (file_exists($pagefilename)) {
                    // Check if there is a bookmark to use
                    if ($helpMap[$pageid]['section'] != '') {
                        //  If there is a bookmark add it to the page name.
                        $pagename .=  '#' . $helpMap[$pageid]['section'] ;
                    }

                    // Create the fully qualified URL and flag the document found
                    $helpurl = $app->config()->read('param.urlbase');
                    if ($helpurl[strlen($helpurl)-1] == '/') {
                        $helpurl .= $docBase;
                    } else {
                        $helpurl .= '/' . $docBase;
                    }
                    $helpurl .= $pagename;
                    $foundDoc = true;
                }
            }
        }
    }
}

// If the document has been found display it
if ($foundDoc == true) {
    header("Location: $helpurl");
} else {
    // Show the error page.
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    $app->tpl()->assign("URLBASE", $app->config()->read('param.urlbase'));
    $app->tpl()->assign("EMAILADDRESS", $app->config()->read('param.maintainer'));
    $app->tpl()->display("admin/helpfilenotfound.tpl");
}
