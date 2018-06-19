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

// Check is requesting new passwords is allowed
if ($app->config()->read('param.users.newpassword') == false) {
    // Requesting new passwords is not allowed
    $headerResult = \webtemplate\application\Header::sendRedirect('index.php');
    if ($headerResult == false) {
        // Log error is headers not sent
        $app->log()->error(basename(__FILE__) . ":  Failed to send Redirect HTTP Header");
    }
    exit();
}

// Get the users select style
$stylesheetarray = array();
$stylesheetarray[] = '/style/' . $app->config()->read('pref.theme.value') . '/main.css';
$app->tpl()->assign('STYLESHEET', $stylesheetarray);

// Set the Sys admin email address
$app->tpl()->assign("SYSADMINEMAIL", $app->config()->read("param.maintainer"));

// Set LOGIN to true to hide the menus
$app->tpl()->assign('LOGIN', true);

// Set the default page title
$app->tpl()->assign("PAGETITLE", gettext("Request New Password"));

// Check if the docbase parameter is set and the document files are available
$docsAvailable = \webtemplate\general\General::checkdocs(
    $app->config()->read('param.docbase'),
    $language
);
$app->tpl()->assign("DOCSAVAILABLE", $docsAvailable);

// Assign default variables to prevent Template Warnings
$app->tpl()->assign("USERNAME", "");
$app->tpl()->assign("USERID", '0');
$app->tpl()->assign("PASSWDTOKEN", "wwwwwwwwww");

// Set Variables to display the correct part of the Template
$entername = true;
$mailsent = false;
$enterpasswd = false;
$confirmpasswd = false;
$requestcancelled = false;

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

$teststr = "/^(new)|(new_page)|(save)|(cancelpasswdrequest)$/";
if (preg_match($teststr, $tempAction, $regs)) {
    $action = $tempAction;
} else {
    $action = "invalid";
}


