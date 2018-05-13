<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\phpunit;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// Include the Test Database Connection details
require_once dirname(__FILE__) .'/../_data/database.php';

use PHPUnit\Framework\TestCase;

/**
 * REST LOGOUT Resource Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class RestLogOutTest extends TestCase
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
    protected $logout;
    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        global $sessiontest;

        $sessiontest = array(true);
        $this->webtemplate = new \webtemplate\application\Application();
        $this->logout = new \webtemplate\rest\endpoints\Logout($this->webtemplate);
    }

    /**
     * Tears down the fixture, for login, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
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
     * @return null
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
     * @return null
     */
    public function testLoginOptions()
    {
        $method = "OPTIONS";
        $args = array();
        $requestdata = array();
        $result = $this->logout->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertNotContains('GET', $result['options']);
        $this->assertNotContains('HEAD', $result['options']);
        $this->assertContains('POST', $result['options']);
        $this->assertNotContains('PUT', $result['options']);
        $this->assertNotContains('PATCH', $result['options']);
        $this->assertNotContains('DELETE', $result['options']);
        $this->assertContains('OPTIONS', $result['options']);
    }

    /**
     * This function test that the user will be logged out
     *
     * @group unittest
     * @group rest
     *
     * @return null
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
     * @return null
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
