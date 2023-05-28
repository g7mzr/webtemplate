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

use PHPUnit\Framework\TestCase;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// Include the test database connection details
require_once dirname(__FILE__) . '/../_data/database.php';

// Include the Default Preferences
//require_once dirname(__FILE__) .'/../../configs/preferences.php';

/**
 * UserClass Unit Tests
 *
 **/
class RegisterClassTest extends TestCase
{
    /**
     * User Class Object
     *
     * @var  \g7mzr\webtemplate\users\Register
     */
    protected $object;

    /**
     * Database Connection Object
     *
     * @var \g7mzr\db\DBManager
     */
    protected $object2;

    /**
     * User Class Object for MOCK database Class
     *
     * @var \g7mzr\webtemplate\users\Register
     */
    protected $object3;


    /**
     * Valid Database connection
     *
     * @var Valid Database connection
     */
    protected $databaseconnection;

    /**
     * MOCK Database connection
     *
     * @var \g7mzr\db\DBManager
     */
    protected $mockdatabaseconnection;

    /**
     * Configuration Class Object
     *
     * @var \webtemplate\config\Configuration
     */
    protected $config;


    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        global $testdsn, $testName;

        // Check that we can connect to the database
        try {
            $this->object2 = new \g7mzr\db\DBManager(
                $testdsn,
                $testdsn['username'],
                $testdsn['password']
            );
            $setresult = $this->object2->setMode("datadriver");
            if (!\g7mzr\db\common\Common::isError($setresult)) {
                $this->databaseconnection = true;
            } else {
                $this->databaseconnection = false;
                echo $setresult->getMessage();
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $this->databaseconnection = false;
        }
        // Create a new User Object
        $this->object = new\g7mzr\webtemplate\users\Register($this->object2->getDataDriver());

        // Set up the configuration object
        $this->config = new \g7mzr\webtemplate\config\Configure($this->object2->getDataDriver());


// Update the last password change date

        $todaysdate = time();
        $testdate = ($todaysdate - (55 * 86400));
        $datestr = strftime("%Y-%m-%d", $testdate);

        $insertData = array('passwd_changed' => $datestr);
        $searchData = array('user_name' => 'passwduser2');

        $resultId = $this->object2->getDataDriver()->dbupdate('users', $insertData, $searchData);
        if (\g7mzr\db\common\Common::isError($resultId)) {
            //Failed to delete test user.
        }

        // Set up MOCK Connection
        $testdsn['dbtype'] = 'mock';
        $this->mockdatabaseconnection = new \g7mzr\db\DBManager(
            $testdsn,
            $testdsn['username'],
            $testdsn['password']
        );
        $setresult = $this->mockdatabaseconnection->setMode("datadriver");
        $this->object3 = new \g7mzr\webtemplate\users\Register($this->mockdatabaseconnection->getDataDriver());



        $testName =  $this->getName();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        if ($this->databaseconnection === true) {
            //$this->object2->getDataDriver()->disconnect();
        }
    }