// Get the username and send the email confirmation
if ($action == "new") {
    // Validate User

    // Abort the script if the session and page tokens are not the same.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = "1";
    if ($app->tokens()->verifyToken($inputToken, 'RESETPASS', $uid) == false) {
        // Get the year for the Copyright Statement
        $dateArray = getdate();
        $app->tpl()->assign('YEAR', "$dateArray[year]");
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Unable to complete request");
        $header = gettext("Token Check Failure");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }


    // Get the username.  Max size 12 Characters
    $tempuser      = substr(\filter_input(INPUT_POST, 'user_name'), 0, 12);
    $validusernameformat = false;
    //error_log("Passwd user name: " . $tempuser);

    // Check that the username is in a valid format
    if (\webtemplate\general\LocalValidate::username(
        $tempuser,
        $app->config()->read('param.users.regexp')
    )
    ) {
        $validusernameformat = true;
    }

    // If the username is in an invalid format bring up an error
    if ($validusernameformat == false) {
        $app->log()->security(
            gettext("Attempt to get new password with invalid username format")
        );

        // Get the year for the Copyright Statement
        $dateArray = getdate();
        $app->tpl()->assign('YEAR', "$dateArray[year]");
        $template = "global/error.tpl";
        $msg = gettext("The format of the username entered was incorrect");
        $header = gettext("Invalid Username");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }

    // Check that the mail system is active.  If off stop the password reset
    if ($app->mail()->status() == false) {
        // Get the year for the Copyright Statement
        $dateArray = getdate();
        $app->tpl()->assign('YEAR', "$dateArray[year]");
        $app->log()->error(gettext("Reset Password - Mail system is off"));
        $template = "global/error.tpl";
        $msg = gettext("System Error. Unable to complete request");
        $header = gettext("System Error");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }

    $searchresults = $app->edituser()->search('username', $tempuser);
    if ((\webtemplate\general\General::isError($searchresults))
        or (count($searchresults) != 1)
    ) {
        // Get the year for the Copyright Statement
        $dateArray = getdate();
        $app->tpl()->assign('YEAR', "$dateArray[year]");

        $msg = gettext("Unable to create new password request. ");
        $msg .= gettext("Please contact the system Administrator ");
        $msg .= "(" . $app->config()->read('param.maintainer') .")";
        $template = "global/error.tpl";
        $msg = gettext("System Error. Unable to complete request");
        $header = gettext("System Error");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }


    $userid = $searchresults[0]['userid'];
    $usertoken = $app->tokens()->createToken($userid, 'PASSWORD', 1, "", false, false);
    if (\webtemplate\general\General::isError($usertoken)) {
        // Get the year for the Copyright Statement
        $dateArray = getdate();
        $app->tpl()->assign('YEAR', "$dateArray[year]");

        $msg = gettext("Unable to create new password request. ");
        $msg .= gettext("Please contact the system Administrator ");
        $msg .= "(" . $app->config()->read('param.maintainer') .")";
        $template = "global/error.tpl";
        $msg = gettext("System Error. Unable to complete request");
        $header = gettext("System Error");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }

    // Set the e-mail template variables
    $app->user()->register($tempuser, $app->config()->read('pref'));
    if ($app->user()->getUserEmail() == "") {
        // Get the year for the Copyright Statement
        $dateArray = getdate();
        $app->tpl()->assign('YEAR', "$dateArray[year]");
        $errorTxt = getText("Reset Password - User:");
        $errorTxt .= " " . $tempuser  . " ";
        $errorTxt .= gettext("does not have a email address");
        $app->log()->error(gettext($errorTxt));
        $template = "global/error.tpl";
        $msg = gettext("System Error. Unable to complete request");
        $header = gettext("System Error");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }


    $tempURL = $app->config()->read('param.urlbase');
    $tempURL .= "resetpasswd.php?passwdreq=".$usertoken;
    $app->tpl()->assign("EMAILUSERNAME", $app->user()->getRealName());
    $app->tpl()->assign("PASSWODRESETLINK", $tempURL);
    $passwdstrength = $app->config()->read('param.users.passwdstrength');
    $app->tpl()->assign(
        "PASSWDFORMAT",
        \webtemplate\general\General::passwdFormat($passwdstrength)
    );

    // Get the completed e-mail template
    $maildata  = $app->tpl()->fetch("users/passwdrequest.tpl");

    $result = $app->mail()->sendEmail(
        $app->user()->getUserEmail(),
        '',
        '',
        gettext("Password Request"),
        $maildata
    );
    if ($result == false) {
        $app->log()->error($app->mail()->errorMsg());

        // Unable to send email record error
        $msg = gettext("Unable to create new password request. ");
        $msg .= gettext("Please contact the system Administrator ");
        $msg .= "(" . $app->config()->read("param.maintainer") .")";
        $template = "global/error.tpl";
        $msg = gettext("System Error. Unable to complete request");
        $header = gettext("System Error");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);

        // Get the year for the Copyright Statement
        $dateArray = getdate();
        $app->tpl()->assign('YEAR', "$dateArray[year]");

        $app->tpl()->display($template);
        exit();
    }

    $tempstr = gettext("User: ");
    $tempstr .= $tempuser;
    $tempstr .= gettext(" has requested a password reset");
    $app->log()->security($tempstr);

    // Set the page title
    $app->tpl()->assign("PAGETITLE", gettext("Email Confirmation"));


    // Set Variables to display the correct part of the Template
    $entername = false;
    $mailsent = true;
    $enterpasswd = false;
    $confirmpasswd = false;
    $requestcancelled = false;
}


