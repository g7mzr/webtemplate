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

namespace g7mzr\webtemplate\unittest;

use PHPUnit\Framework\TestCase;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// Load the Test Database Configuration File
require_once __DIR__ . '/../_data/database.php';

/**
 * Edit Group Class Unit Tests
 *
 **/
class EditGroupFunctionsClassTest extends TestCase
{
    /*
     * Property: db
     * @var    \g7mzr\db\DBManager
     * @access protected
     */
    protected $db = null;

    /*
     * Property: mockdb
     * @var    \g7mzr\db\DBManager
     * @access protected
     */
    protected $mockDB = null;

    /*
     * Property: tpl
     * @var   \g7mzr\webtemplate\application\SmartyTemplate
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
     * @var   \g7mzr\webtemplate\groups\EditGroup
     * @access protected
     */
    protected $editgroup = null;

    /* property: mockedituser
     * @var   \g7mzr\webtemplate\groups\EditGroup
     * @access protected
     */
    protected $mockeditgroups = null;


    /*
     * property: config
     * @var   \g7mzr\webtemplate\config\Configure
     * @access private
     */
    protected $config = null;

    /**
     * This function sets up the variables and objects for the test
     *
     * @throws \g7mzr\webtemplate\application\exceptions\AppException If unable to set data access mode.
     * @throws \g7mzr\webtemplate\application\exceptions\AppException If unable to connect to database.
     *
     * @return void
     *
     * @access protected
     */
    protected function setup(): void
    {
        global $testdsn;

        try {
            $this->db = new \g7mzr\db\DBManager(
                $testdsn,
                $testdsn['username'],
                $testdsn['password']
            );
            $setresult = $this->db->setMode("datadriver");
            if (\g7mzr\db\common\Common::isError($setresult)) {
                throw new\g7mzr\webtemplate\application\exceptions\AppException($setresult->getMessage());
            }
        } catch (Exception $ex) {
            throw new \g7mzr\webtemplate\application\exceptions\AppException($ex->getMessage());
            $this->databaseconnection = false;
        }
        $this->tpl = new \g7mzr\webtemplate\application\SmartyTemplate();
        $this->editgroup = new\g7mzr\webtemplate\groups\EditGroups($this->db->getDataDriver());
        $configDir = __DIR__ . "/../../configs";
        $this->config = new\g7mzr\webtemplate\config\Configure($configDir);

        // Create a Mock database object
        $testdsn['dbtype'] = 'mock';
        $this->mockDB = new \g7mzr\db\DBManager(
            $testdsn,
            $testdsn['username'],
            $testdsn['password']
        );
        $setresult = $this->mockDB->setMode("datadriver");

        // Create a Mock Edit User object
        $this->mockeditgroup = new \g7mzr\webtemplate\groups\EditGroups($this->mockDB->getDataDriver());
        $this->deleteTestGroup();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->deleteTestGroup();
        $this->db->getDataDriver()->disconnect();
    }


