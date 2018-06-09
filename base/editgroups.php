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

//Create new config class
$configdir = $tpl->getConfigDir(0);
$config = new \webtemplate\config\Configure($configdir);

//Create the logclass
$log = new \webtemplate\general\Log(
    $config->read('param.admin.logging'),
    $config->read('param.admin.logrotate')
);

// Create new Token Class
$token = new \webtemplate\general\Tokens($tpl, $db);

//$tpl->debugging = true;

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

//Set up the users web page style
$stylesheetarray = array();
$stylesheetarray[] = '/style/' . $user->getUserTheme() . '/main.css';
$stylesheetarray[] = '/style/' . $user->getUserTheme() . '/editgroup.css';
$tpl->assign('STYLESHEET', $stylesheetarray);

// Set the Sys admin email address
$tpl->assign("SYSADMINEMAIL", $config->read("param.maintainer"));

// Users real name for displaying on web page
$tpl->assign('USERNAME', $user->getRealName());

// Assign AdminAccess Rights to the template
$tpl->assign('ADMINACCESS', $userGroups->getAdminAccess());

// Tell the templates the user has logged in.  This will display the menus
$tpl->assign('LOGIN', false);

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $config->read('param.docbase'),
    $language
);

$tpl->assign("DOCSAVAILABLE", $docsAvailable);

//Assign default READONLY flag
$tpl->assign("READONLY", '');

// Check the user can access this page
if (!$userGroups->checkGroup("editgroups") == true) {
    // User is not allowed to display the admin page
    $log->security(
        $session->getUserName() .
        gettext(" attempted to access ") .
        basename(__FILE__)
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

//  DO WHAT EVER THIS MODULE NEEDS TO DO
$groups = new \webtemplate\groups\EditGroups($db);

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
    \webtemplate\groups\EditGroupFunctions::newGroup($tpl, $template);
} elseif ($action == "edit") {
    if (\filter_input(INPUT_GET, 'groupid') !== null) {
        $groupId = substr(\filter_input(INPUT_GET, 'groupid'), 0, 5);
    } else {
        $groupId = '';
    }

    $result = \webtemplate\groups\EditGroupFunctions::editGroup(
        $tpl,
        $groups,
        $template,
        $groupId
    );
    if (($result === false) and ($template != 'global/error.tpl')) {
        \webtemplate\groups\EditGroupFunctions::listGroups($tpl, $groups, $template);
    }
} elseif ($action == "showdel") {
    if (\filter_input(INPUT_GET, 'groupid') !== null) {
        $groupId = substr(\filter_input(INPUT_GET, 'groupid'), 0, 5);
    } else {
        $groupId = '';
    }
    $result = \webtemplate\groups\EditGroupFunctions::confirmDeleteGroup(
        $tpl,
        $groups,
        $template,
        $groupId
    );
    if (($result === false) and ($template != 'global/error.tpl')) {
        \webtemplate\groups\EditGroupFunctions::listGroups($tpl, $groups, $template);
    }
} elseif ($action == "del") {
    // Abort the script if the session and page tokens are not the same.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = $user->getUserId();
    if ($token->verifyToken($inputToken, 'GROUPS', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Parameters not updated");
        $header = gettext("Token Check Failure");
        $tpl->assign("ERRORMSG", $msg);
        $tpl->assign("HEADERMSG", $header);
        $tpl->display($template);
        exit();
    }
    if (\filter_input(INPUT_POST, 'groupid') !== null) {
        $groupId = substr(\filter_input(INPUT_POST, 'groupid'), 0, 5);
    } else {
        $groupId = '';
    }

    $result = \webtemplate\groups\EditGroupFunctions::deleteGroup(
        $tpl,
        $groups,
        $template,
        $groupId
    );
    if ($template != 'global/error.tpl') {
        \webtemplate\groups\EditGroupFunctions::listGroups($tpl, $groups, $template);
    }
} elseif ($action == "save") {
    // Abort the script if the session and page tokens are not the same.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = $user->getUserId();
    if ($token->verifyToken($inputToken, 'GROUPS', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Parameters not updated");
        $header = gettext("Token Check Failure");
        $tpl->assign("ERRORMSG", $msg);
        $tpl->assign("HEADERMSG", $header);
        $tpl->display($template);
        exit();
    }
    \webtemplate\groups\EditGroupFunctions::saveGroup(
        $tpl,
        $groups,
        $template,
        $_POST
    );
} elseif ($action == "list") {
    \webtemplate\groups\EditGroupFunctions::listGroups($tpl, $groups, $template);
} else {
    // Invalid comman entered
    $tpl->assign("MSG", gettext("Invalid Command"));
    $groupArray = $groups->getGroupList();
    if (!\webtemplate\general\General::isError($groupArray)) {
        $tpl->assign("RESULTS", $groupArray);
        $template = "groups/list.tpl";
    } else {
        $template = "global/error.tpl";
        $msg = gettext("Error Encountered When Loading Groups");
        $header = gettext("Database Error");
        $tpl->assign("ERRORMSG", $msg);
        $tpl->assign("HEADERMSG", $header);
    }
}

// Create the token for checking the page authenticition
$localtoken = $token->createToken($user->getUserId(), 'GROUPS', 1, ''. true);
$tpl->assign("TOKEN", $localtoken);

//Get the year for the Copyright Statement
$dateArray = getdate();
$tpl->assign("YEAR", "$dateArray[year]");

// Display the Web Page
$tpl->display($template);
