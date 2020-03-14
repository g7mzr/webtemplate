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

/**
 * DataBase Class is a static class used to setup and update the database
 **/
class DataBase
{
    /**
     * This function creates or updates the database
     *
     * @param array   $installConfig Array with info needed to setup the app.
     * @param boolean $unittestdb    True if a test system is to be set up.
     *
     * @return true
     */
    public function createDatabase(array $installConfig, bool $unittestdb)
    {
        $dsn = array(
            'dbtype'  => $installConfig['database_type'],
            'hostspec' => $installConfig['database_host'],
            'databasename' => $installConfig['database_name'],
            'username' => $installConfig['database_user'],
            'password' => $installConfig['database_user_passwd'],
            'disable_iso_date' => 'disable'
        );

        try {
            $dbmanager = new \g7mzr\db\DBManager(
                $dsn,
                $installConfig['database_superuser'],
                $installConfig['database_superuser_passwd']
            );
        } catch (Exception $ex) {
            echo $ex->getMessage();
            return exit(1);
        }

        $result = $dbmanager->setMode("admin");
        if (\g7mzr\db\common\Common::isError($result)) {
            echo "Unable to switch DBManager to admin mode\n";
            return exit(1);
        }

        // Create the Database User
        DataBase::createDatabaseUser(
            $dbmanager,
            $installConfig['database_user'],
            $installConfig['database_user_passwd'],
            $unittestdb
        );


        // Create the blank database owned by the application database user.
        // If $unittestdb is true drop and recreate the test database.
        $databaseExists = DataBase::buildDatabase(
            $dbmanager,
            $installConfig['database_name'],
            $installConfig['database_user'],
            $unittestdb
        );


        // Create or update the schema
        $setschemaresult = $dbmanager->setMode("schema");
        if (\g7mzr\db\common\Common::isError($setschemaresult)) {
            echo "Unable to switch DBManager to schema mode\n";
            return exit(1);
        }

        $schemafile = __DIR__ . "/configure/schema.json";
        $schemabuilt = DataBase::buildSchema(
            $dbmanager,
            $schemafile,
            $databaseExists
        );

        // Populate the database with initial data and where appropriate test data.
        // Create or update the schema
        $setdataresult = $dbmanager->setMode("datadriver");
        if (\g7mzr\db\common\Common::isError($setdataresult)) {
            echo "Unable to switch DBManager to data access mode\n";
            return exit(1);
        }

        $groupNumber = array();
        $groupfile = __DIR__ . "/configure/default_groups.json";
        $defaultgroups = DataBase::createGroups(
            $dbmanager,
            $groupfile,
            $groupNumber
        );

        $userfile = __DIR__ . "/configure/default_users.json";
        $defaultusers = DataBase::createusers(
            $dbmanager,
            $userfile,
            $groupNumber
        );


        // Create the Test Database.
        if ($unittestdb == true) {
            $groupfile = __DIR__ . "/configure/test_groups.json";
            $testgroups = DataBase::createGroups(
                $dbmanager,
                $groupfile,
                $groupNumber
            );
            $userfile = __DIR__ . "/configure/test_users.json";
            $testusers = DataBase::createusers(
                $dbmanager,
                $userfile,
                $groupNumber
            );
        }

        echo "Database Created\n\n";
        return true;
    }


    /**
     * This function creates the database user.  It terminates the install if it
     * encounters any problems
     *
     * @param \g7mzr\db\DBManager $dbmanager  Database class object.
     * @param string              $name       Database User Name.
     * @param string              $passwd     Database Users password.
     * @param boolean             $unittestdb True if a test user is being created.
     *
     * @return boolean Always return true
     */
    private function createDatabaseUser(
        \g7mzr\db\DBManager &$dbmanager,
        string $name,
        string $passwd,
        bool $unittestdb
    ) {
        echo substr("Checking if database user exists      ", 0, 35);
        $checkuser = $dbmanager->getAdminDriver()->userExists($name);
        if (\g7mzr\db\common\Common::isError($checkuser)) {
            echo "Error checking if databaseuser exists\n";
            return exit(1);
        }

        if ($checkuser === true) {
            echo "Ok: Exists\n";
        } else {
            $result = $dbmanager->getAdminDriver()->createUser($name, $passwd, $unittestdb);
            if (\g7mzr\db\common\Common::isError($checkuser)) {
                echo "Error checking if databaseuser exists\n";
                return exit(1);
            }
            echo "Ok: Created\n";
        }
        return true;
    }



