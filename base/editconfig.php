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

/**
 * Global Configuration Functions
 *
 * @category  Admin
 * @package   Edit_Configuration
 * @author    Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright 2010 Sandy McNeil
 * @license   View the license file distributed with this source code
 * @link      http://www.g7mzr.demon.co.uk
 */

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
$mainmenu = $app->config()->readMenu('mainmenu');
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

// Assign zoom textboxes
$app->tpl()->assign("ZOOMON", $app->user()->getUserZoom());

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \g7mzr\webtemplate\general\General::checkdocs(
    $app->config()->read('param.docbase'),
    $language
);
$app->tpl()->assign("DOCSAVAILABLE", $docsAvailable);

// Get the year for the Copyright Statement
$dateArray = getdate();
$app->tpl()->assign("YEAR", "$dateArray[year]");


// Check the user has permission to run this module
if (!$app->usergroups()->checkGroup("admin") == true) {
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

//Set up the page title.
$app->tpl()->assign("PAGETITLE", gettext("Edit Configuration"));

// Set page to open on the requested section.
// If no section selected default to required .
if (\filter_input(INPUT_GET, 'section') !== null) {
    $tempSection = \filter_input(INPUT_GET, 'section');
} elseif (\filter_input(INPUT_POST, 'section') !== null) {
    $tempSection = \filter_input(INPUT_POST, 'section');
} else {
    $tempSection = 'required';
}

// Check that the requested section is valid.
// If not default to required.
$sectionRegex = "/(required)|(admin)|(auth)|(email)/";
if (preg_match($sectionRegex, $tempSection, $regs) == true) {
    $section = $tempSection;
} else {
    $section = 'required';
}

// Tell template which section to load
$app->tpl()->assign("SECTION", $section);

// Get the pagelist
$pagelist = $app->parameters()->getPageList();

// Select the template
$template = $pagelist[$section]['template'];

// Set the selected section
$pagelist[$section]['selected'] = true;

$app->parameters()->setSection($section);

//Set the old Parameters array.  This is a tempory FIX
$parameters = $app->config()->read('param');

// Check if user has requested that the parameters be updated.
// If $_POST['action'] is not set display the current values
// If $_POST['action'] = save then set action to save
// if $_POST['action'] is not set to save then the action is invalid
if (\filter_input(INPUT_POST, 'action') !== null) {
    if (preg_match("/^(save)$/", \filter_input(INPUT_POST, 'action'), $regs)) {
        $action = \filter_input(INPUT_POST, 'action');
    } else {
        $action = "invalid";
    }
} else {
    $action = "new_page";
}

// Validate and save the required parameters
if ($action == "save") {
    // Abort the script if the session and page tokens are not the same.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = $app->user()->getUserId();
    if ($app->tokens()->verifyToken($inputToken, 'CONFIG', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Parameters not updated");
        $header = gettext("Token Check Failure");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }

    $dataok = $app->parameters()->validateParameters($_POST);

    // Check if the datavalidation passed
    if ($dataok == true) {
        // Check if any of the parameters have changed

        $datachanged = $app->parameters()->checkParametersChanged(
            $app->config()->read('param')
        );
        if ($datachanged == true) {
            // At least one required parameter has changed.
            // Set the Parameters array with the new values.
            $parameters = $app->parameters()->getCurrentParameters();

            // Save the new parameters
            $configDir = $app->tpl()->getConfigDir();
            if ($app->parameters()->saveParamFile($configDir[0]) == true) {
                // Parameters saved.  Populate the Change Message
                $msg = $app->parameters()->getLastMsg();
            } else {
                // An error was encountered saving the parameters file
                $msg = gettext("Configuration Parameters not Saved") . "\n";
            }
            $app->tpl()->assign("MSG", $msg);
        } else {
            // No changes were made to the required parameters
            $msg = gettext("You Have Not Made Any Changes") . "\n";
            $app->tpl()->assign("MSG", $msg);
        }
    } else {
        // At least one of the entered parameters was invalid.
        // Populate the error message
        $msg = $app->parameters()->getLastMsg();

        // Assign the message to the template.
        $app->tpl()->assign("MSG", $msg);

        // Load the Parameters array with the invalid data.
        $parameters = $app->parameters()->getCurrentParameters();
    }
}
// If an invalid command has been chosen display this in the MSG box
if ($action == "invalid") {
    $app->tpl()->assign("MSG", gettext("You have made an invalid command"));
}

// Assign the required parameters to the template
$app->tpl()->assign("URLBASE", $parameters['urlbase']);
$app->tpl()->assign("MAINTAINER", $parameters['maintainer']);
$app->tpl()->assign("DOCBASEURL", $parameters['docbase']);
$app->tpl()->assign("COOKIEDOMAIN", $parameters['cookiedomain']);
$app->tpl()->assign("COOKIEPATH", $parameters['cookiepath']);

// Assign the Admin Parameters to the template
$app->tpl()->assign("LOGGING", $parameters['admin']['logging']);
$app->tpl()->assign("LOGROTATE", $parameters['admin']['logrotate']);
$app->tpl()->assign("NEW_WINDOW_ON", $parameters['admin']['newwindow']);
$app->tpl()->assign("MAXRECORDS", $parameters['admin']['maxrecords']);

// Assign the auth parameters to the templat
$app->tpl()->assign("CREATE_ACCOUNT_ON", $parameters['users']['newaccount']);
$app->tpl()->assign("NEW_PASSWORD_ON", $parameters['users']['newpassword']);
$app->tpl()->assign("REGEXP", $parameters['users']['regexp']);
$app->tpl()->assign("REGEXPDESC", $parameters['users']['regexpdesc']);
$app->tpl()->assign("PASSWDSTRENGTH", $parameters['users']['passwdstrength']);
$app->tpl()->assign("PASSWDAGE", $parameters['users']['passwdage']);
$app->tpl()->assign("AUTOCOMPLETE", $parameters['users']['autocomplete']);
$app->tpl()->assign("AUTOLOGOUT", $parameters['users']['autologout']);

// Assign the email parameters to the template
$app->tpl()->assign(
    "MAIL_DELIVERY_METHOD",
    $parameters['email']['smtpdeliverymethod']
);
$app->tpl()->assign("EMAILADDRESS", $parameters['email']['emailaddress']);
$app->tpl()->assign("SMTPSERVER", $parameters['email']['smtpserver']);
$app->tpl()->assign("SMTPUSERNAME", $parameters['email']['smtpusername']);
$app->tpl()->assign("SMTPPASSWD", $parameters['email']['smtppassword']);
$app->tpl()->assign("SMTP_DEBUG_ON", $parameters['email']['smtpdebug']);

//Add new parameters here.

// Assign the pagelist to the template
$app->tpl()->assign("PAGELIST", $pagelist);

// Create the token for checking the page authenticition
$localtoken = $app->tokens()->createToken(
    $app->user()->getUserId(),
    'CONFIG',
    1,
    '',
    true
);
$app->tpl()->assign("TOKEN", $localtoken);

// Display the Web Page
$app->tpl()->display($template);
