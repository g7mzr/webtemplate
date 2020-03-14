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
 * REST LOGIN Resource Unit Tests
 *
 **/
class RestLoginTest extends TestCase
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
    protected $login;
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
        $this->login = new\g7mzr\webtemplate\rest\endpoints\Login($this->webtemplate);
    }

    /**
     * Tears down the fixture, for login, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->webtemplate->session()->destroy();
    }

     /**
     * This function test to see if the user is authorised to use access the resource
     * pointed to by this endpoint
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testpermissions()
    {
        $result = $this->login->permissions();
        $this->assertTrue($result);
    }

    /**
     * This function test the options available for the login endpoint
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testLoginOptions()
    {
        $method = "OPTIONS";
        $args = array();
        $requestdata = array();
        $result = $this->login->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertStringNotContainsString('GET', $result['options']);
        $this->assertStringNotContainsString('HEAD', $result['options']);
        $this->assertStringContainsString('POST', $result['options']);
        $this->assertStringNotContainsString('PUT', $result['options']);
        $this->assertStringNotContainsString('PATCH', $result['options']);
        $this->assertStringNotContainsString('DELETE', $result['options']);
        $this->assertStringContainsString('OPTIONS', $result['options']);
    }

    /**
     * This function tests the HEAD Method.
     *
     * The HEAD method is present as part of the endPointCommon Trait.  However it
     * should return a Method Not Present Error if the GET method is not available.
     *
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testLoginHead()
    {
        $method = "HEAD";
        $args = array();
        $requestdata = array();
        $result = $this->login->endpoint($method, $args, $requestdata);
        $this->assertEquals(405, $result['code']);
        $this->assertEquals(
            'Method not implemented',
            $result['data']['ErrorMsg']
        );
    }

    /**
     * This function tests the POST Method with Arguments
     *
     * This function tests the Post method with arguments contained in the URL.  This
     * is not allowed and the endpoint should return an error.
     *
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testLoginFailIfArgs()
    {
        $method = "POST";
        $args = array(1);
        $requestdata = array();
        $result = $this->login->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals(
            'login: Invalid number of arguments',
            $result['data']['ErrorMsg']
        );
    }

    /**
     * This function tests the POST Method without the username and password
     *
     * This function tests the Post method without the username and password  This
     * is not allowed and the endpoint should return an error.
     *
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testLoginMissingUserNameAndPassword()
    {
        $method = "POST";
        $args = array();
        $requestdata = array();
        $result = $this->login->endpoint($method, $args, $requestdata);
        $this->assertEquals(403, $result['code']);
        $this->assertEquals(
            'Invalid username and password',
            $result['data']['ErrorMsg']
        );
    }


    /**
     * This function tests the POST Method with valid user and wrong password
     *
     * This function tests the Post method with a valid user but with the wrong
     * password.  This should return an error.
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testLoginBadPassword()
    {
        $method = "POST";
        $args = array();
        $requestdata = array(
            "username" => "admin",
            "password" => "testUser2"
        );
        $result = $this->login->endpoint($method, $args, $requestdata);
        $this->assertEquals(403, $result['code']);
        $this->assertEquals(
            'Invalid username and password',
            $result['data']['ErrorMsg']
        );
    }

    /**
     * This function tests the POST Method with valid user and password
     *
     * This function tests the Post method with a valid user and password.  The
     * login should be successful and return a HTTP Code of 200 and the users
     * profile.
     *
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testLoginSucess()
    {
        $method = "POST";
        $args = array();
        $requestdata = array(
            "username" => "phpunit",
            "password" => "phpUnit1"
        );
        $result = $this->login->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals(
            "Logged in",
            $result['data']['Msg']
        );
        $this->assertEquals(
            "phpunit",
            $result['data']['user']
        );
        $this->assertEquals(
            "Phpunit User",
            $result['data']['realname']
        );
        $this->assertEquals(
            "phpunit@example.com",
            $result['data']['email']
        );
        $this->assertEquals(
            2,
            $result['data']['displayrows']
        );
    }
}
