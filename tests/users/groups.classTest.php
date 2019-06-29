<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Unit Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace webtemplate\unittest;

use PHPUnit\Framework\TestCase;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// include the test database connection configuration
require_once dirname(__FILE__) . '/../_data/database.php';

/**
 * User Groups Class Unit Tests
 *
 **/
class GroupsClassTest extends TestCase
{
    /**
     * User Groups Object - Admin
     *
     * @var \webtemplate\users\Groups
     */
    protected $object;

    /**
     * User Groups Object - Normal
     *
     * @var \webtemplate\users\Groups
     */
    protected $object2;

    /**
     * Database Connection Object
     *
     * @var \g7mzr\db\DBManager
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
     * @var \g7mzr\db\DBManager
     *
     * @access protected
     */
    protected $mockDB;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        global $testdsn, $sitePreferences;

        // Check that we can connect to the database
        try {
            $this->object3 = new \g7mzr\db\DBManager(
                $testdsn,
                $testdsn['username'],
                $testdsn['password']
            );
            $setresult = $this->object3->setMode("datadriver");
            if (!\g7mzr\db\common\Common::isError($setresult)) {
                $this->databaseconnection = true;
            } else {
                $this->databaseconnection = false;
                echo $setresult->getMessage();
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $this->databaseconnection = false;
        }
        $this->object = new \webtemplate\users\Groups($this->object3->getDataDriver(), '1'); //Admin
        $this->object2 = new \webtemplate\users\Groups($this->object3->getDataDriver(), '3');//User
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        if ($this->databaseconnection === true) {
            $this->object3->getDataDriver()->disconnect();
        }
    }

    /**
     * Test that the last message created by the Class is available
     *
     * @group unittest
     * @group users
     *
     * @return void
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
     * @return void
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
     * @return void
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
     * @return void
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
     * @return void
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
     * @return void
     */
    public function testConstructorErrors()
    {
        $mockdsn = array();
        $mockdsn["dbtype"] = "mock";
        $mockdsn["hostspec"]  = "server";
        $mockdsn["database"] = "database";
        $mockdsn["username"] = "user";
        $mockdsn["password"] = "password";

        try {
            $db = new \g7mzr\db\DBManager(
                $mockdsn,
                $mockdsn['username'],
                $mockdsn['password']
            );
            $setresult = $db->setMode("datadriver");
            if (\g7mzr\db\common\Common::isError($setresult)) {
                $this->fail("testConstructorErrors: " . $setresult->getMessage());
            }
        } catch (Exception $ex) {
            $this->fail("testConstructorErrors: " . $ex->getMessage());
        }

        // Fail getting Group List
        $groupclass = new \webtemplate\users\Groups($db->getDataDriver(), '1');
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

        $db->getDataDriver()->control($functions, $data);
        $groupclass = new \webtemplate\users\Groups($db->getDataDriver(), '1');
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


        $db->getDataDriver()->control($functions, $data);
        $groupclass = new \webtemplate\users\Groups($db->getDataDriver(), '1');
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

        $db->getDataDriver()->control($functions, $data);
        $groupclass = new \webtemplate\users\Groups($db->getDataDriver(), '1');
        $this->assertEquals(
            'Error gettings Users permissions',
            $groupclass->getLastMsg()
        );
        $groupclass = null;
    }
}
