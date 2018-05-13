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
$tpl = new \webtemplate\application\SmartyTemplate();

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
$db = \webtemplate\db\DB::load($dsn);
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

//Create new config class
$config = new \webtemplate\config\Configure($db);

//Create the logclass
$log = new \webtemplate\general\Log(
    $config->read('param.admin.logging'),
    $config->read('param.admin.logrotate')
);

/* Default to the user needing to log in */
$tpl->assign('LOGIN', 'true');

// Set the Sys admin email address
$tpl->assign("SYSADMINEMAIL", $config->read("param.maintainer"));


// Enable users to request new passwords themselves by displaying new password Link
$tpl->assign("NEWPASSWDENABLED", $config->read('param.users.newpassword'));

// Enable new users to Register themselves bu displaying Register Link
$tpl->assign("SELFREGISTER", $config->read('param.users.newaccount'));

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $config->read('param.docbase'),
    $language
);
$tpl->assign("DOCSAVAILABLE", $docsAvailable);

// Enable or Disable AUTOCOMPLETE
$tpl->assign("AUTOCOMPLETE", $config->read('param.users.autocomplete'));

// Display Links in New Windows
if ($config->read('param.admin.newwindow') == true) {
    $tpl->assign("NEWWINDOW", true);
} else {
    $tpl->assign("NEWWINDOW", false);
}

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

/* Create a user object for the current user */
$user = new \webtemplate\users\User($db);

// Force user to newpasswrd if they are trying to circumvent the options
if ($session->getPasswdChange() === true) {
    //The user is not logged in. Display a login screen
    header('Location: newpasswd.php');
    exit();
}

if ($session->getUserName() == '') {
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

            $passwdstrength = $config->read('param.users.passwdstrength');

            // Check that username and password meet our standards.
            $regexp = $config->read('param.users.regexp');
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
                $userLoggedIn = $user->login(
                    $username,
                    $password,
                    $config->read('param.users.passwdage'),
                    $config->read('pref')
                );
                if (!\webtemplate\general\General::isError($userLoggedIn)) {
                    // User is logged in
                    // Set the Session userName and userId Session Variables
                    //$session->setUserName($username);
                    //$session->setUserID($user->getUserId());
                    $session->createSession($username, $user->getUserId(), false);

                    // Get the users groups
                    $userGroups = new \webtemplate\users\Groups(
                        $db,
                        $user->getUserId()
                    );

                    // Assign AdminAccess Rights to the template
                    $tpl->assign('ADMINACCESS', $userGroups->getAdminAccess());

                    // Tell template the user is logged in.
                    $tpl->assign('LOGIN', 'false');

                    // Set the last logged in date
                    $tpl->assign("LASTLOGEDIN", $user->getLastSeenDate());

                    // Set up the users prefered style sheets
                    $stylesheetarray = array();
                    $tempStyle = 'style/';
                    $tempStyle .= $user->getUserTheme();
                    $tempStyle .= '/main.css';
                    $stylesheetarray[] = $tempStyle;
                    $tempStyle = 'style/';
                    $tempStyle .= $user->getUserTheme();
                    $tempStyle .= '/editsettings.css';
                    //$stylesheetarray[] = $tempStyle;
                    $tpl->assign('STYLESHEET', $stylesheetarray);

                    // Set up blank message

                    $msg = '';
                    // If the user is a member of the admin group check to see
                    // if the mandatory parameters are set.
                    // If not display an error.
                    if ($userGroups->checkGroup("admin") == true) {
                        $msg .= \webtemplate\general\General::checkkeyParameters(
                            $config->read("param.urlbase"),
                            $config->read("param.email.emailaddress"),
                            $config->read("param.maintainer")
                        );
                    }

                    // Check Password age if enabled
                    $msg .= $user->getPasswdAgeMsg();

                    //Display Message
                    if ($msg != '') {
                        $tpl->assign("MSG", $msg);
                    }
                } else {
                    // The user was not logged in for some reason.
                    // Display the error message.
                    $tpl->assign('MSG', $userLoggedIn->getMessage());
                    if ($userLoggedIn->getCode() == 1) {
                        $log->error($userLoggedIn->getMessage());
                    } elseif ($userLoggedIn->getCode() == 2) {
                        $log->security("Attempt to Access Locked Account $username");
                    } elseif ($userLoggedIn->getCode() == 3) {
                        $log->security("Failed Login for $username");
                    } elseif ($userLoggedIn->getCode() == 5) {
                        $session->setUserName($username);
                        $session->setPasswdChange(true);
                        //The user is not logged in. Display a login screen
                        header('Location: newpasswd.php');
                        exit();
                    } else {
                        $log->error(
                            "Failed To Register $username during login process"
                        );
                    }
                }
            } else {
                // The user name or password were in an invalid format.
                // Tell the user they failled to log in but not the exact reason.
                $tempMsg =  gettext("Invalid Username and password");
                $tpl->assign('MSG', $tempMsg);
            }
        } else {
            // The User has pressed the return key but not entered a user name
            // or password
            $tempMsg =  gettext("Invalid Username and password");
            $tpl->assign('MSG', $tempMsg);
        }
    }
} else {
    // User is logged in.  Display the main page using User's own preferences.
    $tpl->assign('LOGIN', 'false');
    $result = $user->register($session->getUserName(), $config->read('pref'));
    if (\webtemplate\general\General::isError($result)) {
        $log->error(
            basename(__FILE__) . ": Failed To Register logged in user $username"
        );
    }

    // Get the users groups
    $userGroups = new \webtemplate\users\Groups($db, $user->getUserId());
    // Assign AdminAccess Rights to the template
    $tpl->assign('ADMINACCESS', $userGroups->getAdminAccess());
    $stylesheetarray = array();
    $stylesheetarray[] = 'style/' . $user->getUserTheme() . '/main.css';
    $tpl->assign('STYLESHEET', $stylesheetarray);

    // If the user is a member of the admin group chek to see if the mandatory
    // parameters are set.  If not display an error.
    if ($userGroups->checkGroup("admin") == true) {
        $msg = \webtemplate\general\General::checkkeyParameters(
            $config->read("param.urlbase"),
            $config->read("param.email.emailaddress"),
            $config->read("param.maintainer")
        );
        if ($msg != '') {
            $tpl->assign("MSG", $msg);
        }
    }
}

// Set the last logged in date
if ($user->getLastSeenDate() > 0) {
    $datetime = substr($user->getLastSeenDate(), 0, 10);
    $datetime .= " at ";
    $datetime .= substr($user->getLastSeenDate(), 11, 5);
    $tpl->assign("LASTLOGEDIN", $datetime);
} else {
     $tpl->assign("LASTLOGEDIN", '');
}

/* Configure the correct user */
$tpl->assign("USERNAME", $user->getRealName());

//$tpl->assign('MSG',"This is a test message.\n\nTo see how the message box works.");
/* Get the year for the Copyright Statement at */
$dateArray = getdate();
$tpl->assign("YEAR", "$dateArray[year]");

$db->disconnect();

/* Display the Web Page.  If the script is called without the user being logged in,
   requesting to be logged in, requesting a new password or requesting to register as
   a user the login page will be displayed */
$tpl->display('global/main.tpl');
