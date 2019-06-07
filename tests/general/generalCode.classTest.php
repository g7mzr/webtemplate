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

use PHPUnit\Framework\TestCase;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

/**
 * General Code Class Unit Tests
 *
 **/
class GeneralCodeTest extends TestCase
{
    /**
     * General Object
     *
     * @var LocalValidate

    protected $object;

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
     * Test the correct configuration for the users language can be selected
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testgetconfigfile()
    {
        $configDir = array(dirname(__FILE__) . "/../_data");
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en';
        $configfile = \webtemplate\general\General::getconfigfile($configDir);
        $this->assertEquals('en.conf', $configfile, 'Failed to set en config file');

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr';
        $configfile = \webtemplate\general\General::getconfigfile($configDir);
        $this->assertEquals('fr.conf', $configfile, 'Failed to set fr config file');

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ia';
        $configfile = \webtemplate\general\General::getconfigfile($configDir);
        $this->assertEquals(
            'en.conf',
            $configfile,
            'Failed to set default config file'
        );
    }

    /**
     * Test that passwords which met the required conditions can be generated
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testgeneratePassword()
    {
        $passwd = \webtemplate\general\General::generatePassword();
        $passwdExp = "/^.*(?=.{8,20})(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).*$/";
        $result = preg_match($passwdExp, $passwd);
        $this->assertEquals(1, $result);
    }

    /**
     * Test the warning is the Key Parameters can be displayed or inhibited as
     * if the parameters are set or not
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testcheckKeyParameters()
    {

        // Check Reason for message being displayed is retuned
        $urlbase = '';
        $emailaddress = '';
        $maintainer = '';
        $msg = \webtemplate\general\General::checkkeyParameters(
            $urlbase,
            $emailaddress,
            $maintainer
        );
        $this->assertStringContainsString('ADMIN', $msg);

        // Check no message is returned if all key parameters set
        $urlbase = 'http://www.g7mzr.demon.co.uk';
        $emailaddress = 'g7mzrdev@gmail.com';
        $maintainer = 'g7mzrdev@gmail.com';
        $msg = \webtemplate\general\General::checkkeyParameters(
            $urlbase,
            $emailaddress,
            $maintainer
        );
        $this->assertEmpty($msg);

        // Check message is returned if URL base not set
        $urlbase = '';
        $emailaddress = 'g7mzrdev@gmail.com';
        $maintainer = 'g7mzrdev@gmail.com';
        $msg = \webtemplate\general\General::checkkeyParameters(
            $urlbase,
            $emailaddress,
            $maintainer
        );
        $this->assertStringContainsString('URL', $msg);

        // Check message is returned if application's emial address is not set
        $urlbase = 'http://www.g7mzr.demon.co.uk';
        $emailaddress = '';
        $maintainer = 'g7mzrdev@gmail.com';
        $msg = \webtemplate\general\General::checkkeyParameters(
            $urlbase,
            $emailaddress,
            $maintainer
        );
        $this->assertStringContainsString('Application', $msg);

        // Check message is returned if maintainer's emial address is not set
        $urlbase = 'http://www.g7mzr.demon.co.uk';
        $emailaddress = 'g7mzrdev@gmail.com';
        $maintainer = '';
        $msg = \webtemplate\general\General::checkkeyParameters(
            $urlbase,
            $emailaddress,
            $maintainer
        );
        $this->assertStringContainsString('Maintainer', $msg);
    }

    /**
     * Test the documentation path can be set
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testcheckDocs()
    {
        global $parameters; //, $language;;

        $language = "en";
        $docbase = "";
        $docs_available = \webtemplate\general\General::checkdocs(
            $docbase,
            $language
        );
        $this->assertFalse($docs_available, 'Failed with no path set');

        $language = 'en';
        $docbase = dirname(__FILE__) . "/../_data/docs/%lang%/";
        $docs_available = \webtemplate\general\General::checkdocs(
            $docbase,
            $language
        );
        $this->assertTrue($docs_available, 'Failed with correct path and file set');

        $language = 'fr';
        $docbase = dirname(__FILE__) . "/../_data/docs/%lang%/";
        $docs_available = \webtemplate\general\General::checkdocs(
            $docbase,
            $language
        );
        $this->assertTrue($docs_available, 'Failed with correct path and file set');

        $docbase = dirname(__FILE__) . "/";
        $docs_available = \webtemplate\general\General::checkdocs(
            $docbase,
            $language
        );
        $this->assertFalse($docs_available, 'Failed with incorrect path');
    }

    /**
     * Test the password format message are correct
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testPasswdFormat()
    {
        // Check Length Only
        $result = \webtemplate\general\General::passwdFormat('1');
        $this->assertStringContainsString("No other checks", $result);

        //Check Length and Lower Case Letters Only
        $result = \webtemplate\general\General::passwdFormat('2');
        $this->assertStringContainsString("Lower Case letters only", $result);

        // Check length and Lower & Upper case letters
        $result = \webtemplate\general\General::passwdFormat('3');
        $this->assertStringContainsString("one lower and one upper case letter", $result);

        // Check length, lower & upper case letters and numbers
        $result = \webtemplate\general\General::passwdFormat('4');
        $this->assertStringContainsString("and one number", $result);

        // Check length, lower & upper case letters, numbers and Special characters
        $result = \webtemplate\general\General::passwdFormat('5');
        $this->assertStringContainsString("and one special character", $result);

        // Check Defaults
        $result = \webtemplate\general\General::passwdFormat('6');
        $this->assertStringContainsString("and one special character", $result);
    }

    /**
     * Test Webtemplate Password Encryption function
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testEncryptPasswd()
    {
        // Get Password HASH
        $hash = \webtemplate\general\General::encryptPasswd("apple");
        $this->assertStringContainsString('$2y$', $hash);
        $this->assertStringContainsString('10$', $hash);
    }

    /**
     * Test Webtemplate Password verification function
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testVerifyPasswd()
    {
        // Get Password HASH
        $hash = \webtemplate\general\General::encryptPasswd("apple");
        $result = \webtemplate\general\General::verifyPasswd('apple', $hash);
        $this->assertTrue($result);
        $result = \webtemplate\general\General::verifyPasswd('apples', $hash);
        $this->assertFalse($result);
    }


    /**
     * Test Webtemplate Raise Error Function
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testraiseError()
    {
        // Raise an error
        $err = \webtemplate\general\General::raiseError("This is testRaiseError", 1);
        $this->assertTrue(is_a($err, '\webtemplate\application\Error'));
        $this->assertEquals($err->getMessage(), "This is testRaiseError");
        $this->assertEquals($err->getCode(), 1);
    }

    /**
     * Test Webtemplate isError Function
     *
     * @group unittest
     * @group general
     *
     * @depends testraiseError
     *
     * @return void
     */
    public function testisError()
    {
        // Raise an error
        $err = \webtemplate\general\General::raiseError("This is testisError", 2);
        $this->assertTrue(\webtemplate\general\General::isError($err));

        // Test with an non error object
        $str = "This is a String";
        $this->assertFalse(\webtemplate\general\General::isError($str));
    }
}
