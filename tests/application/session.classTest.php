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
 * Session Class Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class SessionTest extends TestCase
{
    /**
     * Session Class
     *
     * @var \webtemplate\application\Session
     */
    protected $object;

    /**
     * Database Class
     *
     * @var \webtemplate\db\DB
     */
    protected $object2;


    /**
     * Database Connection
     *
     * @var boolean
     */
    protected $databaseconnection;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        global $testdsn, $sessiontest, $tpl;

        // Set up global variables
        if ($this->getName() != "testSetCookie") {
            $sessiontest = array(true);
        }

        // Set up a Smarty Template object to get config variables
        $tpl = new \webtemplate\application\SmartyTemplate;

        if ($this->getName() == "testGarbageCollection") {
            // Alter location of Config Directory to tests/_data
            $systemdir = dirname(dirname(dirname(__FILE__)));
            $tpl->setConfigDir($systemdir . '/tests/_data');
            //print_r($tpl);
        }

        //Set up the correct language and associated templates
        $languageconfig = "en.conf";
        $tpl->configLoad($languageconfig);

        // Check that we can connect to the database
        $this->object2 = \webtemplate\db\DB::load($testdsn);

        if (!\webtemplate\general\General::isError($this->object2)) {
            $this->databaseconnection = true;
        } else {
            $this->databaseconnection = false;
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
        if ($this->databaseconnection === true) {
            // Delete test data from the login
            $searchData = array(
                'user_name' => array('type' => '=', 'data' => 'phpunit')
            );
            $result = $this->object2->dbdeletemultiple('logindata', $searchData);
            $this->object2->disconnect();
        }
    }

    /**
     * This function tests that a new session can be created
     *
     * @group unittest
     * @group general
     *
     * @return null
     */
    public function testNewSession()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.1';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("webdatabase", $sessiontest['name']);
            $this->assertEquals(0, $sessiontest['expire']);
            $this->assertEquals("cookiepath", $sessiontest['path']);
            $this->assertEquals("cookiedomain", $sessiontest['domain']);
            $this->assertFalse($sessiontest['secure']);
            $this->assertTrue($sessiontest['httponly']);

            // Prepare the Global Variables
            // Test with permenant session
            $_SERVER['REMOTE_ADDR'] = '10.1.1.1';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '5';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertNotEquals(0, $sessiontest['expire']);
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests that a new session can be created
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     *
     * @return null
     */
    public function testExistingSession()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.2';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("webdatabase", $sessiontest['name']);
            $this->assertEquals(0, $sessiontest['expire']);
            $this->assertEquals("cookiepath", $sessiontest['path']);
            $this->assertEquals("cookiedomain", $sessiontest['domain']);
            $this->assertFalse($sessiontest['secure']);
            $this->assertTrue($sessiontest['httponly']);


            // Prepare the Global Variables
            // Reuse the session variable created in the earler test
            $_COOKIE['webdatabase'] = $sessiontest['value'];

            // Create a new Session Object
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("webdatabase", $sessiontest['name']);
            $this->assertEquals($_COOKIE['webdatabase'], $sessiontest['value']);
            $this->assertEquals(0, $sessiontest['expire']);
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests that the user name can be retrieved
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     *
     * @return null
     */
    public function testGetUserName()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.3';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("", $this->object->getUserName());
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests that the user Id can be retrieved
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     *
     * @return null
     */
    public function testGetUserId()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.4';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("", $this->object->getUserId());
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests that the password changed flag can be retrieved
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     *
     * @return null
     */
    public function testGetPasswdChange()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.5';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
             $this->assertFalse($this->object->getPasswdChange());
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests that sessions can be created
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     * @depends testGetUserName
     * @depends testGetUserId
     *
     * @return null
     */
    public function testCreateSession()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.6';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );

            // Test with a invalid user
            $result = $this->object->createSession("testuser", "1", false);
            $this->assertFalse($result);

            // Test with a valid user
            $result = $this->object->createSession("phpunit", "2", true);
            $this->assertTrue($result);
            $this->assertEquals("phpunit", $this->object->getUserName());
            $this->assertEquals("2", $this->object->getUserId());
            $this->assertTrue($this->object->getPasswdChange());
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests that sessions are persistant
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     * @depends testGetUserName
     * @depends testGetUserId
     * @depends testCreateSession
     *
     * @return null
     */
    public function testSessionPersitance()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.7';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            // Create a Session for a valid user
            $result = $this->object->createSession("phpunit", "2", false);
            $this->assertTrue($result);
            $this->assertEquals("phpunit", $this->object->getUserName());
            $this->assertEquals("2", $this->object->getUserId());


            // Reuse the session variable created in the earler test
            $_COOKIE['webdatabase'] = $sessiontest['value'];

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("phpunit", $this->object->getUserName());
            $this->assertEquals("2", $this->object->getUserId());
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests that the user name can be updated
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     * @depends testGetUserName
     * @depends testGetUserId
     * @depends testCreateSession
     * @depends testSessionPersitance
     *
     * @return null
     */
    public function testSetUserName()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.8';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );

            // Create a Session for a valid user
            $result = $this->object->createSession("settingsuser", "3", false);
            $this->assertTrue($result);
            $this->assertEquals("settingsuser", $this->object->getUserName());

            //Change userid to phpunit and test it is stored in the object
            $result = $this->object->setUserName("phpunit");
            $this->assertTrue($result);
            $this->assertEquals("phpunit", $this->object->getUserName());


            // Reuse the session variable created in the earler test
            $_COOKIE['webdatabase'] = $sessiontest['value'];

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("phpunit", $this->object->getUserName());
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests that the userid can be updated
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     * @depends testGetUserName
     * @depends testGetUserId
     * @depends testCreateSession
     * @depends testSessionPersitance
     *
     * @return null
     */
    public function testSetUserId()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.8';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );

            // Create a Session for a valid user
            $result = $this->object->createSession("phpunit", "3", false);
            $this->assertTrue($result);
            $this->assertEquals("3", $this->object->getUserId());

            //Change userid to phpunit and test it is stored in the object
            $result = $this->object->setUserId("2");
            $this->assertTrue($result);
            $this->assertEquals("2", $this->object->getUserId());


            // Reuse the session variable created in the earler test
            $_COOKIE['webdatabase'] = $sessiontest['value'];

            // Create a new session and retreve the session name;
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("2", $this->object->getUserId());
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests that the password change flag can be updated
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     * @depends testGetUserName
     * @depends testGetUserId
     * @depends testCreateSession
     * @depends testSessionPersitance
     *
     * @return null
     */
    public function testSetPasswdChange()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.8';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );

            // Create a Session for a valid user
            $result = $this->object->createSession("phpunit", "3", false);
            $this->assertTrue($result);
            $this->assertFalse($this->object->getPasswdChange());

            //Change userid to phpunit and test it is stored in the object
            $result = $this->object->setPasswdChange(true);
            $this->assertTrue($result);
            $this->assertTrue($this->object->getPasswdChange());


            // Reuse the session variable created in the earler test
            $_COOKIE['webdatabase'] = $sessiontest['value'];

            // Create a new session and retreve the session name;
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests that sessions can be destroyed.
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     * @depends testGetUserName
     * @depends testGetUserId
     * @depends testCreateSession
     * @depends testSessionPersitance
     *
     * @return null
     */
    public function testSessionDestroy()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.8';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );

            // Create a Session for a valid user
            $result = $this->object->createSession("phpunit", "3", false);
            $this->assertTrue($result);

            // Reuse the session variable created in the earler test
            $sessioncookie = $sessiontest['value'];

            // Destroy Session
            $result = $this->object->destroy();
            $this->assertTrue($result);
            sleep(1);
            $this->assertLessThan(time()-3600, $sessiontest['expire']);
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests the 10 Minute auto Logout
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     * @depends testGetUserName
     * @depends testGetUserId
     * @depends testCreateSession
     * @depends testSessionPersitance
     *
     * @return null
     */
    public function testAutoLogout10Min()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.8';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '2';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );

            // Create a Session for a valid user
            $result = $this->object->createSession("phpunit", "2", false);
            $this->assertTrue($result);

            // Reuse the session variable created in the earler test
            $_COOKIE['webdatabase'] = $sessiontest['value'];
            $sessioncookie = $sessiontest['value'];

            // Create a new Session Object
            // and check that the username has been set to ""
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("phpunit", $this->object->getUserName());

            // Set the Database lastused value back by 11 Minutes
            $timeSeconds = 11 * 3600;
            $lastUsed = date('Y-m-d G:i:s', time() - $timeSeconds);

            $data = array(
                'lastused'  => $lastUsed
            );
            $search = array('cookie' => $sessioncookie);
            $this->object2->dbupdate('logindata', $data, $search);

            // Create a new Session Object
            // and check that the username has been set to ""
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("", $this->object->getUserName());
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests the 20 Minute auto Logout
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     * @depends testGetUserName
     * @depends testGetUserId
     * @depends testCreateSession
     * @depends testSessionPersitance
     *
     * @return null
     */
    public function testAutoLogout20Min()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.8';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '3';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );

            // Create a Session for a valid user
            $result = $this->object->createSession("phpunit", "2", false);
            $this->assertTrue($result);

            // Reuse the session variable created in the earler test
            $_COOKIE['webdatabase'] = $sessiontest['value'];
            $sessioncookie = $sessiontest['value'];

            // Create a new Session Object
            // and check that the username has been set to ""
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '3';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("phpunit", $this->object->getUserName());

            // Set the Database lastused value back by 11 Minutes
            $timeSeconds = 21 * 3600;
            $lastUsed = date('Y-m-d G:i:s', time() - $timeSeconds);

            $data = array(
                'lastused'  => $lastUsed
            );
            $search = array('cookie' => $sessioncookie);
            $this->object2->dbupdate('logindata', $data, $search);

            // Create a new Session Object
            // and check that the username has been set to ""
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("", $this->object->getUserName());
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * This function tests the 30 Minute auto Logout
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     * @depends testGetUserName
     * @depends testGetUserId
     * @depends testCreateSession
     * @depends testSessionPersitance
     *
     * @return null
     */
    public function testAutoLogout30Min()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.8';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '4';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );

            // Create a Session for a valid user
            $result = $this->object->createSession("phpunit", "2", false);
            $this->assertTrue($result);

            // Reuse the session variable created in the earler test
            $_COOKIE['webdatabase'] = $sessiontest['value'];
            $sessioncookie = $sessiontest['value'];

            // Create a new Session Object
            // and check that the username has been set to ""
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("phpunit", $this->object->getUserName());

            // Set the Database lastused value back by 11 Minutes
            $timeSeconds = 31 * 3600;
            $lastUsed = date('Y-m-d G:i:s', time() - $timeSeconds);

            $data = array(
                'lastused'  => $lastUsed
            );
            $search = array('cookie' => $sessioncookie);
            $this->object2->dbupdate('logindata', $data, $search);

            // Create a new Session Object
            // and check that the username has been set to ""
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );
            $this->assertEquals("", $this->object->getUserName());
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests the garbage collection
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     * @depends testGetUserName
     * @depends testGetUserId
     * @depends testCreateSession
     * @depends testSessionPersitance
     *
     * @return null
     */
    public function testGarbageCollection()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.8';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            $this->object = new \webtemplate\application\Session(
                $cookiepath,
                $cookiedomain,
                $autologout,
                $tpl,
                $this->object2
            );

            $this->assertTrue($this->object->getGCRun());
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests that the class will attempt to set a cookie on the
     * client machine
     *
     * @group unittest
     * @group general
     *
     * @depends testNewSession
     * @depends testGetUserName
     * @depends testGetUserId
     *
     * @return null
     */
    public function testSetCookie()
    {
        global $tpl, $sessiontest;
        if ($this->databaseconnection == true) {
            // prepare local variables
            $result = false;

            // Prepare the Global Variables
            $_SERVER['REMOTE_ADDR'] = '10.1.1.100';
            $cookiepath = 'cookiepath';
            $cookiedomain = 'cookiedomain';
            $autologout = '1';

            // Create a new Session Object
            // Test  Session created using Session Close
            try {
                $this->object = new \webtemplate\application\Session(
                    $cookiepath,
                    $cookiedomain,
                    $autologout,
                    $tpl,
                    $this->object2
                );
            } catch (\Throwable $e) {
                $this->assertContains(
                    "Cannot modify header information",
                    $e->getMessage()
                );
                $result = true;
            }
            if ($result == false) {
                $this->fail("setcookie did not throw an exception.");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
}
