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
use g7mzr\db\DBManager;

/**
 * Plugin Class Unit Tests
 *
 **/
class PluginTest extends TestCase
{
    /**
     * Plugin Class
     *
     * @var \g7mzr\webtemplate\application\Plugin
     */
    protected $object;

    /*
     * Application class
     *
     * @var \g7mzr\webtemplate\application\Application
     */
    protected $app;

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

        $this->app = new \g7mzr\webtemplate\application\Application();

        // Initalise the Plugin Class.  This will also initalise all active plugins
        $plugindir = dirname(dirname(__DIR__)) . "/plugins";
        try {
            $this->object = new \g7mzr\webtemplate\application\Plugin($plugindir, $this->app);
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
        $version = $this->object->getPluginVersionInformation();
        $this->assertTrue(array_key_exists("Example", $version));
        $this->assertEquals("Example Plugin", $version['Example']["name"]);
        $this->assertEquals("0.1.0", $version['Example']["version"]);
    }

    /**
     * This function tests to see if the plugin version string can be obtained
     *
     * @group unittest
     * @group application
     *
     * @return void
     */
    public function testGetPluginVersionString()
    {
        $version = $this->object->getPluginVersionStr();
        $this->assertTrue(array_key_exists("Example", $version));
        $this->assertStringContainsString("Name: Example Plugin Version: 0.1.0", $version['Example']);
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
    public function testProcessHook()
    {
        $hookdata = array();
        $hookname = "hookAboutDisplay";
        $this->object->processHook($hookname, $hookdata);
        $result = $this->app->tpl()->getTemplateVars();
        $this->assertTrue(array_key_exists("REGISTEREDUSERS", $result));
        $this->assertEquals(10, $result["REGISTEREDUSERS"]);
    }
}
