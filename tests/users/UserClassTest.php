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

/**
 * UserClass Unit Tests
 *
 **/
class UserClassTest extends TestCase
{
    /**
     * User Class Object
     *
     * @var \g7mzr\webtemplate\users\User
     */
    protected $user;


    /**
     * User Data array
     *
     * @var array
     */
    protected $userData = array();

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        // Create the user data for loading into the user class
        $this->userData = array();
        $this->userData['userName'] = "phpunit";
        $this->userData['userId'] = 2;
        $this->userData['realName'] = 'Phpunit User';
        $this->userData['userEmail'] = 'phpunit@example.com';
        $this->userData['displayRows'] = 2;

        $this->userData['passwdAgeMsg'] = '5 days.';
        $this->userData['theme'] = 'Dusk';
        $this->userData['zoomText'] = true;
        $this->userData['permissions'] = array();
        $this->userData['encryptedPasswd'] = \g7mzr\webtemplate\general\General::encryptPasswd("phpUnit1");

        $testName =  $this->getName();
        if ($testName == 'testGetLastSeenDateLogin') {
            $this->userData['last_seen_date'] = '2018-09-08 20:47:52';
        } else {
            $this->userData['last_seen_date'] = '';
        }
        // Create a new User Object
        $this->user = new \g7mzr\webtemplate\users\User();
        $this->user->loadUserData($this->userData);
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
     * Test that a registered user's username can be returned
     *
     * @group unittest
     * @group users
     *
     * @return void
     */
    public function testGetUserName()
    {
        $username = $this->user->getUserName();
        $this->assertEquals("phpunit", $username);
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
        $realname = $this->user->getRealName();
        $this->assertEquals("Phpunit User", $realname);

        $realname = $this->user->getRealName("New Name");
        $this->assertEquals("New Name", $realname);
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
        $actualuserid = 2;
        $userid = $this->user->getUserId();
        $this->assertEquals($actualuserid, $userid);
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
        $origuseremail = $this->user->getUserEmail();
        $this->assertEquals('phpunit@example.com', $origuseremail);

        $newuseremail = $this->user->getUserEmail('phpunit@example.co.uk');
        $this->assertEquals('phpunit@example.co.uk', $newuseremail);
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
        $passwdok = $this->user->checkPasswd('phpUnit1');
        $this->assertTrue($passwdok);
        $passwdfail = $this->user->checkPasswd('dummy');
        $this->assertFalse($passwdfail);
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
        $origTheme = $this->user->getUserTheme();
        $this->assertEquals('Dusk', $origTheme);
        $newTheme = $this->user->getUserTheme('Blue');
        $this->assertEquals('Blue', $newTheme);
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
        $origZoom = $this->user->getUserZoom();
        $this->assertTrue($origZoom);
        $newZoom = $this->user->getUserZoom(false, true);
        $this->assertFalse($newZoom);
        $noChangeZoom = $this->user->getUserZoom(true);
        $this->assertFalse($noChangeZoom);
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
        $origRows = $this->user->getDisplayRows();
        $this->assertEquals(2, $origRows);
        $newRows = $this->user->getDisplayRows(4);
        $this->assertEquals(4, $newRows);
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
        $lastSeenDate = $this->user->getLastSeenDate();
        $this->assertTrue(strlen($lastSeenDate) == 19);
        $this->assertEquals("2018-09-08 20:47:52", $lastSeenDate);
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
        $lastSeenDate = $this->user->getLastSeenDate();
        $this->assertTrue(strlen($lastSeenDate) == 0);
        $this->assertEquals('', $lastSeenDate);
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
        $this->assertStringContainsString("5 days.", $this->user->getPasswdAgeMsg());
    }
}
