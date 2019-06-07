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
 * REST EXAMPLE Resource Unit Tests
 *
 **/
class RestExampleTest extends TestCase
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
    protected $example;
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
        $this->example = new \webtemplate\rest\endpoints\Example($this->webtemplate);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * This function is a common function used to test the following methods:
     * POST, PUT, PATCH, DELETE
     *
     * @param string $method The HTTP Method being tested.
     *
     * @return boolean The result of the test.
     */
    protected function generalTest(string $method)
    {
        $testresult = true;
        $args = array(1, 'Test');
        $requestdata = array("name" => "phpunit", "passwd" => "mypassword");
        $result = $this->example->endpoint($method, $args, $requestdata);
        if ($result['code'] != 200) {
            $testresult = false;
        }
        if ($result['data']['endpoint'] != "example") {
            $testresult = false;
        }
        if ($result['data']['method'] != $method) {
            $testresult = false;
        }
        if ($result['data']['args'][0] != 1) {
            $testresult = false;
        }
        if ($result['data']['args'][1] != "Test") {
            $testresult = false;
        }
        if ($result['data']['requestdata']['name'] != "phpunit") {
            $testresult = false;
        }
        if ($result['data']['requestdata']['passwd'] != "mypassword") {
            $testresult = false;
        }
        return $testresult;
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
        $result = $this->example->permissions();
        $this->assertTrue($result);
    }

    /**
     * This function test the options available for the example endpoint
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testExampleOptions()
    {
        $method = "OPTIONS";
        $args = array();
        $requestdata = array();
        $result = $this->example->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertStringContainsString('GET', $result['options']);
        $this->assertStringContainsString('HEAD', $result['options']);
        $this->assertStringContainsString('POST', $result['options']);
        $this->assertStringContainsString('PUT', $result['options']);
        $this->assertStringContainsString('PATCH', $result['options']);
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
    public function testExampleHead()
    {
        $method = "HEAD";
        $args = array();
        $requestdata = array();
        $result = $this->example->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals(64, $result['head']);
    }

    /**
     * This function tests that example returns the correct endpoint and method when
     * a GET command is sent with no arguments or request data
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testExampleGETNoArguments()
    {
        $method = "GET";
        $args = array();
        $requestdata = array();
        $result = $this->example->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals('example', $result['data']['endpoint']);
        $this->assertEquals('GET', $result['data']['method']);
        $this->assertEquals(0, count($result['data']['args']));
        $this->assertEquals(0, count($result['data']['requestdata']));
    }

    /**
     * This function tests that example returns an error when there is at least one
     * argument sent to it
     *
     * @group unittest
     * @group void
     *
     * @return void
     */
    public function testExampleGETOneArgument()
    {
        $method = "GET";
        $args = array(1);
        $requestdata = array();
        $result = $this->example->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals(
            'example: GET does not take any arguments',
            $result['data']['ErrorMsg']
        );
        $this->assertEquals(1, count($result['data']['args']));
        $this->assertEquals(1, $result['data']['args'][0]);
        $this->assertEquals(0, count($result['data']['requestdata']));
    }

    /**
     * This function tests that example returns an error when there is at least one
     * item sent to it in requestdata
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testExampleGETOneRequestData()
    {
        $method = "GET";
        $args = array();
        $requestdata = array("name" => "phpunit");
        $result = $this->example->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals(
            'example: GET does not take any arguments',
            $result['data']['ErrorMsg']
        );
        $this->assertEquals(0, count($result['data']['args']));
        $this->assertEquals(1, count($result['data']['requestdata']));
        $this->assertEquals('phpunit', $result['data']['requestdata']['name']);
    }

    /**
     * This function tests the POST method
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testExamplePOST()
    {
        $result = $this->generalTest('POST');
        $this->assertTrue($result);
    }

    /**
     * This function tests the PUT method
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testExamplePUT()
    {
        $result = $this->generalTest('PUT');
        $this->assertTrue($result);
    }

    /**
     * This function tests the PATCH method
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testExamplePATCH()
    {
        $result = $this->generalTest('PATCH');
        $this->assertTrue($result);
    }

     /**
     * This function tests the DELETE method
     *
     * @group unittest
     * @group rest
     *
     * @return void
     */
    public function testExampleDELETE()
    {
        $result = $this->generalTest('DELETE');
        $this->assertTrue($result);
    }
}
