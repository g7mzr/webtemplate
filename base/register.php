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

// Set the Sys admin email address
$tpl->assign("SYSADMINEMAIL", $config->read("param.maintainer"));

// Check is self registration is allowed
if ($config->read('param.users.newaccount') == false) {
    // If not show the index page
    header('Location: index.php');
    exit();
}

// Create the editUser Class
$editUser = new \webtemplate\users\EditUser($db);

// Set the default stylesheet
$stylesheetarray = array();
$stylesheetarray[] = '/style/' . $config->read('pref.theme.value') . '/main.css';
$tpl->assign('STYLESHEET', $stylesheetarray);

// Set LOGIN to true to hide the menus
$tpl->assign('LOGIN', true);

// Set the page title
$tpl->assign("PAGETITLE", gettext("Email Address"));

// Assign Default data to prevent Template warnings
$tpl->assign("USERNAME", "");
$tpl->assign("REALNAME", "");
$tpl->assign("EMAIL", "");
$tpl->assign("PASSWORD1", "");
$tpl->assign("PASSWORD2", "");
$tpl->assign("EMAIL1", "");
$tpl->assign("EMAIL2", "");

// Set the DEFAULT Setting for the Template
$dataReadonly = "";
$enterEmail = true;
$enterDetails = false;
$emailsent = false;
$detailsentered = false;
$accountcancelled = false;


// Check if user has submitted any infornamtion .
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

$teststr = "/^(savenewaccount)|(new_page)|(saveemail)|(cancelnewaccount)$/";
if (preg_match($teststr, $tempAction, $regs)) {
    $action = $tempAction;
} else {
    $action = "invalid";
}

/**
 * Check that the email address submitted by the user is valid and is not in use
 * either by this user or an other user.  If it is valid send a confirmation e-mail
 * with a token to enable the user request the account application form.
 */
