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
require_once dirname(__FILE__) . '/../_data/database.php';


/**
 * Mock Database Driver Class Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class DatabaseDriverMockTest extends TestCase
{

    /**
     * Mock Database Object
     *
     * @var webtemplate\db\DB_DRIVER_MOCK
     */
    protected $object;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp(): void
    {
        global $testdsn, $options;

        // Create a Mock database object
        $testdsn['phptype'] = 'mock';
        $this->object = \webtemplate\db\DB::load($testdsn);
        if (\webtemplate\general\General::isError($this->object)) {
            print_r($this->object);
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown(): void
    {
        unset($this->object);
    }

    /**
     * Test that data can be uploaded to the MOCK Driver
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testControl()
    {
        $functions = array(
            "getGroupList" => array(
               "pass" => true,

            )
        );

        $data = array(
            "getGroupList" => array(
                "0" => array(
                    "group_id" => '1',
                    "group_name" => "Test",
                    "group_description" => "Test Group",
                    "group_useforproduct" => 'Y',
                    "group_editable" => 'N',
                    "group_autogroup" => 'N'
                )
            )
        );
        $this->assertTrue($this->object->control($functions, $data));
    }

    /**
     * Test that the Create Index Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testCreateIndex()
    {
        $result = $this->object->createIndex('table', 'index', 'indexdata');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertEquals('Error Creating Index.', $result->getMessage());
        } else {
            $this->fail("Error: Mock Create Index Test");
        }
    }

    /**
     * Test that the Drop Index Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDropIndex()
    {
        $result = $this->object->dropIndex('table', 'index');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Error Dropping Index table:index',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Drop Index Test");
        }
    }

    /**
     * Test that the Create User Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testCreateUser()
    {
        $result = $this->object->createUser('phpunit', 'password', false);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Error Creating Database User',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Create User Test");
        }
    }

    /**
     * Test that the Drop Index Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDropUser()
    {
        $result = $this->object->dropUser('phpunit');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Error Dropping Database User',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Drop User Test");
        }
    }

    /**
     * Test that the Create Dataabse Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testCreateDatabase()
    {
        $result = $this->object->createDatabase('phpunit', 'phpunit', false);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Error Creating the database',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Create Database Test");
        }
    }


    /**
     * Test that the Create Dataabse Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDatabaseExists()
    {
        $result = $this->object->databaseExists("database");
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                "Error checking the database exists",
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Create Database Test");
        }
    }


    /**
     * Test that the Drop Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDropDatabase()
    {
        $result = $this->object->dropDatabase('phpunit');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Error Dropping Database',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Drop Database Test");
        }
    }

    /**
     * Test that the Create Table Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testCreatetable()
    {
        $table = array();
        $result = $this->object->createTable('phpunit', $table);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Error Creating the Table',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Create Table Test");
        }
    }

    /**
     * Test that the Drop Table Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDropTable()
    {
        $result = $this->object->dropTable('phpunit');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Error Dropping Table phpunit',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Drop Table Test");
        }
    }

    /**
     * Test that the Add Column Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testAddColumn()
    {
        $result = $this->object->addColumn('phpunit', 'name', 'data');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Error adding column to phpunit',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Add Column Test");
        }
    }

    /**
     * Test that the Drop Column Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDropColumn()
    {
        $result = $this->object->dropColumn('phpunit', 'column');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Error dropping column on phpunit',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Drop Column Test");
        }
    }

    /**
     * Test that the Alter Column Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testAlterColumn()
    {
        $result = $this->object->alterColumn('phpunit', 'name', 'old', 'new');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Error changing column Type',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Alter Column Test");
        }
    }

    /**
     * Test that the Save Schema Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testSaveSchema()
    {
        $result = $this->object->saveSchema('1.1', 'phpunit', 'groups');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Error Deleteting Previous Schema',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: MockSave Schema Test");
        }
    }

    /**
     * Test that the Get Schema Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testGetSchema()
    {
        $result = $this->object->getSchema();
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'Schema Not Found',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Get Schema Test");
        }
    }

    /**
     * Test that the Translate Column Function Return the same name
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testTranslateColumn()
    {
        $this->assertEquals('type', $this->object->translateColumn('type'));
    }

    /**
     * Test that the Create FK Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testCreateFK()
    {
        $result = $this->object->createFK('keydata');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                "Error Creating Forign Keys.",
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Create FK Test");
        }
    }

    /**
     * Test that the Drop FK Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testCreateUniqueConstraint()
    {
        $keydata = array("Table", "Column");
        $result = $this->object->createUniqueConstraint($keydata);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                "Error creating Unique Constraint Column on Table",
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Drop FK Test");
        }
    }

    /**
     * Test that the Drop FK Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDropFK()
    {
        $result = $this->object->dropFK('table', 'key');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                "Error deleteting Foreign Key key on table",
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock Drop FK Test");
        }
    }

    /**
     * Test that the getDNVersion returns the expected text
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testGetDBVersion()
    {
        // Test Failure
        $result = $this->object->getDBVersion();
        $this->assertEquals('Error Getting Database Version', $result);

        // Test get a versiom
        $functions = array(
            "testGetDBVersion" => array(
                "pass" => true,

            )
        );
        $data = array('version' => 'Mock DB Driver 1.0');
        $this->object->control($functions, $data);
        $result = $this->object->getDBVersion();
        $this->assertEquals('Mock DB Driver 1.0', $result);
    }

    /**
     * Test that the Start Transaction Function Works
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testStartTransaction()
    {
        // Fail to start a Transaction
        $result = $this->object->startTransaction();
        $this->assertFalse($result);

        // Pass Start Transaction
        $functions = array(
            "testStartTransaction" => array(
                "starttransaction" => true,

            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->startTransaction();
        $this->assertTrue($result);
    }

    /**
     * Test that the End Transaction Function Works
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testEndTransaction()
    {
        // Straight Fail to end a Transaction
        $result = $this->object->endTransaction(true);
        $this->assertFalse($result);

        // Pass end transaction COMMITT
        $functions = array(
            "testEndTransaction" => array(
                "endtransaction" => true,

            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->endTransaction(true);
        $this->assertTrue($result);

        // Fail End Transaction ROLLBACK
        $result = $this->object->endTransaction(false);
        $this->assertFalse($result);
    }

    /**
     * Test that the Insert Function Works
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDbinsert()
    {
        // Test for Failure
        $result = $this->object->Dbinsert('table', 'data');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                "SQL Query Error",
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock DB Insert Test");
        }

        // Test for PASS
        $functions = array(
            "testDbinsert" => array(
                "pass" => true,

            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->Dbinsert('table', 'data');
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail("Error: Mock DB Insert Test");
        }
    }

    /**
     * Test that the get Insert ID Function Works
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDbinsertid()
    {
        // Test for Failure
        $result = $this->object->Dbinsertid('table', 'fields', 'namefield', 'name');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                "Error Getting record ID.",
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock DB Insertid Test");
        }

        // Test for PASS
        $functions = array(
            "testDbinsertid" => array(
                "id" => true,

            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->Dbinsertid('table', 'fields', 'namefield', 'name');
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertEquals(1, $result);
        } else {
            $this->fail("Error: Mock DB Insertid Test");
        }
    }

    /**
     * Test that the DB Update Function Works
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDbupdate()
    {
        $result = $this->object->Dbupdate('table', 'data', 'searchdata');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'SQL Query Error',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock DB Update Test");
        }
    }

    /**
     * Test that the DB Select Single Function Works
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDbselectsingle()
    {
        $result = $this->object->Dbselectsingle('table', 'fields', 'searchdata');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'SQL Query Error',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock DB Select Single Test");
        }

        $functions = array(
            "testDbselectsingle" => array(
                "pass" => true,

            )
        );
        $data = array(
            "testDbselectsingle" => array(
                "group_id" => '1',
                "group_name" => "Test",
                "group_description" => "Test Group",
                "group_useforproduct" => 'Y',
                "group_editable" => 'N',
                "group_autogroup" => 'N'
            )
        );
        $this->object->control($functions, $data);
        $result = $this->object->Dbselectsingle('table', 'fields', 'searchdata');
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertEquals(
                'Test',
                $result['group_name']
            );
        } else {
            $this->fail("Error: Mock DB Select Single Test");
        }
    }

    /**
     * Test that the DB Select Multiple Function Works
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDbselectmultiple()
    {
        $result = $this->object->Dbselectmultiple('table', 'fields', 'searchdata');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'SQL Query Error',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock DB Select Multiple Test");
        }

        $functions = array(
            "testDbselectmultiple" => array(
                "pass" => true,

            )
        );
        $data = array(
            "testDbselectmultiple" => array(
                "0" => array(
                    "group_id" => '1',
                    "group_name" => "Test",
                    "group_description" => "Test Group",
                    "group_useforproduct" => 'Y',
                    "group_editable" => 'N',
                    "group_autogroup" => 'N'
                ),
                "1" => array(
                    "group_id" => '2',
                    "group_name" => "Test Two",
                    "group_description" => "Test Group Two",
                    "group_useforproduct" => 'Y',
                    "group_editable" => 'N',
                    "group_autogroup" => 'N'
                )
            )
        );
        $this->object->control($functions, $data);
        $result = $this->object->Dbselectmultiple('table', 'fields', 'searchdata');
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertEquals('Test', $result[0]['group_name']);
            $this->assertEquals('Test Two', $result[1]['group_name']);
        } else {
            $this->fail("Error: Mock DB Select Multiple Test");
        }
    }

    /**
     * Test that the DB Delete  Function Works
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDbdelete()
    {
        // Test FAILURE
        $result = $this->object->Dbdelete('table', 'searchdata');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'SQL Query Error',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock DB Delete Test");
        }

        // TEST PASS
        $functions = array(
            "testDbdelete" => array(
                "delete" => 'table',

            )
        );
        $data = array();
        $this->object->control($functions, $data);
        $result = $this->object->Dbdelete('table', 'searchdata');
        if (!\webtemplate\general\General::isError($result)) {
            $this->assertTrue($result);
        } else {
            $this->fail("Error: Mock DB Delete Test");
        }
    }

    /**
     * Test that the DB Delete Multiple Function Fails
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDbdeletemultiple()
    {
        $result = $this->object->Dbdeletemultiple('table', 'searchdata');
        if (\webtemplate\general\General::isError($result)) {
            $this->assertStringContainsString(
                'SQL Query Error',
                $result->getMessage()
            );
        } else {
            $this->fail("Error: Mock DB Delete Multiple Test");
        }
    }
}
