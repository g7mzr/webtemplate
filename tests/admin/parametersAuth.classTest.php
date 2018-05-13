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
class ParametersAuthTest extends TestCase
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
        $this->confobj = new \webtemplate\config\Configure(null);
        $this->defaultParameters();

        // Create the Parameters Class
        $this->object = new \webtemplate\admin\Parameters($this->confobj);
        $this->object->setSection('auth');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
        $configDir = dirname(__FILE__) ."/../_data";
        $filename = $configDir . '/parameters.php';
        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    /**
     * This function sets up the default parameters for testing
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
     * @return null
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
     * @return null
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
     * @return null
     */
    public function testvalidateParameters()
    {
        // Create the User Name Regex for testing create_account and newpasswd
        $inputData['username_regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $inputData['username_regexpdesc'] = $regExpStr;
        $inputData['passwdstrength'] = '4';

        // Create Passwdage for testing create_account and newpasswd
        $inputData['passwdage'] = '1';

        // Create autocomplete for testing autocomplete of passwords
        $inputData['autocomplete'] = 'no';

        // Create the input data for autologout
        $inputData['autologout'] = '1';

        // If the input data for create_account and new_password is
        // invalid then default to no (FALSE)

        // Both false
        $inputData['create_account'] = 'no';
        $inputData['new_password'] = 'no';
        $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertFalse($outputArray['users']['newaccount']);
        $this->assertFalse($outputArray['users']['newpassword']);

        // Both true
        $inputData['create_account'] = 'yes';
        $inputData['new_password'] = 'yes';
        $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertTrue($outputArray['users']['newaccount']);
        $this->assertTrue($outputArray['users']['newpassword']);

         // Both failed to validate
        $inputData['create_account'] = 'now';
        $inputData['new_password'] = 'now';
        $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertFalse($outputArray['users']['newaccount']);
        $this->assertFalse($outputArray['users']['newpassword']);

        // Test the User Name ReGEX and Description
        // Set the other variables to false
        $inputData['create_account'] = 'no';
        $inputData['new_password'] = 'no';
        $inputData['passwdage'] = '1';
        $inputData['autocomplete'] = 'no';
        $inputData['autologout'] = '1';

        // Both Validate
        $inputData['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $inputData['regexpdesc'] = $regExpStr;
        $result = $this->object->validateParameters($inputData);
        $this->assertTrue($result);

        // Regex Fails
        $inputData['regexp'] = '/^[a-zA-Z0-9]{5,12};$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $inputData['regexpdesc'] = $regExpStr;
        $result = $this->object->validateParameters($inputData);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertContains(
            "User name Regular Expression contains invalid characters",
            $localStr
        );

        // Regex Description Fails
        $inputData['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only;';
        $inputData['regexpdesc'] = $regExpStr;
        $result = $this->object->validateParameters($inputData);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertContains(
            "User name Regular Expression Description contains invalid characters",
            $localStr
        );

        // Test the Password Strength Variable
        // Set the other variables to pass.
        $inputData['create_account'] = 'no';
        $inputData['new_password'] = 'no';
        $inputData['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $inputData['regexpdesc'] = $regExpStr;
        $inputData['passwdage'] = '1';
        $inputData['autocomplete'] = 'no';
        $inputData['autologout'] = '1';

        // Validates True
        $inputData['passwdstrength'] = '1';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('1', $outputArray['users']['passwdstrength']);

        $inputData['passwdstrength'] = '2';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('2', $outputArray['users']['passwdstrength']);

        $inputData['passwdstrength'] = '3';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('3', $outputArray['users']['passwdstrength']);

        $inputData['passwdstrength'] = '4';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('4', $outputArray['users']['passwdstrength']);

        $inputData['passwdstrength'] = '5';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('5', $outputArray['users']['passwdstrength']);

        // Validated False Low number
        $inputData['passwdstrength'] = '0';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals(5, $outputArray['users']['passwdstrength']);

        // Validated False High number
        $inputData['passwdstrength'] = '6';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals(5, $outputArray['users']['passwdstrength']);

        // Test the Password age Variable
        // Set the other variables to pass.
        $inputData['create_account'] = 'no';
        $inputData['new_password'] = 'no';
        $inputData['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $inputData['regexpdesc'] = $regExpStr;
        $inputData['passwdstrength'] = '4';
        $inputData['autocomplete'] = 'no';
        $inputData['autologout'] = '1';

        // Validate True
        $inputData['passwdage'] = '1';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('1', $outputArray['users']['passwdage']);

        $inputData['passwdage'] = '2';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('2', $outputArray['users']['passwdage']);

        $inputData['passwdage'] = '3';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('3', $outputArray['users']['passwdage']);

        $inputData['passwdage'] = '4';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('4', $outputArray['users']['passwdage']);

        // Validate False Low Number
        $inputData['passwdage'] = '0';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('1', $outputArray['users']['passwdage']);

        // Validate False High Number
        $inputData['passwdage'] = '5';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('1', $outputArray['users']['passwdage']);

        // Test the autocomplete Variable
        // Set the other variables to pass.
        $inputData['create_account'] = 'no';
        $inputData['new_password'] = 'no';
        $inputData['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $inputData['regexpdesc'] = $regExpStr;
        $inputData['passwdstrength'] = '4';
        $inputData['autologout'] = '1';

        //Validate True
        $inputData['autocomplete'] = 'no';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertFalse($outputArray['users']['autocomplete']);

        $inputData['autocomplete'] = 'yes';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertTrue($outputArray['users']['autocomplete']);

        //Validate False
        $inputData['autocomplete'] = 'Bad';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertFalse($outputArray['users']['autocomplete']);


        // Test the Auto Logout Variable Variable
        // Set the other variables to pass.
        $inputData['create_account'] = 'no';
        $inputData['new_password'] = 'no';
        $inputData['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $inputData['regexpdesc'] = $regExpStr;
        $inputData['passwdstrength'] = '4';
        $inputData['autocomplete'] = 'no';
        $inputData['passwdage'] = '1';

        // Validate True
        $inputData['autologout'] = '1';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('1', $outputArray['users']['autologout']);

        $inputData['autologout'] =  '2';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('2', $outputArray['users']['autologout']);

        $inputData['autologout'] =  '3';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('3', $outputArray['users']['autologout']);

        $inputData['autologout'] =  '4';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('4', $outputArray['users']['autologout']);

        $inputData['autologout'] =  '5';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('5', $outputArray['users']['autologout']);

        // Validate False Low Number
        $inputData['autologout'] = '0';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('1', $outputArray['users']['autologout']);

        // Validate False High Number
        $inputData['autologout'] =  '6';
        $result = $this->object->validateParameters($inputData);
        $outputArray = $this->object->getCurrentParameters();
        $this->assertEquals('1', $outputArray['users']['autologout']);
    }

    /**
     * This function tests if the Parameters have been updated
     *
     * @group unittest
     * @group admin
     *
     * @return null
     */
    public function testcheckParametersChanged()
    {
        // No Change
        $parameters['users']['newaccount'] = false;
        $parameters['users']['newpassword'] = true;
        $parameters['users']['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $parameters['users']['regexpdesc'] = $regExpStr;
        $parameters['users']['passwdstrength'] = '4';
        $parameters['users']['passwdage'] = '1';
        $parameters['users']['autocomplete'] = false;
        $parameters['users']['autologout'] =  '1';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertFalse($result);

        // New Account Changed
        $parameters['users']['newaccount'] = true;
        $parameters['users']['newpassword'] = true;
        $parameters['users']['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $parameters['users']['regexpdesc'] = $regExpStr;
        $parameters['users']['passwdstrength'] = '4';
        $parameters['users']['passwdage'] = '1';
        $parameters['users']['autocomplete'] = false;
        $parameters['users']['autologout'] =  '1';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertContains("Create Accounts Parameter Changed", $localStr);

        // New Password Changed
        $parameters['users']['newaccount'] = false;
        $parameters['users']['newpassword'] = false;
        $parameters['users']['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $parameters['users']['regexpdesc'] = $regExpStr;
        $parameters['users']['passwdstrength'] = '4';
        $parameters['users']['passwdage'] = '1';
        $parameters['users']['autocomplete'] = false;
        $parameters['users']['autologout'] =  '1';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertContains("New Password Parameter Changed", $localStr);

        // regexp Changed
        $parameters['users']['newaccount'] = false;
        $parameters['users']['newpassword'] = true;
        $parameters['users']['regexp'] = '/^[a-zA-Z0-9]{5,14}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $parameters['users']['regexpdesc'] = $regExpStr;
        $parameters['users']['passwdstrength'] = '4';
        $parameters['users']['passwdage'] = '1';
        $parameters['users']['autocomplete'] = false;
        $parameters['users']['autologout'] =  '1';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertContains("User Name Regexp Parameter Changed", $localStr);

        // New Description
        $parameters['users']['newaccount'] = false;
        $parameters['users']['newpassword'] = true;
        $parameters['users']['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper case letters and numbers only';
        $parameters['users']['regexpdesc'] = $regExpStr;
        $parameters['users']['passwdstrength'] = '4';
        $parameters['users']['passwdage'] = '1';
        $parameters['users']['autocomplete'] = false;
        $parameters['users']['autologout'] =  '1';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertContains(
            "User Name Regexp Description Parameter Changed",
            $localStr
        );

        // Password Strength has changed
        $parameters['users']['newaccount'] = false;
        $parameters['users']['newpassword'] = true;
        $parameters['users']['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $parameters['users']['regexpdesc'] = $regExpStr;
        $parameters['users']['passwdstrength'] = '2';
        $parameters['users']['passwdage'] = '1';
        $parameters['users']['autocomplete'] = false;
        $parameters['users']['autologout'] =  '1';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertContains("Password Strength has changed", $localStr);

        // Password Aging has changed
        $parameters['users']['newaccount'] = false;
        $parameters['users']['newpassword'] = true;
        $parameters['users']['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $parameters['users']['regexpdesc'] = $regExpStr;
        $parameters['users']['passwdstrength'] = '4';
        $parameters['users']['passwdage'] = '3';
        $parameters['users']['autocomplete'] = false;
        $parameters['users']['autologout'] =  '1';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertContains("Password Ageing has changed", $localStr);

        // Auto Complete has changed
        $parameters['users']['newaccount'] = false;
        $parameters['users']['newpassword'] = true;
        $parameters['users']['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $parameters['users']['regexpdesc'] = $regExpStr;
        $parameters['users']['passwdstrength'] = '4';
        $parameters['users']['passwdage'] = '1';
        $parameters['users']['autocomplete'] = true;
        $parameters['users']['autologout'] =  '1';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertContains("Autocomplete has changed", $localStr);

        // Auto Logout has changed
        $parameters['users']['newaccount'] = false;
        $parameters['users']['newpassword'] = true;
        $parameters['users']['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $parameters['users']['regexpdesc'] = $regExpStr;
        $parameters['users']['passwdstrength'] = '4';
        $parameters['users']['passwdage'] = '1';
        $parameters['users']['autocomplete'] = false;
        $parameters['users']['autologout'] =  '2';
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertContains("Auto Logout has changed", $localStr);
    }

    /**
     * This function tests that the Parameter File can be saved
     *
     * @group unittest
     * @group admin
     *
     * @return null
     */
    public function testsaveParamFile()
    {
        $inputData['create_account'] = 'no';
        $inputData['new_password'] = 'yes';
        $inputData['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
        $regExpStr = 'Must contain upper and lower case letters and numbers only';
        $inputData['regexpdesc'] = $regExpStr;
        $inputData['passwdstrength'] = '4';
        $inputData['passwdage'] = '1';
        $inputData['autocomplete'] = 'no';
        $inputData['autologout'] = '1';

        $this->object->validateParameters($inputData);
        $configDir = dirname(__FILE__) ."/../_data";
        $filesaved = $this->object->saveParamFile($configDir);
        $this->assertTrue($filesaved);
        $filename = $configDir . '/parameters.php';
        $expectedfilename = $configDir . '/parameters_test_file.php';
        $this->assertFileExists($filename);
        $this->assertFileEquals($expectedfilename, $filename);
    }

    /**
     * This function tests that an error is recorded if Parameter File cannot
     * be saved
     *
     * @group unittest
     * @group admin
     *
     * @return null
     */
    public function testsaveParamFileFail()
    {
        $configDir = dirname(__FILE__) ."/../data";
        $filesaved = $this->object->saveParamFile($configDir);
        $this->assertFalse($filesaved);
    }
}
