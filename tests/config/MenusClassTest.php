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
 * Configure Class Unit Tests
 *
 **/
class MenusClassTest extends TestCase
{
    /**
     * Blank Class Object
     *
     * @var\g7mzr\webtemplate\config\Menus
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

        // Create configuration Object
        $configDir = __DIR__ . "/../../configs";
        $this->object = new\g7mzr\webtemplate\config\Menus($configDir);
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
     * Test Main Menu can be read
     *
     * @group unittest
     * @group menus
     *
     * @return void
     */
    public function testMainMenu()
    {
        $mainmenu = $this->object->readMenu("mainmenu");
        $this->assertEquals(count($mainmenu), 4);
        $this->assertEquals($mainmenu['home']['name'], "Home");
        $this->assertTrue($mainmenu['pref']['loggedin']);
        $this->assertFalse($mainmenu['logout']['admin']);
    }

    /**
     * Test that a blank array is returned if an invalid menu name is used
     *
     * @group unittest
     * @group menus
     *
     * @return void
     */
    public function testInvalidMenu()
    {
        $mainmenu = $this->object->readMenu("invalid");
        $this->assertTrue(is_array($mainmenu));
        $this->assertEquals(count($mainmenu), 0);
    }
}
