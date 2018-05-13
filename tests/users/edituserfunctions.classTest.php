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
require_once __DIR__ .'/../_data/database.php';

/**
 * Help Class Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class EditUserFunctionsClassTest extends TestCase
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
     * @var    \webtemplate\users\EditUser
     * @access protected
     */
    protected $edituser = null;

    /* property: mockedituser
     * @var    \webtemplate\users\EditUser
     * @access protected
     */
    protected $mockedituser = null;
    /* property: editusergroups
     * @var    \webtemplate\groups\EditUserGroups
     * @access protected
     */
    protected $editusergroups = null;

    /* property: mockeditusergroups
     * @var    \webtemplate\groups\EditUserGroups
     * @access protected
     */
    protected $mockeditusergroups = null;

    /*
     * property: config
     * @var    \webtemplate\config\Configure
     * @access private
     */
    protected $config = null;

    /*
     * property: newuserflag
     * @var    boolean
     * @access private
     */
    protected $newuserflag = false;
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
        $this->edituser = new \webtemplate\users\EditUser($this->db);
        $this->config = new \webtemplate\config\Configure($this->db);
        $this->editusergroups = new \webtemplate\groups\EditUsersGroups($this->db);

        // Create a Mock database object
        $testdsn['phptype'] = 'mock';
        $this->mockdb = \webtemplate\db\DB::load($testdsn);

        // Create a Mock Edit User object
        $this->mockedituser = new \webtemplate\users\EditUser($this->mockdb);

        // Create Group Obkect Using the mockDB object
        $this->mockeditusergroups = new \webtemplate\groups\EditUsersGroups(
            $this->mockdb
        );
        $this->newuserflag = false;
        $this->deleteTestUser();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
        $this->deleteTestUser();
        $this->db->disconnect();
    }

    /**
     * This function is called to remove the test user from the database
     *
     * This function is called to remove the test user from the database.  It is
     * called by both the setup and teardown functions to ensure that test data
     * is not available to cause tests to fail
     *
     * @return null
     */
    protected function deleteTestUser()
    {
        global $testdsn;

        // Remove the phpunit2 user prior to running any tests.
        if ($this->edituser->checkUserExists('phpunit2')) {
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
            $sql =  "delete from users where user_name = 'phpunit2'";

            $localconn->query($sql);
            $localconn = null;
        }
    }

    /*
     * This function tests the list user function when no searchtype is present
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testListUsersNoSearchType()
    {
        $testdata = array();
        $searchresult = \webtemplate\users\EditUserFunctions::listUsers(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->config->read('param.users.regexp'),
            $testdata
        );
        $this->assertFalse($searchresult);
        $this->assertEquals('users/search.tpl', $this->template);
        $this->assertEquals(
            'Invalid Search Field',
            $this->tpl->getTemplateVars('MSG')
        );
    }

    /*
     * This function tests the list user function when no searchtype is present
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testListUsersNInvalidSearchType()
    {
        $testdata = array();
        $testdata['searchtype'] = 'dummy';
        $searchresult = \webtemplate\users\EditUserFunctions::listUsers(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->config->read('param.users.regexp'),
            $testdata
        );
        $this->assertFalse($searchresult);
        $this->assertEquals('users/search.tpl', $this->template);
        $this->assertEquals(
            'Invalid Search Field',
            $this->tpl->getTemplateVars('MSG')
        );
    }

    /*
     * This function tests the list user function with searchtype username and no
     * searchstr
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testListUsersBlankUserName()
    {
        $testdata = array();
        $testdata['searchtype'] = 'username';
        $searchresult = \webtemplate\users\EditUserFunctions::listUsers(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->config->read('param.users.regexp'),
            $testdata
        );

        $this->assertTrue($searchresult);
        $this->assertEquals('users/list.tpl', $this->template);
        $this->assertEquals(10, $this->tpl->getTemplateVars('USERSFOUND'));
    }

    /*
     * This function tests the list user function with searchtype username and
     * searchstr equal to a valid user
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testListUserWithUsername()
    {
        $testdata = array();
        $testdata['searchtype'] = 'username';
        $testdata['searchstr'] = 'phpunit';
        \webtemplate\users\EditUserFunctions::listUsers(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->config->read('param.users.regexp'),
            $testdata
        );
        $this->assertEquals('users/list.tpl', $this->template);
        $this->assertEquals(1, $this->tpl->getTemplateVars('USERSFOUND'));
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        //print_r($memberdata);
        $this->assertEquals("phpunit", $memberdata[0]['username']);
         $this->assertEquals("Phpunit User", $memberdata[0]['realname']);
    }

    /*
     * This function tests the list user functionwith searchtype username and
     * searchstr equal to an invalid user
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testListUserWithinvalidUsername()
    {
        $testdata = array();
        $testdata['searchtype'] = 'username';
        $testdata['searchstr'] = 'phpunit9';
        \webtemplate\users\EditUserFunctions::listUsers(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->config->read('param.users.regexp'),
            $testdata
        );
        $this->assertEquals('global/error.tpl', $this->template);
        $this->assertEquals(0, $this->tpl->getTemplateVars('USERSFOUND'));
        $this->assertEquals('Not Found', $this->tpl->getTemplateVars('ERRORMSG'));
    }

    /*
     * This function tests the list user function with searchtype email and no
     * searchstr
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testListUsersBlankEmail()
    {
        $testdata = array();
        $testdata['searchtype'] = 'email';
        $searchresult = \webtemplate\users\EditUserFunctions::listUsers(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->config->read('param.users.regexp'),
            $testdata
        );

        $this->assertTrue($searchresult);
        $this->assertEquals('users/list.tpl', $this->template);
        $this->assertEquals(10, $this->tpl->getTemplateVars('USERSFOUND'));
    }

    /*
     * This function tests the list user function with searchtype email and
     * searchstr equal to a valid user
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testListUserWithEmail()
    {
        $testdata = array();
        $testdata['searchtype'] = 'email';
        $testdata['searchstr'] = 'phpunit@example.com';
        \webtemplate\users\EditUserFunctions::listUsers(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->config->read('param.users.regexp'),
            $testdata
        );
        $this->assertEquals('users/list.tpl', $this->template);
        $this->assertEquals(1, $this->tpl->getTemplateVars('USERSFOUND'));
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        //print_r($memberdata);
        $this->assertEquals("phpunit", $memberdata[0]['username']);
         $this->assertEquals("Phpunit User", $memberdata[0]['realname']);
    }

    /*
     * This function tests the list user functionwith searchtype email and
     * searchstr equal to an invalid user
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testListUserWithinvalidEmail()
    {
        $testdata = array();
        $testdata['searchtype'] = 'email';
        $testdata['searchstr'] = 'phpunit99@example.com';
        \webtemplate\users\EditUserFunctions::listUsers(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->config->read('param.users.regexp'),
            $testdata
        );
        $this->assertEquals('global/error.tpl', $this->template);
        $this->assertEquals(0, $this->tpl->getTemplateVars('USERSFOUND'));
        $this->assertEquals('Not Found', $this->tpl->getTemplateVars('ERRORMSG'));
    }






    /*
     * This function tests the list user function with searchtype realname and no
     * searchstr
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testListUsersBlankRealName()
    {
        $testdata = array();
        $testdata['searchtype'] = 'realname';
        $searchresult = \webtemplate\users\EditUserFunctions::listUsers(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->config->read('param.users.regexp'),
            $testdata
        );

        $this->assertTrue($searchresult);
        $this->assertEquals('users/list.tpl', $this->template);
        $this->assertEquals(10, $this->tpl->getTemplateVars('USERSFOUND'));
    }

    /*
     * This function tests the list user function with searchtype  realname and
     * searchstr equal to a valid user
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testListUserWithrealname()
    {
        $testdata = array();
        $testdata['searchtype'] = 'realname';
        $testdata['searchstr'] = 'Phpunit User';
        \webtemplate\users\EditUserFunctions::listUsers(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->config->read('param.users.regexp'),
            $testdata
        );
        $this->assertEquals('users/list.tpl', $this->template);
        $this->assertEquals(1, $this->tpl->getTemplateVars('USERSFOUND'));
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        //print_r($memberdata);
        $this->assertEquals("phpunit", $memberdata[0]['username']);
        $this->assertEquals("Phpunit User", $memberdata[0]['realname']);
    }

    /*
     * This function tests the list user functionwith searchtype email and
     * searchstr equal to an invalid user
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testListUserWithinvalidRealName()
    {
        $testdata = array();
        $testdata['searchtype'] = 'realname';
        $testdata['searchstr'] = 'John Doe';
        \webtemplate\users\EditUserFunctions::listUsers(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->config->read('param.users.regexp'),
            $testdata
        );
        $this->assertEquals('global/error.tpl', $this->template);
        $this->assertEquals(0, $this->tpl->getTemplateVars('USERSFOUND'));
        $this->assertEquals('Not Found', $this->tpl->getTemplateVars('ERRORMSG'));
    }

    /*
     * This function tests the newuser function to check a blank user form is
     * displayed
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testNewUser()
    {

        \webtemplate\users\EditUserFunctions::newUser(
            $this->tpl,
            $this->template
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        $this->assertEquals('New User', $this->tpl->getTemplateVars('PAGETITLE'));
        $this->assertFalse($this->tpl->getTemplateVars('READONLY'));
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        //print_r($memberdata);
        $this->assertEquals("", $memberdata[0]['username']);
        $this->assertEquals("", $memberdata[0]['realname']);
    }

    /*
     * This function tests the Edituser function to check if an invalid user id
     * format fails.  i.e. Letters rather than numbers
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testEditUserInvalidID()
    {
        $testdata = array();
        $testdata['userid'] = 'aaa';
        \webtemplate\users\EditUserFunctions::editUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $testdata
        );
        $this->assertEquals('users/search.tpl', $this->template);
        $this->assertEquals(
            'You have chosen an invalid user. Please try again',
            $this->tpl->getTemplateVars('MSG')
        );
    }

   /*
     * This function tests the Edituser function to check if an invalid user id
     * fails.  i.e. The user does not exit
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testEditUserIDDoesNotExist()
    {
        $testdata = array();
        $testdata['userid'] = '100';
        \webtemplate\users\EditUserFunctions::editUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $testdata
        );
        $this->assertEquals('users/search.tpl', $this->template);
        $this->assertEquals(
            'Not Found. Please Try Again',
            $this->tpl->getTemplateVars('MSG')
        );
    }


    /*
     * This function tests the Edituser function to check if a valid user id
     * and their groups can be retrieved from the database
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testEditUserValidUserID()
    {
        $testdata = array();
        $testdata['userid'] = '2';
        \webtemplate\users\EditUserFunctions::editUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $testdata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertEquals(
            "Edit User: phpunit (Phpunit User)",
            $this->tpl->getTemplateVars('PAGETITLE')
        );
        // Check we have the right member's data
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        //print_r($memberdata);
        $this->assertEquals("phpunit", $memberdata[0]['username']);
        $this->assertEquals("Phpunit User", $memberdata[0]['realname']);
        // Check we have the right GROUPDATA
        $groupresult = $this->tpl->getTemplateVars('GROUPRESULT');
        //print_r($groupresult);
        $this->assertEquals('Y', $groupresult[0]['useringroup']);
        $this->assertEquals('N', $groupresult[1]['useringroup']);
        $this->assertEquals('N', $groupresult[2]['useringroup']);
        $this->assertEquals('I', $groupresult[3]['useringroup']);
        $this->assertEquals('N', $groupresult[4]['useringroup']);
    }


    /*
     * This function tests the Edituser function to check if the correct response is
     * given if the group list cannot be obtained from the database
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testEditUserFailedtoRetriveGroupList()
    {
        $testdata = array();
        $testdata['userid'] = '2';
        \webtemplate\users\EditUserFunctions::editUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->mockeditusergroups,
            $testdata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertEquals(
            "Edit User: phpunit (Phpunit User)",
            $this->tpl->getTemplateVars('PAGETITLE')
        );
        // Check we have the right member's data
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        //print_r($memberdata);
        $this->assertEquals("phpunit", $memberdata[0]['username']);
        $this->assertEquals("Phpunit User", $memberdata[0]['realname']);
        // Check we have the right GROUPDATA
        $this->assertEquals('', $this->tpl->getTemplateVars('GROUPRESULT'));
        //print_r($groupresult);
        $this->assertEquals('SQL Query Error', $this->tpl->getTemplateVars('MSG'));
    }



    /*
     * This function tests the Edituser function to check if a valid user id
     * and their groups can be retrieved from the database
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testEditUserFailedtoRetriveUsersGroup()
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
        $this->mockdb->control($functions, $data);
        $testdata = array();
        $testdata['userid'] = '2';
        \webtemplate\users\EditUserFunctions::editUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->mockeditusergroups,
            $testdata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertEquals(
            "Edit User: phpunit (Phpunit User)",
            $this->tpl->getTemplateVars('PAGETITLE')
        );
        // Check we have the right member's data
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        //print_r($memberdata);
        $this->assertEquals("phpunit", $memberdata[0]['username']);
        $this->assertEquals("Phpunit User", $memberdata[0]['realname']);
        // Check we have the right GROUPDATA
        $groupresult = $this->tpl->getTemplateVars('GROUPRESULT');
        //print_r($groupresult);
        $this->assertEquals('Test', $groupresult[0]['groupname']);
        $this->assertEquals('N', $groupresult[0]['useringroup']);
        $this->assertEquals('SQL Query Error', $this->tpl->getTemplateVars('MSG'));
    }


    /*
     * This function tests the Edituser function to check if the correct response is
     * given if the user data cannot be validated
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testSaveUserInvalidData()
    {
        $testdata = array();
        $testdata['userid'] = '0';
        $testdata['username'] = 'phpunit_99';
        $testdata['realname'] = 'Phpunit User.';
        $testdata['passwd'] = 'password';
        $testdata['useremail'] = "phpunit.example.com";
        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertContains(
            "Invalid User Name",
            $this->tpl->getTemplateVars('MSG')
        );
        $this->assertContains(
            "Invalid Real Name",
            $this->tpl->getTemplateVars('MSG')
        );
        $this->assertContains(
            "Invalid Email Address",
            $this->tpl->getTemplateVars('MSG')
        );
        $this->assertContains(
            "Invalid Password",
            $this->tpl->getTemplateVars('MSG')
        );
    }


    /*
     * This function tests the Edituser function to check if the correct response is
     * given if the user data cannot be validated
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testSaveExistingUserInvalidData()
    {
        $existinguser = $this->edituser->getuser('1');
        if (\webtemplate\general\General::isError($existinguser)) {
            $this->fail("Failed to retrieve userdata");
        }

        $testdata = array();
        $testdata['userid'] = $existinguser[0]['userid'];
        $testdata['username'] = $existinguser[0]['username'];
        $testdata['realname'] = $existinguser[0]['realname'];
        $testdata['passwd'] = '';
        $testdata['useremail'] = "phpunit.example.com";
        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertContains(
            "Edit User: admin (Administrator)",
            $this->tpl->getTemplateVars('PAGETITLE')
        );
        $this->assertContains(
            "Invalid Email Address",
            $this->tpl->getTemplateVars('MSG')
        );
    }

    /*
     * This function tests the Edituser function to check if the correct response is
     * given if the user already exists in the database
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testSaveUserUserExists()
    {
        $testdata = array();
        $testdata['userid'] = '0';
        $testdata['username'] = 'phpunit';
        $testdata['realname'] = 'Phpunit User';
        $testdata['passwd'] = 'Password77';
        $testdata['useremail'] = "phpunit@example.com";
        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertContains(
            "User phpunit exists in the database",
            $this->tpl->getTemplateVars('MSG')
        );
        //Check the invalid data is returned to the webpage
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        $this->assertEquals("phpunit", $memberdata[0]['username']);
        $this->assertEquals("Phpunit User", $memberdata[0]['realname']);
    }

    /*
     * This function tests the Edituser function to check that a new user can be
     * created in the database
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testSaveUserNewUser()
    {
        $testdata = array();
        $testdata['userid'] = '0';
        $testdata['username'] = 'phpunit2';
        $testdata['realname'] = 'Phpunit User';
        $testdata['passwd'] = 'Password77';
        $testdata['useremail'] = "phpunit@example.com";
        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertContains(
            "User Created phpunit2",
            $this->tpl->getTemplateVars('MSG')
        );
        //Check the invalid data is returned to the webpage
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        $this->assertEquals("phpunit2", $memberdata[0]['username']);
        $this->assertEquals("Phpunit User", $memberdata[0]['realname']);

        // User has been created.  Retrieve the user to see if they really exist
        $userexists = $this->edituser->checkUserExists('phpunit2');
        $this->assertTrue($userexists);

        $this->newuserflag = true;
    }


    /*
     * This function tests the Edituser function to check that an existing user can
     * be modified
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testModifyUser()
    {
        // Create the test user to be modified.
        $testdata = array();
        $testdata['userid'] = '0';
        $testdata['username'] = 'phpunit2';
        $testdata['realname'] = 'Phpunit User';
        $testdata['passwd'] = 'Password77';
        $testdata['useremail'] = "phpunit@example.com";
        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertContains(
            "User Created phpunit2",
            $this->tpl->getTemplateVars('MSG')
        );

        // Get the user ID.  It is needed to update the user
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        $userid = $memberdata[0]['userid'];

        // Create the Modified data to save
        $modifieddata = array();
        $modifieddata['userid'] = $userid;
        $modifieddata['username'] = 'phpunit2';
        $modifieddata['realname'] = 'Phpunit User';
        $modifieddata['passwd'] = '';
        $modifieddata['useremail'] = "phpunit2@example.com";
        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $modifieddata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertContains(
            "E-Mail Address Changed",
            $this->tpl->getTemplateVars('MSG')
        );

        $modifiedmemberdata = $this->tpl->getTemplateVars('RESULTS');
        $this->assertEquals(
            "phpunit2@example.com",
            $modifiedmemberdata[0]['useremail']
        );

        $userdata = $this->edituser->getuser($userid);
        if (\webtemplate\general\General::isError($userdata)) {
            $this->fail("Failed to retrieve userdata");
        } else {
            $this->assertEquals("phpunit2@example.com", $userdata[0]['useremail']);
        }
    }



    /*
     * This function tests the Edituser function to check that a duplicate user
     * cannot be created in the database
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testSaveDuplicateUserDatabasefail()
    {
        $testdata = array();
        $testdata['userid'] = '0';
        $testdata['username'] = 'phpunit';
        $testdata['realname'] = 'Phpunit User';
        $testdata['passwd'] = 'Password77';
        $testdata['useremail'] = "phpunit@example.com";
        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->mockedituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertEquals('global/error.tpl', $this->template);
        //Check the Page Title

        $this->assertContains(
            "Database Error",
            $this->tpl->getTemplateVars('HEADERMSG')
        );
    }


    /*
     * This function tests the Edituser function to check that an existing user can
     * be modified
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testModifyUserNoChanges()
    {
        // Create the test user to be modified.
        $testdata = array();
        $testdata['userid'] = '0';
        $testdata['username'] = 'phpunit2';
        $testdata['realname'] = 'Phpunit User';
        $testdata['passwd'] = 'Password77';
        $testdata['useremail'] = "phpunit@example.com";
        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertContains(
            "User Created phpunit2",
            $this->tpl->getTemplateVars('MSG')
        );

        // Get the user ID.  It is needed to update the user
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        $userid = $memberdata[0]['userid'];

        // Create the Modified data to save
        $modifieddata = array();
        $modifieddata['userid'] = $userid;
        $modifieddata['username'] = 'phpunit2';
        $modifieddata['realname'] = 'Phpunit User';
        $modifieddata['passwd'] = '';
        $modifieddata['useremail'] = "phpunit@example.com";
        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $modifieddata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertContains(
            "You have not made any changes",
            $this->tpl->getTemplateVars('MSG')
        );
    }

    /*
     * This function tests the Edituser function to check that an existing user can
     * be modified
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testAddUserPlusGroups()
    {
        // Create the test user to be modified.
        $testdata = array();
        $testdata['userid'] = '0';
        $testdata['username'] = 'phpunit2';
        $testdata['realname'] = 'Phpunit User';
        $testdata['passwd'] = 'Password77';
        $testdata['useremail'] = "phpunit@example.com";
        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertContains(
            "User Created phpunit2",
            $this->tpl->getTemplateVars('MSG')
        );

        // Get the user ID.  It is needed to update the user
        $memberdata = $this->tpl->getTemplateVars('RESULTS');
        $userid = $memberdata[0]['userid'];

        // Create the Modified data to save
        $modifieddata = array();
        $modifieddata['userid'] = $userid;
        $modifieddata['username'] = 'phpunit2';
        $modifieddata['realname'] = 'Phpunit User';
        $modifieddata['passwd'] = '';
        $modifieddata['useremail'] = "phpunit@example.com";
        $modifieddata['admin'] = 'Y';
        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->edituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $modifieddata
        );
        $this->assertEquals('users/edit.tpl', $this->template);
        //Check the Page Title
        $this->assertContains(
            "Added to group: admin",
            $this->tpl->getTemplateVars('MSG')
        );
    }

    /*
     * This function tests that an error is returned if the group array cannot be
     * populated.
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testFailGettingGroups()
    {
        // Create the test user to be modified.
        $testdata = array();
        $testdata['userid'] = '0';
        $testdata['username'] = 'phpunit2';
        $testdata['realname'] = 'Phpunit User';
        $testdata['passwd'] = 'Password77';
        $testdata['useremail'] = "phpunit@example.com";


        // Set up the MOCK Database Driver
        $functions = array(
            'checkUserExists' => array(
                "pass" => true,
                "notfound" => true
            )
        );
        $mockdata = array (
            'checkUserExists' => $testdata
        );

        // Set up Valid Data so the save passes but the getid fails
        $this->mockdb->control($functions, $mockdata);

        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->mockedituser,
            $this->mockeditusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertContains(
            "Database Error",
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertContains(
            "An error occured when retrieving the groups.",
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }

    /*
     * This function tests that an error is returned if user cannot be saved
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testFailSavingUser()
    {
        // Create the test user to be modified.
        $testdata = array();
        $testdata['userid'] = '0';
        $testdata['username'] = 'phpunit2';
        $testdata['realname'] = 'Phpunit User';
        $testdata['passwd'] = 'Password77';
        $testdata['useremail'] = "phpunit@example.com";


        // Set up the MOCK Database Driver
        $functions = array(
            'checkUserExists' => array(
                "pass" => true,
                "notfound" => true
            )
        );
        $mockdata = array (
            'checkUserExists' => $testdata
        );

        // Set up Valid Data so the save passes but the getid fails
        $this->mockdb->control($functions, $mockdata);

        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->mockedituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertContains(
            "Database Error",
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertContains(
            "An error occured when saving the user's details.",
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }

    /*
     * This function tests that an error is returned if the user's details cannot
     * be retrieved after the save
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testFailRetrievingUserAfterSave()
    {
        // Create the test user to be modified.
        $testdata = array();
        $testdata['userid'] = '0';
        $testdata['username'] = 'phpunit2';
        $testdata['realname'] = 'Phpunit User';
        $testdata['passwd'] = 'Password77';
        $testdata['useremail'] = "phpunit@example.com";


        // Set up the MOCK Database Driver
        $functions = array(
            'checkUserExists' => array(
                "pass" => true,
                "notfound" => true
            ),
            'insertUser' => array(
                "pass" => true,
                'id' => true
            )
        );
        $mockdata = array (
            'checkUserExists' => $testdata,
            'insertUser' => array(
                'user_id' =>100,
                'user_name' => "phpunit2",
                'real_name' => "Phpunit User",
                'password' => "Password77",
                'user_email' => "phpunit2@example.com",
                'user_enabled' => 'Y'
            )
        );

        // Set up Valid Data so the save passes but the getid fails
        $this->mockdb->control($functions, $mockdata);

        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->mockedituser,
            $this->editusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertContains(
            "Database Error",
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertContains(
            "An error occured retrieving the user's details",
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }



    /*
     * This function tests that an error is returned if the user'sgroups cannot
     * be save
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testFailSavingUsersGroups()
    {
        // Create the test user to be modified.
        $testdata = array();
        $testdata['userid'] = '100';
        $testdata['username'] = 'phpunit2';
        $testdata['realname'] = 'Phpunit User';
        $testdata['useremail'] = "phpunit@example.com";
        $testdata['userenabled'] = 'Y';
        $testdata['admin'] = 'Y';

        // Set up the MOCK Database Driver
        $functions = array(
            'checkUserExists' => array(
                "pass" => true,
                "notfound" => false
            ),
            'updateUser' => array(
                "update" => true,
                'id' => true
            ),
            'getuser' => array(
                "pass" => true
            ),
            'getGroupList' => array(
                'pass' => 'true'
            ),
            'getUsersExplicitGroups' => array(
                'pass' => true,
                'notfound' => true
            )
        );
        $mockdata = array (
            'checkUserExists' => $testdata,
            'updateUser' => array(
                'user_id' =>100,
                'user_name' => "phpunit2",
                'user_realname' => "Phpunit User",
                'user_email' => "phpunit@example.com",
                'user_enabled' => 'Y',
                'user_disable_mail' => 'N',
                'admin' => 'Y'
            ),
           'getuser' => array(
                'user_id' =>100,
                'user_name' => "phpunit2",
                'user_realname' => "Phpunit User",
                'password' => "Password77",
                'user_email' => "phpunit@example.com",
                'user_enabled' => 'Y',
                'user_disable_mail' => 'N',
                'date' => '2018/04/01',
                'passwd_changed' => '2018/04/01'
            ),
            'getGroupList' => array(
                '0' => array(
                    "group_id" => '1',
                    "group_name" => "admin",
                    "group_description" => "Test Group",
                    "group_useforproduct" => 'Y',
                    "group_editable" => 'Y',
                    "group_autogroup" => 'N',
                    "group_admingroup" => 'Y'
                )
            )
        );

        // Set up Valid Data so the save passes but the getid fails
        $this->mockdb->control($functions, $mockdata);

        \webtemplate\users\EditUserFunctions::saveUser(
            $this->tpl,
            $this->template,
            $this->mockedituser,
            $this->mockeditusergroups,
            $this->config->read("param.users"),
            $testdata
        );
        $this->assertContains(
            "Database Error",
            $this->tpl->getTemplateVars('HEADERMSG')
        );
        $this->assertContains(
            "An error occured when saving the user's groups",
            $this->tpl->getTemplateVars('ERRORMSG')
        );
    }
}
