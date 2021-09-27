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

// Load the Test Database Configuration File
require_once dirname(__FILE__) . '/../_data/database.php';

/**
 * Parameters Class Unit Tests
 *
 **/
class ParametersTest extends TestCase
{
    /**
     * Parameters Class Object
     *
     * @var\g7mzr\webtemplate\admin\Parameters
     */
    protected $object;

    /**
     * Configuration Object
     *
     * @var\g7mzr\webtemplate\config\Configure
     */
    protected $confobj;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {

        // Create configuration Object
        global $testdsn;
        // Check that we can connect to the database
        try {
            $db = new \g7mzr\db\DBManager(
                $testdsn,
                $testdsn['username'],
                $testdsn['password']
            );
            $setresult = $db->setMode("datadriver");
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

        // Create configuration Object
        $this->confobj = new\g7mzr\webtemplate\config\Configure($db->getDataDriver());

        // Create a menus object
        $configDir = __DIR__ . "/../../configs";
        $menus = new \g7mzr\webtemplate\config\Menus($configDir);

        // Create the Parameters Class
        $this->object = new \g7mzr\webtemplate\admin\Parameters($this->confobj, $menus);
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
     * This function tests that a valid section can be selected
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testSetSection()
    {
        $result = $this->object->setSection('admin');
        $this->assertTrue($result);
    }

    /**
     * This function tests that an error is thrown if an invalid section is chosen
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testSetSectionFail()
    {
        $classname = "\\g7mzr\\webtemplate\\application\\exceptions\\AppException";
        try {
            $this->object->setSection('faulty');
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertStringContainsString(
                "Invalid Parameter Section: faulty",
                $ex->getMessage()
            );
        }
    }

    /**
     * This function tests that an error is thrown if getLastMessage is
     * called prior to a section being chosen
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testGetLastMessageFail()
    {
        $classname = "\\g7mzr\\webtemplate\\application\\exceptions\\AppException";
        try {
            $this->object->getLastMsg();
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertStringContainsString(
                "Invalid Class.",
                $ex->getMessage()
            );
        }
    }

    /**
     * This function tests to see that the page list is returned
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testGetPageList()
    {
        $pagelist = $this->object->getPageList();
        $this->assertTrue(array_key_exists('required', $pagelist));
        $this->assertTrue(array_key_exists('admin', $pagelist));
        $this->assertTrue(array_key_exists('auth', $pagelist));
        $this->assertTrue(array_key_exists('email', $pagelist));
    }

    /**
     * This function tests that an error is thrown if getCurrentParameters is
     * called prior to a section being chosen
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testGetCurrentParametersFail()
    {
        $classname = "\\g7mzr\\webtemplate\\application\\exceptions\\AppException";
        try {
            $this->object->getCurrentParameters();
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertStringContainsString(
                "Invalid Class.",
                $ex->getMessage()
            );
        }
    }

    /**
     * This function tests that an error is thrown if saveParamFile is
     * called prior to a section being chosen
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testsaveParamFileFail()
    {
        $classname = "\\g7mzr\\webtemplate\\application\\exceptions\\AppException";
        try {
            $this->object->saveParamFile('dummy');
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertStringContainsString(
                "Invalid Class.",
                $ex->getMessage()
            );
        }
    }

    /**
     * This function tests that an error is thrown if validateParameters is
     * called prior to a section being chosen
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testvalidateParametersFail()
    {
        $classname = "\\g7mzr\\webtemplate\\application\\exceptions\\AppException";
        try {
            $data = array("test" => "one");
            $this->object->validateParameters($data);
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertStringContainsString(
                "Invalid Class.",
                $ex->getMessage()
            );
        }
    }

    /**
     * This function tests that an error is thrown if checkParametersChanged is
     * called prior to a section being chosen
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testcheckParametersChangedFail()
    {
        $classname = "\\g7mzr\\webtemplate\\application\\exceptions\\AppException";
        try {
            $data = array("test" => "one");
            $this->object->checkParametersChanged($data);
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertStringContainsString(
                "Invalid Class.",
                $ex->getMessage()
            );
        }
    }
}
