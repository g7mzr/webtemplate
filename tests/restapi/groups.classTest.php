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

namespace webtemplate\unittest;

// Include the Class Autoloader
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/constants.php';
require_once __DIR__ . "/../_data/database.php";

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
class GroupsTest extends TestCase
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
     * Property: deleteTestGroup
     * @var boolean
     * @access private
     */
    private $deleteTestGroup;

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

        // Set deleteTestGroup to false
        $this->deleteTestGroup = false;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     *
     * @throws \Exception If unable to connect to the remote database.
     *
     * @access protected
     */
    protected function tearDown(): void
    {
        global $testdsn;
        if ($this->deleteTestGroup == true) {
            // DELETE THE Test USER
            $conStr = sprintf(
                "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
                $testdsn["hostspec"],
                '5432',
                $testdsn["database"],
                $testdsn["username"],
                $testdsn["password"]
            );

            // Create the PDO object and Connect to the database
            try {
                $localconn = new \PDO($conStr);
            } catch (\Exception $e) {
                //print_r($e->getMessage());
                throw new \Exception('Unable to connect to the database');
            }
            //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $localconn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
            $sql = "delete from groups where group_name = '" . TESTGROUPNAME . "'";

            $localconn->query($sql);
            $localconn = null;
        }
    }

    /**
     * Login
     *
     * This function is used to login to the API in order to carry out the required
     * tests.
     *
     * @return  boolean True if the login is successful
     *
     * @access private
     */
    private function login()
    {
        $loginpostdata = array();
        $loginpostdata["username"] = USERNAME;
        $loginpostdata["password"] = PASSWORD;
        $loginrequest = new Request('post');
        $loginrequest->setEndPoint("api/v1/login");
        $loginrequest->setURLEncodedData($loginpostdata);

        $loginrunok = $this->apiclient->httpPost($loginrequest);
        if ($loginrunok === false) {
            return false;
        }

        //Get the Response
        $loginpostresponse = $this->apiclient->getResponse();

        // Check the JSON Responce
        $loginprocesseddata = $loginpostresponse->getProcessedData();

        if (!array_key_exists("Msg", $loginprocesseddata)) {
            return false;
        }

        if ($loginprocesseddata['Msg'] != "Logged in") {
            return false;
        }

        // Login Sucessful
        return true;
    }

    /**
     * Logout
     *
     * This function is used to logout of the API
     *
     * @return boolean True if the logout is successful.
     *
     * @access private
     */
    private function logout()
    {
        $logoutrequest = new Request('post');
        $logoutrequest->setEndPoint("api/v1/logout");

        $logoutrunok = $this->apiclient->httpPost($logoutrequest);
        $this->assertTrue($logoutrunok);
        if ($logoutrunok === false) {
            return false;
        }

        //Get the Response
        $logoutpostresponse = $this->apiclient->getResponse();

        // Check the JSON Responce
        $logoutprocesseddata = $logoutpostresponse->getProcessedData();

        if (!array_key_exists("Msg", $logoutprocesseddata)) {
            return false;
        }

        if ($logoutprocesseddata['Msg'] != "Logged out") {
            return false;
        }

        // Login Sucessful
        return true;
    }

    /**
     * createGroupArray
     *
     * This function creates a array containing the details of the group to be
     * created.
     *
     * @return array Containing details of group to be created.
     *
     * @access private
     */
    private function createGroupArray()
    {
        $group = array();
        $group["groupname"] = TESTGROUPNAME;
        $group["description"] = TESTGROUPDESCRIPTION;
        $group["useforproduct"] = TESTGROUPUSEFORPRODUCT;
        $group["autogroup"] = TESTGROUPAUTOGROUP;
        return $group;
    }

    /**
     * testGetGroupsNotloggedIn
     *
     * This function tests that no groups data is returned if the client is not logged in.
     *
     * @group unittest
     * @group groups
     *
     * @return void
     *
     * @access protected
     */
    public function testGetGroupsNotloggedIn()
    {
        $getgroupsrequest = new Request();
        $getgroupsrequest->setEndPoint("api/v1/groups");

        $getgroupsrunok = $this->apiclient->httpGet($getgroupsrequest);
        $this->assertTrue($getgroupsrunok);

        // Get the respose data
        $getgroupsresponse = $this->apiclient->getResponse();

        // Check the http response
        $getgroupshttpresponsecode = $getgroupsresponse->getHTTPResponseCode();
        $this->assertEquals("401", $getgroupshttpresponsecode);

        // Check the returned data
        $getgroupssprocesseddata = $getgroupsresponse->getProcessedData();
        $this->assertEquals(
            "Login required to use this resource",
            $getgroupssprocesseddata['ErrorMsg']
        );
    }

    /**
     * testGetOptions
     *
     * This function tests that the options (available commands) can be returned
     *
     * @group unittest
     * @group groups
     *
     * @return void
     *
     * @access protected
     */
    public function testGetOptions()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $getoptionsrequest = new Request();
        $getoptionsrequest->setEndPoint("api/v1/groups");

        $getoptionsrunok = $this->apiclient->httpOptions($getoptionsrequest);
        if ($getoptionsrunok === false) {
            $this->assertFail("HTTP Options Command failed");
        }

        // Get the respose data
        $getoptionsresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getoptionsprocesseddata = $getoptionsresponse->getProcessedData();


        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results

        // Check the http response
        $getoptionshttpresponsecode = $getoptionsresponse->getHTTPResponseCode();
        $this->assertEquals("200", $getoptionshttpresponsecode);

        // Get the options
        $options = $getoptionsresponse->getAllow();
        $this->assertContains("GET", $options);
        $this->assertContains("POST", $options);
        $this->assertContains("PUT", $options);
        $this->assertContains("HEAD", $options);
        $this->assertContains("OPTIONS", $options);
        $this->assertContains("DELETE", $options);

        $this->assertNotContains("PATCH", $options);

        // Check there is no processed data
        $this->assertEquals(0, count($getoptionsprocesseddata));
    }

    /**
     * testGetHead
     *
     * This function tests that the Head command returns the length of the data only
     *
     * @group unittest
     * @group groups
     *
     * @return void
     *
     * @access protected
     */
    public function testGetHead()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $getheadrequest = new Request();
        $getheadrequest->setEndPoint("api/v1/groups");

        $getheadrunok = $this->apiclient->httpHead($getheadrequest);
        if ($getheadrunok === false) {
            $this->assertFail("HTTP Options Command failed");
        }

        // Get the respose data
        $getheadresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getheadprocesseddata = $getheadresponse->getProcessedData();


        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results

        // Check the http response
        $getheadhttpresponsecode = $getheadresponse->getHTTPResponseCode();
        $this->assertEquals("200", $getheadhttpresponsecode);

         // Check the content length
        $contentlength = $getheadresponse->getContentLength();
        $this->assertEquals(948, $contentlength);

        // Check there is no processed data
        $this->assertEquals(0, count($getheadprocesseddata));
    }

    /**
     * testGetAllGroupsloggedIn
     *
     * This function tests that all the user data can be returned if the client is
     * logged in
     *
     * @group unittest
     * @group groups
     *
     * @return void
     *
     * @access protected
     */
    public function testGetAllGroupsLoggedIn()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $getgroupsrequest = new Request();
        $getgroupsrequest->setEndPoint("api/v1/groups");

        $getgroupsrunok = $this->apiclient->httpGet($getgroupsrequest);
        if ($getgroupsrunok === false) {
            $this->assertFail("HTTP GET Command failed");
        }

        // Get the respose data
        $getgroupsresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getgroupsprocesseddata = $getgroupsresponse->getProcessedData();


        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results

        // Check the http response
        $getgroupshttpresponsecode = $getgroupsresponse->getHTTPResponseCode();
        $this->assertEquals("200", $getgroupshttpresponsecode);

        // Confirm that 10 groups are returned
        $this->assertEquals(5, count($getgroupsprocesseddata));
    }

    /**
     * testGetGroupOneloggedIn
     *
     * This function tests that all the data for group_id 1 can be returned if the
     * client is logged in
     *
     * @group unittest
     * @group groups
     *
     * @return void
     *
     * @access protected
     */
    public function testGetGrouprOneLoggedIn()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $getgroupsrequest = new Request();
        $getgroupsrequest->setEndPoint("api/v1/groups/1");

        $getgroupsrunok = $this->apiclient->httpGet($getgroupsrequest);
        if ($getgroupsrunok !== true) {
            $this->assertFail("HTTP GET Command failed");
        }

        // Get the respose data
        $getgroupsresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getgroupsprocesseddata = $getgroupsresponse->getProcessedData();


        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results

        // Check the http response
        $getgroupshttpresponsecode = $getgroupsresponse->getHTTPResponseCode();
        $this->assertEquals("200", $getgroupshttpresponsecode);

        // Confirm that 10 groups are returned
        $this->assertEquals(1, count($getgroupsprocesseddata));
        $this->assertEquals("admin", $getgroupsprocesseddata[0]["groupname"]);
        $this->assertEquals("Administrators", $getgroupsprocesseddata[0]["description"]);
    }

    /**
     * testGetGroupAdminloggedIn
     *
     * This function tests that all the data for group_name admin can be returned if the
     * client is logged in
     *
     * @group unittest
     * @group groups
     *
     * @return void
     *
     * @access protected
     */
    public function testGetGroupAdminLoggedIn()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $getgroupsrequest = new Request();
        $getgroupsrequest->setEndPoint("api/v1/groups/admin");

        $getgroupsrunok = $this->apiclient->httpGet($getgroupsrequest);
        if ($getgroupsrunok !== true) {
            $this->assertFail("HTTP GET Command failed");
        }

        // Get the respose data
        $getgroupsresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getgroupsprocesseddata = $getgroupsresponse->getProcessedData();


        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results

        // Check the http response
        $getgroupshttpresponsecode = $getgroupsresponse->getHTTPResponseCode();
        $this->assertEquals("200", $getgroupshttpresponsecode);

        // Confirm that 10 groups are returned
        $this->assertEquals(1, count($getgroupsprocesseddata));
        $this->assertEquals("1", $getgroupsprocesseddata[0]["groupid"]);
        $this->assertEquals("admin", $getgroupsprocesseddata[0]["groupname"]);
        $this->assertEquals("Administrators", $getgroupsprocesseddata[0]["description"]);
    }

    /**
     * testPostNewGroup
     *
     * This function POSTs a new group to the data base.  The data is sent in JSON
     * format.
     *
     * @group unittest
     * @group group
     *
     * @return void
     *
     * @access protected
     */
    public function testPostNewGroup()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $postgroupsrequest = new Request();
        $postgroupsrequest->setEndPoint("api/v1/groups");
        $postdata = $this->createGroupArray();
        $postgroupsrequest->setJSONEncodedData($postdata);

        $postrunok = $this->apiclient->httpPost($postgroupsrequest);
        if ($postrunok === false) {
            $this->fail("Unable to post new group to database for test: " . __FUNCTION__);
        }
        // Get the respose data
        $postgroupsresponse = $this->apiclient->getResponse();

        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results
        // Check the HTTP Responce and set the flag to delete the user.
        $postgroupshttpresponsecode = $postgroupsresponse->getHTTPResponseCode();
        $this->assertStringContainsString("201", $postgroupshttpresponsecode);
        if ($postgroupshttpresponsecode == "201") {
            $this->deleteTestGroup = true;
        }

        // Check the returned data against what was sent
        $returnedData = $postgroupsresponse->getProcessedData();
        $this->assertEquals($postdata['groupname'], $returnedData[0]['groupname']);
        $this->assertEquals($postdata['description'], $returnedData[0]['description']);
        $this->assertEquals($postdata['useforproduct'], $returnedData[0]['useforproduct']);
        $this->assertEquals($postdata['autogroup'], $returnedData[0]['autogroup']);
        $this->assertEquals("Y", $returnedData[0]['editable']);
    }


    /**
     * testPostDuplicateGroup
     *
     * This function POSTs a duplicate group to the data base and check that a conflict
     * error is returned.  The data is sent in JSON format.
     *
     * @group unittest
     * @group group
     *
     * @return void
     *
     * @access protected
     */
    public function testPostDuplicateGroup()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $postgroupsrequest = new Request();
        $postgroupsrequest->setEndPoint("api/v1/groups");
        $postdata = $this->createGroupArray();
        $postgroupsrequest->setJSONEncodedData($postdata);

        $postrunok = $this->apiclient->httpPost($postgroupsrequest);
        if ($postrunok === false) {
            $this->fail("Unable to post new group to database for test: " . __FUNCTION__);
        }
        // Get the respose data
        $postgroupsresponse = $this->apiclient->getResponse();

        // Post the duplicate group.
        $postrunok2 = $this->apiclient->httpPost($postgroupsrequest);
        if ($postrunok2 === false) {
            $this->fail("Unable to post duplicate group to database for test: " . __FUNCTION__);
        }
        // Get the respose data
        $postduplicategroupsresponse = $this->apiclient->getResponse();




        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results
        // Check the HTTP Responce and set the flag to delete the user.
        $postgroupshttpresponsecode = $postgroupsresponse->getHTTPResponseCode();
        $this->assertStringContainsString("201", $postgroupshttpresponsecode);
        if ($postgroupshttpresponsecode == "201") {
            $this->deleteTestGroup = true;
        }

        // Check the returned data against what was sent
        $returnedData = $postgroupsresponse->getProcessedData();
        $this->assertEquals($postdata['groupname'], $returnedData[0]['groupname']);
        $this->assertEquals($postdata['description'], $returnedData[0]['description']);
        $this->assertEquals($postdata['useforproduct'], $returnedData[0]['useforproduct']);
        $this->assertEquals($postdata['autogroup'], $returnedData[0]['autogroup']);
        $this->assertEquals("Y", $returnedData[0]['editable']);

        // Check the response of the duplicate post
        // Check the HTTP Responce and set the flag to delete the user.
        $postduplicategroupshttpresponsecode = $postduplicategroupsresponse->getHTTPResponseCode();
        $this->assertStringContainsString("409", $postduplicategroupshttpresponsecode);
    }


    /**
     * testUpdateGroup
     *
     * This function POSTs a new group to the data base then applies an update to the
     * group using the PUT command.  The data is sent in JSON format.
     *
     * @group unittest
     * @group group
     *
     * @return void
     *
     * @access protected
     */
    public function testUpdateGroup()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $postgroupsrequest = new Request();
        $postgroupsrequest->setEndPoint("api/v1/groups");
        $postdata = $this->createGroupArray();
        $postgroupsrequest->setJSONEncodedData($postdata);

        $postrunok = $this->apiclient->httpPost($postgroupsrequest);
        if ($postrunok === false) {
            $this->fail("Unable to post new group to database for test: " . __FUNCTION__);
        }

        // Get the response data
        $postgroupsresponse = $this->apiclient->getResponse();

        // Create the Updated group information
        $putdata = $this->createGroupArray();
        $putdata['description'] = "This group has been updated";
        $putdata['useforproduct'] = "N";

        // Create and populate the new request
        $putgroupsrequest = new Request();
        $putgroupsrequest->setEndPoint("api/v1/groups");
        $putgroupsrequest->setJSONEncodedData($putdata);
        $putgroupsrequest->setURLArguments(array(TESTGROUPNAME));
        $putrunok = $this->apiclient->httpPut($putgroupsrequest);
        if ($putrunok === false) {
            $this->fail("Unable to put group update to database for test: " . __FUNCTION__);
        }

        // Get the response data
        $putgroupsresponse = $this->apiclient->getResponse();


        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results
        // Check the HTTP Responce and set the flag to delete the user.
        $postgroupshttpresponsecode = $postgroupsresponse->getHTTPResponseCode();
        $this->assertStringContainsString("201", $postgroupshttpresponsecode);
        if ($postgroupshttpresponsecode == "201") {
            $this->deleteTestGroup = true;
        }

        // Check the update was successful.
        $putgroupshttpresponsecode = $putgroupsresponse->getHTTPResponseCode();
        $this->assertStringContainsString("201", $putgroupshttpresponsecode);

        // Check the returned data against what was sent
        $returnedData = $putgroupsresponse->getProcessedData();
        $this->assertEquals($putdata['groupname'], $returnedData[0]['groupname']);
        $this->assertEquals($putdata['description'], $returnedData[0]['description']);
        $this->assertEquals($putdata['useforproduct'], $returnedData[0]['useforproduct']);
        $this->assertEquals($putdata['autogroup'], $returnedData[0]['autogroup']);
        $this->assertEquals("Y", $returnedData[0]['editable']);
    }



    /**
     * testDeleteGroup
     *
     * This function POSTs a new group to the data base then deletes it.  It then
     * checks that the group has been deleted.  The data is sent in JSON format.
     *
     * @group unittest
     * @group group
     *
     * @return void
     *
     * @access protected
     */
    public function testDeleteGroup()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $postgroupsrequest = new Request();
        $postgroupsrequest->setEndPoint("api/v1/groups");
        $postdata = $this->createGroupArray();
        $postgroupsrequest->setJSONEncodedData($postdata);

        $postrunok = $this->apiclient->httpPost($postgroupsrequest);
        if ($postrunok === false) {
            $this->fail("Unable to post new group to database for test: " . __FUNCTION__);
        }
        // Get the respose data
        $postgroupsresponse = $this->apiclient->getResponse();

        $deletegrouprequest = new Request();
        $deletegrouprequest->setEndPoint("api/v1/groups");
        $deletegrouprequest->setURLArguments(array(TESTGROUPNAME));
        $deleterunok = $this->apiclient->httpDelete($deletegrouprequest);
        if ($deleterunok === false) {
            $this->fail("Unable to delete group from database for test: " . __FUNCTION__);
        }

        //get the responce data
        $deletegroupresponse = $this->apiclient->getResponse();

        // Check id the group still exists
        $getgroupsrequest = new Request();
        $getgroupsrequest->setEndPoint("api/v1/groups/admin");

        $getgroupsrunok = $this->apiclient->httpGet($getgroupsrequest);
        if ($getgroupsrunok !== true) {
            $this->assertFail("HTTP GET Command failed for test " . __FUNCTION__);
        }

        // Get the respose data
        $getgroupsresponse = $this->apiclient->getResponse();


        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results
        // Check the HTTP Responce and set the flag to delete the user.
        $postgroupshttpresponsecode = $postgroupsresponse->getHTTPResponseCode();
        $this->assertStringContainsString("201", $postgroupshttpresponsecode);
        if ($postgroupshttpresponsecode == "201") {
            $this->deleteTestGroup = true;
        }

        // Check the delete command run okay
        $deletegrouphttpresponsecode = $deletegroupresponse->getHTTPResponseCode();
        $this->assertStringContainsString("204", $deletegrouphttpresponsecode);

        // Check the GET Response
        $getgroupshttpresponsecode = $getgroupsresponse->getHTTPResponseCode();
        $this->assertEquals("200", $getgroupshttpresponsecode);
    }
}
