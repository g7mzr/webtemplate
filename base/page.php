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
    $app = new \g7mzr\webtemplate\application\Application();
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
$mainmenu = $app->menus()->readMenu('mainmenu');
$app->tpl()->assign('MAINMENU', $mainmenu);

/* Send the HTTP Headers required by the application */
$headerResult = \g7mzr\webtemplate\application\Header::sendHeaders();
if ($headerResult == false) {
    // Log error is headers not sent
    $app->log()->error(basename(__FILE__) . ":  Failed to send HTTP Headers");
}

// Check if the user is logged in.
if ($app->session()->getUserName() == '') {
    // Tell the templates the user has not logged in.
    $app->tpl()->assign('LOGIN', true);

    // Get the Default style
    $stylesheetarray = array();
    $stylesheetarray[] = 'style/' . $app->config()->read('pref.theme.value') . '/main.css';
    $stylesheetarray[] = 'style/' . $app->config()->read('pref.theme.value') . '/page.css';
    $app->tpl()->assign('STYLESHEET', $stylesheetarray);
} else {
    // Tell the templates the user has logged in.  This will display the menus
    $app->tpl()->assign('LOGIN', false);

    // Get the users select style
    $stylesheetarray = array();
    $stylesheetarray[] = 'style/' . $app->user()->getUserTheme() . '/main.css';
    $stylesheetarray[] = 'style/' . $app->user()->getUserTheme() . '/page.css';
    $app->tpl()->assign('STYLESHEET', $stylesheetarray);


    // Users real name for displaying on web page
    $app->tpl()->assign('USERNAME', $app->user()->getRealName());

    // Assign AdminAccess Rights to the template
    $app->tpl()->assign('ADMINACCESS', $app->usergroups()->getAdminAccess());
}

// Set the Sys admin email address
$app->tpl()->assign("SYSADMINEMAIL", $app->config()->read("param.maintainer"));

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \g7mzr\webtemplate\general\General::checkdocs(
    $app->config()->read('param.docbase'),
    $language
);

$app->tpl()->assign("DOCSAVAILABLE", $docsAvailable);


//DO WHAT EVER THIS MODULE NEEDS TO DO  ^[\w,\s-]+\.[A-Za-z]{3}$
$pageFound = false;
if (\filter_input(INPUT_GET, "id") !== null) {
    // Get the maximum file length allowed

    $fileName = substr(\filter_input(INPUT_GET, "id"), 0, 30);
    // Check id is a valid file name
    if (\g7mzr\webtemplate\general\LocalValidate::htmlFile($fileName)) {
        // Create the template
        $template = 'pages/' . $fileName . '.tpl';

        // Check template exists
        if ($app->tpl()->templateExists($template)) {
            $pageFound = true;
        }
    }
}

// Display an error Message if a page is not found.
if ($pageFound == false) {
    $logmsg = $app->user()->getUserName() . " requested " .   $fileName;
    $app->log()->security("page.php: " . $logmsg);
    $template = 'global/error.tpl';
    $msg = gettext("The Page you have requested ($fileName) was not found.");
    $header =  gettext("Page Not Found");
    $app->tpl()->assign('ERRORMSG', $msg);
    $app->tpl()->assign('HEADERMSG', $header);
}

// Get the year for the Copyright Statement
$dateArray = getdate();
$app->tpl()->assign('YEAR', "$dateArray[year]");

// Display the Web Page
$app->tpl()->display($template);
