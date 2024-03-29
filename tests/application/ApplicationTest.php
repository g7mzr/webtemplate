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

namespace g7mzr\webtemplate\phpunit;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// Include the Test Database Connection details
require_once dirname(__FILE__) . '/../_data/database.php';

use PHPUnit\Framework\TestCase;

// Define Application Name and Versions for test
DEFINE("APPNAME", "Web Database Skeleton");
DEFINE("APPSHORTNAME", "Webtemplate");
DEFINE("APPVERSION", "1.0.0-dev");
DEFINE("APIVERSION", "0.1.0");
DEFINE("LANGUAGE", "en");
DEFINE("UPDATESERVER", "https://admin.starfleet.g7mzr.ampr.org/");

/**
 * Application Class Unit Tests
 *
 **/
class ApplicationTest extends TestCase
{
    /**
     * Session Class
     *
     * @var \g7mzr\webtemplate\application\Application
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
            $this->object = new \g7mzr\webtemplate\application\Application();
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
     * This function tests that the Application Short Name is returned
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testApplicationShortName()
    {
        $result = $this->object->appshortname();
        $this->assertEquals(APPSHORTNAME, $result);
    }

    /**
     * This function tests that the Application Version is returned
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
     * This function tests that the API Version is returned
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
            '\g7mzr\webtemplate\config\Configure'
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
            '\g7mzr\db\interfaces\InterfaceDatabaseDriver'
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
            '\g7mzr\webtemplate\users\editUser'
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
            '\g7mzr\webtemplate\groups\EditUsersGroups'
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
            '\g7mzr\webtemplate\general\Log'
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
            '\g7mzr\webtemplate\application\Session'
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
            '\g7mzr\webtemplate\application\SmartyTemplate'
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
            '\g7mzr\webtemplate\users\User'
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
            '\g7mzr\webtemplate\users\Groups'
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
            '\g7mzr\webtemplate\groups\EditGroups'
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
            '\g7mzr\webtemplate\admin\Parameters'
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
            '\g7mzr\webtemplate\admin\Preferences'
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
            '\g7mzr\webtemplate\general\Mail'
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
            '\g7mzr\webtemplate\general\Tokens'
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
            '\g7mzr\webtemplate\users\EditUserPref'
        );
        $this->assertTrue($result);
    }

    /**
     * This function test that a plugin object can be created
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testPluginObject()
    {
        $result = is_a(
            $this->object->plugin(),
            '\g7mzr\webtemplate\application\Plugin'
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

        $testapp = new \g7mzr\webtemplate\application\Application();
        $username = $testapp->session()->getUserName();
        $this->assertEquals("phpunit", $username);
    }

    /**
     * This function tests that the update server name is returned
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testUpdateServer()
    {
        $server_name = $this->object->updateserver();
        $this->assertEquals($server_name, UPDATESERVER);
    }
}
