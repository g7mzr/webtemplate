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

namespace g7mzr\webtemplate\install;

use g7mzr\webtemplate\application\exceptions\AppException;

/**
 * Description of CreateDatabase
 *
 * @author sandy
 */
class DatabaseManager
{
    /**
     * This array contains the initial application parameters settings
     *
     * @var    array
     * @access private
     */
    private $newParameters = array();

    /**
     * This array contains the initial contents of the applications preferences array.
     *
     * @var    array
     * @access private
     */
    private $newsitePreferences = array();

    /**
     * Property: dsn
     *
     * @var array
     * @access private
     */
    private $dsn = array();

    /**
     * Property: unittest
     *
     * @var boolean
     * @access private
     */
    private $unittestdb = false;

    /**
     * Property dbManager
     *
     * @var \g7mzr\db\DBManager
     * @access private
     */
    private $dbManager = null;

    /**
     * Constructor
     *
     * @param array   $installConfig Array with info needed to setup the app.
     * @param boolean $unittestdb    True if a test system is to be set up.
     *
     * @throws AppException If unable to connect to the database.
     *
     * @access public
     */
    public function __construct(array $installConfig, bool $unittestdb = false)
    {
        $this->dsn = array(
            'dbtype'  => $installConfig['database_type'],
            'hostspec' => $installConfig['database_host'],
            'databasename' => $installConfig['database_name'],
            'username' => $installConfig['database_user'],
            'password' => $installConfig['database_user_passwd'],
            'disable_iso_date' => 'disable'
        );
        $this->unittestdb = $unittestdb;
        try {
            $this->dbManager = new \g7mzr\db\DBManager(
                $this->dsn,
                $installConfig['database_superuser'],
                $installConfig['database_superuser_passwd']
            );
        } catch (Exception $ex) {
            throw AppException("Unable to connect to dbManager: " . $ex->getMessage());
        }
    }

    /**
     * dbExists
     *
     * Check if the database exists. If the database exists and unittestdb is true
     * drop the existing database
     *
     * @param string $dbname The name of the database to be checked.
     *
     * @return boolean True if the database exists false if it does not.
     *
     * @access public
     */
    public function dbExists(string $dbname)
    {
        $result = $this->dbManager->setMode("admin");
        if (\g7mzr\db\common\Common::isError($result)) {
            echo "Unable to switch DBManager to admin mode\n";
            return exit(1);
        }
        // Check if the database exists
        echo substr("Checking if database exists           ", 0, 35);
        $databaseExists = $this->dbManager->getAdminDriver()->databaseExists($dbname);
        if (\g7mzr\db\common\Common::isError($databaseExists)) {
            fwrite(STDERR, "FAILED " . $databaseExists->getMessage() . "\n");
            exit(1);
        }

        if ($databaseExists == true) {
            echo "Ok: Database Exists\n";
        } else {
            "Ok: New database\n";
        }

        // If the database exists and it is a test system drop it.
        if (($databaseExists == true) and ($this->unittestdb == true)) {
            echo substr("Dropping test database               ", 0, 35);
            $result = $this->dbManager->getAdminDriver()->dropDatabase($dbname);
            $databaseExists = false;
            if (\g7mzr\db\common\Common::isError($result)) {
                // If the database create fails terminate the install.
                fwrite(STDERR, "FAILED " . $result->getMessage() . "\n");
                exit(1);
            }
            echo "Ok: Database dropped\n";
        }
        return $databaseExists;
    }


    /**
     * This function creates the database user.  It terminates the install if it
     * encounters any problems
     *
     * @param string  $name       Database User Name.
     * @param string  $passwd     Database Users password.
     * @param boolean $unittestdb True if a test user is being created.
     *
     * @return boolean Always return true
     */
    public function createDatabaseUser(
        string $name,
        string $passwd,
        bool $unittestdb
    ) {
        $result = $this->dbManager->setMode("admin");
        if (\g7mzr\db\common\Common::isError($result)) {
            echo "Unable to switch DBManager to admin mode\n";
            return exit(1);
        }

        echo substr("Checking if database user exists      ", 0, 35);
        $checkuser = $this->dbManager->getAdminDriver()->userExists($name);
        if (\g7mzr\db\common\Common::isError($checkuser)) {
            echo "Error checking if databaseuser exists\n";
            return exit(1);
        }

        if ($checkuser === true) {
            echo "Ok: Exists\n";
        } else {
            $result = $this->dbManager->getAdminDriver()->createUser($name, $passwd, $unittestdb);
            if (\g7mzr\db\common\Common::isError($result)) {
                echo "Error creating database user\n";
                return exit(1);
            }
            echo "Ok: Created\n";
        }
        return true;
    }



