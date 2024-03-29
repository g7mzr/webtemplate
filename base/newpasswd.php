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
$stylesheetarray[] = 'style/' . $app->user()->getUserTheme() . '/main.css';
$stylesheetarray[] = 'style/' . $app->user()->getUserTheme() . '/userprefs.css';
$app->tpl()->assign('STYLESHEET', $stylesheetarray);

// Set the Sys admin email address
$app->tpl()->assign("SYSADMINEMAIL", $app->config()->read("param.maintainer"));

// Restrict the menuses to the non logged in ones
$app->tpl()->assign('LOGIN', true);

// Users real name for displaying on web page
$app->tpl()->assign('USERNAME', $app->user()->getRealName());

// Assign AdminAccess Rights to the template
$app->tpl()->assign('ADMINACCESS', $app->usergroups->getAdminAccess());

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \g7mzr\webtemplate\general\General::checkdocs(
    $app->config()->read('param.docbase'),
    $language
);

$app->tpl()->assign("DOCSAVAILABLE", $docsAvailable);


//DO WHAT EVER THIS MODULE NEEDS TO DO

// Check if user has requested that the parameters be updated.
// If $_POST['action'] is not set display the current values
// If $_POST['action'] = save then set action to save
// if $_POST['action'] is not set to save then the action is invalid
if (\filter_input(INPUT_GET, 'action') !== null) {
    $tempAction = \filter_input(INPUT_GET, 'action');
} elseif (\filter_input(INPUT_POST, 'action') !== null) {
    $tempAction = \filter_input(INPUT_POST, 'action');
} else {
    $tempAction = 'new_page';
}

$teststr = "/^(new)|(save)|(new_page)$/";
if (preg_match($teststr, $tempAction, $regs)) {
    $action = $tempAction;
} else {
    $action = "invalid";
}

if ($action == "save") {
    // Abort the script if the session and page tokens are not the same.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = $app->user()->getUserId();
    if ($app->tokens()->verifyToken($inputToken, 'NEWPASS', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Unable to complete request");
        $header = gettext("Token Check Failure");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }

    if (\filter_input(INPUT_POST, 'cancel') !== null) {
        header('Location: logout.php');
        exit();
    }

    $passwordUpdated = false;
    $temppass1 = substr(\filter_input(INPUT_POST, 'passwd'), 0, 20);
    $temppass2 = substr(\filter_input(INPUT_POST, 'passwd2'), 0, 20);
    $userid      = substr(\filter_input(INPUT_POST, 'userid'), 0, 10);
    $app->tpl()->assign("USERID", $app->session()->getUserName());

    $passwdstrength = $app->config()->read('param.users.passwdstrength');
    if (
        (LocalValidate::password($temppass1, $passwdstrength))
        and ($temppass1 == $temppass2)
    ) {
        $passwdstatus = $app->edituser()->updatePasswd(
            $app->session()->getUserName(),
            $temppass1
        );
        if (!\g7mzr\webtemplate\general\General::isError($passwdstatus)) {
            $app->tpl()->assign("ENTERPASSWORD", false);
            $app->tpl()->assign("CONFIRMPASSWORD", true);
            $app->tpl()->assign("PAGETITLE", gettext("Password Updated"));
            $passwordUpdated = true;
            $app->session()->setPasswdChange(false);
        }
        if ($passwordUpdated == false) {
            // Get the year for the Copyright Statement
            $dateArray = getdate();
            $app->tpl()->assign('YEAR', "$dateArray[year]");
            $str = gettext("Forced password change failed. Userid");
            $app->log()->error($str . " = " . $userid);
            $template = "global/error.tpl";
            $msg = gettext("Unable to change password.  Please try again");
            $header = gettext("Password change failed");
            $app->tpl()->assign("ERRORMSG", $msg);
            $app->tpl()->assign("HEADERMSG", $header);
            $app->tpl()->display($template);
            exit();
        }
    } else {
        $msg = gettext("Unable to validate passwords.\n");
        $msg .= GeneralCode::passwdFormat(
            $app->config()->read('param.users.passwdstrength')
        );
        $msg .= "\n";
        $msg .= gettext("and both passwords are the same");
        $app->tpl()->assign("MSG", $msg);
        $app->tpl()->assign("ENTERPASSWORD", true);
        $app->tpl()->assign("CONFIRMPASSWORD", false);
    }
} else {
    $app->tpl()->assign("ENTERPASSWORD", true);
    $app->tpl()->assign("CONFIRMPASSWORD", false);
    $app->tpl()->assign("PAGETITLE", gettext("Update Password"));
    $app->tpl()->assign("USERID", $app->session()->getUserName());
    $msg = gettext("Your password has expired.");
    $msg .= " " . gettext("It must be changed before you can continue");
    $app->tpl()->assign("MSG", $msg);
}

$template = 'users/newpasswd.tpl';

// Create the token for checking the page authenticition
$localtoken = $app->tokens()->createToken($app->user()->getUserId(), 'NEWPASS', 1, '' . true);
$app->tpl()->assign("TOKEN", $localtoken);

// Get the year for the Copyright Statement
$dateArray = getdate();
$app->tpl()->assign('YEAR', "$dateArray[year]");

// Display the Web Page
$app->tpl()->display($template);