    /**
     * This function creates the database user.  It terminates the install if it
     * encounters any problems
     *
     * @param \g7mzr\db\DBManager $dbmanager  Database class object.
     * @param string              $dbname     Database name.
     * @param string              $name       Database User Name.
     * @param boolean             $unittestdb True if a test database is being created.
     *
     * @return boolean Always return true
     */
    private function buildDatabase(
        \g7mzr\db\DBManager &$dbmanager,
        string $dbname,
        string $name,
        bool $unittestdb
    ) {
        // Create the blank database owned by the application database user.
        // If $unittestdb is true drop and recreate the test database.

        // Check if the database exists
        echo substr("Checking if database exists           ", 0, 35);
        $databaseExists = $dbmanager->getAdminDriver()->databaseExists($dbname);
        if (\g7mzr\db\common\Common::isError($databaseExists)) {
            fwrite(STDERR, "FAILED " . $databaseExists->getMessage() . "\n");
            exit(1);
        }

        if ($databaseExists == true) {
            echo "Ok: Exists\n";
        } else {
            "No: New database\n";
        }

        // If the database exists and it is a test system drop it.
        if (($databaseExists == true) and ($unittestdb == true)) {
            echo substr("Dropping test database               ", 0, 35);
            $result = $dbmanager->getAdminDriver()->dropDatabase($dbname);
            $databaseExists = false;
            if (\g7mzr\db\common\Common::isError($result)) {
                // If the database create fails terminate the install.
                fwrite(STDERR, "FAILED " . $result->getMessage() . "\n");
                exit(1);
            }
        }
        echo "Ok: Database dropped\n";

        if ($databaseExists == false) {
            echo substr("Creating New Database:             ", 0, 35);
            $result = $dbmanager->getAdminDriver()->createDatabase(
                $dbname,
                $name
            );
            // Test that no error was encountered
            if (\g7mzr\db\common\Common::isError($result)) {
                // If the database create fails terminate the install.
                fwrite(STDERR, "FAILED " . $result->getMessage() . "\n");
                exit(1);
            }
        }
        echo "Ok: Created\n";
        return $databaseExists;
    }

    /**
     * This function creates the database schema.  It terminates the install if it
     * encounters any problems
     *
     * @param \g7mzr\db\DBManager $dbmanager      Database class object.
     * @param string              $schemafile     Name of the schema file.
     * @param boolean             $databaseExists True if the database exists.
     *
     * @return boolean Always return true
     */
    private function buildSchema(
        \g7mzr\db\DBManager $dbmanager,
        string $schemafile,
        bool $databaseExists
    ) {
        try {
            $schemaManager = new \g7mzr\db\SchemaManager($dbmanager);
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
     * @param \g7mzr\db\DBManager $dbmanager   Database class object.
     * @param string              $filename    The name of the group data file.
     * @param array               $groupNumber GroupID/Name cross reference.
     *
     * @return boolean Always return true
     */
    private function createGroups(
        \g7mzr\db\DBManager $dbmanager,
        string $filename,
        array &$groupNumber
    ) {

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
            $result = $dbmanager->getDataDriver()->dbinsert('groups', $insertdata);


            // Always check that result is not an error
            if (!\g7mzr\db\common\Common::isError($result)) {
                $groupId = $dbmanager->getDataDriver()->dbinsertid(
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
     * @param \g7mzr\db\DBManager $dbmanager   Database class object.
     * @param string              $filename    The name of the group data file.
     * @param array               $groupNumber GroupID/Name cross reference.
     *
     * @return boolean Always return true
     */
    private function createUsers(
        \g7mzr\db\DBManager $dbmanager,
        string $filename,
        array &$groupNumber
    ) {

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
            $result = $dbmanager->getDataDriver()->dbinsert('users', $insertdata);

            // Check if there was an error
            if (\g7mzr\db\common\Common::isError($result)) {
                $errorMsg = gettext("ERROR Creating Database users. ");
                $errorMsg .= gettext("Unable to create user: ");
                $errorMsg .= $name;
                $errorMsg .= "\n";
                echo $errorMsg;
                exit(1);
            }


            // Get the Id of the iuser just inserted
            $userId = $dbmanager->getDataDriver()->dbinsertid(
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
            $result = DataBase::processUserGroups(
                $dbmanager,
                $userId,
                $groupNumber,
                $userdata
            );
            if (\g7mzr\webtemplate\general\General::isError($result)) {
                $errorMsg = gettext("ERROR Creating Database users. ");
                $errorMsg .= gettext("Unable to create groups for user: ");
                $errorMsg .= $name;
                $errorMsg .= "\n";
                echo $errorMsg;
                exit(1);
            }

            // Add any user specific preferences.
            $result = DataBase::processUserPrefs(
                $dbmanager,
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
     * @param \g7mzr\db\DBManager $dbmanager   Database class object.
     * @param integer             $userId      The users id.
     * @param array               $groupNumber The ids of each of the installed groups.
     * @param array               $userdata    The array used to create the users.
     *
     * @return mixed True is groups assigned pera error otherwise.
     *
     * @access private
     */
    private function processUserGroups(
        \g7mzr\db\DBManager $dbmanager,
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
                $result = $dbmanager->getDataDriver()->dbinsert('user_group_map', $mapdata);

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
     * @param \g7mzr\db\DBManager $dbmanager Database class object.
     * @param integer             $userId    The users id.
     * @param array               $userdata  The array used to create the users.
     *
     * @return mixed True is groups assigned pera error otherwise.
     *
     * @access private
     */
    private function processUserPrefs(
        \g7mzr\db\DBManager $dbmanager,
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
                $result = $dbmanager->getDataDriver()->dbinsert('userprefs', $insertData);

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
