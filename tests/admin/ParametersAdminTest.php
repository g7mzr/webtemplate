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
 * Parameters Admin Class Unit Tests
 *
 **/
class ParametersAdminTest extends TestCase
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
        $this->defaultParameters();

        // Create the Parameters Class
        $configDir = __DIR__ . "/../../configs";
        $menus = new \g7mzr\webtemplate\config\Menus($configDir);
        $this->object = new\g7mzr\webtemplate\admin\Parameters($this->confobj, $menus);
        $this->object->setSection('admin');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $configDir = dirname(__FILE__) . "/../_data";
        $filename = $configDir . '/parameters.php';
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * This function sets up the default parameters for testing
     *
     * @return void
     */
    protected function defaultParameters()
    {
        // Set Up default Parameters Array for Testing.
        $this->confobj->write('param.urlbase', 'http://www.example.com');
        $this->confobj->write('param.maintainer', 'phpunit@example.com');
        $this->confobj->write('param.docbase', 'docs/');
        $this->confobj->write('param.cookiedomain', '');
        $this->confobj->write('param.cookiepath', '/');
        $this->confobj->write('param.admin.logging', '1');
        $this->confobj->write('param.admin.logrotate', '2');
        $this->confobj->write('param.admin.newwindow', true);
        $this->confobj->write('param.admin.maxrecords', '2');
        $this->confobj->write('param.users.newaccount', false);
        $this->confobj->write('param.users.newpassword', true);
        $this->confobj->write('param.users.regexp', '/^[a-zA-Z0-9]{5,12}$/');
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $this->confobj->write('param.users.regexpdesc', $regExpStr);
        $this->confobj->write('param.users.passwdstrength', '4');
        $this->confobj->write('param.users.passwdage', '1');
        $this->confobj->write('param.users.autocomplete', false);
        $this->confobj->write('param.users.autologout', '1');
        $this->confobj->write('param.email.smtpdeliverymethod', 'smtp');
        $this->confobj->write('param.email.emailaddress', 'phpunit@example.com');
        $this->confobj->write('param.email.smtpserver', 'smtp.example.com');
        $this->confobj->write('param.email.smtpusername', 'user');
        $this->confobj->write('param.email.smtppassword', 'password');
        $this->confobj->write('param.email.smtpdebug', false);
    }

    /**
     * This function tests that the last message on a new object is a blank string
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testGetLastMessage()
    {
        $result = $this->object->getLastMsg();
        $this->assertEquals("", $result);
    }


    /**
     * This function tests that the current parameters can be loaded.
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testGetCurrentParameters()
    {
        $result = $this->object->getCurrentParameters('admin');
        $this->assertEquals('http://www.example.com', $result ['urlbase']);
        $this->assertTrue($result['users']['newpassword']);
    }

    /**
     * This function tests that the ADMIN parameters cab be validated
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testvalidateParameters()
    {
        // This function always returns True.
        // It defaults to Original values if validation Fail
        // So we need to test that the values have changed

        // Need Default Values for testing
        $parameters['admin']['logging'] = '1';
        $parameters['admin']['logrotate'] = '2';
        $parameters['admin']['newwindow'] = true;
        $parameters['admin']['maxrecords'] = '2';

        // Admin Data Validates Okay
        $inputData['logging'] = '1';
        $inputData['logrotate'] = '2';
        $inputData['new_window'] = 'yes';
        $inputData['max_records'] = '2';
        $this->object->validateParameters($inputData, $parameters);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('1', $outputArray['admin']['logging']);
        $this->assertEquals('2', $outputArray['admin']['logrotate']);
        $this->assertTrue($outputArray['admin']['newwindow']);
        $this->assertEquals('2', $outputArray['admin']['maxrecords']);

        // Logging Changed
        $inputData['logging'] = '2';
        $inputData['logrotate'] = '2';
        $inputData['new_window'] = 'yes';
        $inputData['max_records'] = '2';
        $this->object->validateParameters($inputData, $parameters);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('2', $outputArray['admin']['logging']);
        $this->assertEquals('2', $outputArray['admin']['logrotate']);
        $this->assertTrue($outputArray['admin']['newwindow']);
        $this->assertEquals('2', $outputArray['admin']['maxrecords']);

        // Log Rotate Changed
        $inputData['logging'] = '10';  // Invalid Logging Number
        $inputData['logrotate'] = '3';
        $inputData['new_window'] = 'yes';
        $inputData['max_records'] = '2';
        $this->object->validateParameters($inputData, $parameters);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('2', $outputArray['admin']['logging']);
        $this->assertEquals('3', $outputArray['admin']['logrotate']);
        $this->assertTrue($outputArray['admin']['newwindow']);
        $this->assertEquals('2', $outputArray['admin']['maxrecords']);

        // New Window Changed
        $inputData['logging'] = '1';
        $inputData['logrotate'] = '6'; // Invalid Log Rotate value
        $inputData['new_window'] = 'no';
        $inputData['max_records'] = '2';
        $this->object->validateParameters($inputData, $parameters);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('1', $outputArray['admin']['logging']);
        $this->assertEquals('3', $outputArray['admin']['logrotate']);
        $this->assertFalse($outputArray['admin']['newwindow']);
        $this->assertEquals('2', $outputArray['admin']['maxrecords']);

        // Max Records Changed
        $inputData['logging'] = '1';
        $inputData['logrotate'] = '2';
        $inputData['new_window'] = 'yes';  // Invalid new Window
        $inputData['max_records'] = '4';
        $this->object->validateParameters($inputData, $parameters);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('1', $outputArray['admin']['logging']);
        $this->assertEquals('2', $outputArray['admin']['logrotate']);
        $this->assertTrue($outputArray['admin']['newwindow']);
        $this->assertEquals('4', $outputArray['admin']['maxrecords']);
    }

    /**
     * This function tests if the Parameters have been updated
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testcheckParametersChanged()
    {
        // To save loading the CLASS with new values every test
        // The PARAMETERS used to test aginst are changed.
        // In the application $parameters would contain the original values

        // No Parameters Changed
        $parameters['admin']['logging'] = '1';
        $parameters['admin']['logrotate'] = '2';
        $parameters['admin']['newwindow'] = true;
        $parameters['admin']['maxrecords'] = '2';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertFalse($result);

        // Logging Changed
        $parameters['admin']['logging'] = '3';
        $parameters['admin']['logrotate'] = '2';
        $parameters['admin']['newwindow'] = true;
        $parameters['admin']['maxrecords'] = '2';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Logging Level Parameter Changed", $localStr);

        // LogRotate Changed
        $parameters['admin']['logging'] = '1';
        $parameters['admin']['logrotate'] = '4';
        $parameters['admin']['newwindow'] = true;
        $parameters['admin']['maxrecords'] = '2';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Log Rotate Parameter Changed", $localStr);

        // New Window Changed
        $parameters['admin']['logging'] = '1';
        $parameters['admin']['logrotate'] = '2';
        $parameters['admin']['newwindow'] = false;
        $parameters['admin']['maxrecords'] = '2';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("New Window Parameter Changed", $localStr);

         // Max Records Changed
        $parameters['admin']['logging'] = '1';
        $parameters['admin']['logrotate'] = '2';
        $parameters['admin']['newwindow'] = true;
        $parameters['admin']['maxrecords'] = '3';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Max Records Parameter Changed", $localStr);
    }

    /**
     * This function tests that the Parameter File can be saved
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testsaveParamFile()
    {
        $filesaved = $this->object->saveParamFile();
        $this->assertTrue($filesaved);
    }
}
