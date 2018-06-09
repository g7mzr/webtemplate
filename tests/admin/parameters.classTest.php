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
 * Parameters Class Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class ParametersTest extends TestCase
{
    /**
     * Parameters Class Object
     *
     * @var ParametersClass
     */
    protected $object;

    /**
     * Configuration Object
     *
     * @var \webtemplate\config\Configure
     */
    protected $confobj;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {

        // Create configuration Object
        $configDir = __DIR__ . "/../../configs";
        $this->confobj = new \webtemplate\config\Configure($configDir);

        // Create the Parameters Class
        $this->object = new \webtemplate\admin\Parameters($this->confobj);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
    }

    /**
     * This function tests that a valid section can be selected
     *
     * @group unittest
     * @group admin
     *
     * @return null
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
     * @return null
     */
    public function testSetSectionFail()
    {
        $classname = "\\webtemplate\\application\\exceptions\\AppException";
        try {
            $this->object->setSection('faulty');
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertContains(
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
     * @return null
     */
    public function testGetLastMessageFail()
    {
        $classname = "\\webtemplate\\application\\exceptions\\AppException";
        try {
            $this->object->getLastMsg();
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertContains(
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
     * @return null
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
     * @return null
     */
    public function testGetCurrentParametersFail()
    {
        $classname = "\\webtemplate\\application\\exceptions\\AppException";
        try {
            $this->object->getCurrentParameters();
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertContains(
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
     * @return null
     */
    public function testsaveParamFileFail()
    {
        $classname = "\\webtemplate\\application\\exceptions\\AppException";
        try {
            $this->object->saveParamFile('dummy');
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertContains(
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
     * @return null
     */
    public function testvalidateParametersFail()
    {
        $classname = "\\webtemplate\\application\\exceptions\\AppException";
        try {
            $data = array("test"=>"one");
            $this->object->validateParameters($data);
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertContains(
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
     * @return null
     */
    public function testcheckParametersChangedFail()
    {
        $classname = "\\webtemplate\\application\\exceptions\\AppException";
        try {
            $data = array("test"=>"one");
            $this->object->checkParametersChanged($data);
            $this->fail(__Function__ . "No exeption thrown");
        } catch (\throwable $ex) {
            $this->assertTrue(is_a($ex, $classname));
            $this->assertContains(
                "Invalid Class.",
                $ex->getMessage()
            );
        }
    }
}
