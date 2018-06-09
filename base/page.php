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
$configdir = $tpl->getConfigDir(0);
$config = new \webtemplate\config\Configure($configdir);

//Create the logclass
$log = new \webtemplate\general\Log(
    $config->read('param.admin.logging'),
    $config->read('param.admin.logrotate')
);


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
    // Tell the templates the user has not logged in.
    $tpl->assign('LOGIN', true);

    // Get the Default style
    $stylesheetarray = array();
    $stylesheetarray[] = 'style/' . $config->read('pref.theme.value') . '/main.css';
    $stylesheetarray[] = 'style/' . $config->read('pref.theme.value') . '/page.css';
    $tpl->assign('STYLESHEET', $stylesheetarray);
} else {
    // Tell the templates the user has logged in.  This will display the menus
    $tpl->assign('LOGIN', false);


    // Configure the correct user and their permissions
    $user = new \webtemplate\users\User($db);
    $user->register($session->getUserName(), $config->read('pref'));

    // Get the users groups
    $userGroups = new \webtemplate\users\Groups($db, $user->getUserId());

    // Get the users select style
    $stylesheetarray = array();
    $stylesheetarray[] = 'style/' . $user->getUserTheme() . '/main.css';
    $stylesheetarray[] = 'style/' . $user->getUserTheme() . '/page.css';
    $tpl->assign('STYLESHEET', $stylesheetarray);


    // Users real name for displaying on web page
    $tpl->assign('USERNAME', $user->getRealName());

    // Assign AdminAccess Rights to the template
    $tpl->assign('ADMINACCESS', $userGroups->getAdminAccess());
}

// Set the Sys admin email address
$tpl->assign("SYSADMINEMAIL", $config->read("param.maintainer"));

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $config->read('param.docbase'),
    $language
);

$tpl->assign("DOCSAVAILABLE", $docsAvailable);


//DO WHAT EVER THIS MODULE NEEDS TO DO  ^[\w,\s-]+\.[A-Za-z]{3}$
$pageFound = false;
if (\filter_input(INPUT_GET, "id") !== null) {
    // Get the maximum file length allowed

    $fileName = substr(\filter_input(INPUT_GET, "id"), 0, 30);
    // Check id is a valid file name
    if (\webtemplate\general\LocalValidate::htmlFile($fileName)) {
        // Create the template
        $template = 'pages/' . $fileName . '.tpl';

        // Check template exists
        if ($tpl->templateExists($template)) {
            $pageFound = true;
        }
    }
}

// Display an error Message if a page is not found.
if ($pageFound == false) {
    $logmsg = $user-> getUserName() . " requested " .   $fileName;
    $log->security("page.php: " . $logmsg);
    $template = 'global/error.tpl';
    $msg = gettext("The Page you have requested ($fileName) was not found.");
    $header =  gettext("Page Not Found");
    $tpl->assign('ERRORMSG', $msg);
    $tpl->assign('HEADERMSG', $header);
}

// Get the year for the Copyright Statement
$dateArray = getdate();
$tpl->assign('YEAR', "$dateArray[year]");

// Display the Web Page
$tpl->display($template);