    /**
     * dbCreate
     *
     * Create the blank Database
     *
     * @param string $dbname The name of the database to be created.
     * @param string $name   Database User Name.
     *
     * @return boolean True if the database is created false if it os not.
     *
     * @access public
     */
    public function dbCreate(string $dbname, string $name)
    {
        $result = $this->dbManager->setMode("admin");
        if (\g7mzr\db\common\Common::isError($result)) {
            echo "Unable to switch DBManager to admin mode\n";
            return exit(1);
        }

        echo substr("Creating New Database:             ", 0, 35);
        $result = $this->dbManager->getAdminDriver()->createDatabase(
            $dbname,
            $name
        );
        // Test that no error was encountered
        if (\g7mzr\db\common\Common::isError($result)) {
            // If the database create fails terminate the install.
            fwrite(STDERR, "FAILED " . $result->getMessage() . "\n");
            exit(1);
        }
        echo "Ok: Created\n";
        return true;
    }


    /**
     * This function creates the database schema.  It terminates the install if it
     * encounters any problems
     *
     * @param string  $schemafile     Name of the schema file.
     * @param boolean $databaseExists True if the database exists.
     *
     * @return boolean Always return true
     */
    public function buildSchema(
        string $schemafile,
        bool $databaseExists
    ) {

        // Create or update the schema
        $setschemaresult = $this->dbManager->setMode("schema");
        if (\g7mzr\db\common\Common::isError($setschemaresult)) {
            echo "Unable to switch DBManager to schema mode\n";
            return exit(1);
        }

        try {
            $schemaManager = new \g7mzr\db\SchemaManager($this->dbManager);
        } catch (throwable $e) {
            echo "Unable to create new SchemaManager\n";
            echo $e->getMessage() . "\n";
            return exit(1);
        }

        if ($databaseExists == true) {
            $newinstall = false;
        } else {
            $newinstall = true;
        }
        echo substr("Creating\\Modifing Database Schema:     ", 0, 35);

        $schemaresult = $schemaManager->autoSchemaManagement($schemafile, $newinstall);
        if (\g7mzr\db\common\Common::isError($schemaresult)) {
            echo "Unable to create new Schema\n";
            echo $schemaresult->getMessage() . "\n";
            return exit(1);
        }
        echo "Ok: Done\n";
    }

    /**
     * This function adds the groups to the new database. It stops if it encounters
     * a problem
     *
     * @param string $filename    The name of the group data file.
     * @param array  $groupNumber GroupID/Name cross reference.
     *
     * @return boolean Always return true
     */
    public function createGroups(
        string $filename,
        array &$groupNumber
    ) {

        $setdataresult = $this->dbManager->setMode("datadriver");
        if (\g7mzr\db\common\Common::isError($setdataresult)) {
            echo "Unable to switch DBManager to data access mode\n";
            return exit(1);
        }

        // Get the Group Datafile.
        $jsonfile = @fopen($filename, 'r');
        if ($jsonfile !== false) {
            $jsonstr = fread($jsonfile, filesize($filename));
            fclose($jsonfile);
        }
        if ($jsonstr === false) {
            $fileloaded = false;
            echo "Unable to load group file: " . $filename . "\n";
            exit(1);
        } else {
            // Convert the json string to an array
            $groups = json_decode($jsonstr, true);
            if ($groups === null) {
                echo "Unable to convert group file" . $filename . "\n";
                exit(1);
            }
        }
        $groupmap = array();
        $groupsCreated = true;

        // Add groups from the groups array in schema.php
        foreach ($groups as $name => $groupdata) {
            echo "Creating Group: " . $name . "\n";
            $insertdata = array (
                "group_name"          => $name,
                "group_description"   => $groupdata['description'],
                "group_useforproduct" => $groupdata['useforproduct'],
                "group_autogroup"     => $groupdata['autogroup'],
                "group_editable"      => $groupdata['editable'],
                "group_admingroup"    => $groupdata['admingroup']
            );

            // Insert a new group to the database
            $result = $this->dbManager->getDataDriver()->dbinsert('groups', $insertdata);


            // Always check that result is not an error
            if (!\g7mzr\db\common\Common::isError($result)) {
                $groupId = $this->dbManager->getDataDriver()->dbinsertid(
                    "groups",
                    "group_id",
                    "group_name",
                    $name
                );
                if (!\g7mzr\db\common\Common::isError($groupId)) {
                    $groupNumber[$name] = $groupId;
                } else {
                    $groupsCreated = false;
                }
            } else {
                $groupsCreated = false;
            }
        }
        if ($groupsCreated == true) {
            return true;
        } else {
            echo "ERROR Creating Database Groups\n";
            return exit(1);
        }
        return true;
    }

