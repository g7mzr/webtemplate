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
 * Edit User Groups Class Unit Tests
 *
 **/
class EditUsersGroupsClassTest extends TestCase
{
    /**
     * Edit Uses Groups Class Object
     *
     * @var \webtemplate\groups\EditUsersGroups
     */
    protected $object;

    /**
     * Database Connection Object
     *
     * @var \webtemplate\db\DB
     */
    protected $object2;

    /**
     * Group Class Object using Mock Database driver
     *
     * @var \webtemplate\groups\EditUsersGroups
     */
    protected $mockGroup;

    /**
     * Mock Database Driver Class
     *
     * @var D\webtemplate\db\DB
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
        $this->object2 = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($this->object2)) {
            $this->databaseconnection = true;
        } else {
            $this->databaseconnection = false;
        }

        // Create Main test Class
        $this->object = new \webtemplate\groups\EditUsersGroups($this->object2);

        // Create a Mock database object
        $testdsn['phptype'] = 'mock';
        $this->mockDB = \webtemplate\db\DB::load($testdsn);

        // Create Group Obkect Using the mockDB obkect
        $this->mockGroup = new \webtemplate\groups\EditUsersGroups($this->mockDB);
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
            $this->object2->disconnect();
        }
    }

    /**
     * Test that if a database connection is availabe the list of groups can be
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
     * Test that if a database connection is availabe the list of groups a user is
     * a member of can be returned.
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testGetUsersGroups()
    {
        if ($this->databaseconnection == true) {
            $groupArray = $this->object->getGroupList();
            if (!\webtemplate\general\General::isError($groupArray)) { // Found groups
                // Select groups user is a member off.
                $addUsersGroups = $this->object->getUsersGroups(1, $groupArray);
                if (\webtemplate\general\General::isError($addUsersGroups)) {
                    // An database error was encountered
                    $this->fail("Error : " . $addUsersGroups->getMessage());
                }

                // GOT THE GROUPS Time to do the Test
                $this->assertEquals('Y', $groupArray[0]['useringroup']);
                $this->assertEquals('N', $groupArray[1]['useringroup']);
            } else {
                // An error was encountered getting the group list
                $this->fail("Error : " . $groupArray->getMessage());
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test to check error conditions using mock group when getting user's groups
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testMockGetUsersGroups()
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
                    "group_autogroup" => 'N',
                    "group_admingroup" => 'N'
                )
            )
        );
        $this->mockDB->control($functions, $data);
        $group = $this->mockGroup->getGroupList();
        if (!\webtemplate\general\General::isError($group)) {
            $addUsersGroups = $this->mockGroup->getUsersGroups(1, $group);
            if (\webtemplate\general\General::isError($addUsersGroups)) {
                // An database error was encountered
                $this->assertEquals(
                    "SQL Query Error",
                    $addUsersGroups->getMessage()
                );
            }
        } else {
            $this->fail("GetUsersGroups Failed getting group list using Mock DB");
        }
    }

    /**
     * Create Change String
     * Check a database connection is available.
     * Test that a user can be removed from all groups they are members of
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testGroupsChangedRemoveAll()
    {
        if ($this->databaseconnection == true) {
            $group = $this->object->getGroupList();
            if (\webtemplate\general\General::isError($group)) {
                $this->fail("Error : " . $group->getMessage());
            } else {
                // Test for a group Change
                $groupchangedresult = $this->object->groupsChanged(1, $group);
                if (\webtemplate\general\General::isError($groupchangedresult)) {
                    $this->fail("Error : " . $group->getMessage());
                } else {
                    $this->assertTrue($groupchangedresult);
                    $this->assertStringContainsString(
                        "Removed from group: admin",
                        $this->object->getChangeString()
                    );
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Create Change String
     * Check a database connection is available.
     * Test that a user can be removed from a specific group
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testGroupsChangedRemoveOneGroup()
    {
        if ($this->databaseconnection == true) {
            $group = $this->object->getGroupList();
            if (\webtemplate\general\General::isError($group)) {
                $this->fail("Error : " . $group->getMessage());
            } else {
                // Test for no group Change
                $counter = 0; // Array Counter
                while ($counter < count($group)) {
                    if ($group[$counter]['groupname'] == 'admin') {
                        $group[$counter]["addusertogroup"] = 'Y';
                    }
                    $counter++;
                }
                $groupchangedresult = $this->object->groupsChanged(1, $group);
                if (\webtemplate\general\General::isError($groupchangedresult)) {
                    $this->fail("Error : " . $group->getMessage());
                } else {
                    $this->assertFalse($groupchangedresult);
                    $this->assertEquals(
                        "",
                        $this->object->getChangeString()
                    );
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Create Change String
     * Check a database connection is available.
     * Test that a user can be added to a specific Group
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testGroupsChangedAddToGroup()
    {
        if ($this->databaseconnection == true) {
            $group = $this->object->getGroupList();
            if (\webtemplate\general\General::isError($group)) {
                $this->fail("Error : " . $group->getMessage());
            } else {
                // Test for Group Change
                $counter = 0; // Array Counter
                while ($counter < count($group)) {
                    if ($group[$counter]['groupname'] == 'editusers') {
                        $group[$counter]["addusertogroup"] = 'Y';
                    }
                    $counter++;
                }
                $groupchangedresult = $this->object->groupsChanged(1, $group);
                if (\webtemplate\general\General::isError($groupchangedresult)) {
                    $this->fail("Error : " . $group->getMessage());
                } else {
                    $this->assertTrue($groupchangedresult);
                    $this->assertStringContainsString(
                        "Added to group: editusers",
                        $this->object->getChangeString()
                    );
                }
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
    public function testMockGroupsChanged()
    {
        $functions = array(
            "getGroupList" => array(
                "pass" => true,
            ),
            "getUsersExplicitGroups" => array(
                "pass" => true,
                "notfound" => true
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
                    "group_autogroup" => 'N',
                    "group_admingroup" => 'N'
                )
            )
        );
        $this->mockDB->control($functions, $data);
        $group = $this->mockGroup->getGroupList();
        if (\webtemplate\general\General::isError($group)) {
            $this->fail("GroupsChanged Failed getting group list using Mock DB");
        }

        // No Groups Found
        $groupchangedresult = $this->mockGroup->groupsChanged(1, $group);
        if (!\webtemplate\general\General::isError($groupchangedresult)) {
            $this->assertFalse($groupchangedresult);
        } else {
            $this->fail("Error : " . $groupchangedresult->getMessage());
        }

        // Failed with SQL Query
        $functions['getUsersExplicitGroups']['pass'] = false;
        $functions['getUsersExplicitGroups']['notfound'] = false;
        $this->mockDB->control($functions, $data);
        $groupchangedresult = $this->mockGroup->groupsChanged(1, $group);
        if (\webtemplate\general\General::isError($groupchangedresult)) {
            $this->assertEquals(
                "SQL Query Error",
                $groupchangedresult->getMessage()
            );
        } else {
            $this->fail("Groups Changed Failed when testing SQL Error");
        }
    }

    /**
     * Check a database connection is available.
     * Delete all groups a user is a member of in the user Group Map
     *
     * @group unittest
     * @group groups
     *
     * @return void
     */
    public function testSaveUsersGroupsDelete()
    {
        if ($this->databaseconnection == true) {
            $group = $this->object->getGroupList();

            // Delete Permissions
            if (\webtemplate\general\General::isError($group)) {
                $this->fail("Error : " . $group->getMessage());
            } else {
                // Delete Permissions
                $datasaved = $this->object->saveUsersGroups(1, $group);
                if (\webtemplate\general\General::isError($datasaved)) {
                    $this->fail("Error : " . $datasaved->getMessage());
                } else {
                    // Test that the Group Permissions have been deleted
                    $groupArray = $this->object->getGroupList();
                    if (!\webtemplate\general\General::isError($groupArray)) {
                        // Select groups user is a member off.
                        $addUsersGroups = $this->object->getUsersGroups(
                            1,
                            $groupArray
                        );
                        if (\webtemplate\general\General::isError($addUsersGroups)) {
                            // An database error was encountered
                            $this->fail(
                                "Error : " . $addUsersGroups->getMessage()
                            );
                        }

                        // GOT THE GROUPS Time to do the Test
                        $this->assertEquals('N', $groupArray[0]['useringroup']);
                    } else {
                        // An error was encountered getting the group list
                        $this->fail("Error : " . $groupArray->getMessage());
                    }
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }



    /**
     * Check a database connection is available.
     * Add the user to the Admin Group in the user Group Map
     *
     * @group unittest
     * @group groups
     *
     * @depends testSaveUsersGroupsDelete
     *
     * @return void
     */
    public function testSaveUsersGroupsSave()
    {
        if ($this->databaseconnection == true) {
            $group = $this->object->getGroupList();

            // Delete Permissions
            if (\webtemplate\general\General::isError($group)) {
                $this->fail("Error : " . $group->getMessage());
            } else {
                // Add Permissions Back on
                $counter = 0; // Array Counter
                while ($counter < count($group)) {
                    if ($group[$counter]['groupname'] == 'admin') {
                        $group[$counter]["addusertogroup"] = 'Y';
                    }
                    $counter++;
                }
                $datasaved = $this->object->saveUsersGroups(1, $group);
                if (\webtemplate\general\General::isError($datasaved)) {
                    $this->fail("Error : " . $datasaved->getMessage());
                } else {
                    // Test that the Group Permissions have been deleted
                    $groupArray = $this->object->getGroupList();
                    if (!\webtemplate\general\General::isError($groupArray)) {
                        // Select groups user is a member off.
                        $addUsersGroups = $this->object->getUsersGroups(
                            1,
                            $groupArray
                        );
                        if (\webtemplate\general\General::isError($addUsersGroups)) {
                            // An database error was encountered
                            $this->fail(
                                "Error : " . $addUsersGroups->getMessage()
                            );
                        }

                        // GOT THE GROUPS Time to do the Test
                        $this->assertEquals('Y', $groupArray[0]['useringroup']);
                    } else {
                        // An error was encountered getting the group list
                        $this->fail("Error : " . $groupArray->getMessage());
                    }
                }
                $datasaved = $this->object->saveUsersGroups(80, $group);
                if (\webtemplate\general\General::isError($datasaved)) {
                    $this->assertStringContainsString(
                        "Unable to save groups",
                        $datasaved->getMessage()
                    );
                } else {
                    $this->fail("Saving Groups for invalid user test failed");
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Check a database connection is available.
     * Test that the Groups cannot be saved for an invalid user
     *
     * @group unittest
     * @group groups
     *
     * @depends testSaveUsersGroupsDelete
     *
     * @return void
     */
    public function testSaveUsersGroupsInvalidUserID()
    {
        if ($this->databaseconnection == true) {
            $group = $this->object->getGroupList();

            // Delete Permissions
            if (\webtemplate\general\General::isError($group)) {
                $this->fail("Error : " . $group->getMessage());
            } else {
                // Add Permissions Back on
                $counter = 0; // Array Counter
                while ($counter < count($group)) {
                    if ($group[$counter]['groupname'] == 'admin') {
                        $group[$counter]["addusertogroup"] = 'Y';
                    }
                    $counter++;
                }

                $datasaved = $this->object->saveUsersGroups(80, $group);
                if (\webtemplate\general\General::isError($datasaved)) {
                    $this->assertStringContainsString(
                        "Unable to save groups",
                        $datasaved->getMessage()
                    );
                } else {
                    $this->fail("Saving Groups for invalid user test failed");
                }
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
    public function testMockSaveUsersGroups()
    {
        $functions = array(
            "getGroupList" => array(
                "pass" => true,
            ),
            "saveUsersGroups" => array(
                "pass" => false,
                "starttransaction" => false
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
                    "group_autogroup" => 'N',
                    "group_admingroup" => 'N'
                )
            )
        );
        $this->mockDB->control($functions, $data);
        $group = $this->mockGroup->getGroupList();
        if (\webtemplate\general\General::isError($group)) {
            $this->fail("SaveUsersGroups Failed getting group list using Mock DB");
        }

        // Fail to start Transaction
        $datasaved = $this->mockGroup->saveUsersGroups(1, $group);
        if (\webtemplate\general\General::isError($datasaved)) {
            $this->assertStringContainsString(
                "Unable to create database transaction",
                $datasaved->getMessage()
            );
        } else {
            $this->fail("SaveUsersGroups Failed Saving users groups using Mock DB");
        }

        $functions["saveUsersGroups"]["starttransaction"] = true;
        $this->mockDB->control($functions, $data);
        $datasaved = $this->mockGroup->saveUsersGroups(1, $group);
        if (\webtemplate\general\General::isError($datasaved)) {
            $this->assertStringContainsString(
                "Unable to save groups",
                $datasaved->getMessage()
            );
        } else {
            $this->fail("SaveUsersGroups Failed Saving users groups using Mock DB");
        }
    }
}
