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

/**
 * Parameters Email Class Unit Tests
 *
 **/
class ParametersEmailTest extends TestCase
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

        // Create configuration Object
        $configDir = __DIR__ . "/../../configs";
        $this->confobj = new\g7mzr\webtemplate\config\Configure($configDir);
        $this->defaultParameters();

        // Create the Parameters Class
        $this->object = new\g7mzr\webtemplate\admin\Parameters($this->confobj);
        $this->object->setSection('email');
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
        $result = $this->object->getCurrentParameters();
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

        // All inputs validate okay
        $inputdata['mail_delivery_method'] = 'smtp';
        $inputdata['email_address_id'] = 'phpunit@example.com';
        $inputdata['smtp_server_id'] = 'smtp.example.com';
        $inputdata['smtp_user_name_id'] = 'user';
        $inputdata['smtp_passwd_id'] = 'password';
        $inputdata['smtp_debug'] = 'no';
        $result = $this->object->ValidateParameters($inputdata);
        $this->assertTrue($result);

        // Email Address fails validation
        $inputdata['mail_delivery_method'] = 'smtp';
        $inputdata['email_address_id'] = 'phpunit.example.com';
        $inputdata['smtp_server_id'] = 'smtp.example.com';
        $inputdata['smtp_user_name_id'] = 'user';
        $inputdata['smtp_passwd_id'] = 'password';
        $inputdata['smtp_debug'] = 'no';
        $result = $this->object->ValidateParameters($inputdata);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Invalid E-mail Address", $localStr);

        // SMTP Server name fails validation
        $inputdata['mail_delivery_method'] = 'smtp';
        $inputdata['email_address_id'] = 'phpunit@example.com';
        $inputdata['smtp_server_id'] = 'smtp';
        $inputdata['smtp_user_name_id'] = 'user';
        $inputdata['smtp_passwd_id'] = 'password';
        $inputdata['smtp_debug'] = 'yes';
        $result = $this->object->ValidateParameters($inputdata);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Invalid SMTP Server", $localStr);

         // SMTP Username  validation
        $inputdata['mail_delivery_method'] = 'smtp';
        $inputdata['email_address_id'] = 'phpunit@example.com';
        $inputdata['smtp_server_id'] = 'smtp.example.com';
        $inputdata['smtp_user_name_id'] = 'user;';
        $inputdata['smtp_passwd_id'] = 'password';
        $inputdata['smtp_debug'] = 'no';
        $result = $this->object->ValidateParameters($inputdata);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Invalid Username", $localStr);

         // SMTP password validation
        $inputdata['mail_delivery_method'] = 'smtp';
        $inputdata['email_address_id'] = 'phpunit@example.com';
        $inputdata['smtp_server_id'] = 'smtp.example.com';
        $inputdata['smtp_user_name_id'] = 'user';
        $inputdata['smtp_passwd_id'] = 'password;';
        $inputdata['smtp_debug'] = 'no';
        $result = $this->object->ValidateParameters($inputdata);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Invalid Password", $localStr);

        // Check that SMTP Server is defned
        $inputdata = array ();
        $inputdata['mail_delivery_method'] = 'smtp';
        $inputdata['email_address_id'] = 'phpunit@example.com';
        $result = $this->object->ValidateParameters($inputdata);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString(
            "You must enter the name of your SMTP server",
            $localStr
        );

        // Check that SMTP Password is defned
        $inputdata = array ();
        $inputdata['mail_delivery_method'] = 'smtp';
        $inputdata['email_address_id'] = 'phpunit@example.com';
        $inputdata['smtp_server_id'] = 'smtp.example.com';
        $inputdata['smtp_user_name_id'] = 'user';
        $result = $this->object->ValidateParameters($inputdata);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString(
            "You must enter both the SMTP Username and Password",
            $localStr
        );

        // Empty input Array
        $inputdata = array();
        $result = $this->object->ValidateParameters($inputdata);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
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
        // No Data Changed
        $parameters['email']['smtpdeliverymethod'] = 'smtp';
        $parameters['email']['emailaddress'] = 'phpunit@example.com';
        $parameters['email']['smtpserver'] = 'smtp.example.com';
        $parameters['email']['smtpusername'] = 'user';
        $parameters['email']['smtppassword'] = 'password';
        $parameters['email']['smtpdebug'] = false;
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertFalse($result);

        // Delivery Method Changed
        $parameters['email']['smtpdeliverymethod'] = 'none';
        $parameters['email']['emailaddress'] = 'phpunit@example.com';
        $parameters['email']['smtpserver'] = 'smtp.example.com';
        $parameters['email']['smtpusername'] = 'user';
        $parameters['email']['smtppassword'] = 'password';
        $parameters['email']['smtpdebug'] = false;
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Mail Delivery Method Changed", $localStr);

        // E-Mail Address Changed
        $parameters['email']['smtpdeliverymethod'] = 'smtp';
        $parameters['email']['emailaddress'] = 'phpunit2@example.com';
        $parameters['email']['smtpserver'] = 'smtp.example.com';
        $parameters['email']['smtpusername'] = 'user';
        $parameters['email']['smtppassword'] = 'password';
        $parameters['email']['smtpdebug'] = false;
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("E-Mail Address Changed", $localStr);

        // SMTP Server Changed
        $parameters['email']['smtpdeliverymethod'] = 'smtp';
        $parameters['email']['emailaddress'] = 'phpunit@example.com';
        $parameters['email']['smtpserver'] = 'smtp1.example.com';
        $parameters['email']['smtpusername'] = 'user';
        $parameters['email']['smtppassword'] = 'password';
        $parameters['email']['smtpdebug'] = false;
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("SMTP Server Changed", $localStr);

        // SMTP User Changed
        $parameters['email']['smtpdeliverymethod'] = 'smtp';
        $parameters['email']['emailaddress'] = 'phpunit@example.com';
        $parameters['email']['smtpserver'] = 'smtp.example.com';
        $parameters['email']['smtpusername'] = 'user1';
        $parameters['email']['smtppassword'] = 'password';
        $parameters['email']['smtpdebug'] = false;
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("SMTP User Name Changed", $localStr);

        // SMTP Password Changed
        $parameters['email']['smtpdeliverymethod'] = 'smtp';
        $parameters['email']['emailaddress'] = 'phpunit@example.com';
        $parameters['email']['smtpserver'] = 'smtp.example.com';
        $parameters['email']['smtpusername'] = 'user';
        $parameters['email']['smtppassword'] = 'password2';
        $parameters['email']['smtpdebug'] = false;
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("SMTP Password Changed", $localStr);

        // SMTP DEBUG Changed
        $parameters['email']['smtpdeliverymethod'] = 'smtp';
        $parameters['email']['emailaddress'] = 'phpunit@example.com';
        $parameters['email']['smtpserver'] = 'smtp.example.com';
        $parameters['email']['smtpusername'] = 'user';
        $parameters['email']['smtppassword'] = 'password';
        $parameters['email']['smtpdebug'] = true;
        $result = $this->object->checkParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("SMTP Debug Changed", $localStr);
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
        $inputdata['mail_delivery_method'] = 'smtp';
        $inputdata['email_address_id'] = 'phpunit@example.com';
        $inputdata['smtp_server_id'] = 'smtp.example.com';
        $inputdata['smtp_user_name_id'] = 'user';
        $inputdata['smtp_passwd_id'] = 'password';
        $inputdata['smtp_debug'] = 'np';
        $result = $this->object->ValidateParameters($inputdata);
        $this->assertTrue($result);

        $configDir = dirname(__FILE__) . "/../_data";
        $filesaved = $this->object->saveParamFile($configDir);
        $this->assertTrue($filesaved);
        $filename = $configDir . '/parameters.json';
        $expectedfilename = $configDir . '/parameters_test_file.json';
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
     * @return void
     */
    public function testsaveParamFileFail()
    {
        $configDir = dirname(__FILE__) . "/../data";
        $filesaved = $this->object->saveParamFile($configDir);
        $this->assertFalse($filesaved);
    }
}
