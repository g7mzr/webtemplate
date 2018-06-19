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

// Create a WEBTEMPLATE CLASS
try {
    $app = new \webtemplate\application\Application();
} catch (\Throwable $e) {
    // Create the Smart Template object
    $tpl = new \webtemplate\application\SmartyTemplate;
    $template = 'global/error.tpl';
    $msg = $e->getMessage();
    $msg .= "\n\n";
    $msg .= gettext("Please Contact your Adminstrator");
    $header =  gettext("Application Error");
    $tpl->assign('ERRORMSG', $msg);
    $tpl->assign('HEADERMSG', $header);
    $dateArray = getdate();
    $tpl->assign("YEAR", "$dateArray[year]");
    $tpl->display($template);
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
$stylesheetarray = array();
$stylesheetarray[] = '/style/' . $app->user()->getUsertheme() . '/main.css';
$stylesheetarray[] = '/style/' . $app->user()->getUsertheme() . '/editconfig.css';
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
$docsAvailable = \webtemplate\general\General::checkdocs(
    $app->config()->read('param.docbase'),
    $language
);

$app->tpl()->assign("DOCSAVAILABLE", $docsAvailable);

// Check the user has permission to run this module
if (!$app->usergroups()->checkGroup("admin") == true) {
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

//Load the Themes Directory
$themesLoaded = $app->preferences()->loadThemes(\filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'));
if ($themesLoaded == false) {
    $template = 'global/error.tpl';
    $msg = $app->preferences()->getLastMsg();
    $header =  gettext("Edit Default Preferences");
    $app->tpl()->assign('ERRORMSG', $msg);
    $app->tpl()->assign('HEADERMSG', $header);
    $app->tpl()->display($template);
    exit();
}


// Set the template up
$template = "admin/prefs.tpl";
$app->tpl()->assign("PAGETITLE", gettext("Edit Default Preferences"));

// Check what the user wants to do
if (\filter_input(INPUT_GET, 'action') !== null) {
    $tempAction = \filter_input(INPUT_GET, 'action');
} elseif (\filter_input(INPUT_POST, 'action') !== null) {
    $tempAction = \filter_input(INPUT_POST, 'action');
} else {
    $tempAction = 'new_page';
}

$teststr = "/^(new_page)|(update)$/";
if (preg_match($teststr, $tempAction, $regs)) {
    $action = $tempAction;
} else {
    $action = "invalid";
}

if ($action == "update") {
    // Abort the script if the session and page tokens are not the same.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = $app->user()->getUserId();
    if ($app->tokens()->verifyToken($inputToken, 'SETTINGS', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Parameters not updated");
        $header = gettext("Token Check Failure");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }

    $dataValid = $app->preferences()->validatePreferences($_POST);
    if ($dataValid == true) {
        $datachanged = $app->preferences()->checkPreferencesChanged();
        if ($datachanged == true) {
            $configDir = dirname($_SERVER['DOCUMENT_ROOT']) . "/configs";
            $dataSaved = $app->preferences()->savePrefFile($configDir);
            if ($dataSaved == true) {
                $app->tpl()->assign("MSG", $msg = $app->preferences()->getLastMsg());
            } else {
                $app->tpl()->assign("MSG", gettext("Preferences File Not Saved"));
            }
        } else {
            $app->tpl()->assign("MSG", gettext("You have not Made any Changes"));
        }
    } else {
        $app->tpl()->assign("MSG", $msg = $app->preferences()->getLastMsg());
    }
}

// Load up the blank form
if ($action == "invalid") {
    $app->tpl()->assign("MSG", gettext("You have chosen an invalid command"));
}

// Create the security token
// Create the token for checking the page authenticition
$localtoken = $app->tokens()->createToken($app->user()->getUserId(), 'SETTINGS', 1, ''. true);
$app->tpl()->assign("TOKEN", $localtoken);

//Set the Default Theme and Get the Preferences
$result = $app->preferences()->setDefaultThemes();
// $sitePreferences = $app->preferences()->getCurrentPreferences();
$installedThemes = $app->preferences()->getThemes();

// Assign Them Data
$app->tpl()->assign("THEME", $installedThemes);
$app->tpl()->assign("THEME_ENABLED", $app->config()->read('pref.theme.enabled'));
// Get Textbox Zoom
$app->tpl()->assign("ZOOM_TEXTAREAS_ENABLED", $app->config()->read('pref.zoomtext.enabled'));
$app->tpl()->assign("ZOOM_TEXTAREAS_ON", $app->config()->read('pref.zoomtext.value'));

// Set the lines to display in searched
$app->tpl()->assign("DISPLAY_ROWS_ENABLED", $app->config()->read('pref.displayrows.enabled'));
$app->tpl()->assign("DISPLAY_ROWS", $app->config()->read('pref.displayrows.value'));


/* Get the year for the Copyright Statement at */
$dateArray = getdate();
$app->tpl()->assign("YEAR", "$dateArray[year]");

/* Display the Web Page */
$app->tpl()->display($template);
