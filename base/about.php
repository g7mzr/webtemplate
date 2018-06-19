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


// Load the menu and assign it to a SMARTY Variable
$mainmenu = $app->config()->readMenu('mainmenu');
$app->tpl()->assign('MAINMENU', $mainmenu);


/* Send the HTTP Headers required by the application */
$headerResult = \webtemplate\application\Header::sendHeaders();
if ($headerResult == false) {
    // Log error is headers not sent
    $app->log->error(basename(__FILE__) . ":  Failed to send HTTP Headers");
}

// Check if the user is logged in.
if ($app->session()->getUserName() == '') {
    //The user is not logged in. Display a login screen
    $headerResult = \webtemplate\application\Header::sendRedirect('index.php');
    if ($headerResult == false) {
        // Log error is headers not sent
        $app->log()->error(
            basename(__FILE__) . ":  Failed to send Redirect HTTP Header"
        );
    }
    exit();
}

// Get the users select style
$stylesheetarray = array();
$stylesheetarray[] = '/style/' . $app->user()->getUserTheme() . '/main.css';
$stylesheetarray[] = '/style/' . $app->user()->getUserTheme() . '/userprefs.css';
$app->tpl()->assign('STYLESHEET', $stylesheetarray);

// Set the Sys admin email address
$app->tpl()->assign("SYSADMINEMAIL", $app->config()->read("param.maintainer"));

// Tell the templates the user has logged in.  This will display the menus
$app->tpl()->assign('LOGIN', false);

// Users real name for displaying on web page
$app->tpl()->assign('USERNAME', $app->user()->getRealName());

// Assign AdminAccess Rights to the template
$app->tpl()->assign('ADMINACCESS', $app->usergroups()->getAdminAccess());

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $app->config()->read('param.docbase'),
    $language
);
$app->tpl()->assign("DOCSAVAILABLE", $docsAvailable);

// Check the user has permission to run this module
if (!$app->usergroups()->checkGroup('admin') == true) {
    // User is not allowed to display the admin page
    $app->log()->security(
        $app->session()->getUserName() .
        gettext(" attempted to access ") .
        basename(__FILE__)
    );
    $template = 'global/error.tpl';
    $msg = gettext("You are not authorised to access this page.");
    $header =  gettext("Authorisation Required");
    $app->tpl()->assign('ERRORMSG', $msg);
    $app->tpl()->assign('HEADERMSG', $header);
    $dateArray = getdate();
    $app->tpl()->assign("YEAR", "$dateArray[year]");
    $app->tpl()->display($template);
    exit();
}

// Get the Version of Database being used
$databaseversion = gettext("Error Getting Database Version");
$result = $app->db()->getDBVersion();
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

$app->tpl()->assign("PHPVERSION", phpversion());
$app->tpl()->assign("SERVERNAME", $_SERVER['SERVER_NAME']);
$app->tpl()->assign("SERVERSOFTWARE", $_SERVER['SERVER_SOFTWARE']);
$app->tpl()->assign("SERVERADMIN", $_SERVER['SERVER_ADMIN']);
$app->tpl()->assign("DATABASEVERSION", $databaseversion);
$app->tpl()->assign("LOGDIRSIZE", $size);
$app->tpl()->assign("PERCENTAGE", $percentage);

$template = 'admin/about.tpl';

// Get the year for the Copyright Statement
$dateArray = getdate();
$app->tpl()->assign('YEAR', "$dateArray[year]");

// Display the Web Page
$app->tpl()->display($template);
