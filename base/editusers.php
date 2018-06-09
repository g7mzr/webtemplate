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
$configdir = $tpl->getConfigDir(0);
$config = new \webtemplate\config\Configure($configdir);

//Create the logclass
$log = new \webtemplate\general\Log(
    $config->read('param.admin.logging'),
    $config->read('param.admin.logrotate')
);

// Load the menu and assign it to a SMARTY Variable
$mainmenu = $config->readMenu('mainmenu');
$tpl->assign('MAINMENU', $mainmenu);

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

// Get the users groups
$userGroups = new \webtemplate\users\Groups($db, $user->getUserId());

// Get the users select style
$stylesheetarray = array();
$stylesheetarray[] = '/style/' . $user->getUserTheme() . '/main.css';
$stylesheetarray[] = '/style/' . $user->getUserTheme() . '/editconfig.css';
$tpl->assign('STYLESHEET', $stylesheetarray);

// Set the Sys admin email address
$tpl->assign("SYSADMINEMAIL", $config->read("param.maintainer"));

// Tell the templates the user has logged in.  This will display the menus
$tpl->assign('LOGIN', false);

// Users real name for displaying on web page
$tpl->assign("USERNAME", $user->getRealName());

// Assign AdminAccess Rights to the template
$tpl->assign('ADMINACCESS', $userGroups->getAdminAccess());

// Set the current user's text box zoom settings
$tpl->assign("ZOOMON", $user->getUserZoom());

//Set default readonly tag for the username
$tpl->assign("READONLY", false);

//Set the default referer
$tpl->assign("REFERER", '');

//Set the default Group Result
$tpl->assign("GROUPRESULT", "");

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $config->read('param.docbase'),
    $language
);

$tpl->assign("DOCSAVAILABLE", $docsAvailable);

// Check the user has permission to run this module
if (!$userGroups->checkGroup("editusers") == true) {
    // User is not allowed to display the admin page
    $log->security(
        $session->getUserName() .
        gettext(" attempted to access ") . basename(__FILE__)
    );
    $template = 'global/error.tpl';
    $msg = gettext("You are not authorised to access this page.");
    $header =  gettext("Authorisation Required");
    $tpl->assign('ERRORMSG', $msg);
    $tpl->assign('HEADERMSG', $header);
    $dateArray = getdate();
    $tpl->assign("YEAR", "$dateArray[year]");
    $tpl->display($template);
    exit();
}

// Set Password aging
if ($config->read('param.users.passwdage') == '1') {
    $tpl->assign("PASSWDAGING", false);
} else {
    $tpl->assign("PASSWDAGING", true);
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
    $editUser = new \webtemplate\users\EditUser($db);
    $groups = new \webtemplate\groups\EditUsersGroups($db);

    if ($action == "list") {
        \webtemplate\users\EditUserFunctions::listUsers(
            $tpl,
            $template,
            $editUser,
            $config->read('param.users.regexp'),
            $_GET
        );
    } elseif ($action == "new") {
        \webtemplate\users\EditUserFunctions::newUser($tpl, $template);
    } elseif ($action == "edit") {
        // Set the link to return to the search page
        if (isset($_SERVER["HTTP_REFERER"])) {
            $referer = $_SERVER["HTTP_REFERER"];
        } else {
            $referer = '';
        }
        // Set the page referer
        $tpl->assign("REFERER", $referer);
        \webtemplate\users\EditUserFunctions::editUser(
            $tpl,
            $template,
            $editUser,
            $groups,
            $_GET
        );
    } elseif ($action == "save") {
        $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
        $uid = $user->getUserId();
        if ($token->verifyToken($inputToken, 'USERS', $uid) == false) {
            $template = "global/error.tpl";
            $msg = gettext("Security Failure. User Details not saved");
            $header = gettext("Token Check Failure");
            $tpl->assign("ERRORMSG", $msg);
            $tpl->assign("HEADERMSG", $header);
            $tpl->display($template);
            exit();
        }
        \webtemplate\users\EditUserFunctions::saveUser(
            $tpl,
            $template,
            $editUser,
            $groups,
            $config->read("param.users"),
            $_POST
        );
    } else {
        $tpl->assign("MSG", gettext("You Have Chosen an Invalid Command"));
        $template = "users/search.tpl";
    }
} else {
    $template = "users/search.tpl";
}

// Create the token for checking the page authenticition
$localtoken = $token->createToken($user->getUserId(), 'USERS', 1, ''. true);
$tpl->assign("TOKEN", $localtoken);

// Get the year for the Copyright Statement at
$dateArray = getdate();
$tpl->assign("YEAR", "$dateArray[year]");

// Display the Web Page
$tpl->display($template);
