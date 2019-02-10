<?php
/**
 * This file is part of WEBTEMPLATE
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package restclient-php
 * @subpackage tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/restclient-php/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\phpunit;

// Include the Class Autoloader
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/constants.php';

use PHPUnit\Framework\TestCase;
use g7mzr\restclient\http\APIClient;
use g7mzr\restclient\options\Options;
use g7mzr\restclient\http\request\Request;
use g7mzr\restclient\http\response\DecodeResponse;

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
    protected function setUp()
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
    protected function tearDown()
    {

    }

    /**
     * testVersionNotloggedIn
     *
     * This function tests that the basic version information can be obtained from
     * the API when the user is not logged in.
     *
     * @group unittest
     * @group error
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
        $versionhttpresponse = $versiongetresponse->getHTTPResponce();
        $this->assertEquals("200", $versionhttpresponse[1]);
        $this->assertEquals("OK", $versionhttpresponse[2]);

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
     * @group error
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
        $loginhttpresponse = $loginpostresponse->getHTTPResponce();
        $this->assertEquals("200", $loginhttpresponse[1]);
        $this->assertEquals("OK", $loginhttpresponse[2]);

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
        $versionhttpresponse = $versiongetresponse->getHTTPResponce();
        $this->assertEquals("200", $versionhttpresponse[1]);
        $this->assertEquals("OK", $versionhttpresponse[2]);

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
        $logouthttpresponse = $logoutpostresponse->getHTTPResponce();
        $this->assertEquals("200", $logouthttpresponse[1]);
        $this->assertEquals("OK", $logouthttpresponse[2]);

         // Check the JSON Responce
        $logoutprocesseddata = $logoutpostresponse->getProcessedData();
        $this->assertEquals("Logged out", $logoutprocesseddata['Msg']);

        //Check when logged out you only get the short version information
        $versionrunok = $this->apiclient->httpGet($versiongetrequest);
        $this->assertTrue($versionrunok);


        // Get the response data
         $versiongetresponse = $this->apiclient->getResponse();

        // Check the http response
        $versionhttpresponse = $versiongetresponse->getHTTPResponce();
        $this->assertEquals("200", $versionhttpresponse[1]);
        $this->assertEquals("OK", $versionhttpresponse[2]);

        $versionprocesseddata = $versiongetresponse->getProcessedData();

        // Check the number of fields in the response array
        $this->assertEquals(3, count($versionprocesseddata));
    }

}
