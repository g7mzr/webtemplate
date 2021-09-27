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
    //The user is not logged in. Display a login screen
    $headerResult = \g7mzr\webtemplate\application\Header::sendRedirect('index.php');
    if ($headerResult == false) {
        // Log error is headers not sent
        $app->log()->error(basename(__FILE__) . ":  Failed to send Redirect HTTP Header");
    }
    exit();
}

// Get the users select style
$stylesheetarray = array();
$stylesheetarray[] = '/style/' . $app->user()->getUserTheme() . '/main.css';
$stylesheetarray[] = '/style/' . $app->user()->getUserTheme() . '/editconfig.css';
$app->tpl()->assign('STYLESHEET', $stylesheetarray);

// Set the Sys admin email address
$app->tpl()->assign("SYSADMINEMAIL", $app->config()->read("param.maintainer"));

// Tell the templates the user has logged in.  This will display the menus
$app->tpl()->assign('LOGIN', false);

// Users real name for displaying on web page
$app->tpl()->assign("USERNAME", $app->user()->getRealName());

// Assign AdminAccess Rights to the template
$app->tpl()->assign('ADMINACCESS', $app->usergroups()->getAdminAccess());

// Set the current user's text box zoom settings
$app->tpl()->assign("ZOOMON", $app->user()->getUserZoom());

//Set default readonly tag for the username
$app->tpl()->assign("READONLY", false);

//Set the default referer
$app->tpl()->assign("REFERER", '');

//Set the default Group Result
$app->tpl()->assign("GROUPRESULT", "");

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \g7mzr\webtemplate\general\General::checkdocs(
    $app->config()->read('param.docbase'),
    $language
);

$app->tpl()->assign("DOCSAVAILABLE", $docsAvailable);

// Check the user has permission to run this module
if (!$app->usergroups()->checkGroup("editusers") == true) {
    // User is not allowed to display the admin page
    $app->log()->security(
        $app->session()->getUserName() .
        gettext(" attempted to access ") . basename(__FILE__)
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

// Set Password aging
if ($app->config()->read('param.users.passwdage') == '1') {
    $app->tpl()->assign("PASSWDAGING", false);
} else {
    $app->tpl()->assign("PASSWDAGING", true);
}

$template = "";

//  DO WHAT EVER THIS MODULE NEEDS TO DO
// Check what the user wants to do
if (\filter_input(INPUT_GET, 'action') !== null) {
    $tempAction = \filter_input(INPUT_GET, 'action');
} elseif (\filter_input(INPUT_POST, 'action') !== null) {
    $tempAction = \filter_input(INPUT_POST, 'action');
} else {
    $tempAction = '';
}

if ($tempAction != '') {
    $teststr = "/^(new)|(edit)|(list)|(save)$/";
    if (preg_match($teststr, $tempAction, $regs) == true) {
        $action = $tempAction;
    } else {
        $action = "invalid";
    }

    if ($action == "list") {
        \g7mzr\webtemplate\users\EditUserFunctions::listUsers(
            $app->tpl(),
            $template,
            $app->edituser(),
            $app->config()->read('param.users.regexp'),
            $_GET
        );
    } elseif ($action == "new") {
        \g7mzr\webtemplate\users\EditUserFunctions::newUser($app->tpl(), $template);
    } elseif ($action == "edit") {
        // Set the link to return to the search page
        if (isset($_SERVER["HTTP_REFERER"])) {
            $referer = $_SERVER["HTTP_REFERER"];
        } else {
            $referer = '';
        }
        // Set the page referer
        $app->tpl()->assign("REFERER", $referer);
        \g7mzr\webtemplate\users\EditUserFunctions::editUser(
            $app->tpl(),
            $template,
            $app->edituser(),
            $app->editusersgroups(),
            $_GET
        );
    } elseif ($action == "save") {
        $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
        $uid = $app->user()->getUserId();
        if ($app->tokens()->verifyToken($inputToken, 'USERS', $uid) == false) {
            $template = "global/error.tpl";
            $msg = gettext("Security Failure. User Details not saved");
            $header = gettext("Token Check Failure");
            $app->tpl()->assign("ERRORMSG", $msg);
            $app->tpl()->assign("HEADERMSG", $header);
            $app->tpl()->display($template);
            exit();
        }
        \g7mzr\webtemplate\users\EditUserFunctions::saveUser(
            $app->tpl(),
            $template,
            $app->edituser(),
            $app->editusersgroups(),
            $app->config()->read("param.users"),
            $_POST
        );
    } else {
        $app->tpl()->assign("MSG", gettext("You Have Chosen an Invalid Command"));
        $template = "users/search.tpl";
    }
} else {
    $template = "users/search.tpl";
}

// Create the token for checking the page authenticition
$localtoken = $app->tokens()->createToken($app->user()->getUserId(), 'USERS', 1, '' . true);
$app->tpl()->assign("TOKEN", $localtoken);

// Get the year for the Copyright Statement at
$dateArray = getdate();
$app->tpl()->assign("YEAR", "$dateArray[year]");

// Display the Web Page
$app->tpl()->display($template);
