<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\install;

/**
 * Dependencies Class is the class used to manage webtemplate dependancies for PHP
 * and other modules
 *
 * @category Webtemplate
 * @package  General
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class Dependencies
{
    /**
     * Dependancy errors found flag
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
                for ($i= 0; $i < count($this->phpErrors); $i++) {
                    echo $this->phpErrors[$i] . "\n";
                }
            }
            if (count($this->moduleErrors) > 0) {
                for ($i= 0; $i < count($this->moduleErrors); $i++) {
                    echo $this->moduleErrors[$i] . "\n";
                }
            }
        }
        return $this->errorsFound;
    }

    /**
     * Function to validate the version of PHP being used.
     *
     * @param string $version The Minimum version required
     *
     * @return boolean True if the PHP version is equal or greater to one needed
     */
    public function checkPHP($version)
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
    }


    /**
     * Function to validate that all the PHP Modules are present.
     *
     * @param array $modules Arry of modules to be tested.
     *
     * @return boolean True if all the modules are present
     */
    public function checkPHPModules($modules)
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
     * @param array $modules Arry of modules to be tested.
     *
     * @return boolean True if all the modules are present
     */
    public function checkComposerModules($modules)
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
                $foundVersion = substr(eval('return ' . $cmd .';'), $substr);
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
     * @param string $database      The type of database being used
     * @param array  $modules       Array of modules to be tested.
     * @param array  $installConfig Database login details used during configuration
     *
     * @return boolean True if all the database and drivers are present
     */
    public function checkDatabases($database, $modules, $installConfig)
    {
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
            for ($i= 0; $i < count($errorMsg); $i++) {
                echo $errorMsg[$i] . "\n";
            }
        }
        return $modulesFound;
    }


    /**
     * Function to check that the seleceted dbms is supported by php
     *
     * @param string $database The type of database being used
     * @param array  $modules  Array of modules to be tested.
     * @param array  $errorMsg Error message if database not found
     *
     * @return boolean True if dbms is supported by php
     */
    private function dbmsSupportedPHP($database, $modules, &$errorMsg)
    {

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
     * @param string $database      The type of database being used
     * @param array  $modules       Array of modules to be tested.
     * @param array  $installConfig Database login details used during configuration
     * @param array  $errorMsg      Error message if database not found
     *
     * @return boolean True if dbms is supported by php
     */
    private function dbmsVersion($database, $modules, $installConfig, &$errorMsg)
    {

        $dbversion = $modules[$database]['dbversion'];
        if ($dbversion == 'any') {
            $version = 'any';
        } else {
            $version = 'v' .  $dbversion;
        }

        echo substr("Checking for $database ($version)             ", 0, 35);
        $dsn = array(
            'phptype'  => $database,
            'hostspec' => $installConfig['database_host'],
            'username' => $installConfig['database_superuser'],
            'password' => $installConfig['database_superuser_passwd'],
            'database' => $modules[$database]['templatedb'],
            'disable_iso_date' => 'disable'
        );

        // Load the Database Abstraction layer for Wetemplate
        // Using the Database Superuser and management database
        $db = \webtemplate\db\DB::load($dsn);

        // Test if it has loaded okay
        if (\webtemplate\general\General::isError($db)) {
            // If it has not loaded terminate the install.
            echo "Failed to open Database: " . $db->getMessage() . "\n";
            return false;
        }

        $foundVersion = explode(' ', $db->getDBVersion());
        if ($dbversion == 'any') {
            echo "Ok: Found: v$foundVersion[1]\n";
            return true;
        } elseif (version_compare($foundVersion[1], $dbversion) > -1) {
            echo "Ok: Found: v$foundVersion[1]\n";
            return true;
        } else {
            echo "Found: v$foundVersion[1].\n";
            $errorMsg[] = $modules[$database]['dbupgrade'];
            return false;
        }
    }
}
