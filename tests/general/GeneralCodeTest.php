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

// Define Application Name and Versions for test
DEFINE("UPDATESERVERGENERAL", "https://admin.starfleet.g7mzr.ampr.org/");
DEFINE("APPSHORTNAMEGENERAL", "Webtemplate");

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
        $configfile = \g7mzr\webtemplate\general\General::getconfigfile($configDir);
        $this->assertEquals('en.conf', $configfile, 'Failed to set en config file');

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr';
        $configfile = \g7mzr\webtemplate\general\General::getconfigfile($configDir);
        $this->assertEquals('fr.conf', $configfile, 'Failed to set fr config file');

        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'ia';
        $configfile = \g7mzr\webtemplate\general\General::getconfigfile($configDir);
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
        $passwd = \g7mzr\webtemplate\general\General::generatePassword();
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
        $msg = \g7mzr\webtemplate\general\General::checkkeyParameters(
            $urlbase,
            $emailaddress,
            $maintainer
        );
        $this->assertStringContainsString('ADMIN', $msg);

        // Check no message is returned if all key parameters set
        $urlbase = 'http://www.g7mzr.demon.co.uk';
        $emailaddress = 'g7mzrdev@gmail.com';
        $maintainer = 'g7mzrdev@gmail.com';
        $msg = \g7mzr\webtemplate\general\General::checkkeyParameters(
            $urlbase,
            $emailaddress,
            $maintainer
        );
        $this->assertEmpty($msg);

        // Check message is returned if URL base not set
        $urlbase = '';
        $emailaddress = 'g7mzrdev@gmail.com';
        $maintainer = 'g7mzrdev@gmail.com';
        $msg = \g7mzr\webtemplate\general\General::checkkeyParameters(
            $urlbase,
            $emailaddress,
            $maintainer
        );
        $this->assertStringContainsString('URL', $msg);

        // Check message is returned if application's emial address is not set
        $urlbase = 'http://www.g7mzr.demon.co.uk';
        $emailaddress = '';
        $maintainer = 'g7mzrdev@gmail.com';
        $msg = \g7mzr\webtemplate\general\General::checkkeyParameters(
            $urlbase,
            $emailaddress,
            $maintainer
        );
        $this->assertStringContainsString('Application', $msg);

        // Check message is returned if maintainer's emial address is not set
        $urlbase = 'http://www.g7mzr.demon.co.uk';
        $emailaddress = 'g7mzrdev@gmail.com';
        $maintainer = '';
        $msg = \g7mzr\webtemplate\general\General::checkkeyParameters(
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
        $docs_available = \g7mzr\webtemplate\general\General::checkdocs(
            $docbase,
            $language
        );
        $this->assertFalse($docs_available, 'Failed with no path set');

        $language = 'en';
        $docbase = dirname(__FILE__) . "/../_data/docs/%lang%/";
        $docs_available = \g7mzr\webtemplate\general\General::checkdocs(
            $docbase,
            $language
        );
        $this->assertTrue($docs_available, 'Failed with correct path and file set (en)');

        $language = 'fr';
        $docbase = dirname(__FILE__) . "/../_data/docs/%lang%/";
        $docs_available = \g7mzr\webtemplate\general\General::checkdocs(
            $docbase,
            $language
        );
        $this->assertTrue($docs_available, 'Failed with correct path and file set (fr)');

        $language = 'ge';
        $docbase = dirname(__FILE__) . "/../_data/docs/%lang%/";
        $docs_available = \g7mzr\webtemplate\general\General::checkdocs(
            $docbase,
            $language
        );
        $this->assertTrue($docs_available, 'Failed with incorrect correct path and file set (ge)');

        $docbase = dirname(__FILE__) . "/";
        $docs_available = \g7mzr\webtemplate\general\General::checkdocs(
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
        $result = \g7mzr\webtemplate\general\General::passwdFormat('1');
        $this->assertStringContainsString("No other checks", $result);

        //Check Length and Lower Case Letters Only
        $result = \g7mzr\webtemplate\general\General::passwdFormat('2');
        $this->assertStringContainsString("Lower Case letters only", $result);

        // Check length and Lower & Upper case letters
        $result = \g7mzr\webtemplate\general\General::passwdFormat('3');
        $this->assertStringContainsString("one lower and one upper case letter", $result);

        // Check length, lower & upper case letters and numbers
        $result = \g7mzr\webtemplate\general\General::passwdFormat('4');
        $this->assertStringContainsString("and one number", $result);

        // Check length, lower & upper case letters, numbers and Special characters
        $result = \g7mzr\webtemplate\general\General::passwdFormat('5');
        $this->assertStringContainsString("and one special character", $result);

        // Check Defaults
        $result = \g7mzr\webtemplate\general\General::passwdFormat('6');
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
        $hash = \g7mzr\webtemplate\general\General::encryptPasswd("apple");
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
        $hash = \g7mzr\webtemplate\general\General::encryptPasswd("apple");
        $result = \g7mzr\webtemplate\general\General::verifyPasswd('apple', $hash);
        $this->assertTrue($result);
        $result = \g7mzr\webtemplate\general\General::verifyPasswd('apples', $hash);
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
        $err = \g7mzr\webtemplate\general\General::raiseError("This is testRaiseError", 1);
        $this->assertTrue(is_a($err, '\g7mzr\webtemplate\application\Error'));
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
        $err = \g7mzr\webtemplate\general\General::raiseError("This is testisError", 2);
        $this->assertTrue(\g7mzr\webtemplate\general\General::isError($err));

        // Test with an non error object
        $str = "This is a String";
        $this->assertFalse(\g7mzr\webtemplate\general\General::isError($str));
    }

    /**
     * Test if an empty string is returned if any of the parameter strings  of
     * checkupdate are empty.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testUpdateEmptyParameters()
    {
        // Test with all parameters empty
        $result1 = \g7mzr\webtemplate\general\General::checkUpdate();
        $this->assertEquals("", $result1);

        // Test with only $app name empty
        $result2 = \g7mzr\webtemplate\general\General::checkUpdate(
            "",
            UPDATESERVERGENERAL,
            "1.0.0"
        );
        $this->assertEquals("", $result2);


        // Test with only $updateSever empty
        $result3 = \g7mzr\webtemplate\general\General::checkUpdate(
            APPSHORTNAMEGENERAL,
            "",
            "1.0.0"
        );
        $this->assertEquals("", $result3);


        // Test with only $currentVersion empty
        $result4 = \g7mzr\webtemplate\general\General::checkUpdate(
            APPSHORTNAMEGENERAL,
            UPDATESERVERGENERAL
        );
        $this->assertEquals("", $result4);
    }

    /**
     * Test dor empty jason string
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testEmptyJSONString()
    {
        $result = \g7mzr\webtemplate\general\General::checkUpdate(
            "update",
            UPDATESERVERGENERAL,
            "1.0.0"
        );
        $this->assertEquals("", $result);
    }

    /**
     * Test if an empty string is returned if an invalid URL is entered.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testUpdateInvalidURL()
    {
        // Test for am invalid URL
        $testurl = "htts://one";
        $result = \g7mzr\webtemplate\general\General::checkUpdate(
            APPSHORTNAMEGENERAL,
            $testurl,
            "1.0.0"
        );
        $this->assertEquals("", $result);
    }

    /**
     * Test if an empty string is returned if an invalid URL is entered.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testUpdateInvalidAppShortName()
    {
        // Test for am invalid URL
        $testAppShortName = "example";
        $result = \g7mzr\webtemplate\general\General::checkUpdate(
            $testAppShortName,
            UPDATESERVERGENERAL,
            "1.0.0"
        );
        $this->assertEquals("", $result);
    }

    /**
     * Test if one update message is returned.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testUpdatePassOneUpdate()
    {
        // Test for am invalid URL
        $result = \g7mzr\webtemplate\general\General::checkUpdate(
            APPSHORTNAMEGENERAL,
            UPDATESERVERGENERAL,
            "1.0.0"
        );
        $this->assertStringContainsString("2.1.0", $result);
        $this->assertStringNotContainsString("1.0.0", $result);
        $this->assertStringNotContainsString("0.5.0", $result);
        $this->assertStringNotContainsString("0.1.0", $result);
    }

    /**
     * Test if two update messages are returned.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testUpdatePassTwoUpdate()
    {
        // Test for am invalid URL
        $result = \g7mzr\webtemplate\general\General::checkUpdate(
            APPSHORTNAMEGENERAL,
            UPDATESERVERGENERAL,
            "0.5.0"
        );
        $this->assertStringContainsString("2.1.0", $result);
        $this->assertStringContainsString("1.0.0", $result);
        $this->assertStringNotContainsString("0.5.0", $result);
        $this->assertStringNotContainsString("0.1.0", $result);
    }

    /**
     * Test if zero update messages are returned.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testUpdatePassZeroUpdate()
    {
        // Test for am invalid URL
        $result = \g7mzr\webtemplate\general\General::checkUpdate(
            APPSHORTNAMEGENERAL,
            UPDATESERVERGENERAL,
            "2.1.0"
        );
        $this->assertStringNotContainsString("2.1.0", $result);
        $this->assertStringNotContainsString("1.0.0", $result);
        $this->assertStringNotContainsString("0.5.0", $result);
        $this->assertStringNotContainsString("0.1.0", $result);
    }

    /**
     * This function tests the sizeFormat Function
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testSizeFormat()
    {
        //Test Bytes
        $byteResult = \g7mzr\webtemplate\general\General::sizeFormat(208);
        $this->assertEquals("208 bytes", $byteResult);

        //Test KB
        $kbResult = \g7mzr\webtemplate\general\General::sizeFormat(3508);
        $this->assertEquals("3.4 KB", $kbResult);

        // Test MB
        $mbResult = \g7mzr\webtemplate\general\General::sizeFormat(21088564);
        $this->assertEquals("20.1 MB", $mbResult);

        // Test GB
        $gbResult = \g7mzr\webtemplate\general\General::sizeFormat(2208878564);
        $this->assertEquals("2.1 GB", $gbResult);
    }

    /**
     * This function tests the directorySize Function
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testDirectorySize()
    {
        $direcory = __DIR__ . "/../_data";
        $result = \g7mzr\webtemplate\general\General::getDirectorySize($direcory);
        $this->assertEquals(1310, $result['size']);
        $this->assertEquals(14, $result['count']);
        $this->assertEquals(9, $result['dircount']);
    }
}
