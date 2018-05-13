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
 * DataBase Class is a static class used to setup and update the database
 *
 * @category Webtemplate
 * @package  Install
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

class DataBase
{
    /**
     * This function creates or updates the database
     *
     * @param array   $installConfig Array with info needed to setup the app
     * @param boolean $unittestdb    True if a test system is to be set up
     *
     * @return true
     */
    public function createDatabase($installConfig, $unittestdb)
    {

        $installdsn = array(
            'phptype'  => $installConfig['database_type'],
            'hostspec' => $installConfig['database_host'],
            'username' => $installConfig['database_superuser'],
            'password' => $installConfig['database_superuser_passwd'],
            'disable_iso_date' => 'disable'
        );

        if ($installConfig['database_type'] == 'pgsql') {
            $installdsn['database'] = 'template1';
        }

        $dsn = array(
            'phptype'  => $installConfig['database_type'],
            'hostspec' => $installConfig['database_host'],
            'database' => $installConfig['database_name'],
            'username' => $installConfig['database_user'],
            'password' => $installConfig['database_user_passwd'],
            'disable_iso_date' => 'disable'
        );

        // Load the Database Abstraction layer for Wetemplate
        // Using the Database Superuser and management database
        $db = \webtemplate\db\DB::load($installdsn);

        // Test if it has loaded okay
        if (\webtemplate\general\General::isError($db)) {
            // If it has not loaded terminate the install.
            fwrite(STDERR, "FAILED: " . $db->getMessage() . "\n");
            exit(1);
        }


        // Create the Database User
        DataBase::createDatabaseUser(
            $db,
            $installConfig['database_user'],
            $installConfig['database_user_passwd'],
            $unittestdb
        );


        // Create the blank database owned by the application database user.
        // If $unittestdb is true drop and recreate the test database.
        $databaseExists = DataBase::buildDatabase(
            $db,
            $installConfig['database_name'],
            $installConfig['database_user'],
            $unittestdb
        );

        // Reload the abstraction layer with the applicate database
        // and user for the next actions
        $db = \webtemplate\db\DB::load($dsn);

        // test if the abstraction layer loaded okay.
        if (\webtemplate\general\General::isError($db)) {
            // If it has not loaded terminate the install
            fwrite(STDERR, "FAILED " . $db->getMessage() . "\n");
            exit(1);
        }


        // create the schema object
        $schema = new \webtemplate\db\schema\SchemaFunctions($db);


        // Check is the database exists
        if ($databaseExists == true) {
            DataBase::updateDatabase($db, $schema);
            DataBase::updateGroups($db, $schema, $groupNumber);
            DataBase::saveSchema($db);
        }

        // Check is the database exists
        if ($databaseExists == false) {
            // Create an array to hold the Group Data
            $groupNumber = array();

            DataBase:: createSchema($schema, $groupNumber);

            DataBase::createGroups($schema, $groupNumber);

            DataBase::saveSchema($db);

            DataBase::createDatabaseUsers($schema, $groupNumber);

            //Create Unit Test Users
            if ($unittestdb == true) {
                DataBase::initaliseTestSystem($schema, $groupNumber);
            }
            echo "Database Created\n\n";
        }
        return true;
    }


    /**
     * This function creates the database user.  It terminates the install if it
     * encounters any problems
     *
     * @param pointer $db         Database class object
     * @param string  $name       Database User Name
     * @param string  $passwd     Database Users password
     * @param boolean $unittestdb True if a test user is being created
     *
     * @return boolean Always return true
     */
    private function createDatabaseUser(&$db, $name, $passwd, $unittestdb)
    {
        $dbUser = $db->createUser(
            $name,
            $passwd,
            $unittestdb
        );

        // Test that the User was created or exists okay
        if (\webtemplate\general\General::isError($dbUser)) {
            // If the database create fails terminate the install.
            fwrite(STDERR, "FAILED " . $dbUser->getMessage() . "\n");
            exit(1);
        }
    }



    /**
     * This function creates the database user.  It terminates the install if it
     * encounters any problems
     *
     * @param pointer $db         Database class object
     * @param string  $dbname     Database name
     * @param string  $name       Database User Name
     * @param boolean $unittestdb True if a test database is being created
     *
     * @return boolean Always return true
     */
    private function buildDatabase(&$db, $dbname, $name, $unittestdb)
    {
        // Create the blank database owned by the application database user.
        // If $unittestdb is true drop and recreate the test database.

        $databaseExists = $db->databaseExists($dbname);

        // Test taht no error was encountered
        if (\webtemplate\general\General::isError($databaseExists)) {
            // If the database create fails terminate the install.
            fwrite(STDERR, "FAILED " . $databaseExists->getMessage() . "\n");
            exit(1);
        }

        if (($databaseExists == true) and ($unittestdb == true)) {
            $result = $db->dropDatabase($dbname);
            $databaseExists = false;
            if (\webtemplate\general\General::isError($result)) {
                // If the database create fails terminate the install.
                fwrite(STDERR, "FAILED " . $result->getMessage() . "\n");
                exit(1);
            }
        }

        if ($databaseExists == false) {
            $result = $db->createDatabase(
                $dbname,
                $name
            );


            // Test that no error was encountered
            if (\webtemplate\general\General::isError($result)) {
                // If the database create fails terminate the install.
                fwrite(STDERR, "FAILED " . $result->getMessage() . "\n");
                exit(1);
            }
        }

        return $databaseExists;
    }


