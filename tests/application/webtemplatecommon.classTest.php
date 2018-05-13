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

// Include the Test Database Connection details
require_once __DIR__  .'/../_data/database.php';

use PHPUnit\Framework\TestCase;

/**
 * WebTemplateClass Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
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
     * @return null No return data
     */
    protected function setUp()
    {
        $this->object = new \webtemplate\application\SmartyTemplate();
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
     * Test that the DSN can be loaded with the database connection information
     * from the SMARTY Config File.
     *
     * @group unittest
     * @group general
     *
     * @return null
     */
    public function testLoadDSN()
    {
        \webtemplate\application\WebTemplateCommon::loadDSN($this->object, $dsn);
        $this->assertEquals($dsn["phptype"], 'pgsql');
    }

    /**
     * Test that the database name can be extracted from the SMARTY Config File
     *
     * @group unittest
     * @group general
     *
     * @return null
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
