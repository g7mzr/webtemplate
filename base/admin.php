<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Application Module
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
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
    error_log(basename(__FILE__) . ": " . $e->getMessage());
    header('Location: syserror.html');
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
    $app->log()->error(basename(__FILE__) . ":  Failed to send HTTP Headers");
}

// Check if the user is logged in.
if ($app->session()->getUserName() == '') {
    //The user is not logged in. Display a login screen
    $headerResult = \webtemplate\application\Header::sendRedirect('index.php');
    if ($headerResult == false) {
        // Log error is headers not sent
        $app->log()->error(basename(__FILE__) . ":  Failed to send Redirect HTTP Header");
    }
    exit();
}

// Get the users select style
$stylesheetarray   = array();
$stylesheetarray[] = '/style/' . $app->user()->getUserTheme() . '/main.css';
$stylesheetarray[] = '/style/' . $app->user()->getUserTheme() . '/admin.css';
$app->tpl()->assign('STYLESHEET', $stylesheetarray);

// Set the Sys admin email address
$app->tpl()->assign("SYSADMINEMAIL", $app->config()->read("param.maintainer"));

// Users real name for displaying on web page
$app->tpl()->assign('USERNAME', $app->user()->getRealName());


// Assign AdminAccess Rights to the template
$app->tpl()->assign('ADMINACCESS', $app->usergroups()->getAdminAccess());

// Tell the templates the user has logged in.  This will display the menus
$app->tpl()->assign('LOGIN', false);

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $app->config()->read('param.docbase'),
    $language
);

$app->tpl()->assign("DOCSAVAILABLE", $docsAvailable);

// Check the user has permission to run this module
if (!$app->usergroups()->getAdminAccess() == true) {
    // User is not allowed to display the admin page
    $app->log()->security(
        $app->session()->getUserName() .
        gettext(" attempted to access ") .
        basename(__FILE__)
    );
    $template = 'global/error.tpl';
    $msg = gettext("You are not authorised to access administrative pages.");
    $header =  gettext("Authorisation Required");
    $app->tpl()->assign('ERRORMSG', $msg);
    $app->tpl()->assign('HEADERMSG', $header);
    $dateArray = getdate();
    $app->tpl()->assign("YEAR", "$dateArray[year]");
    $app->tpl()->display($template);
    exit();
}

// Get the menu
$menuitems = $app->config()->readMenu('adminpagelist');
$app->tpl()->assign("PAGELIST", $menuitems);

// Get The users groups
$grouplist = $app->usergroups()->getGroups();
$app->tpl()->assign("GROUPLIST", $grouplist);

// $app->tpl()->debugging = true;
//ASSIGN PERMISIONS TO SHOW MENU ITEMS
$app->tpl()->assign('EDITSETTINGS', $app->usergroups()->checkGroup('admin'));
$app->tpl()->assign('EDITCONFIG', $app->usergroups()->checkGroup('admin'));
$app->tpl()->assign('ABOUT', $app->usergroups()->checkGroup('admin'));
$app->tpl()->assign('EDITUSERS', $app->usergroups()->checkGroup('editusers'));
$app->tpl()->assign('EDITGROUPS', $app->usergroups()->checkGroup('editgroups'));


// Run the module code.  If this case show the Admin Page
$template = 'admin/admin.tpl';

// Get the year for the Copyright Statement
$dateArray = getdate();
$app->tpl()->assign('YEAR', "$dateArray[year]");

// Display the Web Page
$app->tpl()->display($template);
