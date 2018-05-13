#!/usr/bin/env php
<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//System Files
require_once __DIR__ . "/includes/global.php";

// Include the composer Autoloader
require_once __DIR__ . '/vendor/autoload.php';

use GetOpt\GetOpt;
use GetOpt\Option;
//use GetOpt\Command;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Missing;

// Include the following files if they exist
// If not they will be created later
if (file_exists('./configs/preferences.php')) {
    include './configs/preferences.php';
}
if (file_exists('./configs/parameters.php')) {
    include './configs/parameters.php';
}


//error_reporting(null);
// ini_set("display_errors", 0);

// GLOBAL Variables
// Program Name
$programName = "Webtemplate";
$version = "0.5.0+";

$getOpt = new GetOpt();

// Define the comon options
$getOpt->addOptions(
    [
        Option::create(null, 'check-modules', GetOpt::NO_ARGUMENT)
            ->setDescription(
                "Check if required php extensions and modules are loaded and quit"
            ),

        Option::create(null, 'unit-test', GetOpt::NO_ARGUMENT)
            ->setDescription("Set up the database for testing with phpunit"),

        Option::create(null, 'version', GetOpt::NO_ARGUMENT)
            ->setDescription("Show the version information and quit"),

        Option::create(null, 'help', GetOpt::NO_ARGUMENT)
            ->setDescription("Show this help and quit")
    ]
);
// process arguments and catch user errors
try {
    try {
        $getOpt->process();
    } catch (Missing $exception) {
        // catch missing exceptions if help is requested
        if (!$getOpt->getOption('help')) {
            throw $exception;
        }
    }
} catch (ArgumentException $exception) {
    file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL);
    echo PHP_EOL . $getOpt->getHelpText();
    exit;
}

// show version and quit
if ($getOpt->getOption('version')) {
    echo sprintf('%s - Install: %s' . PHP_EOL, $programName, $version);
    exit;
}

// show help and quit
if ($getOpt->getOption('help')) {
    echo $getOpt->getHelpText();
    exit;
}

// Set the control variables so that makedocs knows what to do
$modulesOnly = $getOpt->getOption('check-modules');
$unittestdb  = $getOpt->getOption('unit-test');

// Load config.php.  If it does not exist don't worry
// as checkInstallConfig will catch it.
if (file_exists("config.php")) {
    include 'config.php';
}

$filemanger = new \webtemplate\install\FileManager(__DIR__);

echo "Checking config.php:  ";
$result = $filemanger->checkInstallConfig($installConfig);
if (webtemplate\general\General::isError($result)) {
    echo "\n\n";
    echo $result->getMessage();
    exit(1);
}
echo "Done\n";


// Check the PHP, PEAR and other dependancies
$dep = new \webtemplate\install\Dependencies();

$result = $dep->checkPHP(\webtemplate\install\DependenciesData::$phpVersion);
if (webtemplate\general\General::isError($result)) {
    echo "\n\n";
    echo $result->getMessage();
    echo "\n\n";
    exit(1);
}


$dep->checkPHPModules(\webtemplate\install\DependenciesData::$phpModules);
$dep->checkComposerModules(\webtemplate\install\DependenciesData::$composerModules);
$result = $dep->printErrorMsgs();
if ($result == true) {
    echo "\n\n";
    exit(1);
}

// Check that the database and drivers are available
$result = $dep->checkDatabases(
    $installConfig['database_type'],
    \webtemplate\install\DependenciesData::$databases,
    $installConfig
);
if ($result == false) {
    echo "\n\n";
    exit(1);
}

// If modules only stop after the modules are built
if ($modulesOnly == true) {
    echo "\n\n";
    exit(0);
}

\webtemplate\install\DataBase::createDatabase($installConfig, $unittestdb);


// Create local.conf if it does not exist
echo "Checking local.conf:  ";
$result = $filemanger->createLocalConf($installConfig);
if (webtemplate\general\General::isError($result)) {
    echo "\n\n";
    echo $result->getMessage();
    exit(1);
}
if ($result == true) {
     echo "File Created\n\n";
} else {
    echo "File exists. No updates carried out\n\n";
}


// Create or update the parameters file
echo "Checking parameters.php:  ";
$result = $filemanger->createParameters($parameters);
if ($result === true) {
    echo "File Created/Updated\n\n";
} else {
    echo "Error creating/Updating parameters.php\n\n";
}

// Create or update the preferences file
echo "checking preferences.php: ";
$result = $filemanger->createPreferences($sitePreferences);
if ($result === true) {
    echo "File Created/Updated\n\n";
} else {
    echo "Error creating/Updating parameters.php\n\n";
}


if ($unittestdb == true) {
    // Create a system for testing
    echo "Creating tests/_data/database.php:  ";

    // Create the config file to be used for testing
    $result = $filemanger->createTestConf($installConfig);
    if (webtemplate\general\General::isError($result)) {
        echo "\n\n";
        echo $result->getMessage();
        exit(1);
    }

    // Finished creating the test file.
    echo "File Created\n\n";
    echo "Test System Created.  Exiting\n\n";
    exit(0);
}

// Delete the existing templates
echo "Deleting compiled templates: ";
$filemanger->deleteCompiledTemplates();
echo "Done\n\n";



if (substr(PHP_OS, 0, 3) != 'WIN') {  //Check if runnning on Windows or *nix
    if (posix_getuid() != 0) { // If running on *nix check running as root
        $msg = "Error: install.php must be run as root to update file permissions.";
        $msg .= "\n\n";
        fwrite(STDERR, $msg);
        exit(1);
    }
}

echo "Setting file permissions:  ";
//$filemanger->setPermissions($installConfig);
echo "Done \n\n";

?>
