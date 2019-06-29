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

// Load the Test Database Configuration File
require_once dirname(__FILE__) . '/../_data/database.php';

/**
 * Edit Groups Class Unit Tests
 *
 **/
class EditGroupsClassTest extends TestCase
{
    /**
     * Groups Class Object
     *
     * @var \webtemplate\groups\EditGroups
     */
    protected $object;

    /**
     * Database Connection Object
     *
     * @var \g7mzr\db\DBManager
     */
    protected $object2;

    /**
     * Group Class Object using Mock Database driver
     *
     * @var \webtemplate\groups\EditGroups
     */
    protected $mockGroup;

    /**
     * Mock Database Driver Class
     *
     * @var \g7mzr\db\DBManager
     */
    protected $mockDB;
    /**
     * Valid Database Connection Flag
     *
     * @var Valid Database connection
     */
    protected $databaseconnection;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        global $testdsn, $options;

        // Check that we can connect to the database
        try {
            $this->object2 = new \g7mzr\db\DBManager(
                $testdsn,
                $testdsn['username'],
                $testdsn['password']
            );
            $setresult = $this->object2->setMode("datadriver");
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

        // Create Main test Class
        $this->object = new \webtemplate\groups\EditGroups($this->object2->getDataDriver());

        // Create a Mock database object
        $testdsn['dbtype'] = 'mock';
        $this->mockDB = new \g7mzr\db\DBManager(
            $testdsn,
            $testdsn['username'],
            $testdsn['password']
        );
        $setresult = $this->mockDB->setMode("datadriver");

        // Create Group Obkect Using the mockDB obkect
        $this->mockGroup = new \webtemplate\groups\EditGroups($this->mockDB->getDataDriver());
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown():void
    {
        if ($this->databaseconnection === true) {
            $this->object2->getDataDriver()->disconnect();
        }
    }

    /**
     * Test that the message string can be returned
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testGetMessage()
    {
        $msg = $this->object->getMessage();
        $this->assertEquals('', $msg);
    }

    /**
     * Test that if a database connection is available the list of groups can be
     * returned
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testGetGroupList()
    {
        if ($this->databaseconnection == true) {
            $group = $this->object->getGroupList();
            if (\webtemplate\general\General::isError($group)) {
                $this->fail("Error : " . $group->getMessage());
            } else {
                $this->assertEquals('admin', $group[0]['groupname']);
                $this->assertEquals('editusers', $group[1]['groupname']);
                $this->assertEquals('editgroups', $group[2]['groupname']);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test to check error conditions using mock group
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testMockGetGroupList()
    {
        $group = $this->mockGroup->getGroupList();
        if (\webtemplate\general\General::isError($group)) {
            $this->assertEquals('SQL Query Error', $group->getMessage());
        } else {
            $this->fail("GetGroupList Test Failed using Mock DB");
        }
    }
    /**
     * Test that the change string can be returned as is empty.  If changes have been
     * made to a group this will list the fields that have been changed
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testGetChangeString()
    {
        $changestring = $this->object->getChangeString();
        $this->assertEquals('', $changestring);
    }

    /**
     * Test that if a database connection is availabe a single group's fields can be
     * returned from the database
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testGetSingleGroup()
    {
        if ($this->databaseconnection == true) {
            //Get the Admin Group
            $group = $this->object->getSingleGroup(1);
            if (\webtemplate\general\General::isError($group)) {
                $this->fail("Error : " . $group->getMessage());
            } else {
                $this->assertEquals('admin', $group[0]['groupname']);
            }

            // Get a group that does not exist
            $group = $this->object->getSingleGroup(100000);
            if (\webtemplate\general\General::isError($group)) {
                $this->assertEquals('Not Found', $group->getMessage());
            } else {
                $this->fail("Found a group that should not exist");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that if a database connection is availabe if any of a group's fields
     * have been changed
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testGroupDataChanged()
    {
        if ($this->databaseconnection == true) {
            //Existing Group Test
            $group = $this->object->groupDataChanged(
                1,
                'admin1',
                'Administrators1',
                'Y',
                'N'
            );
            if (\webtemplate\general\General::isError($group)) {
                $this->fail("Error : " . $group->getMessage());
            } else {
                $this->assertFalse($group);
                $changestring = $this->object->getChangeString();
                $this->assertStringContainsString('System', $changestring);
            }

            // New Group Test
            $group = $this->object->groupDataChanged(
                0,
                'admin1',
                'Administrators1',
                'Y',
                'N'
            );
            if (\webtemplate\general\General::isError($group)) {
                $this->fail("Error : " . $group->getMessage());
            } else {
                $this->assertTrue($group);
                $changestring = $this->object->getChangeString();
                $this->assertStringContainsString('New Group', $changestring);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
        /**
     * Test to check error conditions using mock group when checking in a user's
     * group membership has changed
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testMockGroupDataChanged()
    {
        $functions = array(
            "groupDataChanged" => array(
                "pass" => false,
            )
        );
        $data = array(
            "groupDataChanged" => array(
                "group_id" => '1',
                "group_name" => "Test",
                "group_description" => "Test Group",
                "group_useforproduct" => 'Y',
                "group_editable" => 'Y',
                "group_autogroup" => 'N'
            )
        );

        // Test That the SQL Query Fails
        $this->mockDB->getDataDriver()->control($functions, $data);
        $group = $this->mockGroup->groupDataChanged(
            '1',
            'Test',
            'Test Group',
            'Y',
            'N'
        );
        if (\webtemplate\general\General::isError($group)) {
            $this->assertEquals("SQL Query Error", $group->getMessage());
        } else {
            $this->fail("GroupDataChanged Failed using Mock DB");
        }

        // Test No Change to Group
        $functions['groupDataChanged']['pass'] = true;
        $this->mockDB->getDataDriver()->control($functions, $data);
        $group = $this->mockGroup->groupDataChanged(
            '1',
            'Test',
            'Test Group',
            'Y',
            'N'
        );
        if (!\webtemplate\general\General::isError($group)) {
            $this->assertFalse($group);
        } else {
            $this->fail("GroupDataChanged Failed using Mock DB");
        }

        // Test Autogroup Change
        $this->mockDB->getDataDriver()->control($functions, $data);
        $group = $this->mockGroup->groupDataChanged(
            '1',
            'Test',
            'Test Group',
            'N',
            'Y'
        );
        if (!\webtemplate\general\General::isError($group)) {
            $this->assertTrue($group);
        } else {
            $this->fail("GroupDataChanged Failed using Mock DB");
        }
    }


    /**
     * Test that if a database connection is availabe that an updated or new group
     * can be saved to the database
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testSaveGroup()
    {
        if ($this->databaseconnection == true) {
            $gid = 0;
            $groupsaved = $this->object->saveGroup(
                $gid,
                'phpunit',
                'Phpunit Test Group',
                'Y',
                'N'
            );
            if (\webtemplate\general\General::isError($groupsaved)) {
                $this->fail("Saving Group Test : " . $groupsaved->getMessage());
            } else {
                $this->assertTrue($groupsaved);
            }
            $groupsaved = $this->object->saveGroup(
                $gid,
                'phpunit',
                'Phpunit Test Group2',
                'Y',
                'N'
            );
            if (\webtemplate\general\General::isError($groupsaved)) {
                $this->fail("Update Group Test : " . $groupsaved->getMessage());
            } else {
                $this->assertTrue($groupsaved);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test to check error conditions using mock group when saving a groups details
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testMockSaveGroup()
    {
        $functions = array(
            "saveGroup" => array(
                "pass" => false
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
        $this->mockDB->getDataDriver()->control($functions, $data);
        $gid = '0';
        $group = $this->mockGroup->saveGroup(
            $gid,
            'test',
            'Test Group',
            'N',
            'N',
            'N'
        );
        if (\webtemplate\general\General::isError($group)) {
            $this->assertEquals("Errors Saving Group test", $group->getMessage());
        } else {
            $this->fail("SaveGroup Failed getting group list using Mock DB");
        }

        // Save data.  No id
        $functions['saveGroup']['pass'] = true;
        $this->mockDB->getDataDriver()->control($functions, $data);
        $gid = '0';
        $group = $this->mockGroup->saveGroup(
            $gid,
            'test',
            'Test Group',
            'N',
            'N',
            'N'
        );
        if (\webtemplate\general\General::isError($group)) {
            $this->assertEquals(
                "Error getting id for group test",
                $group->getMessage()
            );
        } else {
            $this->fail("SaveGroup Failed Inserting New Record Test iusing MOCKDB");
        }

        // Update data fail.
        $functions['saveGroup']['pass'] = false;
        $this->mockDB->getDataDriver()->control($functions, $data);
        $gid = '10';
        $group = $this->mockGroup->saveGroup(
            $gid,
            'test',
            'Test Group',
            'N',
            'N',
            'N'
        );
        if (\webtemplate\general\General::isError($group)) {
            $this->assertEquals("Error updating group: test", $group->getMessage());
        } else {
            $this->fail("SaveGroup Failed updating record using Mock DB");
        }
    }
    /**
     * Test that if a database connection is availabe a group can be deleted from the
     * database
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testDeleteGroup()
    {
        if ($this->databaseconnection == true) {
            $gid = 0;
            $group = $this->object->getGroupList();
            if (\webtemplate\general\General::isError($group)) {
                $this->fail("Error : " . $group->getMessage());
            } else {
                foreach ($group as $groupdetails) {
                    if ($groupdetails['groupname'] == 'phpunit') {
                        $gid = $groupdetails['groupid'];
                    }
                }
            }
            if ($gid <> 0) {
                $groupdeleted = $this->object->deleteGroup($gid);
                if (\webtemplate\general\General::isError($groupdeleted)) {
                    $this->fail("Error : " . $groupdeleted->getMessage());
                } else {
                    $this->assertEquals(1, $groupdeleted);
                }
            } else {
                $msg = "PHPUNIT Group does not exist in the database";
                $this->fail("Group Delete Test. " . $msg);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that if a database connection is availabe a group exists in the database
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testCheckGroupExists()
    {
        if ($this->databaseconnection == true) {
            $groupexists = $this->object->checkGroupExists('admin');
            if (\webtemplate\general\General::isError($groupexists)) {
                $this->fail("Error : " . $groupexists->getMessage());
            } else {
                $this->assertTrue($groupexists);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test to check error conditions using mock group when checking in a user's
     * group membership has changed
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testMockCheckGroupExists()
    {
        $functions = array(
            "checkGroupExists" => array(
                "pass" => false,
                "notfound" => false

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
        $this->mockDB->getDataDriver()->control($functions, $data);
        $group = $this->mockGroup->checkGroupExists('test');
        if (\webtemplate\general\General::isError($group)) {
            $this->assertEquals("SQL Query Error", $group->getMessage());
        } else {
            $this->fail("SaveUsersGroups Failed getting group list using Mock DB");
        }
    }


    /**
     * Test that if a database connection is availabe a groups fields are valid
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testValidateGroupData()
    {
        if ($this->databaseconnection == true) {
            // Test a for a Pass and that useforproduct and autogroup are set.
            $inputData = array("groupid" => '0',
                               "groupname" => 'GroupName',
                               "description" => 'Group Description',
                               "useforproduct" => 'Y',
                               "autogroup" => 'Y');
            $groupdata = $this->object->ValidateGroupData($inputData);
            $this->assertEquals('', $groupdata[0]['msg']);
            $this->assertEquals('Y', $groupdata[0]['useforproduct']);
            $this->assertEquals('Y', $groupdata[0]['autogroup']);

            // Test a for a Group ID Fail.
            $inputData = array("groupid" => 'A',
                               "groupname" => 'GroupName',
                               "description" => 'Group Description',
                               "useforproduct" => 'Y',
                               "autogroup" => 'Y');
            $groupdata = $this->object->ValidateGroupData($inputData);
            $this->assertStringContainsString("Invalid Group Id", $groupdata[0]['msg']);

            // Test for A Group Name Fail
            $inputData = array("groupid" => '1',
                               "groupname" => 'Group Name',
                               "description" => 'Group Description',
                               "useforproduct" => 'Y',
                               "autogroup" => 'Y');
            $groupdata = $this->object->ValidateGroupData($inputData);
            $this->assertStringContainsString("Invalid Group Name", $groupdata[0]['msg']);

             // Test for A Group Description Fail
            $inputData = array("groupid" => '1',
                               "groupname" => 'GroupName',
                               "description" => 'Group Description;',
                               "useforproduct" => 'Y',
                               "autogroup" => 'Y');
            $groupdata = $this->object->ValidateGroupData($inputData);
            $this->assertStringContainsString("Invalid Group Description", $groupdata[0]['msg']);

             // Test that useforproduct and autogroup are not set
            $inputData = array("groupidi" => '1',
                               );
            $groupdata = $this->object->ValidateGroupData($inputData);
            $this->assertEquals('N', $groupdata[0]['useforproduct']);
            $this->assertEquals('N', $groupdata[0]['autogroup']);
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that if a database connection is available get the id of the admin group
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testGetGroupID()
    {
        if ($this->databaseconnection == true) {
            $groupid = $this->object->getGroupid('admin');
            if (!\webtemplate\general\General::isError($groupid)) {
                $this->assertEquals('1', $groupid);
            } else {
                $this->fail("Test getGroupID failed " . $groupid->getMessage());
            }
        }
    }

    /**
     * Test that if a database connection is available test that the correct error
     * is received using the mock database driver
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testGetGroupIDFail()
    {
        if ($this->databaseconnection == true) {
            $groupid = $this->mockGroup->getGroupid('admin');
            if (!\webtemplate\general\General::isError($groupid)) {
                $this->fail("Test getGroupIDfailed did nor return an error object");
            } else {
                $this->assertStringContainsString("SQL Query Error", $groupid->getMessage());
            }
        }
    }
}