    /**
     * This function is called to remove the test group from the database
     *
     * This function is called to remove the test group from the database.  It is
     * called by both the setup and teardown functions to ensure that test data
     * is not available to cause tests to fail
     *
     * @throws \Exception If unable to connect to the remote database.
     *
     * @return void
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
                $testdsn["databasename"],
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
     * @return void
     */
    public function testListGroups()
    {
        \g7mzr\webtemplate\groups\EditGroupFunctions::listGroups(
            $this->tpl,
            $this->editgroup,
            $this->template
        );
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testListGroupsDatabaseError()
    {
        \g7mzr\webtemplate\groups\EditGroupFunctions::listGroups(
            $this->tpl,
            $this->mockeditgroup,
            $this->template
        );
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testNewGroup()
    {
        \g7mzr\webtemplate\groups\EditGroupFunctions::newGroup(
            $this->tpl,
            $this->template
        );
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testEditGroup()
    {
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::editGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            "1"
        );
        $this->assertTrue($result);
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testEditGroupInvalidGroup()
    {
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::editGroup(
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
     * @return void
     */
    public function testEditGroupDoesnotExist()
    {
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::editGroup(
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
     * @return void
     */
    public function testEditGroupDatabaseError()
    {
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::editGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            "1"
        );
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testConfirmDeleteGroup()
    {
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::confirmDeleteGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            "1"
        );
        $this->assertTrue($result);
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testConfirmDeleteGroupInvalidGroup()
    {
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::confirmDeleteGroup(
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
     * @return void
     */
    public function testConfirmDeleteGroupDoesnotExist()
    {
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::confirmDeleteGroup(
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
     * @return void
     */
    public function testConfirmDeleteGroupDatabaseError()
    {
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::confirmDeleteGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            "1"
        );
        $this->assertStringContainsString(
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
     * @return void
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
        if (\g7mzr\webtemplate\general\General::isError($groupinserted)) {
            $this->fail("Unable to insert test group");
        }
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::deleteGroup(
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
     * @return void
     */
    public function testDeleteGroupInvalidGroup()
    {
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::deleteGroup(
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
     * @return void
     */
    public function testDeleteGroupDoesnotExist()
    {
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::deleteGroup(
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
     * @return void
     */
    public function testDeleteGroupDatabaseError()
    {
        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::deleteGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            "1"
        );
        $this->assertStringContainsString(
            'global/error.tpl',
            $this->template
        );
        $this->assertEquals(
            'Database Error',
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testSaveGroup()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertTrue($result);
        $this->assertStringContainsString('Created', $this->tpl->getTemplateVars('MSG'));
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
     * @return void
     */
    public function testSaveGroupExistingGroup()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "admin";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertFalse($result);
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testSaveNewGroupDataInvalid()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit;";
        $inputdata['description'] = "This is a test group for PHPUNIT;";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertFalse($result);
        $this->assertStringContainsString(
            'Invalid Group Name',
            $this->tpl->getTemplateVars('MSG')
        );
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testSaveExistingGroupDataInvalid()
    {
        $inputdata = array();
        $inputdata['groupid'] = '1';
        $inputdata['groupname'] = "admin";
        $inputdata['description'] = "This is a test group for PHPUNIT;";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertFalse($result);
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testSaveGroupNoChanges()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertTrue($result);
        $this->assertStringContainsString('Created', $this->tpl->getTemplateVars('MSG'));
        $groupdata = $this->tpl->getTemplateVars('RESULTS');
        $groupid = $groupdata[0]['groupid'];
        $inputdata['groupid'] = $groupid;
        $nochangeresult = \g7mzr\webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertFalse($nochangeresult);
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testSaveGroupDescriptionChanges()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertTrue($result);
        $this->assertStringContainsString('Created', $this->tpl->getTemplateVars('MSG'));
        $groupdata = $this->tpl->getTemplateVars('RESULTS');
        $groupid = $groupdata[0]['groupid'];
        $inputdata['groupid'] = $groupid;
         $inputdata['description'] = "This is an updated test group for PHPUNIT";
        $changeresult = \g7mzr\webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->editgroup,
            $this->template,
            $inputdata
        );
        $this->assertTrue($changeresult);
        $this->assertStringContainsString(
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
     * @return void
     */
    public function testSaveGroupDatabaseFailGroupExists()
    {
        $inputdata = array();
        $inputdata['groupid'] = '0';
        $inputdata['groupname'] = "phpunit";
        $inputdata['description'] = "This is a test group for PHPUNIT";
        $inputdata['useforproduct'] = 'Y';
        $inputdata['autogroup'] = 'Y';

        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            $inputdata
        );
        $this->assertFalse($result);
        $this->assertEquals('global/error.tpl', $this->template);
        //Check the Page Title

        $this->assertStringContainsString(
            "Database Error",
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertStringContainsString(
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
     * @return void
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
        $this->mockDB->getDataDriver()->control($functions, $data);


        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            $inputdata
        );

        $this->assertFalse($result);
        $this->assertEquals('global/error.tpl', $this->template);
        //Check the Page Title

        $this->assertStringContainsString(
            "Database Error",
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertStringContainsString(
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
     * @return void
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
        $this->mockDB->getDataDriver()->control($functions, $data);


        $result = \g7mzr\webtemplate\groups\EditGroupFunctions::saveGroup(
            $this->tpl,
            $this->mockeditgroup,
            $this->template,
            $inputdata
        );

        $this->assertFalse($result);
        $this->assertEquals('global/error.tpl', $this->template);
        //Check the Page Title

        $this->assertStringContainsString(
            "Database Error",
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertStringContainsString(
            "An error occured retrieving the group details",
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }
}
