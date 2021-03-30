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
}