    /**
     * This function updates and existing database.  It stops if it encounters
     * a problem
     *
     * @param pointer $db              Database object
     * @param pointer $schemaFunctions Schemafunctions class object
     *
     * @return boolean Always return true
     */
    private function updateDatabase(&$db, &$schemaFunctions)
    {

        // The database exists so check the schema and update if necessary
        $oldschema = $db->getSchema();
        if (\webtemplate\general\General::isError($oldschema)) {
            fwrite(STDERR, "FAILED Getting Schema\n");
            exit(1);
        }

        $result = $schemaFunctions->updateSchema(
            \webtemplate\db\schema\SchemaData::$schema,
            $oldschema['schema']
        );

        if (\webtemplate\general\General::isError($result)) {
            fwrite(STDERR, "Error updating the Schema");
            exit(1);
        }

        echo "Database updated\n\n";
        return true;
    }


    /**
     * This function adds the tables to the new database. It stops if it encounters
     * a problem
     *
     * @param pointer $schemaFunctions Schemafunctions class object
     * @param array   $groupNumber     GroupID/Name cross reference
     *
     * @return boolean Always return true
     */
    private function createSchema(&$schemaFunctions, &$groupNumber)
    {

        // The database does not exists so create a new one
        $result = $schemaFunctions->newSchema(
            \webtemplate\db\schema\SchemaData::$schema
        );
        if (\webtemplate\general\General::isError($result)) {
            fwrite(STDERR, "Failed to create database schema\n");
            exit(1);
        }
    }

    /**
     * This function adds the groups to the new database. It stops if it encounters
     * a problem
     *
     * @param pointer $schemaFunctions Schemafunctions class object
     * @param array   $groupNumber     GroupID/Name cross reference
     *
     * @return boolean Always return true
     */
    private function createGroups(&$schemaFunctions, &$groupNumber)
    {

        // Add Default system groups from the groups array in schema.php
        $result = $schemaFunctions->createGroups(
            \webtemplate\db\schema\SchemaData::$defaultgroups,
            $groupNumber
        );

        // Always check that result is not an error
        if (\webtemplate\general\General::isError($result)) {
            fwrite(STDERR, $result->getMessage() . "\n\n");
            exit(1);
        }
    }


    /**
     * This function adds the groups to the new database. It stops if it encounters
     * a problem
     *
     * @param pointer $db              Database object
     * @param pointer $schemaFunctions Schema functions class object
     * @param array   $groupNumber     GroupID/Name cross reference
     *
     * @return boolean Always return true
     */
    private function updateGroups(&$db, &$schemaFunctions, &$groupNumber)
    {

        $oldschema = $db->getSchema();
        if (\webtemplate\general\General::isError($oldschema)) {
            fwrite(STDERR, "FAILED Getting Schema\n");
            exit(1);
        }

        $result = $schemaFunctions->updateGroups(
            \webtemplate\db\schema\SchemaData::$defaultgroups,
            $oldschema['groups']
        );
        if (\webtemplate\general\General::isError($result)) {
            fwrite(STDERR, "Error Updating the Groups\n");
            exit(1);
        }
    }


    /**
     * This function adds the user to the new database. It stops if it encounters
     * a problem
     *
     * @param pointer $schemaFunctions Schemafunctions class object
     * @param array   $groupNumber     GroupID/Name cross reference
     *
     * @return boolean Always return true
     */
    private function createDatabaseUsers(&$schemaFunctions, &$groupNumber)
    {
        // Create Default User
        $result = $schemaFunctions->createUsers(
            \webtemplate\db\schema\SchemaData::$defaultUsers,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            fwrite(STDERR, "Error creating main users");
            exit(1);
        }
    }

    /**
     * This function sets up the test system  It stops if it encounters
     * a problem
     *
     * @param pointer $schemaFunctions Schemafunctions class object
     * @param array   $groupNumber     GroupID/Name cross reference
     *
     * @return boolean Always return true
     */
    private function initaliseTestSystem(&$schemaFunctions, &$groupNumber)
    {

        // Setup the test Groups if required
        $result = $schemaFunctions->createGroups(
            \webtemplate\db\schema\SchemaData::$testgroups,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            fwrite(STDERR, $result->getMessage() . "\n\n");
            exit(1);
        }

        // Set up the PHPUNIT and SELENIUM Test Users
        $result = $schemaFunctions->createUsers(
            \webtemplate\db\schema\SchemaData::$testUsers,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            fwrite(STDERR, "Error creating test users");
            exit(1);
        }
    }


    /**
     * This function saves the schema to the database.  It stops if it encounters
     * a problem
     *
     * @param pointer $db Database class object
     *
     * @return boolean Always return true
     */
    private function saveSchema(&$db)
    {
        $result = $db->saveSchema(
            \webtemplate\db\schema\SchemaData::$schema_version,
            \webtemplate\db\schema\SchemaData::$schema,
            \webtemplate\db\schema\SchemaData::$defaultgroups
        );
        if (\webtemplate\general\General::isError($result)) {
            fwrite(STDERR, "Error Saving the new Schema to the database\n");
            exit(1);
        }
    }
}
