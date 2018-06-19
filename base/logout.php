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


// Set the Sys admin email address
$app->tpl()->assign("SYSADMINEMAIL", $app->config()->read("param.maintainer"));

// Load the menu and assign it to a SMARTY Variable
$mainmenu = $app->config()->readMenu('mainmenu');
$app->tpl()->assign('MAINMENU', $mainmenu);

/* Send the HTTP Headers required by the application */
$headerResult = \webtemplate\application\Header::sendHeaders();
if ($headerResult == false) {
    // Log error is headers not sent
    $log->error(basename(__FILE__) . ":  Failed to send HTTP Headers");
}

// Unset and destroy the current session variable
$app->session()->destroy();

// Show the Login Page
header('Location: index.php');
