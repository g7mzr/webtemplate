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
require_once "../includes/general/help.php";

// Create the Smart Template object
$tpl = new \webtemplate\application\SmartyTemplate;

//Set up the correct language and associated templates
$languageconfig = \webtemplate\general\General::getconfigfile($tpl->getConfigDir());
$tpl->assign('CONFIGFILE', $languageconfig);
$tpl->configLoad($languageconfig);
$language = $tpl->getConfigVars('Language');
$templateArray = $tpl->getTemplateDir();
$tpl->setTemplateDir($templateArray[0] . '/' . $language);
$tpl->setCompileId($language);
putenv("LANGUAGE=$language");
setlocale(LC_ALL, $language);
bindtextdomain('messages', '../locale');
textdomain('messages');

// Get the Database login information
\webtemplate\application\WebTemplateCommon::loadDSN($tpl, $dsn);

// Loginto the Database for all classes
$db =& \webtemplate\db\DB::load($dsn);
if (\webtemplate\general\General::isError($db)) {
    // Unable to Connect to the Database
    $template = 'global/error.tpl';
    $msg = gettext("Unable to Connect to the Database.\n\n");
    $msg .= gettext("Please Contact your Adminstrator");
    $header =  gettext("Unable to Connect to the Database");
    $tpl->assign('ERRORMSG', $msg);
    $tpl->assign('HEADERMSG', $header);
    $dateArray = getdate();
    $tpl->assign("YEAR", "$dateArray[year]");
    $tpl->display($template);
    exit();
}

//$tpl->debugging = true;

//Create new config class
$config = new \webtemplate\config\Configure($db);

//Create the logclass
$log = new \webtemplate\general\Log(
    $config->read('param.admin.logging'),
    $config->read('param.admin.logrotate')
);


// Set the Sys admin email address
$tpl->assign("SYSADMINEMAIL", $config->read("param.maintainer"));

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $config->read('param.docbase'),
    $language
);
$tpl->assign("DOCSAVAILABLE", $docsAvailable);

// Load the menu and assign it to a SMARTY Variable
$mainmenu = $config->readMenu('mainmenu');
$tpl->assign('MAINMENU', $mainmenu);

// Initalise the session variables
$session = new \webtemplate\application\Session(
    $config->read('param.cookiepath'),
    $config->read('param.cookiedomain'),
    $config->read('param.users.autologout'),
    $tpl,
    $db
);


/* Send the HTTP Headers required by the application */
$headerResult = \webtemplate\application\Header::sendHeaders();
if ($headerResult == false) {
    // Log error is headers not sent
    $log->error(basename(__FILE__) . ":  Failed to send HTTP Headers");
}

// Check if the user is logged in.
if ($session->getUserName() == '') {
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

                $docBase = $config->read('param.docbase');
                if (is_dir($docroot . '/docs/' . $language)) {
                    $docBase = str_replace("%lang%", $language, $docBase);
                } else {
                    $docBase = str_replace("%lang%", "en", $docBase);
                }

                // Check the file actually exists
                if (file_exists($docroot . '/' . $docBase . $pagename)) {
                    // Check if there is a bookmark to use
                    if ($helpMap[$pageid]['section'] != '') {
                        //  If there is a bookmark add it to the page name.
                        $pagename .=  '#' . $helpMap[$pageid]['section'] ;
                    }

                    // Create the fully qualified URL and flag the document found
                    $helpurl = $config->read('param.urlbase');
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
    $tpl->assign("URLBASE", $config->read('param.urlbase'));
    $tpl->assign("EMAILADDRESS", $config->read('param.maintainer'));
    $tpl->display("admin/helpfilenotfound.tpl");
}
