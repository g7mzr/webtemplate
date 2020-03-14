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

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// Include the Test Database Connection details
require_once dirname(__FILE__) . '/../_data/database.php';

use PHPUnit\Framework\TestCase;

/**
 * REST GROUPS Resource Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class RestGroupsTest extends TestCase
{
    /**
     * property: webtemplate
     * Webtemplate application class
     *
     * @var\g7mzr\webtemplate\application\Application
     */
    protected $webtemplate;

    /**
     * property: version
     * Webtemplate application class
     *
     * @var\g7mzr\webtemplate\rest\endpoint\Version
     */
    protected $groups;

    /**
     * property:groupid
     * ID of any froups created during test
     *
     * @var integer
     */
    protected $groupid = 0;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        global $sessiontest;

        $sessiontest = array(true);
        $this->webtemplate = new\g7mzr\webtemplate\application\Application();
        $this->groups = new\g7mzr\webtemplate\rest\endpoints\Groups($this->webtemplate);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->webtemplate->session()->destroy();
        if ($this->groupid <> 0) {
            $result = $this->webtemplate->editgroups()->deleteGroup($this->groupid);
            if (\g7mzr\webtemplate\general\General::isError($result)) {
                echo "Unable to delete test group\n";
            }
        }
    }

    /**
     * Not Loggedin test
     *
     * This function test to see if the correct response is given if the user is not
     * logged in
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupPermissionsNotloggedIn()
    {
        $result = $this->groups->permissions();
        if ($result === true) {
            $this->fail('User logged in in error');
        } else {
            $this->assertEquals(401, $result['code']);
            $this->assertEquals(
                'Login required to use this resource',
                $result['data']['ErrorMsg']
            );
        }
    }


    /**
     * Logged In no permission to access Groups Test
     *
     * This function test to see if the correct response is given if the user is
     * logged in without permission to access the application groups
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupPermissionsLoggedinNoAccess()
    {
        // Register the user to look as if they are logged in
        $registered = $this->webtemplate->user()->register(
            'secnone',
            $this->webtemplate->config()->read('pref')
        );
        if (\g7mzr\webtemplate\general\General::isError($registered)) {
            $this->fail('Failed to register user for ' . __METHOD__);
        }

        // Get the users groups
        $this->webtemplate->usergroups()->loadusersgroups(
            $this->webtemplate->user()->getUserId()
        );

        $this->webtemplate->session()->createSession(
            'secnone',
            $this->webtemplate->user()->getUserId(),
            false
        );

        $result = $this->groups->permissions();
        if ($result === true) {
            $this->fail('User allowed access in in error');
        } else {
            $this->assertEquals(403, $result['code']);
            $this->assertEquals(
                'You do not have permission to use this resource',
                $result['data']['ErrorMsg']
            );
        }
    }

    /**
     * Logged In with permission to access Groups Test
     *
     * This function test to see if the correct response is given if the user is not
     * logged in
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupPermissionsLoggedinwithAccess()
    {

        // Register the user to look as if they are logged in
        $registered = $this->webtemplate->user()->register(
            'secgroups',
            $this->webtemplate->config()->read('pref')
        );
        if (\g7mzr\webtemplate\general\General::isError($registered)) {
            $this->fail('Failed to register user for ' . __METHOD__);
        }

        // Get the users groups
        $this->webtemplate->usergroups()->loadusersgroups(
            $this->webtemplate->user()->getUserId()
        );

        $this->webtemplate->session()->createSession(
            'secgroups',
            $this->webtemplate->user()->getUserId(),
            false
        );

        $result = $this->groups->permissions();
        if ($result === true) {
            $this->assertTrue($result);
        } else {
            $this->fail('User not logged in');
        }
    }
    /**
     * This function test the options available for the groups endpoint
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsOptions()
    {
        $method = "OPTIONS";
        $args = array();
        $requestdata = array();
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertStringContainsString('GET', $result['options']);
        $this->assertStringContainsString('HEAD', $result['options']);
        $this->assertStringContainsString('POST', $result['options']);
        $this->assertStringContainsString('PUT', $result['options']);
        $this->assertStringNotContainsString('PATCH', $result['options']);
        $this->assertStringContainsString('DELETE', $result['options']);
        $this->assertStringContainsString('OPTIONS', $result['options']);
    }

    /**
     * This function tests the HEAD Method
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsHead()
    {
        $method = "HEAD";
        $args = array();
        $requestdata = array();
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals(948, $result['head']);
    }


    /**
     * This function tests the HEAD Method with an invalid ID
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsHeadInValidID()
    {
        $method = "HEAD";
        $args = array(100);
        $requestdata = array();
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals(
            'Not Found',
            $result['data']['ErrorMsg']
        );
    }

    /**
     * This function tests the GET Method with more than one argument
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsGetTooManyArgs()
    {
        $method = "GET";
        $args = array(1, "TWO");
        $requestdata = array();
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals(
            "groups: Invalid number of arguments",
            $result['data']['ErrorMsg']
        );
    }

    /**
     * This function tests the GET Method with no arguments
     *
     * This test returns all groups.  It checks that 5 groups are returned and that
     * the names of all 5 groups are correct.
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsGetNoArgs()
    {
        $method = "GET";
        $args = array();
        $requestdata = array();
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals(5, count($result['data']));
        $this->assertEquals("admin", $result['data'][0]['groupname']);
        $this->assertEquals("editusers", $result['data'][1]['groupname']);
        $this->assertEquals("editgroups", $result['data'][2]['groupname']);
        $this->assertEquals("testgroup", $result['data'][3]['groupname']);
        $this->assertEquals("testgrouptwo", $result['data'][4]['groupname']);
    }

    /**
     * This function tests the GET Method with one numerical argument
     *
     * This test returns group 2.  It checks that the data for group two is correct
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsGetNumericalArg()
    {
        $method = "GET";
        $args = array(2);
        $requestdata = array();
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals(1, count($result['data']));
        $this->assertEquals(2, $result['data'][0]['groupid']);
        $this->assertEquals("editusers", $result['data'][0]['groupname']);
        $this->assertEquals(
            "Members of this group can create and edit users",
            $result['data'][0]['description']
        );
        $this->assertEquals("N", $result['data'][0]['useforproduct']);
        $this->assertEquals("N", $result['data'][0]['editable']);
        $this->assertEquals("N", $result['data'][0]['autogroup']);
    }

    /**
     * This function tests the GET Method with group Name as argument
     *
     * This test returns group 2.  It checks that the data for group two is correct
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsGetGroupNameArg()
    {
        $method = "GET";
        $args = array('editusers');
        $requestdata = array();
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals(1, count($result['data']));
        $this->assertEquals(2, $result['data'][0]['groupid']);
        $this->assertEquals("editusers", $result['data'][0]['groupname']);
        $this->assertEquals(
            "Members of this group can create and edit users",
            $result['data'][0]['description']
        );
        $this->assertEquals("N", $result['data'][0]['useforproduct']);
        $this->assertEquals("N", $result['data'][0]['editable']);
        $this->assertEquals("N", $result['data'][0]['autogroup']);
    }

    /**
     * This function tests the GET Method with non existant group ID as argument
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsGetInvalidGroupIDArg()
    {
        $method = "GET";
        $args = array(200);
        $requestdata = array();
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals('Not Found', $result['data']['ErrorMsg']);
    }

    /**
     * This function tests the GET Method with non existant group Name as argument
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsGetInvalidGroupNameArg()
    {
        $method = "GET";
        $args = array('edituser');
        $requestdata = array();
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals('Not Found', $result['data']['ErrorMsg']);
    }


    /**
     * This function tests the POST Command - Fail due to missing field
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsPostMissingFields()
    {
        $method = "POST";
        $args = array();
        $requestdata = array(
            "groupid" => 0
        );
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertStringContainsString(
            'The following mandatory fields are missing:',
            $result['data']['ErrorMsg']
        );
        $this->assertStringContainsString(
            'groupname',
            $result['data']['ErrorMsg']
        );
        $this->assertStringContainsString(
            'description',
            $result['data']['ErrorMsg']
        );
        $this->assertStringContainsString(
            'useforproduct',
            $result['data']['ErrorMsg']
        );
        $this->assertStringContainsString(
            'autogroup',
            $result['data']['ErrorMsg']
        );
    }

    /**
     * This function tests the POST Command - Fail due to invalid field
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsPostInvalidFields()
    {
        $method = "POST";
        $args = array();
        $requestdata = array(
            "groupname" => "hffhfhfh8",
            "description" => "hhhh",
            "useforproduct" => "H",
            "autogroup" => "J"
        );
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertStringContainsString(
            'The following fields are invalid:',
            $result['data']['ErrorMsg']
        );
        $this->assertStringContainsString(
            'groupname',
            $result['data']['ErrorMsg']
        );
        $this->assertStringContainsString(
            'description',
            $result['data']['ErrorMsg']
        );
        $this->assertStringContainsString(
            'useforproduct',
            $result['data']['ErrorMsg']
        );
        $this->assertStringContainsString(
            'autogroup',
            $result['data']['ErrorMsg']
        );
    }

    /**
     * This function tests the POST Command Duplicate Group - fails
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsPostDuplicateGroup()
    {
        $method = "POST";
        $args = array();
        $requestdata = array(
            "groupname" => "admin",
            "description" => "REST Test Group",
            "useforproduct" => "N",
            "autogroup" => "N"
        );
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(409, $result['code']);
        $this->assertEquals(
            "Group admin already exists.",
            $result['data']['ErrorMsg']
        );
    }

    /**
     * This function tests the POST Command - Passes
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsPostValidGroup()
    {
        $method = "POST";
        $args = array();
        $requestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "N",
            "autogroup" => "N"
        );
        $result = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(201, $result['code']);
        $this->groupid = $result['data'][0]["groupid"];
        $this->assertEquals(
            $requestdata['groupname'],
            $result['data'][0]['groupname']
        );
        $this->assertEquals(
            $requestdata['description'],
            $result['data'][0]['description']
        );
        $this->assertEquals(
            $requestdata['useforproduct'],
            $result['data'][0]['useforproduct']
        );
        $this->assertEquals(
            $requestdata['autogroup'],
            $result['data'][0]['autogroup']
        );
        $this->assertEquals(
            'Y',
            $result['data'][0]['editable']
        );
    }


    /**
     * This function tests the PUT Command using groupid - Passes
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsPutValidDataGroupID()
    {
        $postmethod = "POST";
        $postargs = array();

        // Create the inital Group
        $postrequestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "N",
            "autogroup" => "N"
        );
        $postresult = $this->groups->endpoint(
            $postmethod,
            $postargs,
            $postrequestdata
        );
        $this->assertEquals(201, $postresult['code']);
        $this->groupid = $postresult['data'][0]["groupid"];


        // Edit the existing group
        $putmethod = "PUT";
        $putargs = array($this->groupid);

        $putrequestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "Y",
            "autogroup" => "N"
        );
        $putresult = $this->groups->endpoint($putmethod, $putargs, $putrequestdata);
        $this->assertEquals(201, $putresult['code']);
        $this->assertEquals(
            $this->groupid,
            $putresult['data'][0]['groupid']
        );
        $this->assertEquals(
            $putrequestdata['groupname'],
            $putresult['data'][0]['groupname']
        );
        $this->assertEquals(
            $putrequestdata['description'],
            $putresult['data'][0]['description']
        );
        $this->assertEquals(
            $putrequestdata['useforproduct'],
            $putresult['data'][0]['useforproduct']
        );
        $this->assertEquals(
            $putrequestdata['autogroup'],
            $putresult['data'][0]['autogroup']
        );
        $this->assertEquals(
            'Y',
            $putresult['data'][0]['editable']
        );
    }

    /**
     * This function tests the PUT Command using groupname - Passes
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsPutValidDataGroupName()
    {
        $postmethod = "POST";
        $postargs = array();

        // Create the inital Group
        $postrequestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "N",
            "autogroup" => "N"
        );
        $postresult = $this->groups->endpoint(
            $postmethod,
            $postargs,
            $postrequestdata
        );
        $this->assertEquals(201, $postresult['code']);
        $this->groupid = $postresult['data'][0]["groupid"];


        // Edit the existing group
        $putmethod = "PUT";
        $putargs = array($postrequestdata['groupname']);

        $putrequestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "Y",
            "autogroup" => "N"
        );
        $putresult = $this->groups->endpoint($putmethod, $putargs, $putrequestdata);
        $this->assertEquals(201, $putresult['code']);
        $this->assertEquals(
            $this->groupid,
            $putresult['data'][0]['groupid']
        );
        $this->assertEquals(
            $putrequestdata['groupname'],
            $putresult['data'][0]['groupname']
        );
        $this->assertEquals(
            $putrequestdata['description'],
            $putresult['data'][0]['description']
        );
        $this->assertEquals(
            $putrequestdata['useforproduct'],
            $putresult['data'][0]['useforproduct']
        );
        $this->assertEquals(
            $putrequestdata['autogroup'],
            $putresult['data'][0]['autogroup']
        );
        $this->assertEquals(
            'Y',
            $putresult['data'][0]['editable']
        );
    }

    /**
     * This function tests the PUT Command using invalid data - fails
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsPutInValidDataGroupName()
    {
        $postmethod = "POST";
        $postargs = array();

        // Create the inital Group
        $postrequestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "N",
            "autogroup" => "N"
        );
        $postresult = $this->groups->endpoint(
            $postmethod,
            $postargs,
            $postrequestdata
        );
        $this->assertEquals(201, $postresult['code']);
        $this->groupid = $postresult['data'][0]["groupid"];


        // Edit the existing group
        $putmethod = "PUT";
        $putargs = array($postrequestdata['groupname']);

        $putrequestdata = array(
            //"groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "Y",
            "autogroup" => "N"
        );
        $putresult = $this->groups->endpoint($putmethod, $putargs, $putrequestdata);
        $this->assertEquals(400, $putresult['code']);
        $this->assertEquals(
            'The following mandatory fields are missing: groupname ',
            $putresult['data']['ErrorMsg']
        );
    }

    /**
     * This function tests the PUT Command missing argument (Group ID or Name)-fails
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsPutMissingArgument()
    {
        $postmethod = "POST";
        $postargs = array();

        // Create the inital Group
        $postrequestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "N",
            "autogroup" => "N"
        );
        $postresult = $this->groups->endpoint(
            $postmethod,
            $postargs,
            $postrequestdata
        );
        $this->assertEquals(201, $postresult['code']);
        $this->groupid = $postresult['data'][0]["groupid"];


        // Edit the existing group
        $putmethod = "PUT";
        $putargs = array();

        $putrequestdata = array(
            //"groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "Y",
            "autogroup" => "N"
        );
        $putresult = $this->groups->endpoint($putmethod, $putargs, $putrequestdata);
        $this->assertEquals(400, $putresult['code']);
        $this->assertEquals(
            'groups: Invalid number put of arguments',
            $putresult['data']['ErrorMsg']
        );
    }


    /**
     * This function tests the PUT Command using groupid - Passes
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsPutGroupDataNoChange()
    {
        $postmethod = "POST";
        $postargs = array();

        // Create the inital Group
        $postrequestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "N",
            "autogroup" => "N"
        );
        $postresult = $this->groups->endpoint(
            $postmethod,
            $postargs,
            $postrequestdata
        );
        $this->assertEquals(201, $postresult['code']);
        $this->groupid = $postresult['data'][0]["groupid"];


        // Edit the existing group
        $putmethod = "PUT";
        $putargs = array($this->groupid);

        $putrequestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "N",
            "autogroup" => "N"
        );
        $putresult = $this->groups->endpoint($putmethod, $putargs, $putrequestdata);
        $this->assertEquals(400, $putresult['code']);
        $this->assertEquals(
            'You have not made any changes',
            $putresult['data']['ErrorMsg']
        );
    }


    /**
     * This function tests the PUT Command using Invalidgroupid - Passes
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGroupsPutGroupInvalidID()
    {
        // Edit the existing group
        $putmethod = "PUT";
        $putargs = array(100000000);

        $putrequestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "N",
            "autogroup" => "N"
        );
        $putresult = $this->groups->endpoint($putmethod, $putargs, $putrequestdata);
        $this->assertEquals(400, $putresult['code']);
        $this->assertEquals(
            'The following fields are invalid: groupid ',
            $putresult['data']['ErrorMsg']
        );
    }



    /**
     * This function tests the DELETE method with No arguments - Fails
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testDeleteGroupNoArgs()
    {
        $method = "DELETE";
        $args = array();
        $requestdata = array();
        $deleteresult = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $deleteresult['code']);
        $this->assertEquals(
            'groups (delete): Invalid number of arguments',
            $deleteresult['data']['ErrorMsg']
        );
    }

    /**
     * This function tests the DELETE method to delete a systems group - Fails
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testDeleteSystemGroup()
    {
        $method = "DELETE";
        $args = array(1);
        $requestdata = array();
        $deleteresult = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $deleteresult['code']);
        $this->assertEquals(
            'System groups cannot be deleted',
            $deleteresult['data']['ErrorMsg']
        );
    }


    /**
     * This function tests the DELETE method to delete a nonexistent group ID - Fails
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testDeleteGroupBadID()
    {
        $method = "DELETE";
        $args = array(100);
        $requestdata = array();
        $deleteresult = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(204, $deleteresult['code']);
    }

    /**
     * This function tests the DELETE method to delete a nonexistent group Name-fails
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testDeleteGroupBadName()
    {
        $method = "DELETE";
        $args = array('badgroup');
        $requestdata = array();
        $deleteresult = $this->groups->endpoint($method, $args, $requestdata);
        $this->assertEquals(204, $deleteresult['code']);
    }

    /**
     * This function tests the DELETE method to delete a newly created group - pass
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testDeleteGroupUsingID()
    {

        $postmethod = "POST";
        $postargs = array();
        $postrequestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "N",
            "autogroup" => "N"
        );
        $postresult = $this->groups->endpoint(
            $postmethod,
            $postargs,
            $postrequestdata
        );
        $this->assertEquals(201, $postresult['code']);
        $gid = $postresult['data'][0]['groupid'];

        $deletemethod = "DELETE";
        $deleteargs = array($gid);
        $deleterequestdata = array();
        $deleteresult = $this->groups->endpoint(
            $deletemethod,
            $deleteargs,
            $deleterequestdata
        );
        $this->assertEquals(204, $deleteresult['code']);
    }

    /**
     * This function tests the DELETE method to delete a newly created group - pass
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testDeleteGroupUsingName()
    {

        $postmethod = "POST";
        $postargs = array();
        $postrequestdata = array(
            "groupname" => "restgroup",
            "description" => "REST Test Group",
            "useforproduct" => "N",
            "autogroup" => "N"
        );
        $postresult = $this->groups->endpoint(
            $postmethod,
            $postargs,
            $postrequestdata
        );
        $this->assertEquals(201, $postresult['code']);
        $gid = $postresult['data'][0]['groupid'];

        $deletemethod = "DELETE";
        $deleteargs = array($postrequestdata['groupname']);
        $deleterequestdata = array();
        $deleteresult = $this->groups->endpoint(
            $deletemethod,
            $deleteargs,
            $deleterequestdata
        );
        $this->assertEquals(204, $deleteresult['code']);
    }
}
