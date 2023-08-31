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
 * REST VERSION Resource Unit Tests
 *
 **/
class RestVersionTest extends TestCase
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
    protected $version;
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
        $this->version = new\g7mzr\webtemplate\rest\endpoints\Version($this->webtemplate);
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
     * @return void
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
     * @return void
     */
    public function testVersionGetLoggedIn()
    {
        // Register the user to look as if they are logged in
        $userData = array();
        $registered = $this->webtemplate->register()->register(
            'phpunit',
            $this->webtemplate->config()->read('pref'),
            $userData
        );
        if (\g7mzr\webtemplate\general\General::isError($registered)) {
            $this->fail('Failed to register user for ' . __METHOD__);
        }

        // Get the users groups
        $this->webtemplate->usergroups()->loadusersgroups(
            $userData['userId']
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
     * @return void
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
     * @return void
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
     * @return void
     */
    public function testVersionOptions()
    {
        $method = "OPTIONS";
        $args = array();
        $requestdata = array();
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertEquals(200, $result['code']);
        $this->assertStringContainsString('GET', $result['options']);
        $this->assertStringContainsString('HEAD', $result['options']);
        $this->assertStringNotContainsString('POST', $result['options']);
        $this->assertStringNotContainsString('PUT', $result['options']);
        $this->assertStringNotContainsString('PATCH', $result['options']);
        $this->assertStringNotContainsString('DELETE', $result['options']);
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
     * @return void
     */
    public function testVersionPost()
    {
        $method = "POST";
        $args = array();
        $requestdata = array();
        $result = $this->version->endpoint($method, $args, $requestdata);
        $this->assertEquals(405, $result['code']);
        $this->assertEquals('Method not implemented', $result['data']['ErrorMsg']);
        $this->assertStringContainsString('GET', $result['options']);
        $this->assertStringContainsString('HEAD', $result['options']);
        $this->assertStringContainsString('OPTIONS', $result['options']);
    }

    /**
     * This function tests to ensure the endpoint is removed from the request data
     * if it exists
     *
     * @group unittest
     * @group rest
     *
     * @return void
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
     * @return void
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
     * @return void
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
