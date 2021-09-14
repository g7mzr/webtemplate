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

namespace g7mzr\webtemplate\install\commands;

use GetOpt\Command;
use GetOpt\GetOpt;
use GetOpt\Operand;
use GetOpt\Option;

/**
 * Setup is a test command for PharApp.
 *
 * @package  PharApp
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  https://github.com/g7mzr/phar-app/blob/master/LICENSE GNU GPL v3.0
 */
class InstallCommand extends Command
{
    /**
     * Constructor
     *
     * Constructor used for InstallCommand Class.  It is used to initialise the
     * command including setting up operands and help text.
     *
     * @param GetOpt $getOpt Pointer to the GetOpt structure for PharApp.
     *
     */
    public function __construct(GetOpt $getOpt)
    {
        parent::__construct('install', [$this, 'handle']);

        // Set up Operands fot Test Command

        // Setup description for Test Command
        $this->setDescription(
            'This command is used to install a new version of webtemplate' . PHP_EOL
        )->setShortDescription('Install New version of Webtemplate');

        $this->addOptions(
            [
            Option::create(null, 'check-modules', GetOpt::NO_ARGUMENT)
                ->setDescription(
                    "Check if required php extensions and modules are loaded and quit"
                ),

            Option::create(null, 'unit-test', GetOpt::NO_ARGUMENT)
                ->setDescription("Set up the database for testing with phpunit")
            ]
        );
    }

