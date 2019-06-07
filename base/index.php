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

/* Default to the user needing to log in */
$app->tpl()->assign('LOGIN', true);

// Set the Sys admin email address
$app->tpl()->assign("SYSADMINEMAIL", $app->config()->read("param.maintainer"));


// Enable users to request new passwords themselves by displaying new password Link
$app->tpl()->assign("NEWPASSWDENABLED", $app->config()->read('param.users.newpassword'));

// Enable new users to Register themselves bu displaying Register Link
$app->tpl()->assign("SELFREGISTER", $app->config()->read('param.users.newaccount'));

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $app->config()->read('param.docbase'),
    $language
);
$app->tpl()->assign("DOCSAVAILABLE", $docsAvailable);

// Enable or Disable AUTOCOMPLETE
$app->tpl()->assign("AUTOCOMPLETE", $app->config()->read('param.users.autocomplete'));

// Display Links in New Windows
if ($app->config()->read('param.admin.newwindow') == true) {
    $app->tpl()->assign("NEWWINDOW", true);
} else {
    $app->tpl()->assign("NEWWINDOW", false);
}

// Load the menu and assign it to a SMARTY Variable
$mainmenu = $app->config()->readMenu('mainmenu');
$app->tpl()->assign('MAINMENU', $mainmenu);

/* Send the HTTP Headers required by the application */
$headerResult = \webtemplate\application\Header::sendHeaders();
if ($headerResult == false) {
    // Log error is headers not sent
    $app->log()->error(basename(__FILE__) . ":  Failed to send HTTP Headers");
}

// Force user to newpasswrd if they are trying to circumvent the options
if ($app->session()->getPasswdChange() === true) {
    //The user is not logged in. Display a login screen
    header('Location: newpasswd.php');
    exit();
}

