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

namespace webtemplate\phpunit;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// Include the Test Database Connection details
require_once dirname(__FILE__) . '/../_data/database.php';

use PHPUnit\Framework\TestCase;

// Define Application Name and Versions for test
DEFINE("APPNAME", "Web Database Skeleton");
DEFINE("APPVERSION", "0.5.0+");
DEFINE("APIVERSION", "0.1.0");
DEFINE("LANGUAGE", "en");

/**
 * Application Class Unit Tests
 *
 **/
class ApplicationTest extends TestCase
{
    /**
     * Session Class
     *
     * @var \webtemplate\application\Application
     */
    protected $object;


    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        global $sessiontest;

        // Stop Sessions Setting Cookies during this test
        $sessiontest = array(true);

        try {
            $this->object = new \webtemplate\application\Application();
        } catch (\Throwable $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $this->object->session()->destroy();
    }


    /**
     * This function tests that the Application Name is returned
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testApplicationName()
    {
        $result = $this->object->appname();
        $this->assertEquals(APPNAME, $result);
    }

    /**
     * This function tests that the Application Name is returned
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testApplicationVersion()
    {
        $result = $this->object->appversion();
        $this->assertEquals(APPVERSION, $result);
    }

    /**
     * This function tests that the Application Name is returned
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testAPIVersion()
    {
        $result = $this->object->apiversion();
        $this->assertEquals(APIVERSION, $result);
    }

    /**
     * This function tests that the Application Name is returned
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testLanguage()
    {
        $result = $this->object->language();
        $this->assertEquals(LANGUAGE, $result);
    }

    /**
     * This function tests the production flag
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testProduction()
    {
        $result = $this->object->production();
        $this->assertFalse($result);
    }

    /**
     * This function tests that a config Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testConfigObject()
    {
        $result = is_a(
            $this->object->config(),
            '\webtemplate\config\Configure'
        );
        $this->assertTrue($result);
    }

    /**
     * This function tests that a DB Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testDBObject()
    {
        $result = is_a(
            $this->object->db(),
            'webtemplate\db\DatabaseDriverpgsql'
        );
        $this->assertTrue($result);
    }

    /**
     * This function tests that a editUser Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testEditUserobject()
    {
        $result = is_a(
            $this->object->edituser(),
            '\webtemplate\users\editUser'
        );
        $this->assertTrue($result);
    }

    /**
     * This function tests that a editusersgroups Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testEditUsersGroupsObject()
    {
        $result = is_a(
            $this->object->editusersgroups(),
            '\webtemplate\groups\EditUsersGroups'
        );
        $this->assertTrue($result);
    }


    /**
     * This function tests that a log Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testLogObject()
    {
        $result = is_a(
            $this->object->log(),
            '\webtemplate\general\Log'
        );
        $this->assertTrue($result);
    }

    /**
     * This function tests that a Session Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testSessionObject()
    {
        $result = is_a(
            $this->object->session(),
            '\webtemplate\application\Session'
        );
        $this->assertTrue($result);
    }


    /**
     * This function tests that a TPL Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testTPLObject()
    {
        $result = is_a(
            $this->object->tpl(),
            '\webtemplate\application\SmartyTemplate'
        );
        $this->assertTrue($result);
    }

    /**
     * This function tests that a user Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testUserObject()
    {
        $result = is_a(
            $this->object->user(),
            '\webtemplate\users\user'
        );
        $this->assertTrue($result);
    }


    /**
     * This function tests that a usersgroups Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testUsersGroupsObject()
    {
        $result = is_a(
            $this->object->usergroups(),
            '\webtemplate\users\Groups'
        );
        $this->assertTrue($result);
    }

        /**
     * This function tests that a editgroups Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testEditGroupsObject()
    {
        $result = is_a(
            $this->object->editgroups(),
            '\webtemplate\groups\EditGroups'
        );
        $this->assertTrue($result);
    }

    /**
     * This function tests that a Parameters Object Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testParametersObject()
    {
        $result = is_a(
            $this->object->parameters(),
            '\webtemplate\admin\Parameters'
        );
        $this->assertTrue($result);
    }

    /**
     * This function tests that a Preferences Object Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testPreferencesObject()
    {
        $result = is_a(
            $this->object->preferences(),
            '\webtemplate\admin\Preferences'
        );
        $this->assertTrue($result);
    }

    /**
     * This function tests that a Mail Object Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testMailObject()
    {
        $result = is_a(
            $this->object->mail(),
            '\webtemplate\general\Mail'
        );
        $this->assertTrue($result);
    }

    /**
     * This function tests that a Tokens Object Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testTokensObject()
    {
        $result = is_a(
            $this->object->tokens(),
            '\webtemplate\general\Tokens'
        );
        $this->assertTrue($result);
    }

    /**
     * This function tests that a edituserpref Object Exists
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testEditUserPrefObject()
    {
        $result = is_a(
            $this->object->edituserpref(),
            '\webtemplate\users\EditUserPref'
        );
        $this->assertTrue($result);
    }

    /**
     * This function tests that a username can be retrieved from the session
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testUserRetrieved()
    {
        global $sessiontest;

        $this->object->session()->createSession('phpunit', '2', false);

        // Reuse the session variable created in the earler test
        $_COOKIE['webdatabase'] = $sessiontest['value'];

        $testapp = new \webtemplate\application\Application();
        $username = $testapp->session()->getUserName();
        $this->assertEquals("phpunit", $username);
    }
}
