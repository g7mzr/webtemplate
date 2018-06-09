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
$db =& \webtemplate\db\DB::load($dsn);
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
$user = new \webtemplate\users\user($db);
$user->register($session->getUserName());

// Get the users groups
$userGroups = new \webtemplate\users\Groups($db, $user->getUserId());

// Get the users select style
$stylesheetarray = array();
$stylesheetarray[] = 'style/' . $user->getUserTheme() . '/main.css';
$stylesheetarray[] = 'style/' . $user->getUserTheme() . '/userprefs.css';
$tpl->assign('STYLESHEET', $stylesheetarray);

// Set the Sys admin email address
$tpl->assign("SYSADMINEMAIL", $config->read("param.maintainer"));

// Restrict the menuses to the non logged in ones
$tpl->assign('LOGIN', true);

// Users real name for displaying on web page
$tpl->assign('USERNAME', $user->getRealName());

// Assign AdminAccess Rights to the template
$tpl->assign('ADMINACCESS', $userGroups->getAdminAccess());

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $config->read('param.docbase'),
    $language
);

$tpl->assign("DOCSAVAILABLE", $docsAvailable);


//DO WHAT EVER THIS MODULE NEEDS TO DO

/* Create and Edit User Class */
$updateuser = new \webtemplate\users\EditUser($db);

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
    $uid = $user->getUserId();
    if ($token->verifyToken($inputToken, 'NEWPASS', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Unable to complete request");
        $header = gettext("Token Check Failure");
        $tpl->assign("ERRORMSG", $msg);
        $tpl->assign("HEADERMSG", $header);
        $tpl->display($template);
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
    $tpl->assign("USERID", $session->getUserName());

    $passwdstrength = $config->read('param.users.passwdstrength');
    if ((LocalValidate::password($temppass1, $passwdstrength))
        and ($temppass1 == $temppass2)
    ) {
        $passwdstatus = $updateuser->updatePasswd(
            $session->getUserName(),
            $temppass1
        );
        if (!\webtemplate\general\General::isError($passwdstatus)) {
            $tpl->assign("ENTERPASSWORD", false);
            $tpl->assign("CONFIRMPASSWORD", true);
            $tpl->assign("PAGETITLE", gettext("Password Updated"));
            $passwordUpdated = true;
            $session->setPasswdChange(false);
        }
        if ($passwordUpdated == false) {
            // Get the year for the Copyright Statement
            $dateArray = getdate();
            $tpl->assign('YEAR', "$dateArray[year]");
            $str = gettext("Forced password change failed. Userid");
            $log->error($str . " = " . $userid);
            $template = "global/error.tpl";
            $msg = gettext("Unable to change password.  Please try again");
            $header = gettext("Password change failed");
            $tpl->assign("ERRORMSG", $msg);
            $tpl->assign("HEADERMSG", $header);
            $tpl->display($template);
            exit();
        }
    } else {
        $msg = gettext("Unable to validate passwords.\n");
        $msg .= GeneralCode::passwdFormat(
            $config->read('param.users.passwdstrength')
        );
        $msg .= "\n";
        $msg .= gettext("and both passwords are the same");
        $tpl->assign("MSG", $msg);
        $tpl->assign("ENTERPASSWORD", true);
        $tpl->assign("CONFIRMPASSWORD", false);
    }
} else {
    $tpl->assign("ENTERPASSWORD", true);
    $tpl->assign("CONFIRMPASSWORD", false);
    $tpl->assign("PAGETITLE", gettext("Update Password"));
    $tpl->assign("USERID", $session->getUserName());
    $msg = gettext("Your password has expired.");
    $msg .= " " . gettext("It must be changed before you can continue");
    $tpl->assign("MSG", $msg);
}

$template = 'users/newpasswd.tpl';

// Create the token for checking the page authenticition
$localtoken = $token->createToken($user->getUserId(), 'NEWPASS', 1, ''. true);
$tpl->assign("TOKEN", $localtoken);

// Get the year for the Copyright Statement
$dateArray = getdate();
$tpl->assign('YEAR', "$dateArray[year]");

// Display the Web Page
$tpl->display($template);