if ($app->session()->getUserName() == '') {
    // The user is not logged in.  Lets see what they want to do.
    if (filter_input(INPUT_POST, "Login_Button") !== null) {
        // The user is requesting to log in.
        if ((filter_input(INPUT_POST, 'username') !== null)
            and filter_input(INPUT_POST, 'password') !== null
        ) {
            // The user name and password fields are set.
            // Transfer them to temporary variables and
            // limit length to maximum field size
            $tantedTempUser = substr(filter_input(INPUT_POST, 'username'), 0, 12);
            $tantedTempPass = substr(filter_input(INPUT_POST, 'password'), 0, 20);

            $passwdstrength = $app->config()->read('param.users.passwdstrength');

            // Check that username and password meet our standards.
            $regexp = $app->config()->read('param.users.regexp');
            if ((\webtemplate\general\LocalValidate::username(
                $tantedTempUser,
                $regexp
            ))
                and (\webtemplate\general\LocalValidate::password(
                    $tantedTempPass,
                    $passwdstrength
                ))
            ) {
                $username = $tantedTempUser;
                $password   = $tantedTempPass;

                // Username and password are in a valid format.
                // Try to log the user in
                $userLoggedIn = $app->user()->login(
                    $username,
                    $password,
                    $app->config()->read('param.users.passwdage'),
                    $app->config()->read('pref')
                );
                if (!\webtemplate\general\General::isError($userLoggedIn)) {
                    // User is logged in
                    // Set the Session userName and userId Session Variables
                    //$app->session()->setUserName($username);
                    //$app->session()->setUserID($app->user()->getUserId());
                    $app->session()->createSession($username, $app->user()->getUserId(), false);

                    // Get the users groups
                    $app->usergroups()->loadusersgroups($app->user()->getUserId());

                    // Assign AdminAccess Rights to the template
                    $app->tpl()->assign('ADMINACCESS', $app->usergroups()->getAdminAccess());

                    // Tell template the user is logged in.
                    $app->tpl()->assign('LOGIN', false);

                    // Set the last logged in date
                    $app->tpl()->assign("LASTLOGEDIN", $app->user()->getLastSeenDate());

                    // Set up the users prefered style sheets
                    $stylesheetarray = array();
                    $tempStyle = 'style/';
                    $tempStyle .= $app->user()->getUserTheme();
                    $tempStyle .= '/main.css';
                    $stylesheetarray[] = $tempStyle;
                    $tempStyle = 'style/';
                    $tempStyle .= $app->user()->getUserTheme();
                    $tempStyle .= '/editsettings.css';
                    //$stylesheetarray[] = $tempStyle;
                    $app->tpl()->assign('STYLESHEET', $stylesheetarray);

                    // Set up blank message

                    $msg = '';
                    // If the user is a member of the admin group check to see
                    // if the mandatory parameters are set.
                    // If not display an error.
                    if ($app->usergroups()->checkGroup("admin") == true) {
                        $msg .= \webtemplate\general\General::checkkeyParameters(
                            $app->config()->read("param.urlbase"),
                            $app->config()->read("param.email.emailaddress"),
                            $app->config()->read("param.maintainer")
                        );
                    }

                    // Check Password age if enabled
                    $msg .= $app->user()->getPasswdAgeMsg();

                    //Display Message
                    if ($msg != '') {
                        $app->tpl()->assign("MSG", $msg);
                    }
                } else {
                    // The user was not logged in for some reason.
                    // Display the error message.
                    $app->tpl()->assign('MSG', $userLoggedIn->getMessage());
                    if ($userLoggedIn->getCode() == 1) {
                        $app->log()->error($userLoggedIn->getMessage());
                    } elseif ($userLoggedIn->getCode() == 2) {
                        $app->log()->security("Attempt to Access Locked Account $username");
                    } elseif ($userLoggedIn->getCode() == 3) {
                        $app->log()->security("Failed Login for $username");
                    } elseif ($userLoggedIn->getCode() == 5) {
                        $app->session()->setUserName($username);
                        $app->session()->setPasswdChange(true);
                        //The user is not logged in. Display a login screen
                        header('Location: newpasswd.php');
                        exit();
                    } else {
                        $app->log()->error(
                            "Failed To Register $username during login process"
                        );
                    }
                }
            } else {
                // The user name or password were in an invalid format.
                // Tell the user they failled to log in but not the exact reason.
                $tempMsg =  gettext("Invalid Username and password");
                $app->tpl()->assign('MSG', $tempMsg);
            }
        } else {
            // The User has pressed the return key but not entered a user name
            // or password
            $tempMsg =  gettext("Invalid Username and password");
            $app->tpl()->assign('MSG', $tempMsg);
        }
    }
} else {
    // User is logged in.  Display the main page using User's own preferences.
    $app->tpl()->assign('LOGIN', false);
    $result = $app->user()->register($app->session()->getUserName(), $app->config()->read('pref'));
    if (\webtemplate\general\General::isError($result)) {
        $app->log()->error(
            basename(__FILE__) . ": Failed To Register logged in user $username"
        );
    }

    // Get the users groups
    $app->usergroups()->loadusersgroups($app->user()->getUserId());

    // Assign AdminAccess Rights to the template
    $app->tpl()->assign('ADMINACCESS', $app->usergroups()->getAdminAccess());
    $stylesheetarray = array();
    $stylesheetarray[] = 'style/' . $app->user()->getUserTheme() . '/main.css';
    $app->tpl()->assign('STYLESHEET', $stylesheetarray);

    // If the user is a member of the admin group chek to see if the mandatory
    // parameters are set.  If not display an error.
    if ($app->usergroups()->checkGroup("admin") == true) {
        $msg = \webtemplate\general\General::checkkeyParameters(
            $app->config()->read("param.urlbase"),
            $app->config()->read("param.email.emailaddress"),
            $app->config()->read("param.maintainer")
        );
        if ($msg != '') {
            $app->tpl()->assign("MSG", $msg);
        }
    }
}

// Set the last logged in date
if ($app->user()->getLastSeenDate() > 0) {
    $datetime = substr($app->user()->getLastSeenDate(), 0, 10);
    $datetime .= " at ";
    $datetime .= substr($app->user()->getLastSeenDate(), 11, 5);
    $app->tpl()->assign("LASTLOGEDIN", $datetime);
} else {
     $app->tpl()->assign("LASTLOGEDIN", '');
}

/* Configure the correct user */
$app->tpl()->assign("USERNAME", $app->user()->getRealName());

//$app->tpl()->assign('MSG',"This is a test message.\n\nTo see how the message box works.");
/* Get the year for the Copyright Statement at */
$dateArray = getdate();
$app->tpl()->assign("YEAR", "$dateArray[year]");

/* Display the Web Page.  If the script is called without the user being logged in,
   requesting to be logged in, requesting a new password or requesting to register as
   a user the login page will be displayed */
$app->tpl()->display('global/main.tpl');
