<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Facebook\WebDriver;

use PHPUnit\Framework\TestCase;

// Load the Selenium Driver and Application Data.
require_once "constants.php";
require_once dirname(__FILE__) .'/../_data/database.php';

// Include the Class Autoloader to load database driver
require_once __DIR__ . '/../../includes/global.php';

/**
 * Login/Logout Functional Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

class RegisterTest extends TestCase
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
     * @return null No return data
     */
    protected function setUp()
    {
        global $testdsn;

        // Set up the user details
        $this->username = "testuser";
        $this->useremail = "testuser@example.com";

        // Load the Webdriver from constants.php
        $this->webDriver = MyWebDriver::load();

        // Delete the test user if they exist in the database
        $db = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($db)) {
            $searchdata = array('user_name'=> $this->username);
            $result = $db->dbdelete('users', $searchdata);
            if (\webtemplate\general\General::isError($result)) {
                echo "Error deleting test user";
            }
        } else {
            echo "Failed to connect to Database";
        }
    }

    /**
     * Function to close the Webdriver after each test is complete
     *
     * @return null no return data
     */
    public function tearDown()
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

        // Delete the test user if they exist in the database
        $db = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($db)) {
            $searchdata = array('user_name'=> $this->username);
            $result = $db->dbdelete('users', $searchdata);
            if (\webtemplate\general\General::isError($result)) {
                echo "Error deleting test user";
            }
        } else {
            echo "Failed to connect to Database";
        }
    }


    /**
     * Login to the User Authentication Page and enable the self registration
     * capability of the application
     *
     * @return boolean True if Self registration is enabled false otherwise
     */
    private function enableSelfRegistration()
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
            $this->webDriver->findElement(WebDriverBy::id('create_account'))
        );

        // Change the Option to YES
        $createAccSelect->selectByVisibleText("Yes");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Logout of the Website and Check the Link exists
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();

        $checkCreateAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        if (strpos($checkCreateAccount->getText(), "[New Account]") === false) {
            $selfregistrationenabled = false;
        }

        return $selfregistrationenabled;
    }





    /**
     * Login to the User Authentication Page and disable the self registration
     * capability of the application
     *
     * @return boolean True if Self registration is enabled false otherwise
     */
    private function disableSelfRegistration()
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
            $this->webDriver->findElement(WebDriverBy::id('create_account'))
        );
        $createAccounts = $createAccSelect->getFirstSelectedOption()->getText();

        // Change the Option to YES
        $createAccSelect->selectByVisibleText("No");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();

        $checkCreateAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));

        if (strpos($checkCreateAccount->getText(), "[New Account]") !== false) {
            $selfregistrationenabled = false;
        }

        return $selfregistrationenabled;
    }

    /**
     * This function returns the URL from the test email created by the application
     *
     * @return string Registration URL from email
     */
    private function getRegistrationURL()
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
     * Check that the Self Registration link does not exist.
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testNoSelfRegistrationLink()
    {

        // Open the Website and check the create account link is missing
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $checkCreateAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertNotContains(
            '[New Account]',
            $checkCreateAccount->getText()
        );
    }


    /**
     * Check that the Self Registration link can be activated and deactivated
     *
     * @group selenium
     * @group register
     *
     * @depends testNoSelfRegistrationLink
     *
     * @return null No return data
     */
    public function testSelfRegistrationActive()
    {

        // Open the Website and check the create account link is missing
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $checkNoNewAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertNotContains(
            '[New Account]',
            $checkNoNewAccount->getText()
        );

        // Enable the Self Registration Link
        $enableresult = $this->enableSelfRegistration();
        $this->assertTrue($enableresult);

        $checkNewAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertContains(
            '[New Account]',
            $checkNewAccount->getText()
        );

        // Disable the Self Registration Link
        $disableresult = $this->disableSelfRegistration();
        $this->assertTrue($disableresult);

        $checkCreateAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertNotContains(
            '[New Account]',
            $checkCreateAccount->getText()
        );
    }

    /**
     * Test the responce when register.php is called from the web browser when
     * self registration is disabled.
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testSelfRegistrationDisabled()
    {
        $this->webDriver->get(URL . "register.php");
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Test the responce when register.php is called from the home page and the
     * email system is currently disabled.
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testSelfRegistrationNoEmail()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enavle registration
        $registrationEnabled = $this->enableSelfRegistration();
        $this->assertTrue($registrationEnabled);

        // Create new account request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[New Account]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Email Address',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('email1'))
            ->sendKeys($this->useremail);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Get an error responce
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
        $disableRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableRegistration);
    }



    /**
     * Test calling webtemplate with an invalid registration request
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testSelfRegistrationInvalidRequest()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enavle registration
        $registrationEnabled = $this->enableSelfRegistration();
        $this->assertTrue($registrationEnabled);

        $phpscript = "register.php";
        $command = "newuser";
        $validstring = "huqxkj4olg";
        $invalidstring = "hliwqhki";

        // Test for a valid token type not in the database
        $appcomand = $phpscript . "?" . $command . "=" .$validstring;
        $this->webDriver->get(URL . $appcomand);
        $this->assertEquals(
            WEBSITENAME . ': Invalid Request',
            $this->webDriver->getTitle()
        );

        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#error_msg')
        );
        $this->assertRegExp(
            '/The link used has either expired or in incorrect/',
            $checkMsg->getText()
        );

        //Test with incorrect Token length
        $appcomand = $phpscript . "?" . $command . "=" .$invalidstring;
        $this->webDriver->get(URL . $appcomand);
        $this->assertEquals(
            WEBSITENAME . ': Invalid Request',
            $this->webDriver->getTitle()
        );

        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#error_msg')
        );
        $this->assertRegExp(
            '/Unable to validate new account request/',
            $checkMsg->getText()
        );


        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disableRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableRegistration);
    }

    /**
     * Test creating a registration request with an invalid email address
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testSelfRegistrationInvalidEmail()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable registration
        $registrationEnabled = $this->enableSelfRegistration();
        $this->assertTrue($registrationEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);


        // Create new account request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[New Account]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Email Address',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('email1'))
            ->sendKeys("testuser@example.com!");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Get an error responce
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Email Address',
            $this->webDriver->getTitle()
        );

        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp(
            '/Invalid email address format. Please re-enter your email address./',
            $checkMsg->getText()
        );

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disableRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableRegistration);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }

    /**
     * Test creating a registration request with an existing email address
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testSelfRegistrationExistingEmail()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable registration
        $registrationEnabled = $this->enableSelfRegistration();
        $this->assertTrue($registrationEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);


        // Create new account request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[New Account]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Email Address',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('email1'))
            ->sendKeys(USEREMAIL);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        $this->assertEquals(
            WEBSITENAME . ": Register new user - Email Confirmation",
            $this->webDriver->getTitle()
        );

        // Get the email and make sure it is the the right one
        $email = $this->getEmail();
        $this->assertContains(
            'which already exists',
            $email
        );
        $this->assertContains(
            'This attempt to user your email address to create a new account',
            $email
        );

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disableRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableRegistration);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }



    /**
     * Test creating a registration request
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testRegistrationEmailSent()
    {
         // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable registration
        $registrationEnabled = $this->enableSelfRegistration();
        $this->assertTrue($registrationEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

        // Create new account request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[New Account]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Email Address',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('email1'))
            ->sendKeys($this->useremail);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );


        $this->assertEquals(
            WEBSITENAME . ": Register new user - Email Confirmation",
            $this->webDriver->getTitle()
        );


        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disableRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableRegistration);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }



    /**
     * Test creating a registration request and then cancelling it
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testRegistrationCancelRequest()
    {
         // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable registration
        $registrationEnabled = $this->enableSelfRegistration();
        $this->assertTrue($registrationEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

        // Create new account request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[New Account]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Email Address',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('email1'))
            ->sendKeys($this->useremail);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        $this->assertEquals(
            WEBSITENAME . ": Register new user - Email Confirmation",
            $this->webDriver->getTitle()
        );

        $url = $this->getRegistrationURL();
        $this->webDriver->get($url);
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkEmailAddr = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/testuser@example.com/',
            $checkEmailAddr->getText()
        );

        // Cancel Request
        $this->webDriver->findElement(WebDriverBy::id('cancel'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Request Cancelled',
            $this->webDriver->getTitle()
        );
        $checkEmailAddr = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/Your account request has been cancelled./',
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
            '/The link used has either expired or in incorrect/',
            $checkMsg->getText()
        );

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disableRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableRegistration);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }



    /**
     * Test creating a registration request with a duplicate user name
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testRegistrationDuplicateUserName()
    {
         // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable registration
        $registrationEnabled = $this->enableSelfRegistration();
        $this->assertTrue($registrationEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

        // Create new account request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[New Account]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Email Address',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('email1'))
            ->sendKeys($this->useremail);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        $this->assertEquals(
            WEBSITENAME . ": Register new user - Email Confirmation",
            $this->webDriver->getTitle()
        );

        $url = $this->getRegistrationURL();
        $this->webDriver->get($url);
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkEmailAddr = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/testuser@example.com/',
            $checkEmailAddr->getText()
        );

        // Enter Details including duplicate username
        // UserName
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys(USERNAME);

        // Real Name
        $this->webDriver
            ->findElement(WebDriverBy::name('realname'))
            ->sendKeys(USERREALNAME);

        // Password 1
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password1'))
            ->sendKeys(PASSWORD);


        // Password 2
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password2'))
            ->sendKeys(PASSWORD);

        // Submit Details
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Get Error for duplicate user name
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkDupNameMsg= $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Invalid User Name/',
            $checkDupNameMsg->getText()
        );
        $this->assertRegExp(
            '/User Exists/',
            $checkDupNameMsg->getText()
        );

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disableRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableRegistration);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }



    /**
     * Test creating a registration request with an invalid user name
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testRegistrationInvalidUserName()
    {
         // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable registration
        $registrationEnabled = $this->enableSelfRegistration();
        $this->assertTrue($registrationEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

        // Create new account request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[New Account]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Email Address',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('email1'))
            ->sendKeys($this->useremail);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        $this->assertEquals(
            WEBSITENAME . ": Register new user - Email Confirmation",
            $this->webDriver->getTitle()
        );

        $url = $this->getRegistrationURL();
        $this->webDriver->get($url);
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkEmailAddr = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/testuser@example.com/',
            $checkEmailAddr->getText()
        );

        // Enter Details including duplicate username
        // UserName
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys('test_user');

        // Real Name
        $this->webDriver
            ->findElement(WebDriverBy::name('realname'))
            ->sendKeys(USERREALNAME);

        // Password 1
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password1'))
            ->sendKeys(PASSWORD);


        // Password 2
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password2'))
            ->sendKeys(PASSWORD);

        // Submit Details
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Get Error for duplicate user name
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkInvalidUserNameMsg= $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Invalid User Name/',
            $checkInvalidUserNameMsg->getText()
        );
        $this->assertNotRegExp(
            '/User Exists/',
            $checkInvalidUserNameMsg->getText()
        );

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disableRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableRegistration);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }

    /**
     * Test creating a registration with an invalid Real Name
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testRegistrationInvalidRealName()
    {
         // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable registration
        $registrationEnabled = $this->enableSelfRegistration();
        $this->assertTrue($registrationEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

        // Create new account request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[New Account]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Email Address',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('email1'))
            ->sendKeys($this->useremail);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        $this->assertEquals(
            WEBSITENAME . ": Register new user - Email Confirmation",
            $this->webDriver->getTitle()
        );

        $url = $this->getRegistrationURL();
        $this->webDriver->get($url);
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkEmailAddr = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/testuser@example.com/',
            $checkEmailAddr->getText()
        );

        // Enter Details including duplicate username
        // UserName
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys($this->username);

        // Real Name
        $this->webDriver
            ->findElement(WebDriverBy::name('realname'))
            ->sendKeys("Dummay_Name");

        // Password 1
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password1'))
            ->sendKeys(PASSWORD);


        // Password 2
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password2'))
            ->sendKeys(PASSWORD);

        // Submit Details
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Get Error for duplicate user name
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkInvalidRealNameMsg= $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Invalid Real Name/',
            $checkInvalidRealNameMsg->getText()
        );

        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disableRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableRegistration);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }

    /**
     * Test creating a registration with missing or invalid passwords
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testRegistrationInvalidPasswords()
    {
         // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable registration
        $registrationEnabled = $this->enableSelfRegistration();
        $this->assertTrue($registrationEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

        // Create new account request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[New Account]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Email Address',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('email1'))
            ->sendKeys($this->useremail);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        $this->assertEquals(
            WEBSITENAME . ": Register new user - Email Confirmation",
            $this->webDriver->getTitle()
        );

        $url = $this->getRegistrationURL();
        $this->webDriver->get($url);
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkEmailAddr = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/testuser@example.com/',
            $checkEmailAddr->getText()
        );

        // Enter Details including duplicate username
        // UserName
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys($this->username);

        // Real Name
        $this->webDriver
            ->findElement(WebDriverBy::name('realname'))
            ->sendKeys(USERREALNAME);

        /**
         * FIRST TEST WITH PASSWORD ONE MISSING
         */

        // Password 2
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password2'))
            ->sendKeys(PASSWORD);

        // Submit Details
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Get Error for duplicate user name
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkMissingPasswd1Msg= $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Invalid Password/',
            $checkMissingPasswd1Msg->getText()
        );


        /**
         * SECOND TEST PASSWORD ONE NOT EQUAL TO PASSWORD TWO
         */
        // Password 1
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password1'))
            ->sendKeys(PASSWORD);


        // Password 2
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password2'))
            ->sendKeys("Dummy1");

        // Submit Details
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Get Error for duplicate user name
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkPasswdNotEqualMsg= $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Invalid Password/',
            $checkPasswdNotEqualMsg->getText()
        );



        /**
         * THIRD TEST WITH PASSWORD EQUAL BUT DONT MEET REGEXP
         */
        // Password 1
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password1'))
            ->sendKeys("phpUnit");


        // Password 2
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password2'))
            ->sendKeys("phpUnit");

        // Submit Details
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Get Error for duplicate user name
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkMissingPasswd1Msg= $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Invalid Password/',
            $checkMissingPasswd1Msg->getText()
        );


        // Reset to normal
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disableRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableRegistration);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }


    /**
     * Test creating a registration with an valid user
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testRegistrationSucess()
    {
         // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enable registration
        $registrationEnabled = $this->enableSelfRegistration();
        $this->assertTrue($registrationEnabled);

        // Enable the email system
        $testemailon = $this->enableEmail();
        $this->assertTrue($testemailon);

        // Create new account request
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('[New Account]'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Email Address',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('email1'))
            ->sendKeys($this->useremail);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check that the email has been sent and the page updated
        $checkEmailSent = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/A confirmation email has been sent containing a link/',
            $checkEmailSent->getText()
        );

        $this->assertEquals(
            WEBSITENAME . ": Register new user - Email Confirmation",
            $this->webDriver->getTitle()
        );

        $url = $this->getRegistrationURL();
        $this->webDriver->get($url);
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Enter Details',
            $this->webDriver->getTitle()
        );
        $checkEmailAddr = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/testuser@example.com/',
            $checkEmailAddr->getText()
        );

        // Enter Details including duplicate username
        // UserName
        $this->webDriver
            ->findElement(WebDriverBy::name('user_name'))
            ->sendKeys($this->username);

        // Real Name
        $this->webDriver
            ->findElement(WebDriverBy::name('realname'))
            ->sendKeys(USERREALNAME);

        // Password 1
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password1'))
            ->sendKeys(PASSWORD);


        // Password 2
        $this->webDriver
            ->findElement(WebDriverBy::name('new_password2'))
            ->sendKeys(PASSWORD);

        // Submit Details
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Get Error for duplicate user name
        $this->assertEquals(
            WEBSITENAME . ': Register new user - Account Created',
            $this->webDriver->getTitle()
        );
        $checkSuccessMsg= $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/Your account has been created/',
            $checkSuccessMsg->getText()
        );

        // Reset Email and Selef registration to OFF
        $this->webDriver->findElement(WebDriverBy::linkText("Home"))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disableRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableRegistration);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);


        // Test that the new user can log in
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys($this->username);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Logout of the site and check it was successful
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Logout'));
        $link->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Reset the Parameters to their default value
     *
     * @group selenium
     * @group register
     *
     * @return null No return data
     */
    public function testResetParameters()
    {
        // Connect to the site
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Reset to normal
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $disableSelfRegistration = $this->disableSelfRegistration();
        $this->assertTrue($disableSelfRegistration);
        $testemailoff = $this->disableEmail();
        $this->assertTrue($testemailoff);
    }
}
