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

namespace webtemplate\install;

/**
 * Dependencies Class
 *
 * Dependencies Class is the class used to manage webtemplate dependencies for PHP
 * and other modules
 **/
class Dependencies
{
    /**
     * Dependency errors found flag
     *
     * @var    boolean
     * @access protected
     */
    protected $errorsFound;

    /**
     * PHP Error strings
     *
     * @var    array
     * @access protected
     */
    protected $phpErrors  = array();

    /**
     * Other Error strings
     *
     * @var    array
     * @access protected
     */
    protected $moduleErrors  = array();

    /**
     * Constructor
     *
     * @access public
     */
    public function __construct()
    {
        $this->errorsFound = false;
        echo "Checking configuration:\n\n";
    }

    /**
     * Destructor
     *
     * @access public
     */
    public function __destruct()
    {
    }

    /**
     * Function to print out all the errors messages to stdout
     *
     * @return boolean True if there are no errors
     */
    public function printErrorMsgs()
    {
        if ($this->errorsFound == true) {
            echo "\n\n";
            echo "The following PHP Extensions and Modules need to be installed or";
            echo " upgraded.\nPlease use the instructions below:";
            echo "\n\n";

            if (count($this->phpErrors) > 0) {
                for ($i = 0; $i < count($this->phpErrors); $i++) {
                    echo $this->phpErrors[$i] . "\n";
                }
            }
            if (count($this->moduleErrors) > 0) {
                for ($i = 0; $i < count($this->moduleErrors); $i++) {
                    echo $this->moduleErrors[$i] . "\n";
                }
            }
        }
        return $this->errorsFound;
    }

    /**
     * Function to validate the version of PHP being used.
     *
     * @param string $version The Minimum version required.
     *
     * @return boolean True if the PHP version is equal or greater to one needed
     */
    public function checkPHP(string $version)
    {
        echo "PHP Version:\n";
        echo substr("Checking for PHP (v$version)              ", 0, 35);
        $foundVersion = phpversion();
        if (version_compare($foundVersion, $version) > -1) {
            echo "Ok: Found: v$foundVersion\n";
        } else {
            echo "Found: v$foundVersion.\n";
            $this->errorsFound = true;
            $this->phpErrors[] = "Install PHP Version: $version";
        }
        return $this->errorsFound;
    }


    /**
     * Function to validate that all the PHP Modules are present.
     *
     * @param array $modules Array of modules to be tested.
     *
     * @return boolean True if all the modules are present
     */
    public function checkPHPModules(array $modules)
    {
        echo "\nPHP Extensions:\n";
        $modulesFound = true;
        foreach ($modules as $key => $data) {
            echo substr("Checking for $key                    ", 0, 35);
            if (! extension_loaded($data['name'])) {
                echo "Extension not found\n";
                $modulesFound = false;
                $this->errorsFound = true;
                $this->phpErrors[] = $data['install'];
            } else {
                echo "Ok: Found\n";
            }
        }
        return $modulesFound;
    }

    /**
     * Function to validate that all the modules installed via composer are installed
     * and at the correct version.
     *
     * @param array $modules Array of modules to be tested.
     *
     * @return boolean True if all the modules are present
     */
    public function checkComposerModules(array $modules)
    {
        echo "\nComposer Modules:\n";
        $modulesFound = true;
        foreach ($modules as $key => $data) {
            $version = $data['version'];
            if ($version != 'any') {
                $version = 'v' . $version;
            }
            echo substr("Checking for $key ($version)             ", 0, 35);

            $classname = $data['classname'];

            $testClass = null;
            if (class_exists($classname, true)) {
                $testClass = new $classname();
            }
            if (!is_a($testClass, $classname)) {
                echo "Module not found\n";
                $modulesFound  = false;
                $this->errorsFound = true;
                $this->moduleErrors[] = $data['install'];
            } else {
                $version = $data['version'];
                $cmd = $data['versionvar'];
                $substr = $data['versionsubstr'];
                $foundVersion = substr(eval('return ' . $cmd . ';'), $substr);
                if ($data['version'] == 'any') {
                    echo "Ok: Found: v$foundVersion\n";
                } elseif (version_compare($foundVersion, $version) > -1) {
                    echo "Ok: Found: v$foundVersion\n";
                } else {
                    echo "Found: v$foundVersion.\n";
                    $modulesFound  = false;
                    $this->errorsFound = true;
                    $this->moduleErrors[] = $data['install'];
                }
            }
        }
        return $modulesFound;
    }


