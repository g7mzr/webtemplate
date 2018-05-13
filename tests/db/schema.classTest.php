<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\unittest;

use PHPUnit\Framework\TestCase;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// Load the Test Database Configuration File
require_once dirname(__FILE__) .'/../_data/database.php';


/**
 * Test the Schema creation and update functions
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class SCHEMATest extends TestCase
{

    /**
     * Schema Object
     *
     * @var webtemplate\db\SchemaFunctions Object
     */
    protected $object;

    /**
     *  Database Driver
     *
     * @var webtemplate\db\DB_DRIVER_MOCK
     */
    protected $db;
    /**
     * DSN class variable
     *
     * @var array
     */
    protected $dsn;

    /**
     * Test Table
     *
     * @var array
     */
    protected $testTable;


    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        global $testdsn, $options;

        $GLOBALS["unittest"] = true;
        $this->dsn = $testdsn;
        $this->dsn['phptype'] = 'mock';
        $this->db = \webtemplate\db\DB::load($this->dsn);

        if (!\webtemplate\general\General::isError($this->db)) {
            $this->databasecreated = true;
            $this->object = new \webtemplate\db\schema\SchemaFunctions($this->db);
        }

        $this->testTable = array(
            'COLUMNS' => array(
                'user_id' => array(
                    'TYPE' => 'serial',
                    'PRIMARY' => '1'
                ),
                'user_name' => array(
                    'TYPE' => 'varchar(64)',
                    'NOTNULL' => '1',
                    'UNIQUE' => 1
                ),
                'user_passwd' => array(
                    'TYPE' => 'varchar(255)',
                    'NOTNULL' => '1'
                ),
                'user_realname' => array(
                    'TYPE' => 'varchar(64)'
                ),
                'user_email' => array(
                    'TYPE' => 'varchar(64)'
                ),
                'user_enabled' => array(
                    'TYPE' => 'char(1)',
                    'DEFAULT' => 'Y',
                    'NOTNULL' => '1'
                ),
                'user_disable_mail' => array(
                    'TYPE' => 'char(1)',
                    'DEFAULT' => 'N',
                    'NOTNULL' => '1'
                ),
                'last_seen_date' => array(
                    'TYPE' => 'DATETIME'
                ),
                'passwd_changed' => array(
                    'TYPE' => 'DATETIME'
                ),
                'last_failed_login' => array(
                    'TYPE' => 'DATETIME'
                )
            )
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
        $this->db->disconnect();
    }

    /**
     * Test to create the Database Schema
     *
     * @group schema
     * @group unittest
     *
     * @return null
     */
    public function testCreateSchema()
    {
        // Test Create Schema which fails
        $result = $this->object->newSchema(
            \webtemplate\db\schema\SchemaData::$schema
        );
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR creating Database Schema',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No error creating schema");
        }


        // Test Create Schema which passes
        $functions = array(
            'newSchema' => array(
                'pass' => true
            )
        );
        $this->db->control($functions, \webtemplate\db\schema\SchemaData::$schema);
        $result = $this->object->newSchema(
            \webtemplate\db\schema\SchemaData::$schema
        );
        if (\webtemplate\general\General::isError($result)) {
            $this->fail(__FUNCTION__ . ": Failed to create schema");
        } else {
            $this->assertTrue($result);
        }
    }


    /**
     * Test to add groups to the database
     *
     * @group schamea
     * @group unittest
     *
     * @return null
     */
    public function testCreateGroups()
    {

        $groupNumber = array();
        $result = $this->object->createGroups(
            \webtemplate\db\schema\SchemaData::$defaultgroups,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Creating Database Groups',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No error when testing create groups");
        }


        // Fail getting Group ID
        $functions = array(
            'createGroups' => array(
                "pass" => true,
                "id" => false
            )
        );

        $testdata = array(
        );
        $this->db->control($functions, $testdata);
        $result = $this->object->createGroups(
            \webtemplate\db\schema\SchemaData::$defaultgroups,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Creating Database Groups',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No error when testing create groups");
        }

        // Pass the create groups
        $functions = array(
            'createGroups' => array(
                "pass" => true,
                "id" => true
            )
        );

        // Pass the create groups function
        $testdata = array(
            'admin' => 1,
            'editusers' => 2,
            'editgroups' => 3
        );
        $this->db->control($functions, $testdata);
        $result = $this->object->createGroups(
            \webtemplate\db\schema\SchemaData::$defaultgroups,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            $this->fail(__FUNCTION__ . ": Error when testing create groups");
        } else {
            $this->assertTrue($result);
            $this->assertEquals(1, $groupNumber['admin']);
            $this->assertEquals(2, $groupNumber['editusers']);
            $this->assertEquals(3, $groupNumber['editgroups']);
        }
    }

    /**
     * Test to add users with no user preferences to the database
     *
     * @group schema
     * @group unittest
     *
     * @return null
     */
    public function testCreateUsers()
    {
        $groupNumber = array(
            'admin' => 1,
            'editusers' => 2,
            'editgroups' => 3
        );

        // Fail inserting user into the database
        $result = $this->object->createUsers(
            \webtemplate\db\schema\SchemaData::$defaultUsers,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Creating Database users',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No error when testing create users");
        }


        // Fail getting the user id
        $functions = array(
            'createUsers' => array(
                "pass" => true,
                "id" => false
            )
        );
        $testdata = array(
        );
        $this->db->control($functions, $testdata);
        $result = $this->object->createUsers(
            \webtemplate\db\schema\SchemaData::$defaultUsers,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Creating Database users',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No error when testing create users");
        }

        // Fail setting the user group map
        $functions = array(
            'createUsers' => array(
                "pass" => true,
                "id" => true
            )
        );
        $testdata = array(
            'admin' => 1
        );
        $this->db->control($functions, $testdata);
        $result = $this->object->createUsers(
            \webtemplate\db\schema\SchemaData::$defaultUsers,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Creating Database users',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No error when testing create users");
        }


        // Set the user group map.
        // No User Preferences so it passes.
        $functions = array(
            'createUsers' => array(
                "pass" => true,
                "id" => true
            ),
            'processUserGroups' => array(
                "pass" => true
            )
        );
        $testdata = array(
            'admin' => 1
        );
        $this->db->control($functions, $testdata);
        $result = $this->object->createUsers(
            \webtemplate\db\schema\SchemaData::$defaultUsers,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            $this->fail(__FUNCTION__ . ": Error when testing create users");
        } else {
            $this->assertTrue($result);
        }
    }

    /**
     * Test to add users with no user preferences to the database
     *
     * @group schema
     * @group unittest
     *
     * @return null
     */
    public function testCreateUsersPasswdFail()
    {
        $groupNumber = array(
            'admin' => 1,
            'editusers' => 2,
            'editgroups' => 3
        );

        // Set the globals password fail
        $GLOBALS['passwdfail'] = true;


        // Fail inserting user into the database
        $result = $this->object->createUsers(
            \webtemplate\db\schema\SchemaData::$defaultUsers,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'Unable to create Encrypted Password',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No error when testing create users");
        }
    }



    /**
     * Test to add users with preferences to the database
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testCreateUserswithPreferences()
    {
        // Set up group numbers
        $groupNumber = array(
            'admin' => 1,
            'editusers' => 2,
            'editgroups' => 3
        );

        // Add preferences to a user
        $localuser = \webtemplate\db\schema\SchemaData::$defaultUsers;
        $localuser['admin']['prefs'] = array(
            'theme'       => 'text',
            'zoomtext'    => 'false',
            'displayrows' => '2'
        );


        // Test to get to the user preferences.  Fail at add preferences
        $functions = array(
            'createUsers' => array(
                "pass" => true,
                "id" => true
            ),
            'processUserGroups' => array(
                "pass" => true
            )
        );
        $testdata = array(
            'admin' => 1
        );
        $this->db->control($functions, $testdata);
        $result = $this->object->createUsers(
            $localuser,
            $groupNumber
        );
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'Unable to create preferences for user',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No error when testing create users");
        }
    }



    /**
     * Function to test updating Schema with no change
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaNoChange()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;

        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);

        if (\webtemplate\general\General::isError($result)) {
            $this->fail(__FUNCTION__ . ": Error updating schema (No change)");
        } else {
            $this->assertTrue($result);
        }
    }

    /**
     * Function to test updating Schema by adding a new table to the database
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaNewTable()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['groups'] = $this->testTable;

        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Schema',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No Error updating schema (New Table)");
        }

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $this->db->control($functions, $testSchemaNew);
        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(__FUNCTION__ . ": Error updating schema (New Table)");
        }
    }

    /**
     * Function to test updating Schema by dropping a table from the database
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaDropTable()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['groups'] = $this->testTable;
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;

        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Schema',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No Error updating schema (Drop Table)");
        }


        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array("groups");

        $this->db->control($functions, $testdata);
        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(__FUNCTION__ . ": Error updating schema (Drop Table)");
        }
    }

    /**
     * Function to test updating Schema by adding a column to a table in the database
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaNewColumn()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'NOTNULL' => 1
        );

        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Schema',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No Error updating schema (New Table)");
        }

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array("users", "test");
        $this->db->control($functions, $testdata);
        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(__FUNCTION__ . ": Error updating schema (New Table)");
        }
    }


    /**
     * Function to test updating Schema by dropping  a column to a
     * table in the database
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaDropColumn()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'NOTNULL' => 1
        );
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;

        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Schema',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No Error updating schema (Drop Column)");
        }

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array("users", "test");
        $this->db->control($functions, $testdata);
        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(__FUNCTION__ . ": Error updating schema (Drop Column)");
        }
    }


    /**
     * Function to test updating Schema by changing the column type
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaAlterColumnFail()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'NOTNULL' => 1
        );
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['COLUMNS']['test'] = array(
            'type' => 'bigint',
            'NOTNULL' => 1
        );

        $functions = array(
            'updateSchema' => array(
                "fail" => true
            )
        );
        $testdata = array(
            "users",
            "test",
            'type',
            $testSchemaNew['users']['COLUMNS']['test'],
            false
        );
        $this->db->control($functions, $testdata);


        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Schema',
                $result->getMessage()
            );
        } else {
            $this->fail(
                __FUNCTION__ . ": No Error updating schema (Alter Column Type Fail)"
            );
        }
    }


    /**
     * Function to test updating Schema by changing the column type
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaAlterColumnType()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'NOTNULL' => 1
        );
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['COLUMNS']['test'] = array(
            'type' => 'bigint',
            'NOTNULL' => 1
        );

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array(
            "users",
            "test",
            'type',
            $testSchemaNew['users']['COLUMNS']['test'],
            false
        );
        $this->db->control($functions, $testdata);


        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(
                __FUNCTION__ . ": Error updating schema (Alter Column Type)"
            );
        }
    }


    /**
     * Function to test updating Schema by adding NOTNULL to the Column
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaAlterColumnAddNOTNULL()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['COLUMNS']['test'] = array(
            'type' => 'integer'
        );
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'NOTNULL' => 1
        );

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array(
            "users",
            "test",
            'NOTNULL',
            $testSchemaNew['users']['COLUMNS']['test'],
            false
        );
        $this->db->control($functions, $testdata);


        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(
                __FUNCTION__ . ": Error updating schema (Alter Column NOTNULL)"
            );
        }
    }

    /**
     * Function to test updating Schema by dropping NOTNULL from a Column
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaAlterColumnDropNOTNULL()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'NOTNULL' => 1
        );
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['COLUMNS']['test'] = array(
            'type' => 'integer'
        );

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array(
            "users",
            "test",
            'NOTNULL',
            $testSchemaNew['users']['COLUMNS']['test'],
            false
        );
        $this->db->control($functions, $testdata);


        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(
                __FUNCTION__ . ": Error updating schema (Alter Column NOTNULL)"
            );
        }
    }


    /**
     * Function to test updating Schema by dropping NOTNULL from a Column bu setting
     * NOTNULL to zero in the array
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaAlterColumnDropNOTNULLValueZero()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'NOTNULL' => 1
        );
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'NOTNULL' => 0
        );

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array(
            "users",
            "test",
            'NOTNULL',
            $testSchemaNew['users']['COLUMNS']['test'],
            false
        );
        $this->db->control($functions, $testdata);


        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(
                __FUNCTION__ . ": Error updating schema (Alter Column NOTNULL)"
            );
        }
    }

    /**
     * Function to test updating Schema by adding a default value to the column
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaAlterColumnAddDefault()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['COLUMNS']['test'] = array(
            'type' => 'integer'
        );
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'DEFAULT' => 'Y'
        );

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array(
            "users",
            "test",
            'DEFAULT',
            $testSchemaNew['users']['COLUMNS']['test'],
            false
        );
        $this->db->control($functions, $testdata);


        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(
                __FUNCTION__ . ": Error updating schema (Alter Column DEFAULT)"
            );
        }
    }

    /**
     * Function to test updating Schema by adding a default value to the column
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaAlterColumnDropDefault()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'DEFAULT' => 'Y'
        );
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['COLUMNS']['test'] = array(
            'type' => 'integer'
        );

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array(
            "users",
            "test",
            'DEFAULT',
            $testSchemaNew['users']['COLUMNS']['test'],
            false
        );
        $this->db->control($functions, $testdata);


        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(
                __FUNCTION__ . ": Error updating schema (Alter Column DEFAULT)"
            );
        }
    }

    /**
     * Function to test updating Schema by dropping a constraint to the column
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaAlterColumnDropCONTRAINTS()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'CONSTRAINTS' => array('table' => 'groups', 'column' => 'group_id')
        );
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['COLUMNS']['test'] = array(
            'type' => 'integer'
        );

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array(
            "users",
            "test",
            'PK',
            $testSchemaNew['users']['COLUMNS']['test'],
            true,
            'delete'
        );
        $this->db->control($functions, $testdata);


        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(
                __FUNCTION__ . ": Error updating schema (Drop Constraints)"
            );
        }
    }

    /**
     * Function to test updating Schema by adding a constraint to the column
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaAlterColumnAddCONTRAINTS()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['COLUMNS']['test'] = array(
            'type' => 'integer'
        );
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'CONSTRAINTS' => array('table' => 'groups', 'column' => 'group_id')
        );

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array(
            "users",
            "test",
            'PK',
            $testSchemaNew['users']['COLUMNS']['test'],
            false,
            'add'
        );
        $this->db->control($functions, $testdata);


        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(
                __FUNCTION__ . ": Error updating schema (Drop Constraints)"
            );
        }
    }


    /**
     * Function to test updating Schema by changing the constraint on a column
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaAlterColumnChangeCONTRAINTS()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'CONSTRAINTS' => array('table' => 'group', 'column' => 'group_id')
        );
        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['COLUMNS']['test'] = array(
            'type' => 'integer',
            'CONSTRAINTS' => array('table' => 'groups', 'column' => 'group_id')
        );

        $functions = array(
            'updateSchema' => array(
                "pass" => true
            )
        );
        $testdata = array(
            "users",
            "test",
            'PK',
            $testSchemaNew['users']['COLUMNS']['test'],
            true,
            'update'
        );
        $this->db->control($functions, $testdata);


        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(
                __FUNCTION__ . ": Error updating schema (Drop Constraints)"
            );
        }
    }


    /**
     * Function to test updating Schema by creating an index which fails
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaCreateIndexFail()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;

        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['INDEXES'] = array(
           'tokens_user_id_idx' => array('COLUMN' => 'user_id')
        );

        // Fail when Table has never had any indexs.
        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Schema',
                $result->getMessage()
            );
        } else {
            $this->fail(
                __FUNCTION__ . ": NO Error updating schema (Add Index)"
            );
        }

        // Fail when a table has one or more index and a new one is added
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['INDEXES'] = array(
            'tokens_user_id_idx' => array('COLUMN' => 'user_id')
        );

        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['INDEXES'] = array(
           'tokens_user_id_idx' => array('COLUMN' => 'user_id'),
            'groups_user_id_idx' => array('COLUMN' => 'group_id')
        );


        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Schema',
                $result->getMessage()
            );
        } else {
            $this->fail(
                __FUNCTION__ . ": NO Error updating schema (Add Index)"
            );
        }
    }


    /**
     * Function to test updating Schema by creating an index
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaCreateIndex()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;

        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['INDEXES'] = array(
           'tokens_user_id_idx' => array('COLUMN' => 'user_id')
        );

        $functions = array(
            'processIndex' => array(
                "pass" => true
            )
        );
        $testdata = array(
            'users',
            'tokens_user_id_idx',
            'user_id'
        );
        $this->db->control($functions, $testdata);

        // Fail when Table has never had any indexs.
        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(
                __FUNCTION__ . ": Error updating schema (Add Index)"
            );
        }

        // Fail when a table has one or more index and a new one is added

        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['INDEXES'] = array(
            'tokens_user_id_idx' => array('COLUMN' => 'user_id')
        );

        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['INDEXES'] = array(
            'tokens_user_id_idx' => array('COLUMN' => 'user_id'),
            'groups_user_id_idx' => array('COLUMN' => 'group_id')
        );


        $functions = array(
            'processIndex' => array(
                "pass" => true
            )
        );
        $testdata = array(
            'users',
            'groups_user_id_idx',
            'group_id'
        );
        $this->db->control($functions, $testdata);
        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(
                __FUNCTION__ . ": NO Error updating schema (Add Index)"
            );
        }
    }


    /**
     * Function to test updating Schema by dropping an index Fail
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaDropIndexFail()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['INDEXES'] = array(
           'tokens_user_id_idx' => array('COLUMN' => 'user_id')
        );


        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;

        // Fail when Table has never had any indexs.
        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Schema',
                $result->getMessage()
            );
        } else {
            $this->fail(
                __FUNCTION__ . ": No Error updating schema (Drop Index)"
            );
        }
    }


    /**
     * Function to test updating Schema by dropping an index Fail
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateSchemaUpdateIndexFail()
    {
        $testSchemaOld = array();
        $testSchemaOld['users'] = $this->testTable;
        $testSchemaOld['users']['INDEXES'] = array(
           'tokens_user_id_idx' => array('COLUMN' => 'user_id')
        );


        $testSchemaNew = array();
        $testSchemaNew['users'] = $this->testTable;
        $testSchemaNew['users']['INDEXES'] = array(
           'tokens_user_id_idx' => array('COLUMN' => 'group_id')
        );

        // Fail to update the index
        $result = $this->object->updateSchema($testSchemaNew, $testSchemaOld);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Schema',
                $result->getMessage()
            );
        } else {
            $this->fail(
                __FUNCTION__ . ": No Error updating schema (Drop Index)"
            );
        }
    }


    /**
     * Function to test updating Schema
     * Fail to add a new Group to the database
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testAddGroupFail()
    {
        $oldGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $newGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $newGroups['test'] = array(
            'description' => 'This is a UNIT Test Group',
            'useforproduct' => 'N',
            'autogroup' => 'N',
            'editable' => 'N',
            'admingroup' => 'Y'
        );
        $result = $this->object->updateGroups($newGroups, $oldGroups);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Groups',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No error updating groups");
        }
    }



    /**
     * Function to test updating Schema
     * Add a new Group to the database
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testAddGroup()
    {
        $oldGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $newGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $newGroups['test'] = array(
            'description' => 'This is a UNIT Test Group',
            'useforproduct' => 'N',
            'autogroup' => 'N',
            'editable' => 'N',
            'admingroup' => 'Y'
        );



        $functions = array(
            'updateGroups' => array(
                "pass" => true
            )
        );
        $testdata = array(
        );
        $this->db->control($functions, $testdata);

        $result = $this->object->updateGroups($newGroups, $oldGroups);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(__FUNCTION__ . ": Error updating groups");
        }
    }





    /**
     * Function to test updating Schema
     * Fail to drop a new Group from the database
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testDropGroupFail()
    {
        $oldGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $newGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $oldGroups['test'] = array(
            'description' => 'This is a UNIT Test Group',
            'useforproduct' => 'N',
            'autogroup' => 'N',
            'editable' => 'N',
            'admingroup' => 'Y'
        );
        $result = $this->object->updateGroups($newGroups, $oldGroups);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Groups',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No error updating groups");
        }
    }



    /**
     * Function to test updating Schema
     * Drop a Group from the database
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testDropGroup()
    {
        $oldGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $newGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $oldGroups['test'] = array(
            'description' => 'This is a UNIT Test Group',
            'useforproduct' => 'N',
            'autogroup' => 'N',
            'editable' => 'N',
            'admingroup' => 'Y'
        );


        // Group to be deleted is not found in the database
        $functions = array(
            'processDropGroups' => array(
                "pass" => true,
                "delete" => true
            )
        );
        $testdata = array(
        );
        $this->db->control($functions, $testdata);

        $result = $this->object->updateGroups($newGroups, $oldGroups);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(__FUNCTION__ . ": No Error updating groups");
        }
    }


    /**
     * Function to test updating Schema
     * Update a group in the database.  Report Failure
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateGroupFail()
    {
        $oldGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $newGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $newGroups['admin'] = array(
            'description' => 'This is a UNIT Test Group',
            'useforproduct' => 'N',
            'autogroup' => 'N',
            'editable' => 'N',
            'admingroup' => 'Y'
        );


        // Group to be deleted is not found in the database
        /**
        * $functions = array(
            '_processDropGroups' => array(
                "pass" => true,
                "delete" => true
            )
        );
        $testdata = array(
        );
        $this->db->control($functions, $testdata);
*/
        $result = $this->object->updateGroups($newGroups, $oldGroups);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'ERROR Updating Database Groups',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": No Error updating groups");
        }
    }

    /**
     * Function to test updating Schema
     * Update a group in the database.
     *
     * @group unittest
     * @group schema
     *
     * @return null
     */
    public function testUpdateGroup()
    {
        $oldGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $newGroups = \webtemplate\db\schema\SchemaData::$defaultgroups;
        $newGroups['admin'] = array(
            'description' => 'This is a UNIT Test Group',
            'useforproduct' => 'N',
            'autogroup' => 'N',
            'editable' => 'N',
            'admingroup' => 'Y'
        );


        // Group to be deleted is not found in the database
        $functions = array(
            'updateGroups' => array(
                "update" => true
            )
        );
        $testdata = array(
        );
        $this->db->control($functions, $testdata);

        $result = $this->object->updateGroups($newGroups, $oldGroups);
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail(__FUNCTION__ . ": Error updating groups");
        }
    }
}
