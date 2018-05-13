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

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

use PHPUnit\Framework\TestCase;

/**
 * SmartyClass Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class SmartyTemplateTest extends TestCase
{
    /**
     * Smarty Object
     *
     * @var Smarty Object
     */
    protected $object;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
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
     * Test that a SMARTY Instance can be created
     *
     * @group unittest
     * @group general
     *
     * @return null
     */
    public function testSmartyConstructor()
    {
        $tpl = new \webtemplate\application\SmartyTemplate();
        $this->assertNotNUll($tpl);
        $this->assertTrue(is_a($tpl, '\webtemplate\application\SmartyTemplate'));
    }
}
