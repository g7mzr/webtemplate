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
 * Login/Logout Endpoint Tests
 *
 * This class contains the WEBTEMPLATE RrestFull API Login/Logout Endpoints Tests
 */
class LoginTest extends TestCase
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
     * This function tests that a user can login and logout using the API
     *
     * @group unittest
     * @group RestFul
     *
     * @return void
     *
     * @access protected
     */
    public function testLogin()
    {

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
    }

    /**
     * This function tests that an error will be returned when login is attempted
     * using an invalid username and password combination
     *
     * @group unittest
     * @group RestFul
     *
     * @return void
     *
     * @access protected
     */
    public function testLoginFail()
    {

        $loginpostdata = array();
        $loginpostdata["username"] = USERNAME;
        $loginpostdata["password"] = "Dummy1";
        $loginrequest = new Request('post');
        $loginrequest->setEndPoint("api/v1/login");
        $loginrequest->setURLEncodedData($loginpostdata);

        $loginrunok = $this->apiclient->httpPost($loginrequest);
        $this->assertTrue($loginrunok);


        $loginpostresponse = $this->apiclient->getResponse();

        // Check the HTTP Respose
        $loginhttpresponse = $loginpostresponse->getHTTPResponce();
        $this->assertEquals("403", $loginhttpresponse[1]);
        $this->assertEquals("Forbidden", $loginhttpresponse[2]);

         // Check the JSON Responce
        $loginprocesseddata = $loginpostresponse->getProcessedData();
        $this->assertEquals(
            "Invalid username and password",
            $loginprocesseddata['ErrorMsg']
        );
    }
}
