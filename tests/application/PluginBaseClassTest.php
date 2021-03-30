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
use g7mzr\webtemplate\application\SmartyTemplate;
use g7mzr\db;
use \g7mzr\webtemplate\application\plugins\PluginConst;

/**
 * Plugin Base Class Unit Tests called using Example Plugin
 *
 **/
class PluginBaseClassTest extends TestCase
{
    /**
     * Plugin Class
     *
     * @var\g7mzr\webtemplate\plugins\Example\Plugin
     */
    protected $object;

    /**
     * Application Class
     *
     * @var \g7mzr\webtemplate\application\Application
     */
    protected $app = null;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        global $sessiontest, $testdsn;

        // Stop Sessions Setting Cookies during this test
        $sessiontest = array(true);

        // Initalise the Application Class.
        $this->app = new \g7mzr\webtemplate\application\Application();

        // Initalise the Example Plugin
        try {
            $this->object = new \g7mzr\webtemplate\plugins\Example\Plugin($this->app);
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
    }

    /**
     * This function tests to see if the plugin version information can be obtained
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testGetPluginVersion()
    {
        $version = $this->object->getVersionInformation();
        //$this->assertTrue(array_key_exists("Example", $version));
        $this->assertEquals("Example Plugin", $version["name"]);
        $this->assertEquals("0.1.0", $version["version"]);
    }

    /**
     * This function tests to see if the plugin name and version string can be obtained
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testGetNameAndVersionString()
    {
        $version = $this->object->getVersionInformation(PluginConst::GET_PLUGIN_VERSION_STRING);
        $this->assertStringContainsString("Name: Example Plugin Version: 0.1.0", $version);
    }

    /**
     * This function tests to see if the plugin name string can be obtained
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testGetNameString()
    {
        $version = $this->object->getVersionInformation(PluginConst::GET_PLUGIN_NAME);
        $this->assertStringContainsString("Example", $version);
    }

    /**
     * This function tests to see if the plugin version string can be obtained
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testGetVersionString()
    {
        $version = $this->object->getVersionInformation(PluginConst::GET_PLUGIN_VERSION);
        $this->assertStringContainsString("0.1.0", $version);
    }


    /**
     * This function tests to see if an error is thrown if an invalid option is chosed
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testGetVersionInvalidOption()
    {
        try {
            $version = $this->object->getVersionInformation(100);
            $this->fail("getVersionInformation invald option did not throw an error");
        } catch (\Throwable $ex) {
            $this->assertStringContainsString(
                "Plugin Version Information: Invalid Option",
                $ex->getMessage()
            );
        }
    }

    /**
     * This function tests that the file name of the DB Schema can be returned from
     * the plugin.  The test is to see if the base names match.
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testGetSchemaFileName()
    {
        $result = $this->object->getDBSchema();
        $this->assertEquals("schema.json", basename($result));
    }

    /**
     * This function tests that the file name of the DB Schema can be returned from
     * the plugin.  The test is to see if the base names match.
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testDBDataFileName()
    {
        $result = $this->object->getDBData();
        $this->assertEquals("", basename($result));
    }

    /**
     * This function tests that hooks can be processed
     *
     * This function tests that hooks can be processed by using the "hookAboutDisplay"
     * Hook.  No data is passed to this hook.  Confirmation that the hook is run is
     * obtained by checking in to Smarty Template Variables have been assigned
     * 1. REGISTEREDUSERS value = 10

     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testHookAboutDisplay()
    {
        $hookdata = array();
        $hookname = "hookAboutDisplay";
        $this->object->hookAboutDisplay($hookdata);
        $result = $this->app->tpl()->getTemplateVars();
        $this->assertTrue(array_key_exists("REGISTEREDUSERS", $result));
        $this->assertEquals(10, $result["REGISTEREDUSERS"]);
    }
}