// if $_GET['passwdreq'] is set then then resetpasswd.php
// is responding to a new token request
if (\filter_input(INPUT_GET, 'passwdreq') !== null) {
    // Set the page title
    $app->tpl()->assign("PAGETITLE", gettext("Enter Password"));

    // Set Variables to display the correct part of the Template
    $entername = false;
    $mailsent = false;
    $enterpasswd = true;
    $confirmpasswd = false;
    $requestcancelled = false;

    $validpasswdtoken = false;
    $userid = '0';
    $passwdreq = substr(\filter_input(INPUT_GET, 'passwdreq'), 0, 10);
    if (webtemplate\general\LocalValidate::token($passwdreq) == true) {
        $userid = $app->tokens()->getTokenUserid($passwdreq, 'PASSWORD');
        if ($userid > 0) {
            $validpasswdtoken = true;
            $app->tpl()->assign("USERID", $userid);
            $app->tpl()->assign("PASSWDTOKEN", $passwdreq);
        }
    }

    if ($validpasswdtoken == false) {
        // Get the year for the Copyright Statement
        $dateArray = getdate();
        $app->tpl()->assign('YEAR', "$dateArray[year]");
        $template = "global/error.tpl";
        $msg = gettext("This is not a valid password change token");
        $header = gettext("Invalid Request");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }
    $expiretime = $app->tokens()->getExpireTime();
    $expiretimestamp = date('d F Y', $expiretime);
    $expiretimestamp .= " at " . date("G:i:s", $expiretime);
    $expiretimestamp .= " " . date("T");
    $app->tpl()->assign("EXPIREDATE", $expiretimestamp);
}


// Save the new Password to the database
if ($action == "save") {
    // Abort the script if the session and page tokens are not the same.
    $inputToken = \filter_input(\INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = $app->user()->getUserId();
    if ($app->tokens()->verifyToken($inputToken, 'RESETPASS', 1) == false) {
        // Get the year for the Copyright Statement
        $dateArray = getdate();
        $app->tpl()->assign('YEAR', "$dateArray[year]");
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Unable to complete request");
        $header = gettext("Token Check Failure");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);
        $app->tpl()->display($template);
        exit();
    }

    $passwordUpdated = false;
    $temppass1 = substr(\filter_input(INPUT_POST, 'newpasswd'), 0, 20);
    $temppass2 = substr(\filter_input(INPUT_POST, 'newpasswd2'), 0, 20);
    $passwdreq   = substr(\filter_input(INPUT_POST, 'passwdtoken'), 0, 10);
    $userid      = substr(\filter_input(INPUT_POST, 'userid'), 0, 10);
    $app->tpl()->assign("PASSWDTOKEN", $passwdreq);
    $app->tpl()->assign("USERID", $userid);

    $passwdstrength = $app->config()->read('param.users.passwdstrength');
    if ((\webtemplate\general\LocalValidate::password($temppass1, $passwdstrength))
        and ($temppass1 == $temppass2)
    ) {
        $validpasswdtoken = false;
        if (\webtemplate\general\LocalValidate::token($passwdreq) == true) {
            $userid = $app->tokens()->getTokenUserid($passwdreq, 'PASSWORD');
            if ($userid > 0) {
                $validpasswdtoken = true;
            }
        }

        if ($validpasswdtoken == true) {
            // Get username
            if (\webtemplate\general\LocalValidate::dbid($userid) == true) {
                $temuserdetails = $app->edituser()->getUser($userid);
                if (!\webtemplate\general\General::isError($temuserdetails)) {
                    $passwdstatus = $app->edituser()->updatePasswd(
                        $temuserdetails[0]['username'],
                        $temppass1
                    );
                    if (!\webtemplate\general\General::isError($passwdstatus)) {
                        $app->tpl()->assign("PAGETITLE", gettext("Password Updated"));
                        $deletetoken = $app->tokens()->deleteToken($passwdreq);
                        if (\webtemplate\general\General::isError($deletetoken)) {
                            $tempstr = gettext("resetpasswd: ");
                            $tempstr .= gettext("Failed to delete token");
                            $tempstr .= gettext(" after password changed");
                            $app->log()->error($tempstr);
                        }

                        // Password has been updated
                        $passwordUpdated = true;

                        // Update Flags to set Template to correct mode
                        $entername = false;
                        $mailsent = false;
                        $enterpasswd = false;
                        $confirmpasswd = true;
                        $requestcancelled = false;
                    }
                }
            }
        }
        if ($passwordUpdated == false) {
            // Get the year for the Copyright Statement
            $dateArray = getdate();
            $app->tpl()->assign('YEAR', "$dateArray[year]");
            $app->log()->error(gettext("Password reset failed. Userid = " . $userid));
            $template = "global/error.tpl";
            $msg = gettext("Unable to reset Password.  Please try again");
            $header = gettext("Password Reset failed");
            $app->tpl()->assign("ERRORMSG", $msg);
            $app->tpl()->assign("HEADERMSG", $header);
            $app->tpl()->display($template);
            exit();
        }
    } else {
        // Set the page title
        $app->tpl()->assign("PAGETITLE", gettext("Enter Password"));

        $msg = gettext("Unable to validate passwords.\n");
        $msg .= \webtemplate\general\General::passwdFormat(
            $app->config()->read('param.users.passwdstrength')
        );
        $msg .= "\n";
        $msg .= gettext("and both passwords are the same");
        $app->tpl()->assign("MSG", $msg);
        $app->tpl()->assign("ENTERPASSWORD", true);
        $app->tpl()->assign("REQUESTPASSWORD", false);
        $entername = false;
        $mailsent = false;
        $enterpasswd = true;
        $confirmpasswd = false;
        $requestcancelled = false;
        $expiretime = $app->tokens()->getExpireTime();
        $expiretimestamp = date('d F Y', $expiretime);
        $expiretimestamp .= " at " . date("G:i:s", $expiretime);
        $expiretimestamp .= " " . date("T");
        $app->tpl()->assign("EXPIREDATE", $expiretimestamp);
    }
}

