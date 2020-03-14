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
 * Parameters Required Class Unit Tests
 *
 **/
class ParametersRequiredTest extends TestCase
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
        $this->object->setSection('required');
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
        // All required Parameters to Validate
        $inputData['url_base_id'] = 'http://www.example.com';
        $inputData['maintainer_id'] = 'phpunit@example.com';
        $inputData['doc_baseurl_id'] = 'docs/';
        $inputData['cookie_domain_id'] = '';
        $inputData['cookie_path_id'] = '/';
        $result = $this->object->validateParameters($inputData);
        $this->assertTrue($result);

        // URL BAse Fails
        $inputData['url_base_id'] = 'htp://www.example.com';
        $inputData['maintainer_id'] = 'phpunit@example.com';
        $inputData['doc_baseurl_id'] = 'docs/';
        $inputData['cookie_domain_id'] = '';
        $inputData['cookie_path_id'] = '/';
        $result = $this->object->validateParameters($inputData);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString('Invalid URL Base', $localStr);

        // Maintainer Fails
        $inputData['url_base_id'] = 'http://www.example.com';
        $inputData['maintainer_id'] = 'phpunitexample.com';
        $inputData['doc_baseurl_id'] = 'docs/';
        $inputData['cookie_domain_id'] = '';
        $inputData['cookie_path_id'] = '/';
        $result = $this->object->validateParameters($inputData);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Invalid Maintainer's Email Address", $localStr);

        // Doc Base Fails
        $inputData['url_base_id'] = 'http://www.example.com';
        $inputData['maintainer_id'] = 'phpunit@example.com';
        $inputData['doc_baseurl_id'] = '/docs/';
        $inputData['cookie_domain_id'] = '';
        $inputData['cookie_path_id'] = '/';
        $result = $this->object->validateParameters($inputData);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Invalid Document Path", $localStr);

        // Cookie Domain Fails
        $inputData['url_base_id'] = 'http://www.example.com';
        $inputData['maintainer_id'] = 'phpunit@example.com';
        $inputData['doc_baseurl_id'] = 'docs/';
        $inputData['cookie_domain_id'] = '.example.com/';
        $inputData['cookie_path_id'] = '/';
        $result = $this->object->validateParameters($inputData);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Invalid Cookie Domain", $localStr);

        // Cookie Path Fails
        $inputData['url_base_id'] = 'http://www.example.com';
        $inputData['maintainer_id'] = 'phpunit@example.com';
        $inputData['doc_baseurl_id'] = 'docs/';
        $inputData['cookie_domain_id'] = '';
        $inputData['cookie_path_id'] = 'path;/';
        $result = $this->object->validateParameters($inputData);
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Invalid Cookie Path", $localStr);

        // No Input Data
        $testData['Test'] = 'http://www.example.com';
        $result = $this->object->validateParameters($testData);
        $this->assertFalse($result);
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
        $parameters['urlbase'] = 'http://www.example.com';
        $parameters['maintainer'] = 'phpunit@example.com';
        $parameters['docbase'] = 'docs/';
        $parameters['cookiedomain'] = '';
        $parameters['cookiepath'] = '/';
        $result = $this->object->CheckParametersChanged($parameters);
        $this->assertFalse($result);

        // URL BASE Changed
        $parameters['urlbase'] = 'http://www.example2.com';
        $parameters['maintainer'] = 'phpunit@example.com';
        $parameters['docbase'] = 'docs/';
        $parameters['cookiedomain'] = '';
        $parameters['cookiepath'] = '/';
        $result = $this->object->CheckParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Base URL Changed", $localStr);

        // Maintainer Changed
        $parameters['urlbase'] = 'http://www.example.com';
        $parameters['maintainer'] = 'phpunit2@example.com';
        $parameters['docbase'] = 'docs/';
        $parameters['cookiedomain'] = '';
        $parameters['cookiepath'] = '/';
        $result = $this->object->CheckParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Maintainer's Address Changed", $localStr);

        // DOCBASE Changed
        $parameters['urlbase'] = 'http://www.example.com';
        $parameters['maintainer'] = 'phpunit@example.com';
        $parameters['docbase'] = 'docs/en/';
        $parameters['cookiedomain'] = '';
        $parameters['cookiepath'] = '/';
        $result = $this->object->CheckParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Docs Base URL Changed", $localStr);

         // Cookie Domain Changed
        $parameters['urlbase'] = 'http://www.example.com';
        $parameters['maintainer'] = 'phpunit@example.com';
        $parameters['docbase'] = 'docs/';
        $parameters['cookiedomain'] = '.example.com';
        $parameters['cookiepath'] = '/';
        $result = $this->object->CheckParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Cookie Domain Changed", $localStr);

        // Cookie Path Changed
        $parameters['urlbase'] = 'http://www.example.com';
        $parameters['maintainer'] = 'phpunit@example.com';
        $parameters['docbase'] = 'docs/';
        $parameters['cookiedomain'] = '';
        $parameters['cookiepath'] = '/phpunit/';
        $result = $this->object->CheckParametersChanged($parameters);
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString("Cookie Path Changed", $localStr);
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
