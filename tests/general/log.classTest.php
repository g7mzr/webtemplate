<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\unittest;

use PHPUnit\Framework\TestCase;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

/**
 * LogClass Class Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class LogTest extends TestCase
{
    /**
     * Log Objects
     *
     * @var Log Objects
     */
    protected $dailyobject;
    protected $Weeklyobject;
    protected $monthlybject;

    /**
     * Log file names
     *
     * @var Logfile Names
     */
    protected $dailyName;
    protected $weeklyName;
    protected $monthlyName;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        $this->dailyobject = new \webtemplate\general\Log(5, 1);
        $this->weeklyobject = new \webtemplate\general\Log(5, 2);
        $this->monthlyobject = new \webtemplate\general\Log(5, 3);
        $_SERVER["REMOTE_ADDR"] = '10.1.1.1';

        $dirName =  dirname(__FILE__) . "/../../logs/";
        $this->dailyName = $dirName . "daily-" . date("Ymd_", time()) . "main.log";
        $this->weeklyName = $dirName ."weekly-" .date("YW_", time()) . "main.log";
        $this->monthlyName = $dirName ."monthly-".date("Ym_", time()) . "main.log";
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
        $dirName = dirname(__FILE__) . "/../../logs/";
        array_map('unlink', glob($dirName . "*.log"));
    }

    /**
     * Test that the Error Files are created
     *
     * @group unittest
     * @group general
     *
     * @return null
     */
    public function testerror()
    {
        $result = $this->dailyobject->error("Daily Error Test Data");
        $this->assertTrue($result);
        $this->assertFileExists($this->dailyName);
        $result = $this->weeklyobject->error("Weekly Error Test Data");
        $this->assertTrue($result);
        $this->assertFileExists($this->weeklyName);
        $result = $this->monthlyobject->error("Monthly Error Test Data");
        $this->assertTrue($result);
        $this->assertFileExists($this->monthlyName);
    }

     /**
     * Test Warning messages are logged
     *
     * @group unittest
     * @group general
     *
     * @return null
     */
    public function testWarn()
    {
        $result = $this->dailyobject->warn("Warn Test Data");
        $this->assertTrue($result);
    }

     /**
     * Test Information Messages are logged
     *
     * @group unittest
     * @group general
     *
     * @return null
     */
    public function testIinfo()
    {
        $result = $this->dailyobject->Info("Info Test Data");
        $this->assertTrue($result);
    }

     /**
     * Test Debug Messages are logged
     *
     * @group unittest
     * @group general
     *
     * @return null
     */
    public function testDebug()
    {
        $result = $this->dailyobject->debug("Debug Test Data");
        $this->assertTrue($result);
    }

     /**
     * Test Trace Messages are logged
     *
     * @group unittest
     * @group general
     *
     * @return null
     */
    public function testTrace()
    {
        $result = $this->dailyobject->trace("Trace Test Data");
        $this->assertTrue($result);
    }

     /**
     * Test Security Messages are logged
     *
     * @group unittest
     * @group general
     *
     * @return null
     */
    public function testSecurity()
    {
        $result = $this->dailyobject->Security("Security Test Data");
        $this->assertTrue($result);
    }
}
