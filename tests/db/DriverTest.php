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

// Include the test Database configuration values
require_once dirname(__FILE__) . '/../_data/database.php';

/**
 * Postgresql Database Driver Class Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class DriverTest extends TestCase
{
    /**
     * Database deiver Object
     *
     * @var pgsql Object
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
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown():void
    {
    }

    /**
     * Test that the correct error message is returned for each error code
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testErrorMessage()
    {
        //test For Valid Messages
        $msg = \webtemplate\db\DB::errorMessage(DB_OK);
        $this->assertEquals($msg, "no error");

        $msg = \webtemplate\db\DB::errorMessage(DB_ERROR);
        $this->assertEquals($msg, "unknown error");

        $msg = \webtemplate\db\DB::errorMessage(DB_ERROR_NOT_FOUND);
        $this->assertEquals($msg, "not found");

        $msg = \webtemplate\db\DB::errorMessage(DB_USER_NOT_FOUND);
        $this->assertEquals($msg, "user not found");

        $msg = \webtemplate\db\DB::errorMessage(DB_CANNOT_CONNECT);
        $this->assertEquals($msg, "unable to connect to the database");

        $msg = \webtemplate\db\DB::errorMessage(DB_ERROR_QUERY);
        $this->assertEquals($msg, "sql query failed");

        $msg = \webtemplate\db\DB::errorMessage(DB_ERROR_TRANSACTION);
        $this->assertEquals($msg, "Transaction Error");

        $msg = \webtemplate\db\DB::errorMessage(DB_ERROR_SAVE);
        $this->assertEquals($msg, "unable to save data");

        $msg = \webtemplate\db\DB::errorMessage(DB_NOT_IMPLEMENTED);
        $this->assertEquals($msg, "function not implemented");

        // Test for Invalid Message Number
        $msg = \webtemplate\db\DB::errorMessage(100);
        $this->assertEquals($msg, "unknown error");
    }

    /**
     * Test that the database driver exists
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testDriverExists()
    {
        global $testdsn;


        // Test for valid Driver File using default PHP version
        $driverExists = \webtemplate\db\DB::driverExists($testdsn["phptype"]);
        $this->assertTrue($driverExists);

        // Test for invalid Driver File using default PHP version
        $driverExists = \webtemplate\db\DB::driverExists("test");
        $this->assertFalse($driverExists);

        // Test for valid Driver File using dummy PHP version
        $driverExists = \webtemplate\db\DB::driverExists(
            $testdsn["phptype"],
            "100.0.0"
        );
        $this->assertFalse($driverExists);
    }

    /**
     * Test that the database driver can be loaded
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testLoad()
    {
        global $testdsn;

        // save phpytpe
        $tempPHPType = $testdsn['phptype'];

        // test to see failure if $dsn['phptype'] is empty
        $testdsn['phptype'] = '';
        $db = \webtemplate\db\DB::load($testdsn);
        if (\webtemplate\general\General::isError($db)) {
            $this->assertEquals("No RDBMS driver specified", $db->getMessage());
        } else {
            $this->fail("Loaded DB when no RDBMS Driver Specified");
        }

        // test to see failure if $dsn['phptype'] is invalid
        $testdsn['phptype'] = 'test';
        $db = \webtemplate\db\DB::load($testdsn);
        if (\webtemplate\general\General::isError($db)) {
            $this->assertEquals(
                "Unable to load database driver test",
                $db->getMessage()
            );
        } else {
            $this->fail("Loaded DB when invalid RDBMS Driver Specified");
        }

        // restore phpytpe
        $testdsn['phptype'] = $tempPHPType;

        // Save database
        $tempDatabase = $testdsn['database'];

        // Test connect to invalid database
        $testdsn['database'] = 'dummy';
        $db = \webtemplate\db\DB::load($testdsn);
        if (\webtemplate\general\General::isError($db)) {
            $this->assertEquals(
                "Unable to connect to the database",
                $db->getMessage()
            );
        } else {
            $this->fail("Connected to Invalid Database");
        }

        // Restore Database name
        $testdsn['database'] = $tempDatabase;

        // Pass test
        $db = \webtemplate\db\DB::load($testdsn);
        if (\webtemplate\general\General::isError($db)) {
            $this->fail("Unable to connect to the database");
        } else {
            $db->disconnect();
            $this->assertTrue(true);
        }
    }



    /**
     * Test that the database version number can be returned from the driver
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testGetVersion()
    {
        global $testdsn;

        if ($testdsn['phptype'] == 'pgsql') {
            $expectedresult = "PostgreSQL";
        } else {
            $expectedresult = "fail";
        }
        $db = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($db)) {
            $result = $db->getDBVersion();
            $db->disconnect();
            $this->assertStringContainsString($expectedresult, $result);
        } else {
            $this->fail("Error Connecting to the Database");
        }
    }


    /**
     * Test that a failure of test id
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testGetInsertIDFail()
    {
        global $testdsn;

        $db = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($db)) {
            $result = $db->dbinsertid("dummy", "dummy", "dummy", "dummy");
            $this->assertStringContainsString('Error getting record ID', $result->getMessage());
            $db->disconnect();
        } else {
            $this->fail("Error Connecting to the Database");
        }
    }

    /**
     * Test that a failure of test id
     *
     * @group unittest
     * @group database
     *
     * @return null
     */
    public function testInsertFail()
    {
        global $testdsn;

        $db = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($db)) {
            $result = $db->dbinsert("dummy", array("column" => "test"));
            $this->assertStringContainsString(
                'Error running the Database INSERT Statement',
                $result->getMessage()
            );
            $db->disconnect();
        } else {
            $this->fail("Error Connecting to the Database");
        }
    }
}
