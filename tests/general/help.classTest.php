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

// Include the General Code  Class
require_once dirname(__FILE__) . '/../../includes/general/help.class.php';

/**
 * Help Class Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class HelpTest extends TestCase
{
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
     * Test that the correct help pages are returned using the help map.
     *
     * @group unittest
     * @group general
     *
     * @return null
     */
    public function testHelp()
    {
        global $helpMap;
        $this->assertEquals($helpMap['index']['url'], "index.html");
        $this->assertEquals($helpMap['using']['url'], "using.html");
    }
}
