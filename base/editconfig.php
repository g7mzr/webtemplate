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
$config = new \webtemplate\config\Configure($db);

//Create New  Parameters Editing class
$editParams = new \webtemplate\admin\Parameters($config);


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

// Assign zoom textboxes
$tpl->assign("ZOOMON", $user->getUserZoom());

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $config->read('param.docbase'),
    $language
);
$tpl->assign("DOCSAVAILABLE", $docsAvailable);

// Get the year for the Copyright Statement
$dateArray = getdate();
$tpl->assign("YEAR", "$dateArray[year]");


// Check the user has permission to run this module
if (!$userGroups->checkGroup("admin") == true) {
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

//Set up the page title.
$tpl->assign("PAGETITLE", gettext("Edit Configuration"));

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
$tpl->assign("SECTION", $section);

// Get the pagelist
$pagelist = $editParams->getPageList();

// Select the template
$template = $pagelist[$section]['template'];

// Set the selected section
$pagelist[$section]['selected'] = true;

$editParams->setSection($section);

//Set the old Parameters array.  This is a tempory FIX
$parameters = $config->read('param');

// Check if user has requested that the parameters be updated.
// If $_POST['action'] is not set display the current values
// If $_POST['action'] = save then set action to save
// if $_POST['action'] is not set to save then the action is invalid
if (\filter_input(INPUT_POST, 'action') !== null) {
    if (preg_match("/^(save)$/", \filter_input(INPUT_POST, 'action'), $regs)) {
        $action =\filter_input(INPUT_POST, 'action');
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
    $uid = $user->getUserId();
    if ($token->verifyToken($inputToken, 'CONFIG', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Parameters not updated");
        $header = gettext("Token Check Failure");
        $tpl->assign("ERRORMSG", $msg);
        $tpl->assign("HEADERMSG", $header);
        $tpl->display($template);
        exit();
    }

    $dataok = $editParams->validateParameters($_POST);

    // Check if the datavalidation passed
    if ($dataok == true) {
        // Check if any of the parameters have changed

        $datachanged = $editParams->checkParametersChanged(
            $config->read('param')
        );
        if ($datachanged == true) {
            // At least one required parameter has changed.
            // Set the Parameters array with the new values.
            $parameters = $editParams->getCurrentParameters();

            // Save the new parameters
            $configDir = $tpl->getConfigDir();
            if ($editParams->saveParamFile($configDir[0]) == true) {
                // Parameters saved.  Populate the Change Message
                $msg = $editParams->getLastMsg();
            } else {
                // An error was encountered saving the parameters file
                $msg = gettext("Configuration Parameters not Saved") . "\n";
            }
            $tpl->assign("MSG", $msg);
        } else {
            // No changes were made to the required parameters
            $msg = gettext("You Have Not Made Any Changes") . "\n";
            $tpl->assign("MSG", $msg);
        }
    } else {
        // At least one of the entered parameters was invalid.
        // Populate the error message
        $msg = $editParams->getLastMsg();

        // Assign the message to the template.
        $tpl->assign("MSG", $msg);

        // Load the Parameters array with the invalid data.
        $parameters = $editParams->getCurrentParameters();
    }
}
// If an invalid command has been chosen display this in the MSG box
if ($action == "invalid") {
    $tpl->assign("MSG", gettext("You have made an invalid command"));
}

// Assign the required parameters to the template
$tpl->assign("URLBASE", $parameters['urlbase']);
$tpl->assign("MAINTAINER", $parameters['maintainer']);
$tpl->assign("DOCBASEURL", $parameters['docbase']);
$tpl->assign("COOKIEDOMAIN", $parameters['cookiedomain']);
$tpl->assign("COOKIEPATH", $parameters['cookiepath']);

// Assign the Admin Parameters to the template
$tpl->assign("LOGGING", $parameters['admin']['logging']);
$tpl->assign("LOGROTATE", $parameters['admin']['logrotate']);
$tpl->assign("NEW_WINDOW_ON", $parameters['admin']['newwindow']);
$tpl->assign("MAXRECORDS", $parameters['admin']['maxrecords']);

// Assign the auth parameters to the templat
$tpl->assign("CREATE_ACCOUNT_ON", $parameters['users']['newaccount']);
$tpl->assign("NEW_PASSWORD_ON", $parameters['users']['newpassword']);
$tpl->assign("REGEXP", $parameters['users']['regexp']);
$tpl->assign("REGEXPDESC", $parameters['users']['regexpdesc']);
$tpl->assign("PASSWDSTRENGTH", $parameters['users']['passwdstrength']);
$tpl->assign("PASSWDAGE", $parameters['users']['passwdage']);
$tpl->assign("AUTOCOMPLETE", $parameters['users']['autocomplete']);
$tpl->assign("AUTOLOGOUT", $parameters['users']['autologout']);

// Assign the email parameters to the template
$tpl->assign(
    "MAIL_DELIVERY_METHOD",
    $parameters['email']['smtpdeliverymethod']
);
$tpl->assign("EMAILADDRESS", $parameters['email']['emailaddress']);
$tpl->assign("SMTPSERVER", $parameters['email']['smtpserver']);
$tpl->assign("SMTPUSERNAME", $parameters['email']['smtpusername']);
$tpl->assign("SMTPPASSWD", $parameters['email']['smtppassword']);
$tpl->assign("SMTP_DEBUG_ON", $parameters['email']['smtpdebug']);

// Asign the pagelist to the template
$tpl->assign("PAGELIST", $pagelist);

// Create the token for checking the page authenticition
$localtoken = $token->createToken($user->getUserId(), 'CONFIG', 1, ''. true);
$tpl->assign("TOKEN", $localtoken);

// Display the Web Page
$tpl->display($template);
