<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage SELENIUM Functional Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace Facebook\WebDriver;

use PHPUnit\Framework\TestCase;

// Load the Selenium Driver and Application Data.
require_once "constants.php";
require_once dirname(__FILE__) . '/../_data/database.php';

// Include the Class Autoloader to load database driver
require_once __DIR__ . '/../../includes/global.php';

/**
 * Password Reset Functional Tests
 *
 **/
class PasswordResetTest extends TestCase
{

    /**
     * Remote Webdriver
     *
     * @var WebDriver
     */
    protected $webDriver;

    /**
     * Registration test user name
     *
     * @var string
     */
    protected $username;

    /**
     * Registration test user email address
     *
     * @var string
     */
    protected $useremail;


    /**
     * Setup the following Class Variables using constants.php:
     * BROWSER: The Web browser to be used for the tests
     * URL: The Web location of the test site.
     *
     * @return void
     */
    protected function setUp(): void
    {
        global $testdsn;

        // Load the Webdriver from constants.php
        $this->webDriver = MyWebDriver::load();

        // Reset the SECUSER Password to the default value
        $db = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($db)) {
            $encryptPasswd = \webtemplate\general\General::encryptPasswd(
                SECUSERPASSWORD
            );
            $insertdata = array('user_passwd' => $encryptPasswd);
            $searchdata = array('user_name' => SECUSERUSERNAME);
            $result = $db->dbupdate('users', $insertdata, $searchdata);
            if (\webtemplate\general\General::isError($result)) {
                echo "Error Resetting SECUSER Password";
            }
        } else {
            echo "Failed to connect to Database";
        }
    }

    /**
     * Function to close the Webdriver after each test is complete
     *
     * @return void
     */
    public function tearDown(): void
    {

        global $testdsn;

        $status = $this->getStatus();

        // Test that $this-webdriver is a webdriver object if its is not don't
        // do anything
        if (is_a($this->webDriver, "Facebook\WebDriver\Remote\RemoteWebDriver")) {
            // Check if the test has failed
            if ($status == \PHPUnit\Runner\BaseTestRunner::STATUS_ERROR
                || $status == \PHPUnit\Runner\BaseTestRunner::STATUS_FAILURE
            ) {
                //Check that the browser is not htmlunit or that we are testing
                // the Help Files.
                if ((BROWSER != "htmlunit")
                    and ($this->getName() != 'testHelpFiles')
                ) {
                    // Build up the file name
                    $dir = dirname(__FILE__)  . '/screenshots/';
                    $date_format = date('Ymd_H:i:s');
                    $classname = str_replace(
                        'Facebook\\WebDriver\\',
                        '',
                        get_class($this)
                    );
                    $test_details = $classname . '_' . $this->getName();
                    $file_name = $dir . $date_format . '_' . $test_details . '.png';

                    // Capture the Screen Shot
                    $this->webDriver->takeScreenshot($file_name);
                }
            }

            // Quit the webdriver
            $this->webDriver->quit();
        }
        // Reset the SECUSER Password to the default value
        $db = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($db)) {
            $encryptPasswd = \webtemplate\general\General::encryptPasswd(
                SECUSERPASSWORD
            );
            $insertdata = array('user_passwd' => $encryptPasswd);
            $searchdata = array('user_name' => SECUSERUSERNAME);
            $result = $db->dbupdate('users', $insertdata, $searchdata);
            if (\webtemplate\general\General::isError($result)) {
                echo "Error Resetting SECUSER Password";
            }
        } else {
            echo "Failed to connect to Database";
        }
    }


    /**
     * Login to the User Authentication Page and enable the password reset
     * capability of the application
     *
     * @return boolean True if password reset is enabled false otherwise
     */
    private function enablePasswordReset()
    {
        $selfregistrationenabled = true;

        // Enter the User Name and Password
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();

        //Switch to the Admin Page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();

        /**
         * SET THE SELF REGISTRATION TO ON
         */

        //Switch to the Edit Configuration Page
        $this->webDriver->findElement(WebDriverBy::LinkText('Setup'))->click();

        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();

        // Test that the New Account Option is set to No
        $createAccSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('new_password'))
        );

        // Change the Option to YES
        $createAccSelect->selectByVisibleText("Yes");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Logout of the Website and Check the Link exists
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();

        $checkCA = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        if (strpos($checkCA->getText(), "[Forgot Your Password?]") === false) {
            $selfregistrationenabled = false;
        }

        return $selfregistrationenabled;
    }


    /**
     * Login to the User Authentication Page and disable the reset password
     * capability of the application
     *
     * @return boolean True if reset password is enabled false otherwise
     */
    private function disablePasswordReset()
    {
        $selfregistrationenabled = true;

        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();

        //Switch to the Admin Page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        //Switch to the Edit Configuration Page
        $this->webDriver->findElement(WebDriverBy::LinkText('Setup'))->click();

        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();

        // Test that the New Account Option is set to Yes
        $createAccSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('new_password'))
        );
        $createAccounts = $createAccSelect->getFirstSelectedOption()->getText();

        // Change the Option to YES
        $createAccSelect->selectByVisibleText("No");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();

        $checkCA = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));

        if (strpos($checkCA->getText(), "[Forgot Your Password?]") !== false) {
            $selfregistrationenabled = false;
        }

        return $selfregistrationenabled;
    }

    /**
     * This function returns the URL from the test email created by the application
     *
     * @return string reset password URL from email
     */
    private function getResetPasswdURL()
    {

        $url = "";
        $data = $this->getEmail();
        if ($data <> "") {
            $lines = explode("\n", $data);
            $url = $lines[13];
        }
        return $url;
    }

    /**
     * This function get the mail test file from the test server
     *
     * @return string Contents of the email
     */
    private function getEmail()
    {
        $data = "";
        $conn_id = ftp_connect(FTPSERVER);
        $login_result = ftp_login($conn_id, FTPUSER, FTPPASSWD);
        if ((!$conn_id) || (!$login_result)) {
            return $data;
        } else {
            if (ftp_chdir($conn_id, FTPPATH)) {
                ob_start();
                $result = ftp_get(
                    $conn_id,
                    "php://output",
                    "mailer.testfile",
                    FTP_BINARY
                );
                $data = ob_get_contents();
                ob_end_clean();
            }
        }

        ftp_close($conn_id);
        return $data;
    }



    /**
     * Login to the email and enable the test email system for the application
     *
     * @return boolean True if email is enabled false otherwise
     */
    private function enableEmail()
    {
        $emailenabled = true;

        // Enter the User Name and Password
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();

        //Switch to the Admin Page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();

        /**
         * SET THE email TO test
         */

        //Switch to the Edit Configuration Page
        $this->webDriver->findElement(WebDriverBy::LinkText('Setup'))->click();

        $this->webDriver->findElement(WebDriverBy::LinkText('e-mail'))->click();

        // Test that the New Account Option is set to No
        $mailDeliverySelectOff = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );

        // Change the Option to YES
        $mailDeliverySelectOff->selectByVisibleText("Test");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();


        $mailDeliverySelectTest = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );
        $mailDeliveryMethod = $mailDeliverySelectTest
            ->getFirstSelectedOption()
            ->getText();
        if ($mailDeliveryMethod != "Test") {
            $emailenabled = false;
        }


        // Logout of the Website and Check the Link exists
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();

        return $emailenabled;
    }

    /**
     * Login to the email and Disable the test email system for the application
     *
     * @return boolean True if email is enabled false otherwise
     */
    private function disableEmail()
    {
        $emailenabled = true;

        // Enter the User Name and Password
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();

        //Switch to the Admin Page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();

        /**
         * SET THE email TO test
         */

        //Switch to the Edit Configuration Page
        $this->webDriver->findElement(WebDriverBy::LinkText('Setup'))->click();

        $this->webDriver->findElement(WebDriverBy::LinkText('e-mail'))->click();

        // Test that the New Account Option is set to No
        $mailDeliverySelectTest = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );

        // Change the Option to YES
        $mailDeliverySelectTest->selectByVisibleText("None");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();


        $mailDeliverySelectNone = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );
        $mailDeliveryMethod = $mailDeliverySelectNone
            ->getFirstSelectedOption()
            ->getText();
        if ($mailDeliveryMethod != "None") {
            $emailenabled = false;
        }


        // Logout of the Website and Check the Link exists
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();

        return $emailenabled;
    }


    /**
     * Check that the reset password link does not exist.
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testNoResetPasswdLink()
    {

        // Open the Website and check the reset password link is missing
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $checkCreateAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringNotContainsString(
            '[Forgot Your Password?]',
            $checkCreateAccount->getText()
        );
    }


    /**
     * Check that the Reset Password link can be activated and deactivated
     *
     * @group selenium
     * @group resetpassword
     *
     * @depends testNoResetPasswdLink
     *
     * @return void
     */
    public function testSelfRegistrationActive()
    {

        // Open the Website and check the create account link is missing
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $checkNoNewAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringNotContainsString(
            '[New Account]',
            $checkNoNewAccount->getText()
        );

        // Enable Password Reset
        $enableresult = $this->enablePasswordReset();
        $this->assertTrue($enableresult);

        $checkNewAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringContainsString(
            '[Forgot Your Password?]',
            $checkNewAccount->getText()
        );

        // Disable Password Reset
        $disableresult = $this->disablePasswordReset();
        $this->assertTrue($disableresult);

        $checkCreateAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringNotContainsString(
            '[Forgot Your Password?]',
            $checkCreateAccount->getText()
        );
    }

    /**
     * Test the response when resetpasswd.php is called from the web browser when
     * password reset is disabled.
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testPasswdResetDisabled()
    {
        $this->webDriver->get(URL . "resetpasswd.php");
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Test the response when passwd reset is called from the home page and the
     * email system is currently disabled.
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testPasswdResetNoEmail()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable Password Reset
        $passwdResetEnabled = $this->enablePasswordReset();
        $this->assertTrue($passwdResetEnabled);

        // Create a password reset request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[Forgot Your Password?]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Request New Password',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys(SECUSERUSERNAME);
        $this->webDriver->findElement(WebDriverBy::id('newpasswdreq'))->click();

        // Get an error response
        $this->assertEquals(
            WEBSITENAME . ': System Error',
            $this->webDriver->getTitle()
        );
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#error_msg')
        );
        $this->assertRegExp(
            '/System Error. Unable to complete request/',
            $checkMsg->getText()
        );

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disablePasswdReset = $this->disablePasswordReset();
        $this->assertTrue($disablePasswdReset);
    }

    /**
     * Test for an invalid request from the user.
     * The token used does not exist
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testInvalidRequest()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable Password Reset
        $passwdResetEnabled = $this->enablePasswordReset();
        $this->assertTrue($passwdResetEnabled);


        $phpscript = "resetpasswd.php";
        $command = "passwdreq";
        $validstring = "huqxkj4olg";
        $invalidstring = "hliwqhki";

        // Test for a valid token type not in the database
        $appcomand = $phpscript . "?" . $command . "=" . $validstring;
        $this->webDriver->get(URL . $appcomand);
        $this->assertEquals(
            WEBSITENAME . ': Invalid Request',
            $this->webDriver->getTitle()
        );

        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#error_msg')
        );
        $this->assertRegExp(
            '/This is not a valid password change token/',
            $checkMsg->getText()
        );

        //Test with incorrect Token length
        $appcomand = $phpscript . "?" . $command . "=" . $invalidstring;
        $this->webDriver->get(URL . $appcomand);
        $this->assertEquals(
            WEBSITENAME . ': Invalid Request',
            $this->webDriver->getTitle()
        );

        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#error_msg')
        );
        $this->assertRegExp(
            '/This is not a valid password change token/',
            $checkMsg->getText()
        );

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disablePasswdReset = $this->disablePasswordReset();
        $this->assertTrue($disablePasswdReset);
    }


    /**
     * Test for an invalid username format.
     * The format of the username does not match the one expected by the application
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testInvalidUsernameFormat()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable Password Reset
        $passwdResetEnabled = $this->enablePasswordReset();
        $this->assertTrue($passwdResetEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

        // Create a password reset request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[Forgot Your Password?]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Request New Password',
            $this->webDriver->getTitle()
        );

        // Enter an username with invalid characters
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys('hyjbg!');
        $this->webDriver->findElement(WebDriverBy::id('newpasswdreq'))->click();

        $this->assertEquals(
            WEBSITENAME . ': Invalid Username',
            $this->webDriver->getTitle()
        );


        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disablePasswdReset = $this->disablePasswordReset();
        $this->assertTrue($disablePasswdReset);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }

    /**
     * Test for an invalid username
     * The username does exist in the database
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testInvalidUsername()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable Password Reset
        $passwdResetEnabled = $this->enablePasswordReset();
        $this->assertTrue($passwdResetEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

         // Create a password reset request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[Forgot Your Password?]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Request New Password',
            $this->webDriver->getTitle()
        );

        // Enter a username that does not exist in the database
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys('hyjbgdf');
        $this->webDriver->findElement(WebDriverBy::id('newpasswdreq'))->click();

        $this->assertEquals(
            WEBSITENAME . ': System Error',
            $this->webDriver->getTitle()
        );

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disablePasswdReset = $this->disablePasswordReset();
        $this->assertTrue($disablePasswdReset);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }

    /**
     * Test that the email has been sent
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testEmailSent()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable Password Reset
        $passwdResetEnabled = $this->enablePasswordReset();
        $this->assertTrue($passwdResetEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

         // Create a password reset request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[Forgot Your Password?]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Request New Password',
            $this->webDriver->getTitle()
        );

        // Enter a username that does not exist in the database
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys(SECUSERUSERNAME);
        $this->webDriver->findElement(WebDriverBy::id('newpasswdreq'))->click();

        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Email Confirmation',
            $this->webDriver->getTitle()
        );

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        $email = $this->getEmail();
        $this->assertStringContainsString(SECUSEREMAIL, $email);
        $this->assertStringContainsString("Password Request", $email);

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disablePasswdReset = $this->disablePasswordReset();
        $this->assertTrue($disablePasswdReset);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }
    /**
     * Test that the user can cancel the password rest request
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testCancelRequest()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable Password Reset
        $passwdResetEnabled = $this->enablePasswordReset();
        $this->assertTrue($passwdResetEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

         // Create a password reset request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[Forgot Your Password?]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Request New Password',
            $this->webDriver->getTitle()
        );

        // Enter a username
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys(SECUSERUSERNAME);
        $this->webDriver->findElement(WebDriverBy::id('newpasswdreq'))->click();

        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Email Confirmation',
            $this->webDriver->getTitle()
        );

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        // Get the URL for the Password reset
        $url = $this->getResetPasswdURL();
        $this->webDriver->get($url);
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Enter Password',
            $this->webDriver->getTitle()
        );


        // Cancel Request
        $this->webDriver->findElement(WebDriverBy::id('cancel'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Request Cancelled',
            $this->webDriver->getTitle()
        );
        $checkEmailAddr = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/Your password request has been cancelled./',
            $checkEmailAddr->getText()
        );

        // Check that the request has been cancelled by using the same token
        $this->webDriver->get($url);
        $this->assertEquals(
            WEBSITENAME . ': Invalid Request',
            $this->webDriver->getTitle()
        );

        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#error_msg')
        );
        $this->assertRegExp(
            '/This is not a valid password change token/',
            $checkMsg->getText()
        );



        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disablePasswdReset = $this->disablePasswordReset();
        $this->assertTrue($disablePasswdReset);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }

    /**
     * Test that an error occurs if the password format is wrong
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testInvalidPasswordFormat()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable Password Reset
        $passwdResetEnabled = $this->enablePasswordReset();
        $this->assertTrue($passwdResetEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

         // Create a password reset request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[Forgot Your Password?]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Request New Password',
            $this->webDriver->getTitle()
        );

        // Enter a username
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys(SECUSERUSERNAME);
        $this->webDriver->findElement(WebDriverBy::id('newpasswdreq'))->click();

        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Email Confirmation',
            $this->webDriver->getTitle()
        );

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        // Get the URL for the Password reset
        $url = $this->getResetPasswdURL();
        $this->webDriver->get($url);
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Enter Password',
            $this->webDriver->getTitle()
        );


        // Enter the same password in both boxes but of the wrong format
        $this->webDriver
            ->findElement(WebDriverBy::name('newpasswd'))
            ->sendKeys("qwerty");
        $this->webDriver
            ->findElement(WebDriverBy::name('newpasswd2'))
            ->sendKeys("qwerty");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check that we remain on the same page and an error msg is displayed
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Enter Password',
            $this->webDriver->getTitle()
        );

        $checkPasswdError = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Unable to validate passwords/',
            $checkPasswdError->getText()
        );

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disablePasswdReset = $this->disablePasswordReset();
        $this->assertTrue($disablePasswdReset);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }

    /**
     * Test that an error occurs if the two passwords do not match
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testDifferentPasswords()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable Password Reset
        $passwdResetEnabled = $this->enablePasswordReset();
        $this->assertTrue($passwdResetEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

         // Create a password reset request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[Forgot Your Password?]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Request New Password',
            $this->webDriver->getTitle()
        );

        // Enter a username
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys(SECUSERUSERNAME);
        $this->webDriver->findElement(WebDriverBy::id('newpasswdreq'))->click();

        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Email Confirmation',
            $this->webDriver->getTitle()
        );

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        // Get the URL for the Password reset
        $url = $this->getResetPasswdURL();
        $this->webDriver->get($url);
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Enter Password',
            $this->webDriver->getTitle()
        );


        // Enter different Passwords in each Box
        $this->webDriver
            ->findElement(WebDriverBy::name('newpasswd'))
            ->sendKeys("phpUnit1");
        $this->webDriver
            ->findElement(WebDriverBy::name('newpasswd2'))
            ->sendKeys("phpUnit2");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check that we remain on the same page and an error msg is displayed
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Enter Password',
            $this->webDriver->getTitle()
        );

        $checkPasswdError = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Unable to validate passwords/',
            $checkPasswdError->getText()
        );

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disablePasswdReset = $this->disablePasswordReset();
        $this->assertTrue($disablePasswdReset);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }

    /**
     * Test that the password can be changed.
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testPasswdResetSuccess()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable Password Reset
        $passwdResetEnabled = $this->enablePasswordReset();
        $this->assertTrue($passwdResetEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

         // Create a password reset request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[Forgot Your Password?]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Request New Password',
            $this->webDriver->getTitle()
        );

        // Enter a username
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys(SECUSERUSERNAME);
        $this->webDriver->findElement(WebDriverBy::id('newpasswdreq'))->click();

        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Email Confirmation',
            $this->webDriver->getTitle()
        );

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        // Get the URL for the Password reset
        $url = $this->getResetPasswdURL();
        $this->webDriver->get($url);
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Enter Password',
            $this->webDriver->getTitle()
        );


        // Enter the same password in both boxes but of the wrong format
        $this->webDriver
            ->findElement(WebDriverBy::name('newpasswd'))
            ->sendKeys(PASSWORD);
        $this->webDriver
            ->findElement(WebDriverBy::name('newpasswd2'))
            ->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Confirm that the Password has been updated by checkin title and page body
        $this->assertEquals(
            WEBSITENAME . ': Reset Password - Password Updated',
            $this->webDriver->getTitle()
        );

        $checkPasswdUpdated = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/Your password has been updated/',
            $checkPasswdUpdated->getText()
        );

        // CHECK we can log in using the new password

        // TODO

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disablePasswdReset = $this->disablePasswordReset();
        $this->assertTrue($disablePasswdReset);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }


    /**
     * Reset the Parameters to their default value
     *
     * @group selenium
     * @group resetpassword
     *
     * @return void
     */
    public function testResetParameters()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Reset to normal
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disablePasswdReset = $this->disablePasswordReset();
        $this->assertTrue($disablePasswdReset);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }
}
