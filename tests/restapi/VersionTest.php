<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage RestFul API Functional Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\unittest;

// Include the Class Autoloader
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/constants.php';

use PHPUnit\Framework\TestCase;
use \g7mzr\restclient\http\APIClient;
use \g7mzr\restclient\options\Options;
use \g7mzr\restclient\http\request\Request;
use \g7mzr\restclient\http\response\DecodeResponse;

/**
 * Version Endpoint Tests
 *
 * This class contains the WEBTEMPLATE RrestFull API Version Endpoint Tests
 */
class VersionTest extends TestCase
{
    /**
     * ApiClient
     * @var \g7mzr\restclient\http\APIClient
     * @access protected
     */
    protected $apiclient;

    /**
     * Options
     * @var \g7mzr\restclient\options\Options
     * @access protected
     */
    protected $options;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     *
     * @access protected
     */
    protected function setUp(): void
    {
        $this->options = new Options();
        $this->options->setBaseURL(URL);
        $this->options->setCookieFile(__DIR__ . "/cookies.txt");

        if (PROXY === true) {
            $this->options->setProxyServer(HTTPPROXY);
        }
        $this->apiclient = new APIClient($this->options);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     *
     * @access protected
     */
    protected function tearDown(): void
    {
    }

    /**
     * testVersionNotloggedIn
     *
     * This function tests that the basic version information can be obtained from
     * the API when the user is not logged in.
     *
     * @group unittest
     * @group version
     *
     * @return void
     *
     * @access protected
     */
    public function testVersionNotloggedIn()
    {
        $versiongetrequest = new Request();
        $versiongetrequest->setEndPoint("api/v1/version");

        $versionrunok = $this->apiclient->httpGet($versiongetrequest);
        $this->assertTrue($versionrunok);

        // Get the respose data
         $versiongetresponse = $this->apiclient->getResponse();

        // Check the http response
        $versionhttpresponsecode = $versiongetresponse->getHTTPResponseCode();
        $this->assertEquals("200", $versionhttpresponsecode);

        // Check the returned data
        $versionprocesseddata = $versiongetresponse->getProcessedData();
        $this->assertEquals(WEBSITENAME, $versionprocesseddata['name']);
        $this->assertEquals(VERSION, $versionprocesseddata['Version']);
        $this->assertEquals(APIVERSION, $versionprocesseddata['API']);

        // Check the number of fields in the response array
        $this->assertEquals(3, count($versionprocesseddata));
    }


    /**
     * testVersionloggedIn
     *
     * This function tests that the basic version information can be obtained from
     * the API when the user is not logged in.
     *
     * @group unittest
     * @group version
     *
     * @return void
     *
     * @access protected
     */
    public function testVersionloggedIn()
    {
        // LOGIN to the API
        $loginpostdata = array();
        $loginpostdata["username"] = USERNAME;
        $loginpostdata["password"] = PASSWORD;
        $loginrequest = new Request('post');
        $loginrequest->setEndPoint("api/v1/login");
        $loginrequest->setURLEncodedData($loginpostdata);

        $loginrunok = $this->apiclient->httpPost($loginrequest);
        $this->assertTrue($loginrunok);
        //Get the Response
        $loginpostresponse = $this->apiclient->getResponse();

        // Check the HTTP Respose
        $loginhttpresponsecode = $loginpostresponse->getHTTPResponseCode();
        $this->assertEquals("200", $loginhttpresponsecode);

         // Check the JSON Responce
        $loginprocesseddata = $loginpostresponse->getProcessedData();
        $this->assertEquals("Logged in", $loginprocesseddata['Msg']);

        // Get the full Version Information from the API
        $versiongetrequest = new Request();
        $versiongetrequest->setEndPoint("api/v1/version");

        $versionrunok = $this->apiclient->httpGet($versiongetrequest);
        $this->assertTrue($versionrunok);

        // Get the respose data
         $versiongetresponse = $this->apiclient->getResponse();

        // Check the http response
        $versionhttpresponsecode = $versiongetresponse->getHTTPResponseCode();
        $this->assertEquals("200", $versionhttpresponsecode);

        // Check the returned data
        $versionprocesseddata = $versiongetresponse->getProcessedData();
        $this->assertEquals(WEBSITENAME, $versionprocesseddata['name']);
        $this->assertEquals(VERSION, $versionprocesseddata['Version']);
        $this->assertEquals(APIVERSION, $versionprocesseddata['API']);

        // Check the number of fields in the response array
        $this->assertEquals(8, count($versionprocesseddata));

        //LOGOUT from the API
        $logoutrequest = new Request('post');
        $logoutrequest->setEndPoint("api/v1/logout");

        $logoutrunok = $this->apiclient->httpPost($logoutrequest);
        $this->assertTrue($logoutrunok);

        //Get the Response
        $logoutpostresponse = $this->apiclient->getResponse();

        // Check the HTTP Respose
        $logouthttpresponsecode = $logoutpostresponse->getHTTPResponseCode();
        $this->assertEquals("200", $logouthttpresponsecode);

         // Check the JSON Responce
        $logoutprocesseddata = $logoutpostresponse->getProcessedData();
        $this->assertEquals("Logged out", $logoutprocesseddata['Msg']);

        //Check when logged out you only get the short version information
        $versionrunok = $this->apiclient->httpGet($versiongetrequest);
        $this->assertTrue($versionrunok);


        // Get the response data
         $versiongetresponse = $this->apiclient->getResponse();

        // Check the http response
        $versionhttpresponsecode = $versiongetresponse->getHTTPResponseCode();
        $this->assertEquals("200", $versionhttpresponsecode);

        $versionprocesseddata = $versiongetresponse->getProcessedData();

        // Check the number of fields in the response array
        $this->assertEquals(3, count($versionprocesseddata));
    }

    /**
     * testGetOptions
     *
     * This function tests that the options (available commands) can be returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     *
     * @access protected
     */
    public function testGetOptions()
    {
        // LOGIN to the API
        $loginpostdata = array();
        $loginpostdata["username"] = USERNAME;
        $loginpostdata["password"] = PASSWORD;
        $loginrequest = new Request('post');
        $loginrequest->setEndPoint("api/v1/login");
        $loginrequest->setURLEncodedData($loginpostdata);

        $loginrunok = $this->apiclient->httpPost($loginrequest);
        $this->assertTrue($loginrunok);
        //Get the Response
        $loginpostresponse = $this->apiclient->getResponse();

        // Check the HTTP Respose
        $loginhttpresponsecode = $loginpostresponse->getHTTPResponseCode();
        $this->assertEquals("200", $loginhttpresponsecode);

         // Check the JSON Responce
        $loginprocesseddata = $loginpostresponse->getProcessedData();
        $this->assertEquals("Logged in", $loginprocesseddata['Msg']);


        $getoptionsrequest = new Request();
        $getoptionsrequest->setEndPoint("api/v1/version");

        $getoptionsrunok = $this->apiclient->httpOptions($getoptionsrequest);
        if ($getoptionsrunok === false) {
            $this->assertFail("HTTP Options Command failed");
        }

        // Get the respose data
        $getoptionsresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getoptionsprocesseddata = $getoptionsresponse->getProcessedData();


        // LOGOUT after the test.
        $logoutrequest = new Request('post');
        $logoutrequest->setEndPoint("api/v1/logout");

        $logoutrunok = $this->apiclient->httpPost($logoutrequest);
        $this->assertTrue($logoutrunok);

        //Get the Response
        $logoutpostresponse = $this->apiclient->getResponse();

        // Check the HTTP Respose
        $logouthttpresponsecode = $logoutpostresponse->getHTTPResponseCode();
        $this->assertEquals("200", $logouthttpresponsecode);

         // Check the JSON Responce
        $logoutprocesseddata = $logoutpostresponse->getProcessedData();
        $this->assertEquals("Logged out", $logoutprocesseddata['Msg']);
        // Check the Results

        // Check the http response
        $getoptionshttpresponsecode = $getoptionsresponse->getHTTPResponseCode();
        $this->assertEquals("200", $getoptionshttpresponsecode);

        // Get the options
        $options = $getoptionsresponse->getAllow();
        $this->assertContains("GET", $options);
        $this->assertContains("HEAD", $options);
        $this->assertContains("OPTIONS", $options);

        $this->assertNotContains("POST", $options);
        $this->assertNotContains("PUT", $options);
        $this->assertNotContains("DELETE", $options);
        $this->assertNotContains("PATCH", $options);

        // Check there is no processed data
        $this->assertEquals(0, count($getoptionsprocesseddata));
    }

    /**
     * testGetHead
     *
     * This function tests that the options (available commands) can be returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     *
     * @access protected
     */
    public function testGetHead()
    {
        // LOGIN to the API
        $loginpostdata = array();
        $loginpostdata["username"] = USERNAME;
        $loginpostdata["password"] = PASSWORD;
        $loginrequest = new Request('post');
        $loginrequest->setEndPoint("api/v1/login");
        $loginrequest->setURLEncodedData($loginpostdata);

        $loginrunok = $this->apiclient->httpPost($loginrequest);
        $this->assertTrue($loginrunok);
        //Get the Response
        $loginpostresponse = $this->apiclient->getResponse();

        // Check the HTTP Respose
        $loginhttpresponsecode = $loginpostresponse->getHTTPResponseCode();
        $this->assertEquals("200", $loginhttpresponsecode);

         // Check the JSON Responce
        $loginprocesseddata = $loginpostresponse->getProcessedData();
        $this->assertEquals("Logged in", $loginprocesseddata['Msg']);

        /*****************************************/

        $getheadrequest = new Request();
        $getheadrequest->setEndPoint("api/v1/version");

        $getheadrunok = $this->apiclient->httpHead($getheadrequest);
        if ($getheadrunok === false) {
            $this->assertFail("HTTP Options Command failed");
        }

        // Get the respose data
        $getheadresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getheadprocesseddata = $getheadresponse->getProcessedData();


        /*****************************************************************/

        // LOGOUT after the test.
        $logoutrequest = new Request('post');
        $logoutrequest->setEndPoint("api/v1/logout");

        $logoutrunok = $this->apiclient->httpPost($logoutrequest);
        $this->assertTrue($logoutrunok);

        //Get the Response
        $logoutpostresponse = $this->apiclient->getResponse();

        // Check the HTTP Respose
        $logouthttpresponsecode = $logoutpostresponse->getHTTPResponseCode();
        $this->assertEquals("200", $logouthttpresponsecode);

         // Check the JSON Responce
        $logoutprocesseddata = $logoutpostresponse->getProcessedData();
        $this->assertEquals("Logged out", $logoutprocesseddata['Msg']);
        // Check the Results

        // Check the http response
        $getheadhttpresponsecode = $getheadresponse->getHTTPResponseCode();
        $this->assertEquals("200", $getheadhttpresponsecode);

        // Check the content length
        $contentlength = $getheadresponse->getContentLength();
        $this->assertEquals(263, $contentlength);

        // Check there is no processed data
        $this->assertEquals(0, count($getheadprocesseddata));
    }
}
