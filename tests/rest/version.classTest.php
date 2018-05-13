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
 * REST VERSION Resource Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class RestVersionTest extends TestCase
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
    protected $version;
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
        $this->version = new \webtemplate\rest\endpoints\Version($this->webtemplate);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
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
        $result = $this->version->permissions();
        $this->assertTrue($result);
    }

    /**
     * This function tests to see what version information is received when not
     * logged in
     *
     * @group unittest
     * @group rest
     *
     * @return null
     */
    public function testVersionGETNotLoggedIn()
    {
        $method = "GET";
        $args = array();
        $requestdata = array();
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertArrayHasKey('name', $result['data']);
        $this->assertArrayHasKey('Version', $result['data']);
        $this->assertArrayHasKey('API', $result['data']);
        $this->assertArrayNotHasKey('phpversion', $result['data']);
        $this->assertArrayNotHasKey('servername', $result['data']);
        $this->assertArrayNotHasKey('serversoftware', $result['data']);
        $this->assertArrayNotHasKey('serveradmin', $result['data']);
        $this->assertArrayNotHasKey('databaseversion', $result['data']);
    }


    /**
     * This function tests to see what version information is received when not
     * logged in
     *
     * @group unittest
     * @group rest
     *
     * @return null
     */
    public function testVersionGetLoggedIn()
    {
        // Register the user to look as if they are logged in
        $registered = $this->webtemplate->user()->register(
            'phpunit',
            $this->webtemplate->config()->read('pref')
        );
        if (\webtemplate\general\General::isError($registered)) {
            $this->fail('Failed to register user for ' . __METHOD__);
        }

        // Get the users groups
        $this->webtemplate->usergroups()->loadusersgroups(
            $this->webtemplate->user()->getUserId()
        );

        $method = "GET";
        $args = array();
        $requestdata = array();
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertArrayHasKey('name', $result['data']);
        $this->assertArrayHasKey('Version', $result['data']);
        $this->assertArrayHasKey('API', $result['data']);
        $this->assertArrayHasKey('phpversion', $result['data']);
        $this->assertArrayHasKey('servername', $result['data']);
        $this->assertArrayHasKey('serversoftware', $result['data']);
        $this->assertArrayHasKey('serveradmin', $result['data']);
        $this->assertArrayHasKey('databaseversion', $result['data']);
    }

    /**
     * This function tests to see that Version GET reports an error if there is any
     * data
     * in args
     *
     * @group unittest
     * @group rest
     *
     * @return null
     */
    public function testVersionGetArgsExist()
    {
        $method = "GET";
        $args = array('1');
        $requestdata = array();
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals(
            'version: Invalid number of arguments',
            $result['data']['ErrorMsg']
        );
        $this->assertEquals(
            '1',
            $result['data']['args'][0]
        );
        $this->assertEmpty($result['data']['requestdata']);
    }

    /**
     * This function tests to see that Version GET reports an error if there is
     * any data in requestdata
     *
     * @group unittest
     * @group rest
     *
     * @return null
     */
    public function testVersionGetRequestdataExist()
    {
        $method = "GET";
        $args = array();
        $requestdata = array('1');
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals(
            'version: Invalid number of arguments',
            $result['data']['ErrorMsg']
        );
        $this->assertEquals(
            '1',
            $result['data']['requestdata'][0]
        );
        $this->assertEmpty($result['data']['args']);
    }


    /**
     * This function tests the OPTIONS Method
     *
     * @group unittest
     * @group rest
     *
     * @return null
     */
    public function testVersionOptions()
    {
        $method = "OPTIONS";
        $args = array();
        $requestdata = array();
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertContains('GET', $result['options']);
        $this->assertContains('HEAD', $result['options']);
        $this->assertNotContains('POST', $result['options']);
        $this->assertNotContains('PUT', $result['options']);
        $this->assertNotContains('PATCH', $result['options']);
        $this->assertNotContains('DELETE', $result['options']);
        $this->assertContains('OPTIONS', $result['options']);
    }


    /**
     * This function tests the HEAD Method
     *
     * @group unittest
     * @group rest
     *
     * @return null
     */
    public function testVersionHead()
    {
        $method = "HEAD";
        $args = array();
        $requestdata = array();
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertEquals(65, $result['head']);
    }

    /**
     * This function tests the POST Method fails
     *
     * @group unittest
     * @group rest
     *
     * @return null
     */
    public function testVersionPost()
    {
        $method = "POST";
        $args = array();
        $requestdata = array();
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertEquals(405, $result['code']);
        $this->assertEquals('Method not implemented', $result['data']['ErrorMsg']);
        $this->assertContains('GET', $result['options']);
        $this->assertContains('HEAD', $result['options']);
        $this->assertContains('OPTIONS', $result['options']);
    }

    /**
     * This function tests to ensure the endpoint is removed from the request data
     * if it exists
     *
     * @group unittest
     * @group rest
     *
     * @return null
     */
    public function testVersionRemoverequest()
    {
        $method = "GET";
        $args = array();
        $requestdata = array('request' => 'version');
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
    }


    /**
     * This function tests to see that Version reports an error if there is any data
     * in args
     *
     * @group unittest
     * @group rest
     *
     * @return null
     */
    public function testVersionHEADGetArgsExist()
    {
        $method = "HEAD";
        $args = array('1');
        $requestdata = array();
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals(
            'version: Invalid number of arguments',
            $result['data']['ErrorMsg']
        );
        $this->assertEquals(
            '1',
            $result['data']['args'][0]
        );
        $this->assertEmpty($result['data']['requestdata']);
    }

    /**
     * This function tests to see that Version reports an error if there is any data
     * in requestdata
     *
     * @group unittest
     * @group rest
     *
     * @return null
     */
    public function testVersionHEADRequestdataExist()
    {
        $method = "HEAD";
        $args = array();
        $requestdata = array('1');
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertEquals(400, $result['code']);
        $this->assertEquals(
            'version: Invalid number of arguments',
            $result['data']['ErrorMsg']
        );
        $this->assertEquals(
            '1',
            $result['data']['requestdata'][0]
        );
        $this->assertEmpty($result['data']['args']);
    }
}
