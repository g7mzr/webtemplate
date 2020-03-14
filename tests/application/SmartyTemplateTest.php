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

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

use PHPUnit\Framework\TestCase;

/**
 * SmartyClass Unit Tests
 *
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
     * @return void
     */
    protected function setUp(): void
    {
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
     * Test that a SMARTY Instance can be created
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testSmartyConstructor()
    {
        $tpl = new\g7mzr\webtemplate\application\SmartyTemplate();
        $this->assertNotNUll($tpl);
        $this->assertTrue(is_a($tpl, '\g7mzr\webtemplate\application\SmartyTemplate'));
    }
}