    /**
     * This function adds the groups to the new database. It stops if it encounters
     * a problem
     *
     * @param string $filename    The name of the group data file.
     * @param array  $groupNumber GroupID/Name cross reference.
     *
     * @return boolean Always return true
     */
    public function createUsers(
        string $filename,
        array &$groupNumber
    ) {
        $setdataresult = $this->dbManager->setMode("datadriver");
        if (\g7mzr\db\common\Common::isError($setdataresult)) {
            echo "Unable to switch DBManager to data access mode\n";
            return exit(1);
        }

        // Get the users Datafile.
        $jsonfile = @fopen($filename, 'r');
        if ($jsonfile !== false) {
            $jsonstr = fread($jsonfile, filesize($filename));
            fclose($jsonfile);
        }
        if ($jsonstr === false) {
            $fileloaded = false;
            echo "Unable to load user file: " . $filename . "\n";
            exit(1);
        } else {
            // Convert the json string to an array
            $users = json_decode($jsonstr, true);
            if ($users === null) {
                echo "Unable to convert user file" . $filename . "\n";
                exit(1);
            }
        }


        $usersCreated = true;
        $userId = -1;

        // Add users from the uesr array in schema.php
        // Walk through the USERS Array
        foreach ($users as $name => $userdata) {
            // print the user being created.
            echo "Creating user: " . $name . "\n";

            // Create the Data Array for inserting
            $encryptPasswd = \g7mzr\webtemplate\general\General::encryptPasswd(
                $userdata['passwd']
            );

            // If unable to create password abort save
            if ($encryptPasswd === false) {
                echo "ERROR Creating Database users. Unable to create Encrypted Password\n";
                exit(1);
            }

            $insertdata = array (
                'user_name' => $name,
                'user_realname' => $userdata['realname'],
                'user_email' => $userdata['email'],
                'user_enabled' => $userdata['enabled'],
                'user_disable_mail' => 'N',
                'user_passwd' =>  $encryptPasswd,
                'passwd_changed' => $userdata['passwdchanged']
            );

            // Insert a new group to the database
            $insertresult = $this->dbManager->getDataDriver()->dbinsert('users', $insertdata);

            // Check if there was an error
            if (\g7mzr\db\common\Common::isError($insertresult)) {
                $errorMsg = gettext("ERROR Creating Database users. ");
                $errorMsg .= gettext("Unable to create user: ");
                $errorMsg .= $name;
                $errorMsg .= "\n";
                echo $errorMsg;
                exit(1);
            }


            // Get the Id of the iuser just inserted
            $userId = $this->dbManager->getDataDriver()->dbinsertid(
                "users",
                "user_id",
                "user_name",
                $name
            );

            // Check for an error
            if (\g7mzr\db\common\Common::isError($userId)) {
                $userId = -1;
                $errorMsg = gettext("ERROR Creating Database users. ");
                $errorMsg .= gettext("Unable get UserID for user: ");
                $errorMsg .= $name;
                $errorMsg .= "\n";
                echo $errorMsg;
                exit(1);
            }


            // Add the suer to any groups specified in the UserArray
            $processgroupsresult = $this->processUserGroups(
                $this->dbManager,
                $userId,
                $groupNumber,
                $userdata
            );
            if (\g7mzr\webtemplate\general\General::isError($processgroupsresult)) {
                $errorMsg = gettext("ERROR Creating Database users. ");
                $errorMsg .= gettext("Unable to create groups for user: ");
                $errorMsg .= $name;
                $errorMsg .= "\n";
                echo $errorMsg;
                exit(1);
            }

            // Add any user specific preferences.
            $result = $this->processUserPrefs(
                $this->dbManager,
                $userId,
                $userdata
            );

            if (\g7mzr\webtemplate\general\General::isError($result)) {
                $errorMsg = gettext("ERROR Creating Database users. ");
                $errorMsg .= gettext("Unable to create preferences for user: ");
                $errorMsg .= $name;
                $errorMsg .= "\n";
                echo $errorMsg;
                exit(1);
            }
        }

        return true;
    }



