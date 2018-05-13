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

// Create new Token Class
$token = new \webtemplate\general\Tokens($tpl, $db);

//$tpl->debugging = true;

//Create new config class
$config = new \webtemplate\config\Configure($db);

//Create the logclass
$log = new \webtemplate\general\Log(
    $config->read('param.admin.logging'),
    $config->read('param.admin.logrotate')
);
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

$userid = $user->getUserId();

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
$tpl->assign('LOGIN', 'false');

// Users real name for displaying on web page
$tpl->assign("USERNAME", $user->getRealName());

// Assign AdminAccess Rights to the template
$tpl->assign('ADMINACCESS', $userGroups->getAdminAccess());

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $config->read('param.docbase'),
    $language
);
$tpl->assign("DOCSAVAILABLE", $docsAvailable);

$tpl->assign("PAGETITLE", gettext("User Preferences")  . ": " .$user->getUserName());

// This module does not need to check if user can edit their own preferences.
// Check if an action has been performed
$groups = new \webtemplate\groups\EditUsersGroups($db, $tpl);

//Set up the page array.  This is a tempory location.
$pagelist = array(
    "settings" => array(
        "description" => "General Preferences",
        "template" => "users/preferences/settings.tpl",
        "url" => "userprefs.php?tab=setting",
        "selected" => false
    ),
    "account" => array(
        "description" => "Name and Password",
        "template" => "users/preferences/account.tpl",
        "url" => "userprefs.php?tab=account",
        "selected" => false
    ),
    "permissions" => array(
        "description" => "Permissions",
        "template" => "users/preferences/permissions.tpl",
        "url" => "userprefs.php?tab=permissions",
        "selected" => false
    )
);

// Get which TAB has been selected.
// If no TAB or Invalid TAB selected default to settings
// Check what the user wants to do
if (\filter_input(INPUT_GET, 'tab') !== null) {
    $tempTab = \filter_input(INPUT_GET, 'tab');
} elseif (\filter_input(INPUT_POST, 'tab') !== null) {
    $tempTab = \filter_input(INPUT_POST, 'tab');
} else {
    $tempTab = 'settings';
}


$tabList = "/(settings)|(account)|(permissions)/";
if (preg_match($tabList, $tempTab, $regs) == true) {
    $tab = $tempTab;
} else {
    $tab = 'settings';
}

// Set the selected TAB and Default Template for User Preferences.
$tpl->assign("TAB", $tab);

$template = $pagelist[$tab]['template'];
$pagelist[$tab]['selected'] = true;

// This is the code to display and save the contents of the settings TAB
if ($tab == 'settings') {
    // Check if ACTION has been set and if it conatins a valid choice
    if (\filter_input(INPUT_POST, 'action') !== null) {
        if (preg_match("/^(save)$/", \filter_input(INPUT_POST, 'action'), $regs)) {
            $action = \filter_input(INPUT_POST, 'action');
        } else {
            $action = "invalid";
        }
    } else {
        $action = "new_page";
    }

    $myPrefs = new \webtemplate\users\EditUserPref($db, $user->getUserId());

    // Initalise Preferences
    $result = $myPrefs->loadPreferences(
        filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'),
        $config->read('pref')
    );
    if ($result == false) {
            $log->error(basename(__FILE__) . ": " . $myPrefs->getLastMsg());
            $template = "global/error.tpl";
            $msg = gettext("Unable to get User Preferences. Please try later");
            $header = gettext("Preference Failure");
            $tpl->assign("ERRORMSG", $msg);
            $tpl->assign("HEADERMSG", $header);
            $tpl->display($template);
    }
    // Save the users Preferences
    if ($action == "save") {
        // Abort the script if the session and page tokens are not the same.
        $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
        $uid = $user->getUserId();
        if ($token->verifyToken($inputToken, 'PREFS', $uid) == false) {
            $template = "global/error.tpl";
            $msg = gettext("Security Failure. Settings not updated");
            $header = gettext("Token Check Failure");
            $tpl->assign("ERRORMSG", $msg);
            $tpl->assign("HEADERMSG", $header);
            $tpl->display($template);
            exit();
        }

        $result = $myPrefs->validateUserPreferences($_POST);
        if ($result == true) {
            $result = $myPrefs->checkUserPreferencesChanged();
            $tpl->assign("MSG", $myPrefs->getLastMsg());
            if ($result == true) {
                $result = $myPrefs->saveUserPreferences();
                if ($result == true) {
                    $result = $myPrefs->updatePreferences();
                    if ($result == false) {
                        $tpl->assign("MSG", $myPrefs->getLastMsg());
                    }
                    $newTheme = $myPrefs->getNewTheme(
                        $config->read('pref.theme.value')
                    );
                    $stylesheetarray = array();
                    $stylesheetarray[] = 'style/' . $newTheme . '/main.css';
                    $stylesheetarray[] = 'style/' . $newTheme . '/userprefs.css';
                    $tpl->assign('STYLESHEET', $stylesheetarray);
                } else {
                    $tpl->assign("MSG", $myPrefs->getLastMsg());
                }
            } else {
                $tpl->assign("MSG", $myPrefs->getLastMsg());
            }
        } else {
            $tpl->assign("MSG", $myPrefs->getLastMsg());
        }
    }

    // If an invalid command has been chosen display this in the MSG box
    if ($action == "invalid") {
        $tpl->assign("MSG", gettext("You have made an invalid command."));
    }

    // Get the Thems
    $tpl->assign("THEME_ENABLED", $config->read('pref.theme.enabled'));
    $tpl->assign("THEME", $myPrefs->getInstalledThemes());
    // Get Textbox Zoom.
    // Can the user change it.
    $tpl->assign("ZOOM_TEXTAREAS_ENABLED", $config->read('pref.zoomtext.enabled'));
    $tpl->assign("TEXTAREAS", $myPrefs->getZoomTextOptions());
    // Get the number of rows to sho on a single page
    // Can the user change it
    $tpl->assign(
        "DISPLAY_ROWS_ENABLED",
        $config->read('pref.displayrows.enabled')
    );
    $tpl->assign("DISPLAYROWS", $myPrefs->getDisplayRowsOptions());

    // Mark if the user can change any value
    $enablepreferences = false;
    $localPreferences = $config->read('pref');
    foreach ($localPreferences as $preferenceitem) {
        if ($preferenceitem['enabled'] == true) {
            $enablepreferences = true;
        }
    }
    $tpl->assign("ATLEASTONEPREFERENCE", $enablepreferences);
}


