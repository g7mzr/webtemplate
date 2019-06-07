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

namespace webtemplate\unittest;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// Include the Test Database Connection details
require_once __DIR__  . '/../_data/database.php';

use PHPUnit\Framework\TestCase;

/**
 * WebTemplate Common Class Unit Tests
 *
 **/
class WebTemplateCommonTest extends TestCase
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
        $this->object = new \webtemplate\application\SmartyTemplate();
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
     * Test that the DSN can be loaded with the database connection information
     * from the SMARTY Config File.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testLoadDSN()
    {
        $dsn = array();
        \webtemplate\application\WebTemplateCommon::loadDSN($this->object, $dsn);
        $this->assertEquals($dsn["phptype"], 'pgsql');
    }

    /**
     * Test that the database name can be extracted from the SMARTY Config File
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testgetDatabaseName()
    {
        global $testdsn;
        $dbname = $testdsn["database"];
        $result = \webtemplate\application\WebTemplateCommon::getDatabaseName(
            $this->object
        );
        $this->assertEquals($dbname, $result);
    }
}
