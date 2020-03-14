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

// Include the General Code  Class
require_once dirname(__FILE__) . '/../../includes/general/help.class.php';

/**
 * Help Class Unit Tests
 *
 **/
class HelpTest extends TestCase
{
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
     * Test that the correct help pages are returned using the help map.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testHelp()
    {
        global $helpMap;
        $this->assertEquals($helpMap['index']['url'], "index.html");
        $this->assertEquals($helpMap['using']['url'], "using.html");
    }
}