if ($action == "saveemail") {
    // Check that the mail system is active.  If off stop the registration
    $newMail = new \webtemplate\general\Mail($config->read('param.email'));
    if ($newMail->status() == false) {
        // Get the year for the Copyright Statement
        $dateArray = getdate();
        $tpl->assign('YEAR', "$dateArray[year]");
        $log->error(gettext("New User - Mail system is off"));
        $template = "global/error.tpl";
        $msg = gettext("System Error. Unable to complete request");
        $header = gettext("System Error");
        $tpl->assign("ERRORMSG", $msg);
        $tpl->assign("HEADERMSG", $header);
        $tpl->display($template);
        exit();
    }

    // Abort the script if the session and page tokens are not the same.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = "1";
    if ($token->verifyToken($inputToken, 'REGISTER', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Email has not been sent");
        $header = gettext("Token Check Failure");
        $tpl->assign("ERRORMSG", $msg);
        $tpl->assign("HEADERMSG", $header);
        $tpl->display($template);
        exit();
    }

    $emailok = false;

    // Page Title
    $tpl->assign("PAGETITLE", gettext("Email Address"));

    // Get the tainted email adddresses  Max characters = 60
    if (\filter_input(INPUT_POST, 'email1') !== null) {
        $usermail1 = substr(\filter_input(INPUT_POST, 'email1'), 0, 60);
    } else {
        $usermail1 = '';
    }

    // Validate the email address.  If invalid flag data as false
    if ($usermail1 <> '') {
        if (webtemplate\general\LocalValidate::email($usermail1)) {
            $emailok = true;
        }
    }

    if ($emailok == true) {
        // Create the new Mail Class
        $newMail = new webtemplate\general\Mail($config->read('param.email'));


        if ($editUser->checkEmailExists($usermail1) == false) {
            // Create the e-mail token required for the request
            $emailtoken = $token->createToken(
                "1",
                'NEWACCOUNT',
                24*3,
                $usermail1,
                false,
                false
            );

            // Put the expire time in plain text for the web pages and email
            $expiretime = $token->getExpireTime();
            $expiretimestamp = date('d F Y', $expiretime);
            $expiretimestamp .= " at " . date("G:i:s", $expiretime);
            $expiretimestamp .= " " . date("T");
            $tpl->assign("EMAILADDRESS", $usermail1);
            $tpl->assign("EXPIREDATE", $expiretimestamp);

            $tempURL = $config->read('param.urlbase');
            $tempURL .= "register.php?newuser=".$emailtoken;

            $tpl->assign("NEWACCOUNTLINK", $tempURL);

            $maildata  = $tpl->fetch("users/newuserrequest.tpl");

            $result = $newMail->sendEmail(
                $usermail1,
                '',
                '',
                gettext("Registration Request"),
                $maildata
            );
            if ($result == false) {
                $tpl->assign(
                    "MSG",
                    gettext("Unable to complete your request at this time")
                );
                $enterEmail = true;
                $emailsent = false;
                $log->error($newMail->errorMsg());
            } else {
                $enterEmail = false;
                $emailsent = true;

                // Page Title
                $tpl->assign("PAGETITLE", gettext("Email Confirmation"));
            }
        } else {
            // To protect existing users the system needs to pretend that email
            // addresses already in use are new.  An email will be sent to the
            // address reporting the atempt to register, a security log entry will
            // be recorded and the email confirmation page will be shown.
            $userdetails = $editUser->search("email", $usermail1);
            $tpl->assign("REALNAME", $userdetails[0]["realname"]);
            $tpl->assign("EMAILADDRESS", $usermail1);
            $tpl->assign("ADMINADDR", $config->read("param.maintainer"));
            $maildata  = $tpl->fetch("users/newuserexistingemail.tpl");

            $msg = "Register: Duplicate email address: " . $usermail1;
            $log->security($msg);
            $result = $newMail->sendEmail(
                $usermail1,
                '',
                '',
                gettext("Registration Request"),
                $maildata
            );
            if ($result == false) {
                $tpl->assign(
                    "MSG",
                    gettext("Unable to complete your request at this time")
                );
                $enterEmail = true;
                $emailsent = false;
                $log->error($newMail->errorMsg());
            } else {
                $enterEmail = false;
                $emailsent = true;

                // Page Title
                $tpl->assign("PAGETITLE", gettext("Email Confirmation"));
            }
        }
    } else {
        $msg = gettext("Invalid email address format. ");
        $msg .= gettext("Please re-enter your email address.");
        $tpl->assign("MSG", $msg);
    }
}


/**
 * A token to request a new account is received by the application on a URL.  Check
 * it is a valid token and if it is display the new account form.  The user cannot
 * change his e-mail address at this point.
 */
if (\filter_input(INPUT_GET, 'newuser') !== null) {
    $temptoken = \filter_input(INPUT_GET, 'newuser', FILTER_SANITIZE_STRING);
    if (!webtemplate\general\LocalValidate::token($temptoken)) {
        // This is not a valid webtemplate token
        $template = 'global/error.tpl';
        $msg = gettext("Unable to validate new account request.\n\n");
        $header =  gettext("Invalid Request");
        $tpl->assign('ERRORMSG', $msg);
        $tpl->assign('HEADERMSG', $header);
        $dateArray = getdate();
        $tpl->assign("YEAR", "$dateArray[year]");
        $tpl->display($template);
        exit();
    }

    $requestToken = \filter_input(INPUT_GET, 'newuser', FILTER_SANITIZE_STRING);

    // Validate the token as a new user request.
    if (!$token->verifyToken($requestToken, "NEWACCOUNT", 1)) {
        // This is not a valid NEWUSER token
        $template = 'global/error.tpl';
        $msg = gettext("Unable to validate new account request.\n\n");
        $msg .= gettext("The link used has either expired or in incorrect.");
        $header = gettext("Invalid Request");
        $tpl->assign('ERRORMSG', $msg);
        $tpl->assign('HEADERMSG', $header);
        $dateArray = getdate();
        $tpl->assign("YEAR", "$dateArray[year]");
        $tpl->display($template);
        exit();
    }

    // Page Title
    $tpl->assign("PAGETITLE", gettext("Enter Details"));


    $tpl->assign("EMAIL", $token->getEventData());
    $expiretime = $token->getExpireTime();
    $expiretimestamp = date('d F Y', $expiretime);
    $expiretimestamp .= " at " . date("G:i:s", $expiretime);
    $expiretimestamp .= " " . date("T");
    $tpl->assign("EXPIREDATE", $expiretimestamp);
    $tpl->assign("NEWACCTOKEN", $requestToken);
    $enterEmail = false;
    $enterDetails = true;
}

/**
 * The user has decided to cancel the account creation
 */
if ($action == "cancelnewaccount") {
    // Check the security token is valid.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = "1";
    if ($token->verifyToken($inputToken, 'REGISTER', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Request has not been deleted");
        $header = gettext("Token Check Failure");
        $tpl->assign("ERRORMSG", $msg);
        $tpl->assign("HEADERMSG", $header);
        $tpl->display($template);
        exit();
    }

    $temptoken = \filter_input(INPUT_POST, 'newacc', FILTER_SANITIZE_STRING);
    if (!webtemplate\general\LocalValidate::token($temptoken)) {
        // This is not a valid webtemplate token
        $template = 'global/error.tpl';
        $msg = gettext("Unable to validate  new account cancelation request.\n");
        $header =  gettext("Invalid Request");
        $tpl->assign('ERRORMSG', $msg);
        $tpl->assign('HEADERMSG', $header);
        $dateArray = getdate();
        $tpl->assign("YEAR", "$dateArray[year]");
        $tpl->display($template);
        exit();
    }
    $requestToken = \filter_input(INPUT_POST, 'newacc', FILTER_SANITIZE_STRING);

    $result = $token->deleteToken($requestToken);
    if (webtemplate\general\General::isError($result)) {
        $msg = gettext(__FILE__ . "Error cancelling new account request");
        $log->error($msg);
    }
    $enterEmail = false;
    $accountcancelled = true;

    // Page Title
    $tpl->assign("PAGETITLE", gettext("Request Cancelled"));
}

/**
 * The user has submitted the form to create an account.
 */
if ($action == "savenewaccount") {
    // Check the security token is valid.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = "1";
    if ($token->verifyToken($inputToken, 'REGISTER', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Account has not been created");
        $header = gettext("Token Check Failure");
        $tpl->assign("ERRORMSG", $msg);
        $tpl->assign("HEADERMSG", $header);
        $tpl->display($template);
        exit();
    }

    // Check the New Account Token is Valid
    $temptoken = \filter_input(INPUT_POST, 'newacc', FILTER_SANITIZE_STRING);
    if (!webtemplate\general\LocalValidate::token($temptoken)) {
        // This is not a valid webtemplate token
        $template = 'global/error.tpl';
        $msg = gettext("Unable to validate new account request.\n\n");
        $msg .= gettext("The request has either expired or is invalid.");
        $header = gettext("Invalid Request");
         $tpl->assign('ERRORMSG', $msg);
        $tpl->assign('HEADERMSG', $header);
        $dateArray = getdate();
        $tpl->assign("YEAR", "$dateArray[year]");
        $tpl->display($template);
        exit();
    }

    // Check the token is still valid
    $requestToken = \filter_input(INPUT_POST, 'newacc', FILTER_SANITIZE_STRING);
    if (!$token->verifyToken($requestToken, "NEWACCOUNT", 1)) {
        // This is not a valid webtemplate token
        $template = 'global/error.tpl';
        $msg = gettext("Unable to validate new account request.\n\n");
        $msg .= gettext("The request has either expired or ");
        $msg .= gettext("is not a new account request.\n");
        $header = gettext("Invalid Request");
        $tpl->assign('ERRORMSG', $msg);
        $tpl->assign('HEADERMSG', $header);
        $dateArray = getdate();
        $tpl->assign("YEAR", "$dateArray[year]");
        $tpl->display($template);
        exit();
    }


    // Get the user input and validate the data
    // Set the test variables to their default values
    $usernameok = true;
    $realnameok = true;
    $passwdok = true;
    $userexists = false;
    $userdataok = true;

    // Page Title
    $tpl->assign("PAGETITLE", gettext("Enter Details"));

    // Get the tainted user name.  Max characters = 12
    if (\filter_input(INPUT_POST, 'user_name') !== null) {
        $username = substr(\filter_input(INPUT_POST, 'user_name'), 0, 20);
    } else {
        $username = '';
    }

    // Get the tainted real name.  Max characters = 60
    if (\filter_input(INPUT_POST, 'realname') !== null) {
        $realname = substr(\filter_input(INPUT_POST, 'realname'), 0, 60);
    } else {
        $realname = '';
    }

    // Get the tainted password1.  Max characters = 20
    if (\filter_input(INPUT_POST, 'new_password1') !== null) {
        $passwdOne = substr(\filter_input(INPUT_POST, 'new_password1'), 0, 20);
    } else {
        $passwdOne = '';
    }

    // Get the tainted password2.  Max characters = 20
    if (\filter_input(INPUT_POST, 'new_password2') !== null) {
        $passwdTwo = substr(\filter_input(INPUT_POST, 'new_password2'), 0, 20);
    } else {
        $passwdTwo = '';
    }

    // Validate the username.  If invalid flag data as false
    if (!\webtemplate\general\LocalValidate::username(
        $username,
        $config->read('param.users.regexp')
    )
    ) {
        $usernameok = false;
        $userdataok = false;
    } else {
        // If the username format is valid check if it already in use.
        $checkname = $editUser->checkUserExists($username);
        if (\webtemplate\general\General::isError($checkname)) {
            $userexists = true;
            $userdataok = false;
        } else {
            if ($checkname == true) {
                $userexists = true;
                $userdataok = false;
            }
        }
    }

    // Validate the realname.  If invalid flag data as false
    if (!\webtemplate\general\LocalValidate::realname($realname)) {
        $realnameok = false;
        $userdataok = false;
    }

    // Check the passwords match and are valid. If invalid flag data as false.
    $passwdstrength = $config->read('param.users.passwdstrength');
    if (($passwdOne <> '') and ($passwdOne == $passwdTwo)) {
        if (!\webtemplate\general\LocalValidate::password(
            $passwdOne,
            $passwdstrength
        )
        ) {
            $passwdok = false;
            $userdataok = false;
        }
    } else {
        $passwdok = false;
        $userdataok = false;
    }

    $usermail = $token->getEventData();

    // Assign the input data back to the form
    $tpl->assign("USERNAME", $username);
    $tpl->assign("REALNAME", $realname);
    $tpl->assign("PASSWORD1", $passwdOne);
    $tpl->assign("PASSWORD2", $passwdTwo);
    $tpl->assign("EMAIL", $usermail);


    // If the data is valid save the user's details in the database
    if ($userdataok == true) {
        $userId = '0';
        $result = $editUser->saveuser(
            $userId,
            $username,
            $realname,
            $usermail,
            $passwdOne,
            'N',
            'Y',
            'N'
        );
        if (\webtemplate\general\General::isError($result)) {
            $template = "global/error.tpl";
            $msg =  $result->getMessage();
            $header = gettext("Database Error");
            $tpl->assign("ERRORMSG", $msg);
            $tpl->assign("HEADERMSG", $header);
            $tpl->display($template);
            exit();
        } else {
            $detailsentered = true;
            $enterDetails = false;
            $enterEmail = false;

            // Delete the request Token
            $result = $token->deleteToken($requestToken);
            if (webtemplate\general\General::isError($result)) {
                $msg = gettext(__FILE__ . "Error deleteing new account token");
                $log->error($msg);
            }

            // Page Title
            $tpl->assign("PAGETITLE", gettext("Account Created"));
        }
    } else { // Tell the user what data is invalid.
        $msg = '';
        if ($usernameok == false) {
            $msg .= gettext("Invalid User Name: ");
            $msg .= $config->read('param.users.regexpdesc');
            $msg .= "\n";
        }
        if ($userexists == true) {
            $msg .= gettext("Invalid User Name (User Exists): ");
            $msg .= $config->read('param.users.regexpdesc');
            $msg .= "\n";
        }
        if ($realnameok == false) {
            $msg = $msg .gettext("Invalid Real Name") . "\n";
        }
        if ($passwdok == false) {
            $passwdstrength = $config->read('param.users.passwdstrength');
            $msg .= gettext("Invalid Password: ");
            $msg .= webtemplate\general\General::passwdFormat($passwdstrength);
            $msg .= "\n";
        }
        $tpl->assign("MSG", $msg);
        $enterDetails = true;
        $enterEmail = false;
    }


    $expiretime = $token->getExpireTime();
    $expiretimestamp = date('d F Y', $expiretime);
    $expiretimestamp .= " at " . date("G:i:s", $expiretime);
    $expiretimestamp .= " " . date("T");
    $tpl->assign("EXPIREDATE", $expiretimestamp);
    $tpl->assign("NEWACCTOKEN", $requestToken);
}

// Assign the Template configuration values
$tpl->assign("READONLY", $dataReadonly);
$tpl->assign("ENTEREMAIL", $enterEmail);
$tpl->assign("ENTERDETAILS", $enterDetails);
$tpl->assign("EMAILSENT", $emailsent);
$tpl->assign("DETAILSENTERED", $detailsentered);
$tpl->assign("ACCOUNTCANCELLED", $accountcancelled);

// Set the template
$template = "users/register.tpl";

// Create the token for checking the page authenticition
$localtoken = $token->createToken("1", 'REGISTER', 1, '', true);
$tpl->assign("TOKEN", $localtoken);

/* Get the year for the Copyright Statement */
$dateArray = getdate();
$tpl->assign("YEAR", $dateArray['year']);

/* Display the Web Page */
$tpl->display($template);
