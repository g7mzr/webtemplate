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

// Load the Test Database Configuration File
require_once dirname(__FILE__) .'/../_data/database.php';

/**
 * Blank Class Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class BlankClassTest extends TestCase
{
    /**
     * Blank Class Object
     *
     * @var webtemplate\blank\Blank
     */
    protected $object;


    /**
     * Mock Database Driver Class
     *
     * @var Database Connection object
     */
    protected $mockDB;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        global $testdsn, $options;


        // Create a Mock database object
        $testdsn['phptype'] = 'mock';
        $this->mockDB = \webtemplate\db\DB::load($testdsn);

        // Create Group Obkect Using the mockDB obkect
        $this->object = new \webtemplate\blank\Blank($this->mockDB);
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
     * Test that the change string can be returned
     *
     * @group unittest
     * @group blank
     *
     * @return null
     */
    public function testGetChangeString()
    {
        $msg = $this->object->getChangeString();
        $this->assertEquals('', $msg);
    }

    /**
     * Test if the data has been changed from previously stored
     *
     * @group unittest
     * @group blank
     *
     * @return null
     */
    public function testDataChanged()
    {
        $Id = 1;
        $datatosave = array("testdata");
        $result = $this->object->dataChanged($Id, $datatosave);
        $this->assertTrue($result);
    }

    /**
     * Test that records can be returned
     *
     * @group unittest
     * @group blank
     *
     * @return null
     */
    public function testFetch()
    {
        $Id = 1;
        $result = $this->object->fetch($Id);
        $this->assertEquals("fetcheddata", $result[0]);
    }


    /**
     * Test if the data can be saved
     *
     * @group unittest
     * @group blank
     *
     * @return null
     */
    public function testDataSaved()
    {
        $Id = 1;
        $datatosave = array("testdata");
        $result = $this->object->save($Id, $datatosave);
        $this->assertTrue($result);
    }


    /**
     * Test if the data has been changed from previously stored
     *
     * @group unittest
     * @group blank
     *
     * @return null
     */
    public function testSearch()
    {
        $searchdata = array("testdata");
        $result = $this->object->search($searchdata);
        $this->assertTrue($result);
    }

    /**
     * Test if the data has been changed from previously stored
     *
     * @group unittest
     * @group blank
     *
     * @return null
     */
    public function testValidateData()
    {
        $inputdata = array("testdata");
        $result = $this->object->validate($inputdata);
        $this->assertTrue($result);
    }
}
