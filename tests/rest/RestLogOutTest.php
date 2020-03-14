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
 * REST LOGOUT Resource Unit Tests
 *
 **/
class RestLogOutTest extends TestCase
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
    protected $logout;
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
        $this->logout = new\g7mzr\webtemplate\rest\endpoints\Logout($this->webtemplate);
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
        $result = $this->logout->permissions();
        $this->assertTrue($result);
    }

    /**
     * This function test the options available for the logout endpoint
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
        $result = $this->logout->endpoint($method, $args, $requestdata);
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
     * This function test that the user will be logged out
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testLogOut()
    {
        $method = "POST";
        $args = array();
        $requestdata = array();
        $result = $this->logout->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals("Logged out", $result['data']['Msg']);
    }

    /**
     * This function test that the user will be logged out
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testLogOutInvalidArgs()
    {
        $method = "POST";
        $args = array("test" => 'invalid');
        $requestdata = array();
        $result = $this->logout->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals(
            "logout: Invalid number of arguments",
            $result['data']['ErrorMsg']
        );
    }
}
