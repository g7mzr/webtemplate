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
class UserClassTest extends TestCase
{
    /**
     * User Class Object
     *
     * @var\g7mzr\webtemplate\users\User
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
     * @var\g7mzr\webtemplate\users\User
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
     * @var /webtemplate/config/Configuration
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
        $this->object = new\g7mzr\webtemplate\users\User($this->object2->getDataDriver());

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
        $this->object3 = new \g7mzr\webtemplate\users\User($this->mockdatabaseconnection->getDataDriver());



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
     * Test that a valid user can login to the database.
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testLoginValidUser()
    {
        if ($this->databaseconnection == true) {
            // Good Login
            $user = $this->object->login(
                'phpunit',
                'phpUnit1',
                1,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $this->assertTrue($user);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test a user with an invalid password cannot log in to the database and
     * has to wait 16 seconds before trying again
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testLoginInvalidPassword()
    {
        if ($this->databaseconnection == true) {
            // Bad login Password only
            $user = $this->object->login(
                'phpunit',
                'phpUnit12',
                1,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertEquals(
                    'Invalid Username and password',
                    $user->getMessage()
                );
            } else {
                $this->fail("User : Bad login Test Failed");
            }

            // Good login within 15 seconds
            $user = $this->object->login(
                'phpunit',
                'phpUnit1',
                1,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertEquals(
                    'Invalid Username and password',
                    $user->getMessage()
                );
            } else {
                $this->fail("User : Loggedin within 15 seconds of a failure");
            }

            // Good login after 15 Second wait
            sleep(16);
            $user = $this->object->login(
                'phpunit',
                'phpUnit1',
                1,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User OK: " . $user->getMessage());
            } else {
                $this->assertTrue($user);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that a Bad userbane and Password Fail to Login
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testLoginBadUserPassword()
    {
        if ($this->databaseconnection == true) {
            // Bad Login user and Password
            $user = $this->object->login(
                'nouser',
                'nopassword',
                1,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertEquals(
                    'Invalid Username and password',
                    $user->getMessage()
                );
            } else {
                $this->fail("User : Bad login Test Failed");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that a locked user cannot login
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testLoginLockedUser()
    {
        if ($this->databaseconnection == true) {
            // Locked User
            $user = $this->object->login(
                'lockeduser',
                'lockedUser1',
                1,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertEquals(
                    'Invalid Username and password',
                    $user->getMessage()
                );
            } else {
                $this->fail("User : Locked User Test Failed");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
    /**
     * Test that password that has expired after its 60 day limit must be changed
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testLoginExpiredPassword60Days()
    {
        if ($this->databaseconnection == true) {
             // Expired Password and 60 day limit
            $user = $this->object->login(
                'passwduser',
                'passwduser',
                2,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertEquals(
                    'Your password has expired and must be changed',
                    $user->getMessage()
                );
                $this->assertEquals(5, $user->getCode());
            } else {
                $this->fail("User : Expired Password and 60 day limit Test Failed");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
    /**
     * Test that a valid user with an expired password can login when no
     * limit is set.
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testLoginExpiredPasswordNoLimit()
    {
        if ($this->databaseconnection == true) {
             // Expired Password and no limit
            $user = $this->object->login(
                'passwduser',
                'passwduser',
                1,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : Expired Password and no limit Test Failed");
            } else {
                $this->assertTrue($user);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test Login function using MOCK Database Connection to force failures
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testLoginMock()
    {
        if ($this->databaseconnection == true) {
            $functions = array(
                'login' => array(
                    "pass" => true,
                    "notfound" => false
                )
            );

            $passwd = '$2y$10$II8N.OUTfQygovSVvuH5t.P20IrBj7Upb50WBztwGIbIUAVZCYAsK';
            $testdata = array(
                'login' => array(
                    'user_id' => 100,
                    'user_name' => 'mock',
                    'user_passwd' => $passwd,
                    'user_realname' => 'Mock User',
                    'user_email' => 'mock@example.com',
                    'user_enabled' => 'Y',
                    'user_disable_mail' => 'N',
                    'last_seen_date' => '2015/08/10',
                    'passwd_changed' => '2015/08/10',
                    'last_failed_login' => '2015/01/01 19:00:00'
                )
            );


            // Test login to the mock connection
            $user = $this->object3->login(
                'passwduser',
                'passwduser',
                1,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertStringContainsString(
                    "Error Running SQL Query",
                    $user->getMessage()
                );
            } else {
                $this->fail("User : Mock Driver Login Failed");
            }

            // Set up Valid Data for login that fails Registration
            $this->mockdatabaseconnection->getDataDriver()->control($functions, $testdata);

            // Test login to the mock connection and mock user to pass some tests
            $user = $this->object3->login(
                'mock',
                'phpUnit1',
                1,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertStringContainsString(
                    "Unable to Complete Login Process",
                    $user->getMessage()
                );
            } else {
                $this->fail("User : Mock Driver Login Failed");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
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
    public function testRegister()
    {
        if ($this->databaseconnection == true) {
            // Valid registration
            $user = $this->object->register('phpunit', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $this->assertTrue($user);
            }

            // Bad Registration
            $user = $this->object->register('nouser', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertEquals('Unable to Register User', $user->getMessage());
            } else {
                $this->fail("User : Bad Registration Failed");
            }


            // Valid registration
            $user = $this->object->register(
                'settingsuser',
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $this->assertTrue($user);
                $this->assertEquals('Dusk', $this->object->getUserTheme());
                $this->assertFalse($this->object->getUserZoom());
                $this->assertEquals('2', $this->object->getDisplayRows());
            }


            // Valid registration with new style Dir
            $styleDir = __DIR__ . "/../_data/style/";
            $this->object->setDefaultStyleDir($styleDir);
            $user = $this->object->register(
                'settingsuser',
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $this->assertTrue($user);
                $this->assertEquals('text', $this->object->getUserTheme());
                $this->assertFalse($this->object->getUserZoom());
                $this->assertEquals('2', $this->object->getDisplayRows());
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
            $user = $this->object3->register('mock', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertEquals('Unable to Register User', $user->getMessage());
            } else {
                $this->fail("User : Bad Registration Failed");
            }


            // Set up Valid Data for login that fails Registration
            $this->mockdatabaseconnection->getDataDriver()->control($functions, $testdata);

            $user = $this->object3->register('mock', $this->config->read('pref'));
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

            $user = $this->object3->register('mock', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->assertEquals('Unable to Register User', $user->getMessage());
            } else {
                $this->fail("User : Failed test when update last seen date");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that a registered user's username can be returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testGetUserName()
    {
        if ($this->databaseconnection == true) {
            // Valid registration
            $user = $this->object->register('phpunit', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $username = $this->object->getUserName();
                $this->assertEquals("phpunit", $username);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that registered user's Real Name can be returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testGetRealName()
    {
        if ($this->databaseconnection == true) {
            // Valid registration
            $user = $this->object->register('phpunit', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $realname = $this->object->getRealName();
                $this->assertEquals("Phpunit User", $realname);

                $realname = $this->object->getRealName("New Name");
                $this->assertEquals("New Name", $realname);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that registered user's userid can be returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testGetUserId()
    {
        if ($this->databaseconnection == true) {
            $actualuserid = 2;
            // Valid registration
            $user = $this->object->register('phpunit', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $userid = $this->object->getUserId();
                $this->assertEquals($actualuserid, $userid);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that registered user's e-mail can be returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testGetUserEmail()
    {
        if ($this->databaseconnection == true) {
            // Valid registration
            $user = $this->object->register('phpunit', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $useremail = $this->object->getUserEmail();
                $this->assertEquals('phpunit@example.com', $useremail);
                $useremail = $this->object->getUserEmail('phpunit@example.co.uk');
                $this->assertEquals('phpunit@example.co.uk', $useremail);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test if a registered user's password is valid
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testCheckPasswd()
    {
        if ($this->databaseconnection == true) {
            $user = $this->object->register('phpunit', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $passwdok = $this->object->checkPasswd('phpUnit1');
                $this->assertTrue($passwdok);
                $passwdok = $this->object->checkPasswd('dummy');
                $this->assertFalse($passwdok);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that registered user's theme can be returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testGetUserTheme()
    {
        if ($this->databaseconnection == true) {
            $user = $this->object->register('phpunit', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $theme = $this->object->getUserTheme();
                $this->assertEquals('Dusk', $theme);
                $theme = $this->object->getUserTheme('Blue');
                $this->assertEquals('Blue', $theme);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that registered user's Zoom can be returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testGetUserZoom()
    {
        if ($this->databaseconnection == true) {
            $user = $this->object->register('phpunit', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $zoom = $this->object->getUserZoom();
                $this->assertTrue($zoom);
                $zoom = $this->object->getUserZoom(false, true);
                $this->assertFalse($zoom);
                $zoom = $this->object->getUserZoom(true);
                $this->assertFalse($zoom);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that registered user's Display Rows can be returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testGetDisplayRows()
    {
        if ($this->databaseconnection == true) {
            $user = $this->object->register('phpunit', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $rows = $this->object->getDisplayRows();
                $this->assertEquals(2, $rows);
                 $rows = $this->object->getDisplayRows(4);
                $this->assertEquals(4, $rows);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that the data a user's last login can be returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testGetLastSeenDateLogin()
    {
        if ($this->databaseconnection == true) {
            // Check a date is returned
            $user = $this->object->login(
                'phpunit',
                'phpUnit1',
                1,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("GetLastSeenDate : " . $user->getMessage());
            } else {
                 $this->assertTrue(strlen($this->object->getLastSeenDate()) == 19);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that the data a registered user's last seen date is returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testGetLastSeenDateregister()
    {
        if ($this->databaseconnection == true) {
            // Check no date is returned
            $user = $this->object->register('phpunit', $this->config->read('pref'));
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("User : " . $user->getMessage());
            } else {
                $this->assertTrue(strlen($this->object->getLastSeenDate()) == 0);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test the getPasswdAge Function when the user logs in
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testGetPasswdAgeMsg()
    {
        if ($this->databaseconnection == true) {
            // Check a date is returned
            $user = $this->object->login(
                'passwduser2',
                'passwduser2',
                2,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("GetPasswdAgeMessage: " . $user->getMessage());
            } else {
                 $this->assertStringContainsString("5 days.", $this->object->getPasswdAgeMsg());
            }

            $user = $this->object->login(
                'passwduser2',
                'passwduser2',
                3,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("GetPasswdAgeMessage: " . $user->getMessage());
            } else {
                 $this->assertEquals('', $this->object->getPasswdAgeMsg());
            }

            $user = $this->object->login(
                'passwduser2',
                'passwduser2',
                4,
                $this->config->read('pref')
            );
            if (\g7mzr\webtemplate\general\General::isError($user)) {
                $this->fail("GetPasswdAgeMessage: " . $user->getMessage());
            } else {
                 $this->assertEquals('', $this->object->getPasswdAgeMsg());
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
}
