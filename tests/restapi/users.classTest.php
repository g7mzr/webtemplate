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
class UsersTest extends TestCase
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
     * Property: deleteTestUser
     * @var boolean
     * @access private
     */
    private $deleteTestUser;

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

        // Set deleteTestUser to false
        $this->deleteTestUser = false;
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
        global $testdsn;
        if ($this->deleteTestUser == true) {
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
            $sql = "delete from users where user_name = '" . TESTUSERNAME . "'";

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
        if($logoutrunok === false) {
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
     * createUserArray
     *
     * This function creates a array containing the details of the user to be
     * created.
     *
     * @return array Containing details of user to be created.
     *
     * @access private
     */
    private function createUserArray()
    {
        $user = array();
        $user["username"] = TESTUSERNAME;
        $user["useremail"] = TESTUSEREMAIL;
        $user["realname"] = TESTUSERREALNAME;
        $user["passwd"] = TESTUSERPASSWD;
        $user["userenabled"] = TESTUSERENABLED;
        $user["passwdchange"] = TESTUSERPASWWDCHANGE;
        $user["userdisablemail"] = TESTUSERNOMAIL;
        return $user;
    }

    /**
     * testGetUsersNotloggedIn
     *
     * This function tests that no user data is returned if the client is not logged in.
     *
     * @group unittest
     * @group users
     *
     * @return void
     *
     * @access protected
     */
    public function testGetUsersNotloggedIn()
    {
        $getusersrequest = new Request();
        $getusersrequest->setEndPoint("api/v1/users");

        $getusersrunok = $this->apiclient->httpGet($getusersrequest);
        $this->assertTrue($getusersrunok);

        // Get the respose data
        $getusersresponse = $this->apiclient->getResponse();

        // Check the http response
        $getusershttpresponse = $getusersresponse->getHTTPResponce();
        $this->assertEquals("401", $getusershttpresponse[1]);
        $this->assertEquals("Unauthorised", $getusershttpresponse[2]);

        // Check the returned data
        $getusersprocesseddata = $getusersresponse->getProcessedData();
        $this->assertEquals(
            "Login required to use this resource",
            $getusersprocesseddata['ErrorMsg']
        );
     }

    /**
     * testGetAllUsersloggedIn
     *
     * This function tests that all the user data can be returned if the client is
     * logged in
     *
     * @group unittest
     * @group users
     *
     * @return void
     *
     * @access protected
     */
    public function testGetAllUsersLoggedIn()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $getusersrequest = new Request();
        $getusersrequest->setEndPoint("api/v1/users");

        $getusersrunok = $this->apiclient->httpGet($getusersrequest);
        if ($getusersrunok === false) {
            $this->assertFail("HTTP GET Command failed");
        }

        // Get the respose data
        $getusersresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getusersprocesseddata = $getusersresponse->getProcessedData();


        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results

        // Check the http response
        $getusershttpresponse = $getusersresponse->getHTTPResponce();
        $this->assertEquals("200", $getusershttpresponse[1]);
        $this->assertEquals("OK", $getusershttpresponse[2]);

        // Confirm that 10 users are returned
        $this->assertEquals(10, count($getusersprocesseddata));
    }

    /**
     * testGetUserOneloggedIn
     *
     * This function tests that all the data for user_id 1 can be returned if the
     * client is logged in
     *
     * @group unittest
     * @group users
     *
     * @return void
     *
     * @access protected
     */
    public function testGetUserOneLoggedIn()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $getusersrequest = new Request();
        $getusersrequest->setEndPoint("api/v1/users/1");

        $getusersrunok = $this->apiclient->httpGet($getusersrequest);
        if ($getusersrunok === false) {
            $this->assertFail("HTTP GET Command failed");
        }

        // Get the respose data
        $getusersresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getusersprocesseddata = $getusersresponse->getProcessedData();


        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results

        // Check the http response
        $getusershttpresponse = $getusersresponse->getHTTPResponce();
        $this->assertEquals("200", $getusershttpresponse[1]);
        $this->assertEquals("OK", $getusershttpresponse[2]);

        // Confirm that 10 users are returned
        $this->assertEquals(1, count($getusersprocesseddata));
        $this->assertEquals("admin", $getusersprocesseddata[0]["username"]);
        $this->assertEquals("Administrator", $getusersprocesseddata[0]["realname"]);
    }

    /**
     * testGetUserOneGroupsloggedIn
     *
     * This function tests returns all the groups in the database.  The useringroup
     * field is used to identify which groups the user is a member of
     *
     * @group unittest
     * @group users
     *
     * @return void
     *
     * @access protected
     */
    public function testGetUserOneGroupsLoggedIn()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $getusersrequest = new Request();
        $getusersrequest->setEndPoint("api/v1/users/1/groups");

        $getusersrunok = $this->apiclient->httpGet($getusersrequest);
        if ($getusersrunok === false) {
            $this->assertFail("HTTP GET Command failed");
        }

        // Get the respose data
        $getusersresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getusersprocesseddata = $getusersresponse->getProcessedData();

        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results

        // Check the http response
        $getusershttpresponse = $getusersresponse->getHTTPResponce();
        $this->assertEquals("200", $getusershttpresponse[1]);
        $this->assertEquals("OK", $getusershttpresponse[2]);

        // Confirm that 5 groups are returned
        $this->assertEquals(5, count($getusersprocesseddata));

        //Check if the use is a member of the group
        foreach($getusersprocesseddata as $group) {
            if ($group["useringroup"] == "Y") {
                $this->assertEquals("admin", $group["groupname"]);
            }
            if ($group["useringroup"] == "I") {
                $this->assertEquals("testgroup", $group["groupname"]);
            }
        }
    }


    /**
     * testGetUserAdminloggedIn
     *
     * This function tests that all the data for username admin can be returned if the
     * client is logged in
     *
     * @group unittest
     * @group users
     *
     * @return void
     *
     * @access protected
     */
    public function testGetUserAdminLoggedIn()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $getusersrequest = new Request();
        $getusersrequest->setEndPoint("api/v1/users/admin");

        $getusersrunok = $this->apiclient->httpGet($getusersrequest);
        if ($getusersrunok === false) {
            $this->assertFail("HTTP GET Command failed");
        }

        // Get the respose data
        $getusersresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getusersprocesseddata = $getusersresponse->getProcessedData();


        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results

        // Check the http response
        $getusershttpresponse = $getusersresponse->getHTTPResponce();
        $this->assertEquals("200", $getusershttpresponse[1]);
        $this->assertEquals("OK", $getusershttpresponse[2]);

        // Confirm that 10 users are returned
        $this->assertEquals(1, count($getusersprocesseddata));
        $this->assertEquals("admin", $getusersprocesseddata[0]["username"]);
        $this->assertEquals("Administrator", $getusersprocesseddata[0]["realname"]);
    }

    /**
     * testGetUserAdminGroupsloggedIn
     *
     * This function tests returns all the groups in the database.  The useringroup
     * field is used to identify which groups the user is a member of
     *
     * @group unittest
     * @group users
     *
     * @return void
     *
     * @access protected
     */
    public function testGetUserAdminGroupsLoggedIn()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $getusersrequest = new Request();
        $getusersrequest->setEndPoint("api/v1/users/admin/groups");

        $getusersrunok = $this->apiclient->httpGet($getusersrequest);
        if ($getusersrunok === false) {
            $this->assertFail("HTTP GET Command failed");
        }

        // Get the respose data
        $getusersresponse = $this->apiclient->getResponse();

        // Check the returned data
        $getusersprocesseddata = $getusersresponse->getProcessedData();

        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results

        // Check the http response
        $getusershttpresponse = $getusersresponse->getHTTPResponce();
        $this->assertEquals("200", $getusershttpresponse[1]);
        $this->assertEquals("OK", $getusershttpresponse[2]);

        // Confirm that 5 groups are returned
        $this->assertEquals(5, count($getusersprocesseddata));

        //Check if the use is a member of the group
        foreach($getusersprocesseddata as $group) {
            if ($group["useringroup"] == "Y") {
                $this->assertEquals("admin", $group["groupname"]);
            }
            if ($group["useringroup"] == "I") {
                $this->assertEquals("testgroup", $group["groupname"]);
            }
        }
    }


    /**
     * testPostNewUser
     *
     * This function POSTs a new user to the data base.  The data is sent in JSON
     * format.
     *
     * @group unittest
     * @group users
     *
     * @return void
     *
     * @access protected
     */
    public function testPostNewUser()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $postusersrequest = new Request();
        $postusersrequest->setEndPoint("api/v1/users");
        $postdata = $this->createUserArray();
        $postusersrequest->setJSONEncodedData($postdata);

        $postrunok = $this->apiclient->httpPost($postusersrequest);
        if ($postrunok === false) {
            $this->fail("Unable to post new user to database for test: " . __FUNCTION__);
        }
        // Get the respose data
        $postusersresponse = $this->apiclient->getResponse();

        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results
        // Check the HTTP Responce and set the flag to delete the user.
        $postusershttpresponse = $postusersresponse->getHTTPResponce();
        $this->assertContains("201", $postusershttpresponse[1]);
        $this->assertEquals("Created", $postusershttpresponse[2]);
        if ($postusershttpresponse[1] == "201") {
            $this->deleteTestUser = true;
        }

        // Check the returned data against what was sent
        $returnedData = $postusersresponse->getProcessedData();
        $this->assertEquals($postdata['username'], $returnedData[0]['username']);
        $this->assertEquals($postdata['realname'], $returnedData[0]['realname']);
        $this->assertEquals($postdata['useremail'], $returnedData[0]['useremail']);
        $this->assertEquals($postdata['userenabled'], $returnedData[0]['userenabled']);
        $this->assertEquals($postdata['userdisablemail'], $returnedData[0]['userdisablemail']);
    }

    /**
     * testPostDuplicateNewUser
     *
     * This function POSTs a new user to the data base.  It then tries to recreate the
     * POST the data a second time which should return a 409 Conflict Error.  The
     * data is sent in JSON format.
     *
     * @group unittest
     * @group users
     *
     * @return void
     *
     * @access protected
     */
    public function testPostDulicateNewUser()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $postusersrequest = new Request();
        $postusersrequest->setEndPoint("api/v1/users");
        $postdata = $this->createUserArray();
        $postusersrequest->setJSONEncodedData($postdata);

        $postrunok = $this->apiclient->httpPost($postusersrequest);
        if ($postrunok === false) {
            $this->fail("Unable to post new user to database for test: " . __FUNCTION__);
        }
        // Get the response data from the first post
        $postusersresponse = $this->apiclient->getResponse();

        $postrunok2 = $this->apiclient->httpPost($postusersrequest);
        if ($postrunok2 === false) {
            $this->fail("Unable to post duplicate  user to database for test: " . __FUNCTION__);
        }
        // Get the response data from the first post
        $postusersresponseduplicate = $this->apiclient->getResponse();


        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results
        // Check the HTTP Responce and set the flag to delete the user.
        $postusershttpresponse = $postusersresponse->getHTTPResponce();
        $this->assertContains("201", $postusershttpresponse[1]);
        $this->assertEquals("Created", $postusershttpresponse[2]);
        if ($postusershttpresponse[1] == "201") {
            $this->deleteTestUser = true;
        }

        // Check the results of the second post
        $postusershttpresponseduplicate = $postusersresponseduplicate->getHTTPResponce();
        $this->assertContains("409", $postusershttpresponseduplicate[1]);
        $this->assertEquals("Conflict", $postusershttpresponseduplicate[2]);
        $returnedData = $postusersresponseduplicate->getProcessedData();
        $this->assertEquals("User phpunit99 already exists.", $returnedData["ErrorMsg"]);
    }


    /**
     * testUpdateUser
     *
     * This function POSTs a new user to the data base.  It then uses HTTP PUT to
     * update the userThe data is sent in JSON format.
     *
     * @group unittest
     * @group users
     *
     * @return void
     *
     * @access protected
     */
    public function testUpdateUser()
    {
        // LOGIN for the test
        if ($this->login() === false) {
            $this->fail("Unable to login for test: " . __FUNCTION__);
        }

        $postusersrequest = new Request();
        $postusersrequest->setEndPoint("api/v1/users");
        $postdata = $this->createUserArray();
        $postusersrequest->setJSONEncodedData($postdata);

        $postrunok = $this->apiclient->httpPost($postusersrequest);
        if ($postrunok === false) {
            $this->fail("Unable to post new user to database for test: " . __FUNCTION__);
        }
        // Get the respose data
        $postusersresponse = $this->apiclient->getResponse();

        // Update the user using HTTP PUT
        $putdata = $this->createUserArray();
        $putdata["useremail"] = "testuser@example.com";
        $putdata["userenabled"] = "N";
        $putdata["passwd"] = "";

        $putuserrequest = new Request();
        $endpoint = "api/v1/users/" . TESTUSERNAME;
        $putuserrequest->setEndPoint($endpoint);
        $putuserrequest->setJSONEncodedData($putdata);

        $putrunok = $this->apiclient->httpPut($putuserrequest);
        if ($putrunok === false) {
            $this->fail("Unable to put new user data to database for test: " . __FUNCTION__);
        }
        $putusersresponse = $this->apiclient->getResponse();

        // LOGOUT after the test.
        if ($this->logout() === false) {
            $this->fail("Unable to logout for test: " . __FUNCTION__);
        }

        // Check the Results
        // Check the HTTP Responce to POST command and set the flag to delete the user.
        $postusershttpresponse = $postusersresponse->getHTTPResponce();
        $this->assertContains("201", $postusershttpresponse[1]);
        $this->assertEquals("Created", $postusershttpresponse[2]);
        if ($postusershttpresponse[1] == "201") {
            $this->deleteTestUser = true;
        }

        // Check the HTTP Responce to PUT command
        $putusershttpresponse = $putusersresponse->getHTTPResponce();
        $this->assertContains("201", $putusershttpresponse[1]);
        $this->assertEquals("Created", $putusershttpresponse[2]);


        // Check the returned data against what was sent
        $returnedData = $putusersresponse->getProcessedData();
        $this->assertEquals($putdata['username'], $returnedData[0]['username']);
        $this->assertEquals($putdata['realname'], $returnedData[0]['realname']);
        $this->assertEquals($putdata['useremail'], $returnedData[0]['useremail']);
        $this->assertEquals($putdata['userenabled'], $returnedData[0]['userenabled']);
        $this->assertEquals($putdata['userdisablemail'], $returnedData[0]['userdisablemail']);
    }


}
