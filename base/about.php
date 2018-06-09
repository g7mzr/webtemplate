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
$languageconfig = \webtemplate\general\General::getconfigfile(
    $tpl->getConfigDir()
);
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

/* Initilaise PHP Session handling from session.php */
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

// Configure the correct user and their permissions
$user = new \webtemplate\users\User($db);
$result = \webtemplate\general\General::isError(
    $user->register($session->getUserName(), $config->read('pref'))
);
if ($result) {
    $log->error(
        basename(__FILE__) .
        ": Failed To Register logged in user $username"
    );
}

// Get the users groups
$userGroups = new \webtemplate\users\Groups($db, $user->getUserId());

// Get the users select style
$stylesheetarray = array();
$stylesheetarray[] = '/style/' . $user->getUserTheme() . '/main.css';
$stylesheetarray[] = '/style/' . $user->getUserTheme() . '/userprefs.css';
$tpl->assign('STYLESHEET', $stylesheetarray);

// Set the Sys admin email address
$tpl->assign("SYSADMINEMAIL", $config->read("param.maintainer"));

// Tell the templates the user has logged in.  This will display the menus
$tpl->assign('LOGIN', false);

// Users real name for displaying on web page
$tpl->assign('USERNAME', $user->getRealName());

// Assign AdminAccess Rights to the template
$tpl->assign('ADMINACCESS', $userGroups->getAdminAccess());

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $config->read('param.docbase'),
    $language
);
$tpl->assign("DOCSAVAILABLE", $docsAvailable);

// Check the user has permission to run this module
if (!$userGroups->checkGroup('admin') == true) {
    // User is not allowed to display the admin page
    $log->security(
        $session->getUserName() .
        gettext(" attempted to access ") .
        basename(__FILE__)
    );
    $template = 'global/error.tpl';
    $msg = gettext("You are not authorised to access this page.");
    $header =  gettext("Authorisation Required");
    $tpl->assign('ERRORMSG', $msg);
    $tpl->assign('HEADERMSG', $header);
    $dateArray = getdate();
    $tpl->assign("YEAR", "$dateArray[year]");
    $tpl->display($template);
    exit();
}

// Get the Version of Database being used
$databaseversion = gettext("Error Getting Database Version");
$result = $db->getDBVersion();
if (!\webtemplate\general\General::isError($result)) {
    $databaseversion = $result;
}

// Get the size og the logs Directory
$dir = "../logs";
$ar = \webtemplate\general\General::getDirectorySize($dir);
$size = \webtemplate\general\General::sizeFormat($ar['size']);
$space = round(($ar['size']/1024), 1);
$dskspace = round((disk_free_space($dir)/1024), 1);
$percentage = round((($space/$dskspace) * 100), 2);

$tpl->assign("PHPVERSION", phpversion());
$tpl->assign("SERVERNAME", $_SERVER['SERVER_NAME']);
$tpl->assign("SERVERSOFTWARE", $_SERVER['SERVER_SOFTWARE']);
$tpl->assign("SERVERADMIN", $_SERVER['SERVER_ADMIN']);
$tpl->assign("DATABASEVERSION", $databaseversion);
$tpl->assign("LOGDIRSIZE", $size);
$tpl->assign("PERCENTAGE", $percentage);

$template = 'admin/about.tpl';

// Get the year for the Copyright Statement
$dateArray = getdate();
$tpl->assign('YEAR', "$dateArray[year]");

// Display the Web Page
$tpl->display($template);
