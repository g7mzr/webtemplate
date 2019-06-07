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

//Set up the users web page style
$stylesheetarray = array();
$stylesheetarray[] = '/style/' . $app->user()->getUserTheme() . '/main.css';
$stylesheetarray[] = '/style/' . $app->user()->getUserTheme() . '/editgroup.css';
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

//Assign default READONLY flag
$app->tpl()->assign("READONLY", '');

// Check the user can access this page
if (!$app->usergroups()->checkGroup("editgroups") == true) {
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

// Check if user has requested that the parameters be updated.
// If $_POST['action'] is not set display the current values
// If $_POST['action'] = save then set action to save
// if $_POST['action'] is not set to save then the action is invalid
if (\filter_input(INPUT_GET, 'action') !== null) {
    $tempAction = \filter_input(INPUT_GET, 'action');
} elseif (\filter_input(INPUT_POST, 'action') !== null) {
    $tempAction = \filter_input(INPUT_POST, 'action');
} else {
    $tempAction = 'list';
}

$teststr = "/^(new)|(edit)|(del)|(save)|(showdel)|(list)$/";
if (preg_match($teststr, $tempAction, $regs)) {
    $action = $tempAction;
} else {
    $action = "invalid";
}

// Create the template name variable and set it to empty
$template = '';

if ($action == "new") {
    \webtemplate\groups\EditGroupFunctions::newGroup($app->tpl(), $template);
} elseif ($action == "edit") {
    if (\filter_input(INPUT_GET, 'groupid') !== null) {
        $groupId = substr(\filter_input(INPUT_GET, 'groupid'), 0, 5);
    } else {
        $groupId = '';
    }

    $result = \webtemplate\groups\EditGroupFunctions::editGroup(
        $app->tpl(),
        $app->editgroups(),
        $template,
        $groupId
    );
    if (($result === false) and ($template != 'global/error.tpl')) {
        \webtemplate\groups\EditGroupFunctions::listGroups(
            $app->tpl(),
            $app->editgroups(),
            $template
        );
    }
} elseif ($action == "showdel") {
    if (\filter_input(INPUT_GET, 'groupid') !== null) {
        $groupId = substr(\filter_input(INPUT_GET, 'groupid'), 0, 5);
    } else {
        $groupId = '';
    }
    $result = \webtemplate\groups\EditGroupFunctions::confirmDeleteGroup(
        $app->tpl(),
        $app->editgroups(),
        $template,
        $groupId
    );
    if (($result === false) and ($template != 'global/error.tpl')) {
        \webtemplate\groups\EditGroupFunctions::listGroups($app->tpl(), $app->editgroups(), $template);
    }
} elseif ($action == "del") {
    // Abort the script if the session and page tokens are not the same.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = $app->user()->getUserId();
    if ($app->tokens()->verifyToken($inputToken, 'GROUPS', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Parameters not updated");
        $header = gettext("Token Check Failure");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }
    if (\filter_input(INPUT_POST, 'groupid') !== null) {
        $groupId = substr(\filter_input(INPUT_POST, 'groupid'), 0, 5);
    } else {
        $groupId = '';
    }

    $result = \webtemplate\groups\EditGroupFunctions::deleteGroup(
        $app->tpl(),
        $app->editgroups(),
        $template,
        $groupId
    );
    if ($template != 'global/error.tpl') {
        \webtemplate\groups\EditGroupFunctions::listGroups(
            $app->tpl(),
            $app->editgroups(),
            $template
        );
    }
} elseif ($action == "save") {
    // Abort the script if the session and page tokens are not the same.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = $app->user()->getUserId();
    if ($app->tokens()->verifyToken($inputToken, 'GROUPS', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Parameters not updated");
        $header = gettext("Token Check Failure");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }
    \webtemplate\groups\EditGroupFunctions::saveGroup(
        $app->tpl(),
        $app->editgroups(),
        $template,
        $_POST
    );
} elseif ($action == "list") {
    \webtemplate\groups\EditGroupFunctions::listGroups(
        $app->tpl(),
        $app->editgroups(),
        $template
    );
} else {
    // Invalid comman entered
    $app->tpl()->assign("MSG", gettext("Invalid Command"));
    $groupArray = $app->editgroups()->getGroupList();
    if (!\webtemplate\general\General::isError($groupArray)) {
        $app->tpl()->assign("RESULTS", $groupArray);
        $template = "groups/list.tpl";
    } else {
        $template = "global/error.tpl";
        $msg = gettext("Error Encountered When Loading Groups");
        $header = gettext("Database Error");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
    }
}

// Create the token for checking the page authenticition
$localtoken = $app->tokens()->createToken(
    $app->user()->getUserId(),
    'GROUPS',
    1,
    '',
    true
);
$app->tpl()->assign("TOKEN", $localtoken);

//Get the year for the Copyright Statement
$dateArray = getdate();
$app->tpl()->assign("YEAR", "$dateArray[year]");

// Display the Web Page
$app->tpl()->display($template);