    /**
     * Function to validate that one of the supported databases and drivers are
     * present at the correct versions
     *
     * @param string $database      The type of database being used.
     * @param array  $modules       Array of modules to be tested.
     * @param array  $installConfig Database login details used during configuration.
     *
     * @return boolean True if all the database and drivers are present
     */
    public function checkDatabases(
        string $database,
        array $modules,
        array $installConfig
    ) {
        $errorMsg = array();

        echo "\nChecking for Database and Drivers:\n";
        $modulesFound = true;

        // Check that the database is supported
        if (!array_key_exists($database, $modules)) {
            echo $database . " Databases are not supported.\n\n";
            return false;
        }

        // Check the the PHP PDO is installed
        $modulesFound = $this->dbmsSupportedPHP($database, $modules, $errorMsg);

         // Now test that the Database is present, running and at the correct version
        if ($modulesFound == true) {
            $modulesFound = $this->dbmsVersion(
                $database,
                $modules,
                $installConfig,
                $errorMsg
            );
        }

        // Something has gone wrong with teh database
        if ($modulesFound == false) {
            echo "\n\nDatabase not configured correctly. ";
            echo "Please use the instructions below:\n\n";
            for ($i = 0; $i < count($errorMsg); $i++) {
                echo $errorMsg[$i] . "\n";
            }
        }
        return $modulesFound;
    }


    /**
     * Function to check that the selected dbms is supported by php
     *
     * @param string $database The type of database being used.
     * @param array  $modules  Array of modules to be tested.
     * @param array  $errorMsg Error message if database not found.
     *
     * @return boolean True if dbms is supported by php
     */
    private function dbmsSupportedPHP(
        string $database,
        array $modules,
        array &$errorMsg
    ) {

        // Check that th PHP database module is loaded
        echo substr("Checking for PDO:$database                        ", 0, 35);
        $dbfound = true;
        try {
            $installeddb = \PDO::getAvailableDrivers();
        } catch (\PDOException $ex) {
            $dbfound = false;
        }
        if ($dbfound === true) {
            if (!in_array($database, $installeddb, true)) {
                $dbfound = false;
            }
        }

        if ($dbfound === false) {
            echo "Extension not found\n";
            $errorMsg[] = $modules[$database]['phpinstall'];
            return false;
        } else {
            echo "Ok: Found\n";
            return true;
        }
    }


    /**
     * Function to check that the is at the right version and is running
     *
     * @param string $database      The type of database being used.
     * @param array  $modules       Array of modules to be tested.
     * @param array  $installConfig Database login details used during configuration.
     * @param array  $errorMsg      Error message if database not found.
     *
     * @return boolean True if dbms is supported by php
     */
    private function dbmsVersion(
        string $database,
        array $modules,
        array $installConfig,
        array &$errorMsg
    ) {

        $dbversion = $modules[$database]['dbversion'];
        if ($dbversion == 'any') {
            $version = 'any';
        } else {
            $version = 'v' .  $dbversion;
        }

        echo substr("Checking for $database ($version)             ", 0, 35);
        $dsn = array(
            'dbtype'  => $database,
            'hostspec' => $installConfig['database_host'],
            'username' => $installConfig['database_superuser'],
            'password' => $installConfig['database_superuser_passwd'],
            'adminuser' => $installConfig['database_superuser'],
            'adminpasswd' => $installConfig['database_superuser_passwd'],
            'databasename' => $modules[$database]['templatedb'],
            'disable_iso_date' => 'disable'
        );

        // Load g7mzr\db
        // Using the Database Superuser and management database

        try {
            $dbmanager = new \g7mzr\db\DBManager($dsn, $dsn["adminuser"], $dsn["adminpasswd"]);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            return false;
        }

        $result = $dbmanager->setMode("admin");
        if (\g7mzr\db\common\Common::isError($result)) {
            echo "Unable to switch DBManager to admin mode\n";
            return false;
        }

        $activedbversion = $dbmanager->getAdminDriver()->getDBVersion();
        if (\g7mzr\db\common\Common::isError($dbmanager)) {
            echo "Unable to get DB Version\n";
            return false;
        }

        $foundVersion = explode(' ', $activedbversion);
        if ($dbversion == 'any') {
            echo "Ok: Found: v$foundVersion[1]\n";
            return true;
        } elseif (version_compare($foundVersion[1], $dbversion) >= 0) {
            echo "Ok: Found: v$foundVersion[1]\n";
            return true;
        } else {
            echo "Found: v$foundVersion[1].\n";
            $errorMsg[] = $modules[$database]['dbupgrade'];
            return false;
        }
    }
}
