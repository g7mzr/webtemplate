<?php
namespace webtemplate\unittest;

use PHPUnit\Framework\TestCase;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// Load the Test Database Configuration File
require_once __DIR__ .'/../_data/database.php';

/**
 * Help Class Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class EditGroupFunctionsClassTest extends TestCase
{
    /*
     * Property: db
     * @var    \webtemplate\db\DB
     * @access protected
     */
    protected $db = null;

    /*
     * Property: mockdb
     * @var    \webtemplate\db\DB
     * @access protected
     */
    protected $mockdb = null;

    /*
     * Property: tpl
     * @var    \webtemplate\application\SmartyTemplate
     * @access protected
     */
    protected $tpl = null;

    /*
     * property: template
     * @var    string
     * @access protected
     */
    protected $template = '';

    /* property: edituser
     * @var    \webtemplate\groups\EditGroup
     * @access protected
     */
    protected $editgroup = null;

    /* property: mockedituser
     * @var    \webtemplate\groups\EditGroup
     * @access protected
     */
    protected $mockeditgroups = null;


    /*
     * property: config
     * @var    \webtemplate\config\Configure
     * @access private
     */
    protected $config = null;

    /**
     * This function sets up the variables and objects for the test
     *
     * @return null No data is returned
     *
     * @access protected
     */
    protected function setup()
    {
        global $testdsn;

        $this->db = \webtemplate\db\DB::load($testdsn);
        $this->tpl = new \webtemplate\application\SmartyTemplate();
        $this->editgroup = new \webtemplate\groups\EditGroups($this->db);
        $configDir = __DIR__ . "/../../configs";
        $this->config = new \webtemplate\config\Configure($configDir);

        // Create a Mock database object
        $testdsn['phptype'] = 'mock';
        $this->mockdb = \webtemplate\db\DB::load($testdsn);

        // Create a Mock Edit User object
        $this->mockeditgroup = new \webtemplate\groups\EditGroups($this->mockdb);
        $this->deleteTestGroup();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
        $this->deleteTestGroup();
        $this->db->disconnect();
    }


    /**
     * This function is called to remove the test group from the database
     *
     * This function is called to remove the test group from the database.  It is
     * called by both the setup and teardown functions to ensure that test data
     * is not available to cause tests to fail
     *
     * @return null
     */
    protected function deleteTestGroup()
    {
        global $testdsn;

        // Remove the phpunit2 user prior to running any tests.
        if ($this->editgroup->checkGroupExists('phpunit')) {
            $conStr = sprintf(
                "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
                $testdsn["hostspec"],
                '5432',
                $testdsn["database"],
                $testdsn["username"],
                $testdsn["password"]
            );

            // Create the PDO object and Connect to the database
            try {
                $localconn = new \PDO($conStr);
            } catch (\Throwable $e) {
                //print_r($e->getMessage());
                throw new \Exception('Unable to connect to the database');
            }
            //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $localconn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
            $sql =  "delete from groups where group_name = 'phpunit'";
            $localconn->query($sql);
            $localconn = null;
        }
    }


    /**
     * This function tests that the groups can be displayed.
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testListGroups()
    {
        \webtemplate\groups\EditGroupFunctions::listGroups(
            $this->tpl,
            $this->editgroup,
            $this->template
        );
        $this->assertContains(
            'groups/list.tpl',
            $this->template
        );
        $groupArray =  $this->tpl->getTemplateVars('RESULTS');
        $this->assertEquals('admin', $groupArray[0]['groupname']);
        $this->assertEquals('editusers', $groupArray[1]['groupname']);
        $this->assertEquals('editgroups', $groupArray[2]['groupname']);
    }

    /**
     * This function tests that if a database error is encountered when displaying
     * the groups an error is displayed
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testListGroupsDatabaseError()
    {
        \webtemplate\groups\EditGroupFunctions::listGroups(
            $this->tpl,
            $this->mockeditgroup,
            $this->template
        );
        $this->assertContains(
            'global/error.tpl',
            $this->template
        );
        $this->assertEquals(
            'Database Error',
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertEquals(
            'Error encountered when retreiving groups.',
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }

    /**
     * This function tests that if a database error is encountered when displaying
     * the groups an error is displayed
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testNewGroup()
    {
        \webtemplate\groups\EditGroupFunctions::newGroup(
            $this->tpl,
            $this->template
        );
        $this->assertContains(
            'groups/edit.tpl',
            $this->template
        );
        $this->assertEquals(
            'New Group',
            $this->tpl->getTemplateVars('PAGETITLE')
        );
        $groupArray =  $this->tpl->getTemplateVars('RESULTS');
        $this->assertEquals('', $groupArray[0]['groupname']);
        $this->assertEquals('', $groupArray[0]['description']);
        $this->assertEquals('Y', $groupArray[0]['editable']);
    }

    /**
     * This function tests if group 1 can be displayed in the edit group template
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testEditGroup()
    {
        $result = \webtemplate\groups\EditGroupFunctions::editGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            "1"
        );
        $this->assertTrue($result);
        $this->assertContains(
            'groups/edit.tpl',
            $this->template
        );
        $this->assertEquals(
            'Edit Group',
            $this->tpl->getTemplateVars('PAGETITLE')
        );
        $groupArray =  $this->tpl->getTemplateVars('RESULTS');
        $this->assertEquals('admin', $groupArray[0]['groupname']);
        $this->assertEquals('READONLY', $this->tpl->getTemplateVars('READONLY'));
    }

    /**
     * This function tests that if a invalid groupid is used displaying the group
     * for editing an error is displayed
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testEditGroupInvalidGroup()
    {
        $result = \webtemplate\groups\EditGroupFunctions::editGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            "A"
        );
        $this->assertFalse($result);
        $this->assertEquals('Invalid Group', $this->tpl->getTemplateVars('MSG'));
    }


    /**
     * This function tests that if a invalid groupid is used displaying the group
     * for editing an error is displayed
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testEditGroupDoesnotExist()
    {
        $result = \webtemplate\groups\EditGroupFunctions::editGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            "400"
        );
        $this->assertFalse($result);
        $this->assertEquals('Group Not Found', $this->tpl->getTemplateVars('MSG'));
    }

    /**
     * This function tests that if a invalid groupid is used displaying the group
     * for editing an error is displayed
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testEditGroupDatabaseError()
    {
        $result = \webtemplate\groups\EditGroupFunctions::editGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            "1"
        );
        $this->assertContains(
            'global/error.tpl',
            $this->template
        );
        $this->assertEquals(
            'Database Error',
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertEquals(
            'Error encountered when retreiving group.',
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }

    /**
     * This function tests if group 1 can be displayed in the confirm delete
     * group template
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testConfirmDeleteGroup()
    {
        $result = \webtemplate\groups\EditGroupFunctions::confirmDeleteGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            "1"
        );
        $this->assertTrue($result);
        $this->assertContains(
            'groups/delete.tpl',
            $this->template
        );
        $this->assertEquals(
            'Delete Group',
            $this->tpl->getTemplateVars('PAGETITLE')
        );
        $groupArray =  $this->tpl->getTemplateVars('RESULTS');
        $this->assertEquals('admin', $groupArray[0]['groupname']);
        $this->assertEquals('READONLY', $this->tpl->getTemplateVars('READONLY'));
    }

    /**
     * This function tests that if a invalid groupid is used the group cannot be
     * deleted
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testConfirmDeleteGroupInvalidGroup()
    {
        $result = \webtemplate\groups\EditGroupFunctions::confirmDeleteGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            "A"
        );
        $this->assertFalse($result);
        $this->assertEquals('Invalid Group', $this->tpl->getTemplateVars('MSG'));
    }


    /**
     * This function tests that if a invalid groupid is used displaying the group
     * for editing an error is displayed
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testConfirmDeleteGroupDoesnotExist()
    {
        $result = \webtemplate\groups\EditGroupFunctions::confirmDeleteGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            "400"
        );
        $this->assertFalse($result);
        $this->assertEquals('Group Not Found', $this->tpl->getTemplateVars('MSG'));
    }

    /**
     * This function tests that a database error is reported correctly
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testConfirmDeleteGroupDatabaseError()
    {
        $result = \webtemplate\groups\EditGroupFunctions::confirmDeleteGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            "1"
        );
        $this->assertContains(
            'global/error.tpl',
            $this->template
        );
        $this->assertEquals(
            'Database Error',
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertEquals(
            'Error encountered when retreiving group.',
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }






    /**
     * This function tests if test group can be deleted
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testDeleteGroup()
    {
        // Insert a test group into the database
        $groupid = '0';
        $groupinserted = $this->editgroup->saveGroup(
            $groupid,
            'phpunit',
            'Test Group',
            'N',
            'N'
        );
        if (\webtemplate\general\General::isError($groupinserted)) {
            $this->fail("Unable to insert test group");
        }
        $result = \webtemplate\groups\EditGroupFunctions::deleteGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $groupid
        );
        $this->assertTrue($result);
        $this->assertEquals(
            'Group (' . $groupid . ') Deleted',
            $this->tpl->getTemplateVars('MSG')
        );
    }

    /**
     * This function tests that if a invalid groupid is used the group cannot be
     * deleted
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testDeleteGroupInvalidGroup()
    {
        $result = \webtemplate\groups\EditGroupFunctions::deleteGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            "A"
        );
        $this->assertFalse($result);
        $this->assertEquals('Invalid Group', $this->tpl->getTemplateVars('MSG'));
    }


    /**
     * This function tests that if a invalid groupid is used displaying the group
     * for editing an error is displayed
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testDeleteGroupDoesnotExist()
    {
        $result = \webtemplate\groups\EditGroupFunctions::deleteGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            "400"
        );
        //print_r( $this->tpl->getTemplateVars());
        $this->assertFalse($result);
        $this->assertEquals('Group Not Found', $this->tpl->getTemplateVars('MSG'));
    }

    /**
     * This function tests that a database error is reported correctly
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testDeleteGroupDatabaseError()
    {
        $result = \webtemplate\groups\EditGroupFunctions::deleteGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            "1"
        );
        $this->assertContains(
            'global/error.tpl',
            $this->template
        );
        $this->assertEquals(
            'Database Error',
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertContains(
            'Error encountered when deleting group.',
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }


    /**
     * This function saves a test group to the database
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testSaveGroup()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertTrue($result);
        $this->assertContains('Created', $this->tpl->getTemplateVars('MSG'));
        $groupdata = $this->tpl->getTemplateVars('RESULTS');
        $this->assertNotEquals($inputdata['groupid'], $groupdata[0]['groupid']);
        $this->assertEquals($inputdata['groupname'], $groupdata[0]['groupname']);
    }



    /**
     * This function tries to save a new group which already exists to the database
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testSaveGroupExistingGroup()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "admin";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertFalse($result);
        $this->assertContains(
            'admin already exists in the database',
            $this->tpl->getTemplateVars('MSG')
        );
        $groupdata = $this->tpl->getTemplateVars('RESULTS');
        $this->assertEquals($inputdata['groupid'], $groupdata[0]['groupid']);
        $this->assertEquals($inputdata['groupname'], $groupdata[0]['groupname']);
    }


    /**
     * This function tries to save a new group with an invalid name and description
     * to the database
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testSaveNewGroupDataInvalid()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit;";
        $inputdata['description'] = "This is a test group for PHPUNIT;";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertFalse($result);
        $this->assertContains(
            'Invalid Group Name',
            $this->tpl->getTemplateVars('MSG')
        );
        $this->assertContains(
            'Invalid Group Description',
            $this->tpl->getTemplateVars('MSG')
        );
        $groupdata = $this->tpl->getTemplateVars('RESULTS');
        $this->assertEquals($inputdata['groupid'], $groupdata[0]['groupid']);
        $this->assertEquals($inputdata['groupname'], $groupdata[0]['groupname']);
    }


    /**
     * This function tries to save an existing  group with an invalid description
     * to the database
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testSaveExistingGroupDataInvalid()
    {
        $inputdata = array();
        $inputdata['groupid'] = '1';
        $inputdata['groupname'] = "admin";
        $inputdata['description'] = "This is a test group for PHPUNIT;";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertFalse($result);
        $this->assertContains(
            'Invalid Group Description',
            $this->tpl->getTemplateVars('MSG')
        );
        $groupdata = $this->tpl->getTemplateVars('RESULTS');
        $this->assertEquals($inputdata['groupid'], $groupdata[0]['groupid']);
        $this->assertEquals($inputdata['groupname'], $groupdata[0]['groupname']);
    }

    /**
     * This function saves a test group to the database and tries to save it again
     * with no changes.
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testSaveGroupNoChanges()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertTrue($result);
        $this->assertContains('Created', $this->tpl->getTemplateVars('MSG'));
        $groupdata = $this->tpl->getTemplateVars('RESULTS');
        $groupid = $groupdata[0]['groupid'];
        $inputdata['groupid'] = $groupid;
        $nochangeresult = \webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertFalse($nochangeresult);
        $this->assertContains(
            'You have not made any changes',
            $this->tpl->getTemplateVars('MSG')
        );
    }


    /**
     * This function saves a test group to the database and tries to save it again
     * with the description changed.
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testSaveGroupDescriptionChanges()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertTrue($result);
        $this->assertContains('Created', $this->tpl->getTemplateVars('MSG'));
        $groupdata = $this->tpl->getTemplateVars('RESULTS');
        $groupid = $groupdata[0]['groupid'];
        $inputdata['groupid'] = $groupid;
         $inputdata['description'] = "This is an updated test group for PHPUNIT";
        $changeresult = \webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertTrue($changeresult);
        $this->assertContains(
            'Group Description Changed',
            $this->tpl->getTemplateVars('MSG')
        );
    }


    /**
     * This function tests he correct error message is returned when a database
     * error prevents checking if the new group exists
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testSaveGroupDatabaseFailGroupExists()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            $inputdata
        );
        $this->assertFalse($result);
        $this->assertEquals('global/error.tpl', $this->template);
        //Check the Page Title

        $this->assertContains(
            "Database Error",
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertContains(
            "An error occured checking if the group exists",
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }



    /**
     * This function tests he correct error message is returned when a database
     * error prevents the group being saved
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testSaveGroupDatabaseFailGroupSave()
    {
        // SEt up the INPUT DATA to be saved.
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        // Set up the MOCK Database Driver
        $functions = array(
            "checkGroupExists" => array(
                "pass" => true,
                "notfound" => true
            )
        );
        $data = array(
            "checkGroupExists" => array(
                "0" => array(
                    "group_id" => '10',
                    "group_name" => "phpunit",
                    "group_description" => "This is a test group for PHPUNIT",
                    "group_useforproduct" => 'Y',
                    "group_editable" => 'Y',
                    "group_autogroup" => 'Y'
                )
            )
        );
        $this->mockdb->control($functions, $data);


        $result = \webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            $inputdata
        );

        $this->assertFalse($result);
        $this->assertEquals('global/error.tpl', $this->template);
        //Check the Page Title

        $this->assertContains(
            "Database Error",
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertContains(
            "An error occured when saving the group details",
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }


    /**
     * This function tests he correct error message is returned when a database
     * error prevents the group being saved
     *
     * @group unittest
     * @group groups
     *
     * @return null
     */
    public function testSaveGroupDatabaseFailGroupRetrieve()
    {
        // SEt up the INPUT DATA to be saved.
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        // Set up the MOCK Database Driver
        $functions = array(
            "checkGroupExists" => array(
                "pass" => true,
                "notfound" => true
            ),
            'saveGroup' => array(
                "pass" => true,
                "id" => true
            )
        );
        $data = array(
            "checkGroupExists" => array(
                "0" => array(
                    "group_id" => '10',
                    "group_name" => "phpunit",
                    "group_description" => "This is a test group for PHPUNIT",
                    "group_useforproduct" => 'Y',
                    "group_editable" => 'Y',
                    "group_autogroup" => 'Y'
                )
            ),
            "saveGroup" => array(
                "0" => array(
                    "group_id" => '10',
                    "group_name" => "phpunit",
                    "group_description" => "This is a test group for PHPUNIT",
                    "group_useforproduct" => 'Y',
                    "group_editable" => 'Y',
                    "group_autogroup" => 'Y'
                )
            )
        );
        $this->mockdb->control($functions, $data);


        $result = \webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            $inputdata
        );

        $this->assertFalse($result);
        $this->assertEquals('global/error.tpl', $this->template);
        //Check the Page Title

        $this->assertContains(
            "Database Error",
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertContains(
            "An error occured retrieving the group details",
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }
}