    /**
     * This is the entry point for the command
     *
     * @param GetOpt $getOpt Pointer to the GetOpt structure for PharApp.
     *
     * @return boolean False in an error is encountered.  True otherwise.
     */
    public function handle(GetOpt $getOpt)
    {
        $installConfig = array();

        // Set the control variables so that makedocs knows what to do
        $modulesOnly = $getOpt->getOption('check-modules');
        if (is_null($modulesOnly)) {
            $modulesOnly = false;
        }
        $unittestdb  = $getOpt->getOption('unit-test');
        if (is_null($unittestdb)) {
            $unittestdb = false;
        }

        // Set the base Working Directory
        // as checkInstallConfig will catch it.
        $basedir = dirname(dirname(dirname(__DIR__)));

        // Load config.php.  If it does not exist don't worry
        // as checkInstallConfig will catch it.
        if (file_exists($basedir . "/config.php")) {
            include $basedir . '/config.php';
        }

        // Load the following files if they exist
        // If not they will be created later
        $preferencesfile = $basedir . "/configs/preferences.json";
        if (file_exists($preferencesfile)) {
            $preferencesstr = file_get_contents($preferencesfile);
            $sitePreferences = json_decode($preferencesstr, true);
        } else {
            $sitePreferences = array();
        }

        $parametersfile = $basedir . "/configs/parameters.json";
        if (file_exists($parametersfile)) {
            $parameterstr = file_get_contents($parametersfile);
            $parameters = json_decode($parameterstr, true);
        } else {
            $parameters = array();
        }

        $filemanger = new \g7mzr\webtemplate\install\FileManager($basedir);

        echo "Checking config.php:  ";
        $configresult = $filemanger->checkInstallConfig($installConfig);
        if (\g7mzr\webtemplate\general\General::isError($configresult)) {
            echo "\n\n";
            echo $configresult->getMessage();
            exit(1);
        }
        echo "Done\n";

        // Check the PHP and other dependancies
        $this->checkDependencies($installConfig);

        // If modules only stop after the modules are built
        if ($modulesOnly == true) {
            echo "\n\n";
            exit(0);
        }

        //Create the Database
        $this->createDatabase($installConfig, $unittestdb);


        echo "\nConfiguration Files:\n";

        // Create local.conf if it does not exist
        echo "Checking local.conf:  ";
        $result = $filemanger->createLocalConf($installConfig);
        if (\g7mzr\webtemplate\general\General::isError($result)) {
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
        echo "Checking parameters.json:  ";
        $result = $filemanger->createParameters($parameters);
        if ($result === true) {
            echo "File Created/Updated\n\n";
        } else {
            echo "Error creating/Updating parameters.php\n\n";
        }

        // Create or update the preferences file
        echo "checking preferences.json: ";
        $result = $filemanger->createPreferences($sitePreferences);
        if ($result === true) {
            echo "File Created/Updated\n\n";
        } else {
            echo "Error creating/Updating parameters.php\n\n";
        }

        echo "installing Plugins:    ";
        $this->installPlugins($installConfig);
        echo "Done \n\n";

        if ($unittestdb == true) {
            // Create a system for testing
            echo "Creating tests/_data/database.php:  ";

            // Create the config file to be used for testing
            $result = $filemanger->createTestConf($installConfig);
            if (\g7mzr\webtemplate\general\General::isError($result)) {
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

        return true;
    }

    /**
     * checkDependencies
     *
     * Check that the host system has the correct version of PHP, correct PHP Modules
     * and PHP Database Drivers loaded
     *
     * @param array $installConfig The Configuration Array for access the database.
     *
     * @return void
     *
     * @access private
     */
    private function checkDependencies(array $installConfig)
    {
        $dep = new \g7mzr\webtemplate\install\Dependencies();

        $phpresult = $dep->checkPHP(\g7mzr\webtemplate\install\DependenciesData::$phpVersion);
        if (\g7mzr\webtemplate\general\General::isError($phpresult)) {
            echo "\n\n";
            echo $phpresult->getMessage();
            echo "\n\n";
            exit(1);
        }


        $dep->checkPHPModules(\g7mzr\webtemplate\install\DependenciesData::$phpModules);
        $dep->checkComposerModules(\g7mzr\webtemplate\install\DependenciesData::$composerModules);
        $modulesresult = $dep->printErrorMsgs();
        if ($modulesresult == true) {
            echo "\n\n";
            exit(1);
        }

        // Check that the database and drivers are available
        $dbresult = $dep->checkDatabases(
            $installConfig['database_type'],
            \g7mzr\webtemplate\install\DependenciesData::$databases,
            $installConfig
        );
        if ($dbresult == false) {
            echo "\n\n";
            exit(1);
        }
    }

    /**
     * createDatabase
     *
     * Check if the database exist and create it if it does not or it is a test DB
     *
     * @param array   $installConfig Array with info needed to setup the app.
     * @param boolean $unittestdb    True if a test system is to be set up.
     *
     * @return void
     *
     * @access private
     */
    private function createDatabase(array $installConfig, bool $unittestdb)
    {
        // Initalise the install DB Manager
        $db = new \g7mzr\webtemplate\install\DatabaseManager($installConfig, $unittestdb);

        // Check the database exists.  Drop existing test DB if required
        $checkdbExists = $db->dbExists($installConfig['database_name']);
        if ($checkdbExists === true) {
            echo "Cannot create database it already exists\n";
            exit(1);
        }

        // Check if database user exists if not create them.
        $checkdbuserexist = $db->createDatabaseUser(
            $installConfig['database_user'],
            $installConfig['database_user_passwd'],
            $unittestdb
        );
        if ($checkdbuserexist !== true) {
            echo "Unable to create new database user\n";
            exit(1);
        }

        // Create the Blank Database
        $blankDBCreated = $db->dbCreate(
            $installConfig['database_name'],
            $installConfig['database_user']
        );
        if ($blankDBCreated === false) {
            echo "Unable to create new database\n";
            exit(1);
        }

        $schemafile = __DIR__ . "/../configure/schema.json";
        $schemabuilt = $db->buildSchema(
            $schemafile,
            $checkdbExists
        );
        // Populate the database with its initual users and groups
        if ($checkdbExists === false) {
            $groupNumber = array();
            $groupfile = __DIR__ . "/../configure/default_groups.json";
            $defaultgroups = $db->createGroups(
                $groupfile,
                $groupNumber
            );

            $userfile = __DIR__ . "/../configure/default_users.json";
            $defaultusers = $db->createusers(
                $userfile,
                $groupNumber
            );


            // Create the Test Database.
            if ($unittestdb == true) {
                $groupfile = __DIR__ . "/../configure/test_groups.json";
                $testgroups = $db->createGroups(
                    $groupfile,
                    $groupNumber
                );
                $userfile = __DIR__ . "/../configure/test_users.json";
                $testusers = $db->createusers(
                    $userfile,
                    $groupNumber
                );
            }
        }
        echo "Database Created\n\n";
    }

    /**
     * Install Plugins
     *
     * @param array $installConfig Array with info needed to setup the app.
     *
     * @return void
     *
     * @access private
     */
    private function installPlugins(array $installConfig)
    {
        global $sessiontest;
        $sessiontest = array(true);
        $pluginDir = __DIR__ . "/../../../plugins";
        $filenames = \g7mzr\webtemplate\install\PluginDataBase::getPluginDBFiles($pluginDir);
        \g7mzr\webtemplate\install\PluginDataBase::createPluginSchema($installConfig, $filenames);
    }
}
