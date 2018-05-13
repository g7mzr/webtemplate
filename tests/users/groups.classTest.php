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

// include the test database connection configuration
require_once dirname(__FILE__) .'/../_data/database.php';

/**
 * UserPrefClass Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class GroupsClassTest extends TestCase
{
    /**
     * User Grups Object - Admin
     *
     * @var UsersGroups
     */
    protected $object;

    /**
     * User Grups Object - Normal
     *
     * @var UsersGroups
     */
    protected $object2;

    /**
     * MDB2 Database Connection Object
     *
     * @var MDB2 Database Connection Object
     *
     * @access protected
     */
    protected $object3;

    /**
     * Valid Database Connection Flag
     *
     * @var Valid Database connection
     *
     * @access protected
     */
    protected $databaseconnection;

    /**
     * MOCK Database Connection
     *
     * @var Mock MDB2 Connection
     *
     * @access protected
     */
    protected $mockDB;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        global $testdsn, $sitePreferences;

        // Check that we can connect to the database
        $this->object3 = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($this->object3)) {
            $this->databaseconnection = true;
        } else {
            $this->databaseconnection = false;
        }
        $this->object = new \webtemplate\users\Groups($this->object3, '1'); //Admin
        $this->object2 = new \webtemplate\users\Groups($this->object3, '3');//User
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
        if ($this->databaseconnection === true) {
            $this->object3->disconnect();
        }
    }

    /**
     * Test that the last message created by the Class is available
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testGetLastMsg()
    {
        $localStr = $this->object->getLastMsg();
        $this->assertEquals('', $localStr);
    }

    /**
     * Test that the user is an admin user
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testgetAdminAccess()
    {
        $this->assertTrue($this->object->getAdminAccess());
        $this->assertFalse($this->object2->getAdminAccess());
    }

    /**
     * Test if a user is in agroup or not
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testCheckGroup()
    {
        $this->assertTrue($this->object->checkGroup("editusers"));
        $this->assertTrue($this->object->checkGroup("editgroups"));
        $this->assertFalse($this->object2->checkGroup("editusers"));
    }

    /**
     * Test that the user is an admin user
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testgetGroups()
    {
        $groups = $this->object->getGroups();
        $this->assertTrue(in_array("admin", $groups));
        $this->assertTrue($this->object->getAdminAccess());
    }

    /**
     * Test that the user is an admin user
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testgetGroupDescription()
    {
        $groups = $this->object->getGroupDescription();
        $this->assertEquals("admin", $groups[0]['groupname']);
        $this->assertEquals("Administrators", $groups[0]['description']);
        $this->assertEquals("N", $groups[0]['autogroup']);
    }
    /**
     * Test creating the user/grouop class using the mock database driver
     * to test for errors
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testConstructorErrors()
    {
        $mockdsn["phptype"] = "mock";
        $mockdsn["hostspec"]  = "server";
        $mockdsn["database"] = "database";
        $mockdsn["username"] = "user";
        $mockdsn["password"] = "password";

        $db = \webtemplate\db\DB::load($mockdsn);

        // Fail getting Group List
        $groupclass = new \webtemplate\users\Groups($db, '1');
        $this->assertEquals(
            'Error gettings Users permissions',
            $groupclass->getLastMsg()
        );
        $groupclass = null;

        // Fail getting users groups
        $data = array(
            "getGroupList" => array(
                "0" => array(
                    "group_id" => '1',
                    "group_name" => "Test",
                    "group_description" => "Test Group",
                    "group_useforproduct" => 'Y',
                    "group_editable" => 'N',
                    "group_autogroup" => 'N',
                    "group_admingroup" => 'N'
                )
            ),
            "_usersDirectGroups" => array(
                "0" => array(
                    "group_id" => '1'
                )
            ),
            "_autoGroups" => array(
                "0" => array(
                    "group_id" => '2',
                    "group_name" => "TestTWO",
                    "group_description" => "Test Group TWO",
                    "group_useforproduct" => 'Y',
                    "group_editable" => 'N',
                    "group_autogroup" => 'Y',
                    "group_admingroup" => 'N'
                )
            )


        );

        $functions = array(
            '_usersDirectGroups' => array(
                "pass" => false
            ),
            "_autoGroups" => array(
                "pass" => false
            )
        );

        $db->control($functions, $data);
        $groupclass = new \webtemplate\users\Groups($db, '1');
        $this->assertEquals(
            'Error gettings Users permissions',
            $groupclass->getLastMsg()
        );
        $groupclass = null;

        // Pass _usersDirectGroups
        $functions = array(
            '_usersDirectGroups' => array(
                "pass" => true
            ),
            "_autoGroups" => array(
                "pass" => false
            )
        );


        $db->control($functions, $data);
        $groupclass = new \webtemplate\users\Groups($db, '1');
        $this->assertEquals(
            'Error gettings Users permissions',
            $groupclass->getLastMsg()
        );
        $groupclass = null;

        // PASS AUTO GROUPS
        $functions = array(
            '_usersDirectGroups' => array(
                "pass" => true
            ),
            "_autoGroups" => array(
                "pass" => true
            )
        );

        $db->control($functions, $data);
        $groupclass = new \webtemplate\users\Groups($db, '1');
        $this->assertEquals(
            'Error gettings Users permissions',
            $groupclass->getLastMsg()
        );
        $groupclass = null;
    }
}
