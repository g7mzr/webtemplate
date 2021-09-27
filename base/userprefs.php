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
        $app->log()->error(
            basename(__FILE__) . ":  Failed to send Redirect HTTP Header"
        );
    }
    exit();
}

$userid = $app->user()->getUserId();

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
$app->tpl()->assign("USERNAME", $app->user()->getRealName());

// Assign AdminAccess Rights to the template
$app->tpl()->assign('ADMINACCESS', $app->usergroups()->getAdminAccess());

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \g7mzr\webtemplate\general\General::checkdocs(
    $app->config()->read('param.docbase'),
    $language
);
$app->tpl()->assign("DOCSAVAILABLE", $docsAvailable);

$app->tpl()->assign(
    "PAGETITLE",
    gettext("User Preferences")  . ": " . $app->user()->getUserName()
);

//Set up the page array.  This is a tempory location.
$pagelist = $app->menus()->readMenu('userprefpagelist');

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
$app->tpl()->assign("TAB", $tab);

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

    // Initalise Preferences
    $result = $app->edituserpref()->loadPreferences(
        filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'),
        $app->config()->read('pref')
    );
    if ($result == false) {
            $app->log()->error(
                basename(__FILE__) . ": " . $app->edituserpref()->getLastMsg()
            );
            $template = "global/error.tpl";
            $msg = gettext("Unable to get User Preferences. Please try later");
            $header = gettext("Preference Failure");
            $app->tpl()->assign("ERRORMSG", $msg);
            $app->tpl()->assign("HEADERMSG", $header);
            $app->tpl()->display($template);
    }
    // Save the users Preferences
    if ($action == "save") {
        // Abort the script if the session and page tokens are not the same.
        $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
        $uid = $app->user()->getUserId();
        if ($app->tokens()->verifyToken($inputToken, 'PREFS', $uid) == false) {
            $template = "global/error.tpl";
            $msg = gettext("Security Failure. Settings not updated");
            $header = gettext("Token Check Failure");
            $app->tpl()->assign("ERRORMSG", $msg);
            $app->tpl()->assign("HEADERMSG", $header);
            $app->tpl()->display($template);
            exit();
        }

        $result = $app->edituserpref()->validateUserPreferences($_POST);
        if ($result == true) {
            $result = $app->edituserpref()->checkUserPreferencesChanged();
            $app->tpl()->assign("MSG", $app->edituserpref()->getLastMsg());
            if ($result == true) {
                $result = $app->edituserpref()->saveUserPreferences();
                if ($result == true) {
                    $result = $app->edituserpref()->updatePreferences();
                    if ($result == false) {
                        $app->tpl()->assign(
                            "MSG",
                            $app->edituserpref()->getLastMsg()
                        );
                    }
                    $newTheme = $app->edituserpref()->getNewTheme(
                        $app->config()->read('pref.theme.value')
                    );
                    $stylesheetarray = array();
                    $stylesheetarray[] = 'style/' . $newTheme . '/main.css';
                    $stylesheetarray[] = 'style/' . $newTheme . '/userprefs.css';
                    $app->tpl()->assign('STYLESHEET', $stylesheetarray);
                } else {
                    $app->tpl()->assign("MSG", $app->edituserpref()->getLastMsg());
                }
            } else {
                $app->tpl()->assign("MSG", $app->edituserpref()->getLastMsg());
            }
        } else {
            $app->tpl()->assign("MSG", $app->edituserpref()->getLastMsg());
        }
    }

    // If an invalid command has been chosen display this in the MSG box
    if ($action == "invalid") {
        $app->tpl()->assign("MSG", gettext("You have made an invalid command."));
    }

    // Get the Thems
    $app->tpl()->assign("THEME_ENABLED", $app->config()->read('pref.theme.enabled'));
    $app->tpl()->assign("THEME", $app->edituserpref()->getInstalledThemes());
    // Get Textbox Zoom.
    // Can the user change it.
    $app->tpl()->assign(
        "ZOOM_TEXTAREAS_ENABLED",
        $app->config()->read('pref.zoomtext.enabled')
    );
    $app->tpl()->assign("TEXTAREAS", $app->edituserpref()->getZoomTextOptions());
    // Get the number of rows to sho on a single page
    // Can the user change it
    $app->tpl()->assign(
        "DISPLAY_ROWS_ENABLED",
        $app->config()->read('pref.displayrows.enabled')
    );
    $app->tpl()->assign("DISPLAYROWS", $app->edituserpref()->getDisplayRowsOptions());

    // Mark if the user can change any value
    $enablepreferences = false;
    $localPreferences = $app->config()->read('pref');
    foreach ($localPreferences as $preferenceitem) {
        if ($preferenceitem['enabled'] == true) {
            $enablepreferences = true;
        }
    }
    $app->tpl()->assign("ATLEASTONEPREFERENCE", $enablepreferences);
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
        $uid = $app->user()->getUserId();
        if ($app->tokens()->verifyToken($inputToken, 'PREFS', $uid) == false) {
            $template = "global/error.tpl";
            $msg = gettext("Security Failure. Settings not updated");
            $header = gettext("Token Check Failure");
            $app->tpl()->display($template);
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
        if ($app->user()->checkPasswd($passwd) == true) {
            $resultArray = $app->edituser()->validateUserData(
                $_POST,
                $app->config()->read('param.users'),
                true
            );

            if ($resultArray[0]['msg'] != '') {
                $dataok = false;
            }

            // Check is the returned data is valid
            if ($dataok == true) {
                // Create an edit-user object

                // Save the update data
                $result = $app->edituser()->updateUserDetails(
                    $resultArray[0]["realname"],
                    $resultArray[0]["passwd"],
                    $resultArray[0]["useremail"],
                    $userid
                );
                if (!\g7mzr\webtemplate\general\General::isError($result)) {
                    // Data saved okay
                    $app->tpl()->assign("MSG", gettext("Preferences Updated"));
                    $app->user()->getRealName($resultArray[0]["realname"]);
                    $app->user()->getUserEmail($resultArray[0]["useremail"]);
                } else {
                    // Error saving data.
                    $msg = gettext("Error updating your user preferences.");
                    $app->tpl()->assign("MSG", $msg . "\n" . $result->getMessage());
                }
            } else {
                // Data not validated.  Create an error string and reload the page.
                $msg = $resultArray[0]['msg'];
                $app->tpl()->assign("MSG", $msg);
                //    $app->tpl()->assign("REALNAME", $realname);
                //    $app->tpl()->assign("EMAIL", $usermail);
            }
        } else {
            // The returned current password is is wrong
            // Do not update any of the personal information
            $app->tpl()->assign("MSG", gettext("Invalid Current Password"));
            $app->tpl()->assign("REALNAME", $app->user()->getRealName());
            $app->tpl()->assign("EMAIL", $app->user()->getUserEmail());
        }
    }
    // If an invalid command has been chosen display this in the MSG box
    if ($action == "invalid") {
        $app->tpl()->assign("MSG", gettext("You have made an invalid command"));
    }
    $app->tpl()->assign("REALNAME", $app->user()->getRealName());
    $app->tpl()->assign("EMAIL", $app->user()->getUserEmail());
}

// This is the code to display the contents of the PERMISSIONS TAB
if ($tab == 'permissions') {
    // Get the list of groups
    $grouparray = $app->usergroups()->getGroupDescription();
    if (\g7mzr\webtemplate\general\General::isError($grouparray)) {
        // If it failed publish the error
        $app->tpl()->assign("MSG", $grouparray->getMessage());
    } else {
        $app->tpl()->assign("GROUPRESULT", $grouparray);
    }
}

// Create the token for checking the page authenticition
$localtoken = $app->tokens()->createToken(
    $app->user()->getUserId(),
    'PREFS',
    1,
    '',
    true
);
$app->tpl()->assign("TOKEN", $localtoken);

// Assign the page list
$app->tpl()->assign("PAGELIST", $pagelist);

/* Get the year for the Copyright Statement at */
$dateArray = getdate();
$app->tpl()->assign("YEAR", "$dateArray[year]");

/* Display the Web Page */
$app->tpl()->display($template);