if ($action == "cancelpasswdrequest") {
    // Set Variables to display the correct part of the Template
    $entername = false;
    $mailsent = false;
    $enterpasswd = false;
    $confirmpasswd = false;
    $requestcancelled = true;

    // Check the security token is valid.
    $inputToken = \filter_input(INPUT_POST, 'token', FILTER_SANITIZE_STRING);
    $uid = "1";
    if ($app->tokens()->verifyToken($inputToken, 'RESETPASS', $uid) == false) {
        $template = "global/error.tpl";
        $msg = gettext("Security Failure. Request has not been deleted");
        $header = gettext("Token Check Failure");
        $app->tpl()->assign("ERRORMSG", $msg);
        $app->tpl()->assign("HEADERMSG", $header);

        // Get the year for the Copyright Statement
        $dateArray = getdate();
        $app->tpl()->assign('YEAR', "$dateArray[year]");
        $app->tpl()->display($template);
        exit();
    }

    $temptoken = \filter_input(INPUT_POST, 'passwdreq', FILTER_SANITIZE_STRING);
    if (!webtemplate\general\LocalValidate::token($temptoken)) {
        // This is not a valid webtemplate token
        $template = 'global/error.tpl';
        $msg = gettext("Unable to validate  password reset cancelation request.\n");
        $header =  gettext("Invalid Request");
        $app->tpl()->assign('ERRORMSG', $msg);
        $app->tpl()->assign('HEADERMSG', $header);
        $dateArray = getdate();
        $app->tpl()->assign("YEAR", "$dateArray[year]");
        $app->tpl()->display($template);
        exit();
    }

    $result = $app->tokens()->deleteToken($temptoken);
    if (webtemplate\general\General::isError($result)) {
        $msg = gettext(__FILE__ . "Error cancelling new account request");
        $app->log()->error($msg);
    }
    $enterEmail = false;
    $accountcancelled = true;

    // Page Title
    $app->tpl()->assign("PAGETITLE", gettext("Request Cancelled"));
}





// Set Template variables
$app->tpl()->assign("ENTERNAME", $entername);
$app->tpl()->assign("EMAILSENT", $mailsent);
$app->tpl()->assign("ENTERPASSWORD", $enterpasswd);
$app->tpl()->assign("CONFIRMPASSWORD", $confirmpasswd);
$app->tpl()->assign("REQUESTCANCELLED", $requestcancelled);

$template = "users/resetpasswd.tpl";

// Create the token for checking the page authenticition
$localtoken = $app->tokens()->createToken("1", 'RESETPASS', 1, ''. true);
$app->tpl()->assign("TOKEN", $localtoken);

// Get the year for the Copyright Statement
$dateArray = getdate();
$app->tpl()->assign('YEAR', "$dateArray[year]");

// Display the Web Page
$app->tpl()->display($template);
