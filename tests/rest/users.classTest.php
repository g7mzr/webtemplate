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

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// Include the Test Database Connection details
require_once dirname(__FILE__) . '/../_data/database.php';

use PHPUnit\Framework\TestCase;

/**
 * REST USERS Resource Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class RestUsersTest extends TestCase
{
    /**
     * property: webtemplate
     * Webtemplate application class
     *
     * @var \webtemplate\application\Application
     */
    protected $webtemplate;

    /**
     * property: version
     * Webtemplate application class
     *
     * @var \webtemplate\rest\endpoint\Version
     */
    protected $users;
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
        $this->webtemplate = new \webtemplate\application\Application();
        $this->users = new \webtemplate\rest\endpoints\Users($this->webtemplate);
    }

    /**
     * Tears down the fixture, for login, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
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
    public function testUsersPermissionsNotloggedIn()
    {
        $result = $this->users->permissions();
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
     * Logged In no permission to access Users Test
     *
     * This function test to see if the correct response is given if the user is
     * logged in without permission to access the application groups
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testUsersPermissionsLoggedinNoAccess()
    {
        // Register the user to look as if they are logged in
        $registered = $this->webtemplate->user()->register(
            'secnone',
            $this->webtemplate->config()->read('pref')
        );
        if (\webtemplate\general\General::isError($registered)) {
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

        $result = $this->users->permissions();
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
     * Logged In with permission to access Users Test
     *
     * This function test to see if the correct response is given if the user is not
     * logged in
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testUsersPermissionsLoggedinwithAccess()
    {

        // Register the user to look as if they are logged in
        $registered = $this->webtemplate->user()->register(
            'secuser',
            $this->webtemplate->config()->read('pref')
        );
        if (\webtemplate\general\General::isError($registered)) {
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

        $result = $this->users->permissions();
        if ($result === true) {
            $this->assertTrue($result);
        } else {
            $this->fail('User not logged in');
        }
    }
   /**
     * This function test the options available for the Users endpoint
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testUsersOptions()
    {
        $method = "OPTIONS";
        $args = array();
        $requestdata = array();
        $result = $this->users->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertStringContainsString('GET', $result['options']);
        $this->assertStringContainsString('HEAD', $result['options']);
        $this->assertStringContainsString('POST', $result['options']);
        $this->assertStringContainsString('PUT', $result['options']);
        $this->assertStringNotContainsString('PATCH', $result['options']);
        $this->assertStringNotContainsString('DELETE', $result['options']);
        $this->assertStringContainsString('OPTIONS', $result['options']);
    }

    /**
     * This function test an error message is returned if too many arguments
     * are passed to the GET Method
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGetInvalidArgs()
    {
        $method = "GET";
        $args = array("one","two","three");
        $requestdata = array();
        $result = $this->users->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals(
            'users: Invalid number of arguments',
            $result['data']['ErrorMsg']
        );
    }


    /**
     * This function test all 10 users are returned to GET with no Args
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGetAllUsers()
    {
        $method = "GET";
        $args = array();
        $requestdata = array();
        $result = $this->users->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals(10, count($result['data']));
        $this->assertEquals('admin', $result['data'][0]['username']);
    }

    /**
     * This function test user No. 1 (admin) is returned
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGetUserOne()
    {
        $method = "GET";
        $args = array('1');
        $requestdata = array();
        $result = $this->users->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals(1, count($result['data']));
        $this->assertEquals('admin', $result['data'][0]['username']);
    }

    /**
     * This function test "admin" is returned
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGetUserAdmin()
    {
        $method = "GET";
        $args = array('admin');
        $requestdata = array();
        $result = $this->users->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals(1, count($result['data']));
        $this->assertEquals('admin', $result['data'][0]['username']);
    }

    /**
     * This function user No. 200 is not found
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGetInvalidUserNumber()
    {
        $method = "GET";
        $args = array('200');
        $requestdata = array();
        $result = $this->users->endpoint($method, $args, $requestdata);
        $this->assertEquals(404, $result['code']);
        $this->assertEquals('Not Found', $result['data']['Errormsg']);
    }

    /**
     * This function user dummy is not found
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGetInvalidFormatUserName()
    {
        $method = "GET";
        $args = array('dummy');
        $requestdata = array();
        $result = $this->users->endpoint($method, $args, $requestdata);
        $this->assertEquals(404, $result['code']);
        $this->assertEquals('Not Found', $result['data']['Errormsg']);
    }

    /**
     * This function user dummy is not found
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGetInvalidUserName()
    {
        $method = "GET";
        $args = array('dummydummydummy');
        $requestdata = array();
        $result = $this->users->endpoint($method, $args, $requestdata);
        $this->assertEquals(404, $result['code']);
        $this->assertEquals('Invalid User Name', $result['data']['Errormsg']);
    }

    /**
     * This function user dummy is not found
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testGetInvalidsecondarg()
    {
        $method = "GET";
        $args = array('admin', "test");
        $requestdata = array();
        $result = $this->users->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals('Invalid argument test', $result['data']['Errormsg']);
    }
}