    /**
     * This function assigns new users created during the install process to groups
     *
     * @param \g7mzr\db\DBManager $dbManager   Database class object.
     * @param integer             $userId      The users id.
     * @param array               $groupNumber The ids of each of the installed groups.
     * @param array               $userdata    The array used to create the users.
     *
     * @return mixed True is groups assigned pera error otherwise.
     *
     * @access private
     */
    private function processUserGroups(
        \g7mzr\db\DBManager $dbManager,
        int $userId,
        array $groupNumber,
        array $userdata
    ) {

        $usermapcreated = true;
        // Check and install user's groups if they exist
        if (array_key_exists('groups', $userdata)) {
            // Extract the group array from the main array
            $groups = $userdata['groups'];

            // Walk through the group array adding the details to the
            // user_group_map
            foreach ($groups as $groupId) {
                $mapdata = array (
                    'user_id'  => $userId,
                    'group_id' => $groupNumber[$groupId]
                );

                // Insert the group map
                $result = $dbManager->getDataDriver()->dbinsert('user_group_map', $mapdata);

                // check if there is an error
                if (\g7mzr\db\common\Common::isError($result)) {
                    $usermapcreated = false;
                }
            }
        }

        if ($usermapcreated == true) {
            return true;
        } else {
            // Else return a WEBTEMPLATE error
            $msg = gettext("ERROR Creating user group map");
            $err = \g7mzr\webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
    }



    /**
     * This function creates user specific preferences. It is mainly used for
     * unit and Selenium Tests I
     *
     * @param \g7mzr\db\DBManager $dbManager Database class object.
     * @param integer             $userId    The users id.
     * @param array               $userdata  The array used to create the users.
     *
     * @return mixed True is groups assigned pera error otherwise.
     *
     * @access private
     */
    private function processUserPrefs(
        \g7mzr\db\DBManager $dbManager,
        int $userId,
        array $userdata
    ) {
        $preferencesCreated = true;
        // check and install uers's preferences'
        if (array_key_exists('prefs', $userdata)) {
            // Extract the preferences array from the main user array
            $prefs = $userdata['prefs'];

            // Walk through the preferences array adding thye details
            // to the preferences table
            foreach ($prefs as $settingname => $settingvalue) {
                $insertData = array(
                    'user_id'      => $userId,
                    'settingname'  => $settingname,
                    'settingvalue' => $settingvalue
                );

                // Insert the preferences
                $result = $dbManager->getDataDriver()->dbinsert('userprefs', $insertData);

                // Check if there was an error
                if (\g7mzr\db\common\Common::isError($result)) {
                    $preferencesCreated = false;
                }
            }
        }
        if ($preferencesCreated == true) {
            return true;
        } else {
            // Else return a WEBTEMPLATE error
            $msg = gettext("ERROR Creating user preferences");
            $err = \g7mzr\webtemplate\general\General::raiseError($msg, DB_ERROR);
            return $err;
        }
    }

    /**
     * This function creates the application default configuration Parameters
     *
     * @param string $filename The name of the parameters data file.
     *
     * @return mixed True is groups assigned error otherwise.
     *
     * @access public
     */
    public function createConfigParameters(string $filename)
    {
        echo substr("Adding Configuration Parameters:         ", 0, 35);
        $setdataresult = $this->dbManager->setMode("datadriver");
        if (\g7mzr\db\common\Common::isError($setdataresult)) {
            echo "Unable to switch DBManager to data access mode\n";
            return exit(1);
        }

        // Get the parameters Datafile.
        $jsonfile = @fopen($filename, 'r');
        if ($jsonfile !== false) {
            $jsonstr = fread($jsonfile, filesize($filename));
            fclose($jsonfile);
        }
        if ($jsonstr === false) {
            $fileloaded = false;
            echo "Unable to load parameter file: " . $filename . "\n";
            exit(1);
        } else {
            // Convert the json string to an array
            $this->newParameters = json_decode($jsonstr, true);
            if ($this->newParameters === null) {
                echo "Unable to convert parameter file" . $filename . "\n";
                exit(1);
            }
        }
        $configCreated = true;

        // Walk through the Parameters array adding thye details
        // to the config table
        foreach ($this->newParameters as $key => $value) {
            if (is_int($value)) {
                $type = "int";
            } elseif (is_bool($value)) {
                $type = "bool";
            } else {
                $type = "string";
            }

            $insertData = array(
                'config_key'  => $key,
                'config_array' => "parameters",
                'config_value' => $value,
                'config_type' => $type
            );

            // Insert the preferences
            $result = $this->dbManager->getDataDriver()->dbinsert('config', $insertData);

            // Check if there was an error
            if (\g7mzr\db\common\Common::isError($result)) {
                $configCreated = false;
            }
        }
        echo "Done\n";

        if ($configCreated == true) {
            return true;
        } else {
            // Else return a WEBTEMPLATE error
            $msg = gettext("ERROR Creating application configuration parameters.\n");
            echo $msg;
            exit(1);
        }
    }


    /**
     * This function creates the application default site preferences
     *
     * @param string $filename The name of the parameters data file.
     *
     * @return mixed True is groups assigned error otherwise.
     *
     * @access public
     */
    public function createConfigPreferences(string $filename)
    {
        echo substr("Adding Site Preferences:         ", 0, 35);
        $setdataresult = $this->dbManager->setMode("datadriver");
        if (\g7mzr\db\common\Common::isError($setdataresult)) {
            echo "Unable to switch DBManager to data access mode\n";
            return exit(1);
        }

        // Get the parameters Datafile.
        $jsonfile = @fopen($filename, 'r');
        if ($jsonfile !== false) {
            $jsonstr = fread($jsonfile, filesize($filename));
            fclose($jsonfile);
        }
        if ($jsonstr === false) {
            $fileloaded = false;
            echo "Unable to load test parameter file: " . $filename . "\n";
            exit(1);
        } else {
            // Convert the json string to an array
            $this->newsitePreferences = json_decode($jsonstr, true);
            if ($this->newsitePreferences === null) {
                echo "Unable to convert site preferences file" . $filename . "\n";
                exit(1);
            }
        }
        $configCreated = true;

        //Load the Default Application Site Preferences

        foreach ($this->newsitePreferences as $key => $value) {
            if (is_int($value)) {
                $type = "int";
            } elseif (is_bool($value)) {
                $type = "bool";
            } else {
                $type = "string";
            }

            $insertData = array(
                'config_key'  => $key,
                'config_array' => "preferences",
                'config_value' => $value,
                'config_type' => $type
            );

            // Insert the preferences
            $result = $this->dbManager->getDataDriver()->dbinsert('config', $insertData);

            // Check if there was an error
            if (\g7mzr\db\common\Common::isError($result)) {
                $configCreated = false;
            }
        }

        echo "Done\n";

        if ($configCreated == true) {
            return true;
        } else {
            // Else return a WEBTEMPLATE error
            $msg = gettext("ERROR Creating application Site Preference Data.\n");
            echo $msg;
            exit(1);
        }
    }

    /**
     * This function adds the groups to the new database. It stops if it encounters
     * a problem
     *
     * @param string $filename The name of the parameters data file.
     *
     * @return boolean Always return true
     */
    public function updateTestParameters(string $filename)
    {

        echo substr("Adding Application Test Data:         ", 0, 35);
        $setdataresult = $this->dbManager->setMode("datadriver");
        if (\g7mzr\db\common\Common::isError($setdataresult)) {
            echo "Unable to switch DBManager to data access mode\n";
            return exit(1);
        }

        // Get the parameters Datafile.
        $jsonfile = @fopen($filename, 'r');
        if ($jsonfile !== false) {
            $jsonstr = fread($jsonfile, filesize($filename));
            fclose($jsonfile);
        }
        if ($jsonstr === false) {
            $fileloaded = false;
            echo "Unable to load test parameter file: " . $filename . "\n";
            exit(1);
        } else {
            // Convert the json string to an array
            $testparameters = json_decode($jsonstr, true);
            if ($testparameters === null) {
                echo "Unable to convert test parameter file" . $filename . "\n";
                exit(1);
            }
        }

        $saveResult = true;
        // Start the database transaction
        $this->dbManager->getDataDriver()->startTransaction();

        // Process the array
        foreach ($testparameters as $key => $value) {
            //  Create the array of data values to be updated
            $updateData = array('config_value' => $value);

            // Create the array of the data to be used to select the correct field to update
            $searchData = array('config_key'  => $key);

            // Update the preferences
            $result = $this->dbManager->getDataDriver()->dbupdate('config', $updateData, $searchData);

            // Check if there was an error
            if (\g7mzr\db\common\Common::isError($result)) {
                   $saveResult = false;
            }
        }
        $this->dbManager->getDataDriver()->endTransaction($saveResult);
        echo "Done\n";
        return $saveResult;
    }




    /**
     * This function updates the application default configuration Parameters
     *
     * @param string $filename The name of the parameters data file.
     *
     * @return mixed True is groups assigned error otherwise.
     *
     * @access public
     */
    public function updateConfigParameters(string $filename)
    {
        echo substr("Updating Configuration Parameters:         ", 0, 35);
        $setdataresult = $this->dbManager->setMode("datadriver");
        if (\g7mzr\db\common\Common::isError($setdataresult)) {
            echo "Unable to switch DBManager to data access mode\n";
            return exit(1);
        }

        // Get the parameters Datafile.
        $jsonfile = @fopen($filename, 'r');
        if ($jsonfile !== false) {
            $jsonstr = fread($jsonfile, filesize($filename));
            fclose($jsonfile);
        }
        if ($jsonstr === false) {
            $fileloaded = false;
            echo "Unable to load parameter file: " . $filename . "\n";
            exit(1);
        } else {
            // Convert the json string to an array
            $this->newParameters = json_decode($jsonstr, true);
            if ($this->newParameters === null) {
                echo "Unable to convert parameter file" . $filename . "\n";
                exit(1);
            }
        }
        $configUpdated = true;

        // Get the existing parameters and update the new parameter valuses.  This
        // includes deleting  unused parameters and adding newones.
        $oldParameters = $this->loadConfigArray("parameters");

        // Step throug newParameter list updating values from old parameters
        foreach ($this->newParameters as $key => $value) {
            if (array_key_exists($key, $oldParameters)) {
                $this->newParameters[$key] = $oldParameters[$key];
            }
        }

        $transactionResult = $this->dbManager->getDataDriver()->startTransaction();
        if ($transactionResult != true) {
            echo "Unable to start DB Transaction to update Application Parameters\n";
            exit(1);
        }

        // Delete the existing Application Parameters
        $searchData = array("config_array" => "parameters");
        $deleteResult = $this->dbManager->getDataDriver()->dbdeletemultiple('config', $searchData);
        if (\g7mzr\db\common\Common::isError($deleteResult)) {
                $configCreated = false;
        }

        if ($configCreated == true) {
            // Walk through the preferences array adding thye details
            // to the config table
            foreach ($this->newParameters as $key => $value) {
                if (is_int($value)) {
                    $type = "int";
                } elseif (is_bool($value)) {
                    $type = "bool";
                } else {
                    $type = "string";
                }

                $insertData = array(
                    'config_key'  => $key,
                    'config_array' => "parameters",
                    'config_value' => $value,
                    'config_type' => $type
                );

                // Insert the preferences
                $result = $this->dbManager->getDataDriver()->dbinsert('config', $insertData);

                // Check if there was an error
                if (\g7mzr\db\common\Common::isError($result)) {
                    $configCreated = false;
                }
            }
        }
        echo "Done\n";

        $this->dbManager->getDataDriver()->endTransaction($configUpdated);

        if ($configUpdated == true) {
            return true;
        } else {
            // Else return a WEBTEMPLATE error
            $msg = gettext("ERROR Updating application configuration parameters.\n");
            echo $msg;
            exit(1);
        }
    }





    /**
     * This function updates the application default User Preferences
     *
     * @param string $filename The name of the preferences data file.
     *
     * @return mixed True is groups assigned error otherwise.
     *
     * @access public
     */
    public function updateConfigPreferences(string $filename)
    {
        echo substr("Updating Site Preferences:                   ", 0, 35);
        $setdataresult = $this->dbManager->setMode("datadriver");
        if (\g7mzr\db\common\Common::isError($setdataresult)) {
            echo "Unable to switch DBManager to data access mode\n";
            return exit(1);
        }

        // Get the parameters Datafile.
        $jsonfile = @fopen($filename, 'r');
        if ($jsonfile !== false) {
            $jsonstr = fread($jsonfile, filesize($filename));
            fclose($jsonfile);
        }
        if ($jsonstr === false) {
            $fileloaded = false;
            echo "Unable to load site preferences file: " . $filename . "\n";
            exit(1);
        } else {
            // Convert the json string to an array
            $this->newsitePreferences = json_decode($jsonstr, true);
            if ($this->newsitePreferences === null) {
                echo "Unable to convert site preferenced file" . $filename . "\n";
                exit(1);
            }
        }
        $configUpdated = true;

        // Get the existing defaut user preferences and update.  This
        // includes deleting  unused user preferences and adding new ones.
        $oldParameters = $this->loadConfigArray("preferences");

        // Step throug newParameter list updating values from old parameters
        foreach ($this->newsitePreferences as $key => $value) {
            if (array_key_exists($key, $oldParameters)) {
                $this->newsitePreferences[$key] = $oldParameters[$key];
            }
        }

        $transactionResult = $this->dbManager->getDataDriver()->startTransaction();
        if ($transactionResult != true) {
            echo "Unable to start DB Transaction to update Site Preferences\n";
            exit(1);
        }

        // Delete the existing Application Parameters
        $searchData = array("config_array" => "preferences");
        $deleteResult = $this->dbManager->getDataDriver()->dbdeletemultiple('config', $searchData);
        if (\g7mzr\db\common\Common::isError($deleteResult)) {
                $configCreated = false;
        }

        if ($configCreated == true) {
            // Walk through the preferences array adding thye details
            // to the config table
            foreach ($this->newsitePreferences as $key => $value) {
                if (is_int($value)) {
                    $type = "int";
                } elseif (is_bool($value)) {
                    $type = "bool";
                } else {
                    $type = "string";
                }

                $insertData = array(
                    'config_key'  => $key,
                    'config_array' => "preferences",
                    'config_value' => $value,
                    'config_type' => $type
                );

                // Insert the preferences
                $result = $this->dbManager->getDataDriver()->dbinsert('config', $insertData);

                // Check if there was an error
                if (\g7mzr\db\common\Common::isError($result)) {
                    $configCreated = false;
                }
            }
        }
        echo "Done\n";

        $this->dbManager->getDataDriver()->endTransaction($configUpdated);

        if ($configUpdated == true) {
            return true;
        } else {
            // Else return a WEBTEMPLATE error
            $msg = gettext("ERROR Updating application site preferences.\n");
            echo $msg;
            exit(1);
        }
    }

    /**
     * Load Configuration Array
     *
     * This function loads the Configuration Array named in $arrayName from the
     * database
     *
     * @param string $arrayName The Name of the configuration array to be loaded.
     *
     * @throws \InvalidArgumentException If path has more than 4 elements.
     *
     * @return mixed True if the parameter array is loaded or an Error if it fails
     *
     * @access private
     */
    private function loadConfigArray(string $arrayName)
    {
        $gotdata = true;
        $fields = array(
            "config_key",
            "config_value",
            "config_type"
        );
        $searchdata = array(
            "config_array" => $arrayName
        );
        $result = $this->db->dbselectmultiple("config", $fields, $searchdata);
        if (\g7mzr\db\common\Common::isError($result)) {
            $gotdata = false;
            $errorMsg =  $result->getMessage();
            ;
        } else {
            $temparray = array();
            foreach ($result as $uao) {
                if ($uao['config_type'] == "bool") {
                    $temparray[$uao['config_key']]
                        = $this->setBool($uao['config_value']);
                } elseif ($uao['config_type'] == "int") {
                    $this->parameters[$key[0]][$key[1]][$key[2]][$key[3]]
                        = (int) $uao['config_value'];
                } else {
                    $this->parameters[$uao["config_key"]]
                        = (string) $uao['config_value'];
                }
            }
        }
        if ($gotdata == true) {
            return $temparray;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }
}