    /**
     * Test that a valid user who has logged in can register themselves again
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testValidRegister()
    {
        if ($this->databaseconnection == true) {
            // Valid registration
            $userData = array();
            $validUser = $this->object->register(
                'phpunit',
                $this->config->read('pref'),
                $userData
            );

            if (\g7mzr\webtemplate\general\General::isError($validUser)) {
                $this->fail("User : " . $validUser->getMessage());
            } else {
                $this->assertTrue($validUser);

                // Test that the $userData array is updated correctly
                $this->assertEquals("phpunit", $userData['userName']);
                $this->assertEquals(2, $userData['userId']);
                $this->assertEquals("Phpunit User", $userData['realName']);
                $this->assertEquals('phpunit@example.com', $userData['userEmail']);
                $this->assertEquals('', $userData['passwdAgeMsg']);
                $this->assertEquals(0, count($userData['permissions']));
                $testPasswd = \g7mzr\webtemplate\general\General::encryptPasswd('phpUnit1');
                //$this->assertEquals($testPasswd, $userData['encryptedPasswd']);
                $this->assertEquals('Dusk', $userData['theme']);
                $this->assertTrue($userData['zoomText']);
                $this->assertEquals('2', $userData['displayRows']);
                $this->assertEquals('', $userData['last_seen_date']);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that a valid user who has just logged in can be registered without
     * overwriting the passwdAgeMsg and date_last_seen in the userData array
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testValidRegisterAfterLogin()
    {
        if ($this->databaseconnection == true) {
            // Valid registration
            $userData = array();
            $userData['last_seen_date'] = '2018-09-08 20:47:52';
            $userData['passwdAgeMsg'] = '5 days.';
            $validUser = $this->object->register(
                'phpunit',
                $this->config->read('pref'),
                $userData
            );

            if (\g7mzr\webtemplate\general\General::isError($validUser)) {
                $this->fail("User : " . $validUser->getMessage());
            } else {
                $this->assertTrue($validUser);

                // Test that the $userData array is updated correctly
                $this->assertEquals("phpunit", $userData['userName']);
                $this->assertEquals(2, $userData['userId']);
                $this->assertEquals("Phpunit User", $userData['realName']);
                $this->assertEquals('phpunit@example.com', $userData['userEmail']);
                $this->assertEquals('5 days.', $userData['passwdAgeMsg']);
                $this->assertEquals(0, count($userData['permissions']));
                $testPasswd = \g7mzr\webtemplate\general\General::encryptPasswd('phpUnit1');
                //$this->assertEquals($testPasswd, $userData['encryptedPasswd']);
                $this->assertEquals('Dusk', $userData['theme']);
                $this->assertTrue($userData['zoomText']);
                $this->assertEquals('2', $userData['displayRows']);
                $this->assertEquals('2018-09-08 20:47:52', $userData['last_seen_date']);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
   /**
     * Test that a invalid user cannot register themselves.
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testInvalidRegister()
    {
        if ($this->databaseconnection == true) {
            // Bad Registration
            $badUserData = array();
            $badUser = $this->object->register(
                'nouser',
                $this->config->read('pref'),
                $badUserData
            );
            if (\g7mzr\webtemplate\general\General::isError($badUser)) {
                $this->assertEquals('Unable to Register User', $badUser->getMessage());
            } else {
                $this->fail("User : Bad Registration Failed");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

 /**
     * Test that a user's settings are retrevied furing registration
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testRegisterSettings()
    {
        if ($this->databaseconnection == true) {
            // Valid registration
            $userData = array();
            $user = $this->object->register(
                'settingsuser',
                $this->config->read('pref'),
                $userData
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $this->assertTrue($user);
                $this->assertEquals('Dusk', $userData['theme']);
                $this->assertFalse($userData['zoomText']);
                $this->assertEquals('2', $userData['displayRows']);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }






















    /**
     * Test Registration Process using the MOCK Database
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testRegisterMock()
    {
        if ($this->databaseconnection == true) {
            $functions = array(
                'register' => array(
                    "pass" => true,
                    "notfound" => false,
                    "update" => false
                ),
                '_getPreferences' => array(
                    "pass" => true,
                    "notfound" => false
                )
            );

            $passwd = '$2y$10$II8N.OUTfQygovSVvuH5t.P20IrBj7Upb50WBztwGIbIUAVZCYAsK';
            $testdata = array(
                'register' => array(
                    'user_id' => 100,
                    'user_name' => 'mock',
                    'user_passwd' => $passwd,
                    'user_realname' => 'Mock User',
                    'user_email' => 'mock@example.com',
                    'user_enabled' => 'Y',
                    'user_disable_mail' => 'N',
                    'last_seen_date' => '2015/08/10',
                    'passwd_changed' => '2015/08/10'
                ),
                '_getPreferences' => array(
                    "rowone" => array(
                        'user_id' => '1',
                        'settingname' => 'zoomtext',
                        'settingvalue' => 'true'
                    )
                ),
                '_getUsersPermissions' => array(
                    "0" => array(
                        "group_id" => '1',
                        "group_name" => "Test",
                        "group_description" => "Test Group",
                        "group_useforproduct" => 'Y',
                        "group_editable" => 'N',
                        "group_autogroup" => 'Y',
                        "group_admingroup" => 'Y'
                    )
                )

            );

            // Bad Registration using MOCK Database Driver.
            $userData = array();
            $user = $this->object3->register(
                'mock',
                $this->config->read('pref'),
                $userData
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertEquals('Unable to Register User', $user->getMessage());
            } else {
                $this->fail("User : Bad Registration Failed");
            }


            // Set up Valid Data for login that fails Registration
            $this->mockdatabaseconnection->getDataDriver()->control($functions, $testdata);

            $user = $this->object3->register(
                'mock',
                $this->config->read('pref'),
                $userData
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertEquals('Unable to Register User', $user->getMessage());
            } else {
                $this->fail("User : Bad Registration Failed");
            }



            $functions = array(
                'register' => array(
                    "pass" => true,
                    "notfound" => false
                ),
                '_getUsersPermissions' => array(
                    'pass' => true,
                    'notfound' => true
                ),
                '_getPreferences' => array(
                    "pass" => true,
                    "notfound" => false
                )

            );


            $this->mockdatabaseconnection->getDataDriver()->control($functions, $testdata);

            $user = $this->object3->register(
                'mock',
                $this->config->read('pref'),
                $userData
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertEquals('Unable to Register User', $user->getMessage());
            } else {
                $this->fail("User : Failed test when update last seen date");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
}