// This is the code to display and save the contents of the ACCOUNT TAB
// Check if a valid command has been chosed
if ($tab == 'account') {
    if (\filter_input(INPUT_POST, 'action') !== null) {
        if (preg_match("/^(save)$/", \filter_input(INPUT_POST, 'action'), $regs)) {
            $action = \filter_input(INPUT_POST, 'action');
        } else {
            $action = "invalid";
        }
    } else {
        $action = "new_page";
    }

    // Check if data is to be saved
    if (preg_match("/(save)/", $action, $regs) == true) {
        // Check if the stored token matches the returned one
        $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
        $uid = $user->getUserId();
        if ($token->verifyToken($inputToken, 'PREFS', $uid) == false) {
            $template = "global/error.tpl";
            $msg = gettext("Security Failure. Settings not updated");
            $header = gettext("Token Check Failure");
            $tpl->display($template);
            exit();
        }

        // Get the users current password from the page
        if (\filter_input(INPUT_POST, 'current_password') !== null) {
            $passwd = substr(\filter_input(INPUT_POST, 'current_password'), 0, 20);
        } else {
            $passwd = '';
        }

        // Set up local variables
        $dataok = true;

        // Check if the returned password is correct
        if ($user->checkPasswd($passwd) == true) {
            $edituser = new \webtemplate\users\EditUser($db, $tpl);

            $resultArray = $edituser->validateUserData(
                $_POST,
                $config->read('param.users'),
                true
            );

            if ($resultArray[0]['msg'] != '') {
                $dataok = false;
            }

            // Check is the returned data is valid
            if ($dataok == true) {
                // Create an edit-user object

                // Save the update data
                $result = $edituser->updateUserDetails(
                    $resultArray[0]["realname"],
                    $resultArray[0]["passwd"],
                    $resultArray[0]["useremail"],
                    $userid
                );
                if (!\webtemplate\general\General::isError($result)) {
                    // Data saved okay
                    $tpl->assign("MSG", gettext("Preferences Updated"));
                    $user->getRealName($resultArray[0]["realname"]);
                    $user->getUserEmail($resultArray[0]["useremail"]);
                } else {
                    // Error saving data.
                    $msg = gettext("Error updating your user preferences.");
                    $tpl->assign("MSG", $msg . "\n". $result->getMessage());
                }
            } else {
                // Data not validated.  Create an error string and reload the page.
                $msg = $resultArray[0]['msg'];
                $tpl->assign("MSG", $msg);
                //    $tpl->assign("REALNAME", $realname);
                //    $tpl->assign("EMAIL", $usermail);
            }
        } else {
            // The returned current password is is wrong
            // Do not update any of the personal information
            $tpl->assign("MSG", gettext("Invalid Current Password"));
            $tpl->assign("REALNAME", $user->getRealName());
            $tpl->assign("EMAIL", $user->getUserEmail());
        }
    }
    // If an invalid command has been chosen display this in the MSG box
    if ($action == "invalid") {
        $tpl->assign("MSG", gettext("You have made an invalid command"));
    }
    $tpl->assign("REALNAME", $user->getRealName());
    $tpl->assign("EMAIL", $user->getUserEmail());
}

// This is the code to display the contents of the PERMISSIONS TAB
if ($tab == 'permissions') {
    // Get the list of groups
    $grouparray = $userGroups->getGroupDescription();
    if (\webtemplate\general\General::isError($grouparray)) {
        // If it failed publish the error
        $tpl->assign("MSG", $grouparray->getMessage());
    } else {
        $tpl->assign("GROUPRESULT", $grouparray);
    }
}

// Create the token for checking the page authenticition
$localtoken = $token->createToken($user->getUserId(), 'PREFS', 1, ''. true);
$tpl->assign("TOKEN", $localtoken);

// Assign the page list
$tpl->assign("PAGELIST", $pagelist);

/* Get the year for the Copyright Statement at */
$dateArray = getdate();
$tpl->assign("YEAR", "$dateArray[year]");

/* Display the Web Page */
$tpl->display($template);
