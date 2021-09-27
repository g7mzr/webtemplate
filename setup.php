#!/usr/bin/env php
<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Install
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

//System Files
require_once __DIR__ . "/includes/global.php";

// Include the composer Autoloader
require_once __DIR__ . '/vendor/autoload.php';

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Command;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Missing;


$getOpt = new GetOpt();

// Define the comon options
$getOpt->addOptions(
    [
        Option::create('v', 'version', GetOpt::NO_ARGUMENT)
            ->setDescription("Show the version information and quit"),

        Option::create('h', 'help', GetOpt::NO_ARGUMENT)
            ->setDescription("Show this help and quit")
    ]
);

// Get the Application Configuration File en.conf
$config = parse_ini_file(__DIR__ . "/configs/en.conf");

// Enable the Setup Commands used to Install and Upgarade an Application
$getOpt->addCommand(new \g7mzr\webtemplate\install\commands\InstallCommand($getOpt));
$getOpt->addCommand(new \g7mzr\webtemplate\install\commands\UpgradeCommand($getOpt));
$getOpt->addCommand(new \g7mzr\webtemplate\install\commands\PluginsCommand($getOpt, $config));

// Enable the Setup Commands used in a development system
if ($config['Production'] == false) {
}


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
    echo sprintf(
        PHP_EOL . '%s - Setup: %s' . PHP_EOL . PHP_EOL,
        $config["application_name"],
        $config["application_version"]
    );
    exit;
}

// show help and quit
if ($getOpt->getOption('help')) {
    echo $getOpt->getHelpText();
    exit;
}

$command = $getOpt->getCommand();
if (!$command) {
    // no command given - show help?
    echo $getOpt->getHelpText();
    exit;
}

// call the requested command
call_user_func($command->getHandler(), $getOpt);
