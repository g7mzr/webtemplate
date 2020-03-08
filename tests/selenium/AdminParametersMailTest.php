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

require_once "constants.php";

/**
 * Admin Parameters Mail Functional Tests
 *
 **/
class AdminParametersMailTest extends TestCase
{

    /**
     * Remote Webdriver
     *
     * @var \RemoteWebDriver
     */
    protected $webDriver;

    /**
     * Setup the following Class Variables using constants.php:
     * BROWSER: The Web browser to be used for the tests
     * URL: The Web location of the test site.
     *
     * @return void
     */
    protected function setUp(): void
    {

        // Load the Webdriver from constants.php
        $this->webDriver = MyWebDriver::load();
    }

    /**
     * Function to close the Webdriver after each test is complete
     *
     * @return void
     */
    public function tearDown(): void
    {

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
    }

    /**
     * Logon to the email parameters Page and make sure the existing values are
     * correct.  Instructions for setting up the correct values are in the
     * Developer's Handbook
     *
     * @group selenium
     * @group admin
     *
     * @return void
     */
    public function testMailPageContents()
    {

        // Open the Website and Log in
        // Check that the login was sucessful
        // Move to the Authentications Settings Page
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver->findElement(WebDriverBy::LinkText('Setup'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $this->webDriver->getTitle()
        );
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $checkPageName->getText()
        );

        // Move to the E-mail Setup page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('e-mail'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/e-mail/',
            $checkSelectedSection->getText()
        );

        // Check the Mail Delivery Method
        $mailDeliverySelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );
        $mailDeliveryMethod = $mailDeliverySelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('None', $mailDeliveryMethod);

        // Check the email address is correct
        $emailAddress = $this->webDriver
            ->findElement(WebDriverBy::id('email_address_id'))
            ->getAttribute("value");
        $this->assertEquals(USEREMAIL, $emailAddress);

        // Check the SMTP Server is empty
        $smtpServer = $this->webDriver
            ->findElement(WebDriverBy::id('smtp_server_id'))
            ->getAttribute("value");
        $this->assertEquals('', $smtpServer);

        // Check the SMTP Username is empty
        $smtpUserName = $this->webDriver
            ->findElement(WebDriverBy::id('smtp_user_name_id'))
            ->getAttribute("value");
        $this->assertEquals('', $smtpUserName);

        // Check the SMTP password is empty
        $smtpPasswd = $this->webDriver
            ->findElement(WebDriverBy::id('smtp_passwd_id'))
            ->getAttribute("value");
        $this->assertEquals('', $smtpPasswd);

        // Check that SMTP Debug is disabled.
        $smtpDebugSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('smtp_debug'))
        );
        $smtpDebug = $smtpDebugSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('No', $smtpDebug);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Login to the e-mail parameters page and an error is reported if the page
     * is submitted with no changes
     *
     * @group selenium
     * @group admin
     *
     * @depends testMailPageContents
     *
     * @return void
     */
    public function testMailPageNoChanges()
    {

        // Open the Website and Log in
        // Check that the login was sucessful
        // Move to the Authentications Settings Page
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver->findElement(WebDriverBy::LinkText('Setup'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $this->webDriver->getTitle()
        );
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $checkPageName->getText()
        );

        // Move to the E-mail Setup page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('e-mail'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/e-mail/',
            $checkSelectedSection->getText()
        );

        // Check warning is displayed if page saved with no changes
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/You Have Not Made Any Changes/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Login to the email parameters page and test that the user can change the
     * E-Mail Delivery Method between test and None.
     *
     * @group selenium
     * @group admin
     *
     * @depends testMailPageContents
     *
     * @return void
     */
    public function testMailPageDeliveryMethodTest()
    {

        // Open the Website and check the create account link is missing
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver->findElement(WebDriverBy::LinkText('Setup'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $this->webDriver->getTitle()
        );
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $checkPageName->getText()
        );

        // Move to the E-mail Setup page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('e-mail'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/e-mail/',
            $checkSelectedSection->getText()
        );

        // Test that the delivery method id set to None
        $mailDeliverySelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );
        $mailDeliveryMethod = $mailDeliverySelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('None', $mailDeliveryMethod);

        // Change the Option to Test
        $mailDeliverySelect->selectByVisibleText('Test');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverby::cssSelector('div#msg-box'));
        $this->assertRegExp('/Mail Delivery Method Changed/', $checkMsg->getText());
        $mailDeliverySelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );
        $mailDeliveryMethod = $mailDeliverySelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Test', $mailDeliveryMethod);

        // Change the Option to None
        $mailDeliverySelect->selectByVisibleText('None');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverby::cssSelector('div#msg-box'));
        $this->assertRegExp('/Mail Delivery Method Changed/', $checkMsg->getText());
        $mailDeliverySelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );
        $mailDeliveryMethod = $mailDeliverySelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('None', $mailDeliveryMethod);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Login to the e-mail parameters page and test that the user can change the
     * E-Mail Delivery Method to SMTP.  A SMTP server must be entered
     *
     * @group selenium
     * @group admin
     *
     * @depends testMailPageContents
     *
     * @return void
     */
    public function testMailPageDeliveryMethodSMTP()
    {

        // Open the Website and check the create account link is missing
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver->findElement(WebDriverBy::LinkText('Setup'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $this->webDriver->getTitle()
        );
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $checkPageName->getText()
        );

        // Move to the E-mail Setup page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('e-mail'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/e-mail/',
            $checkSelectedSection->getText()
        );

        // Test that the delivery method id set to None
        $mailDeliverySelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );
        $mailDeliveryMethod = $mailDeliverySelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('None', $mailDeliveryMethod);

        // Change the Option to Test
        $mailDeliverySelect->selectByVisibleText('SMTP');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverby::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/You must enter the name of your SMTP server/',
            $checkMsg->getText()
        );
        $mailDeliverySelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );
        $mailDeliveryMethod = $mailDeliverySelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('None', $mailDeliveryMethod);


        // Change to SMTP with a valid Server
        $mailDeliverySelect->selectByVisibleText('SMTP');

        $this->webDriver->findElement(WebDriverBy::id('smtp_server_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('smtp_server_id'))
            ->sendKeys("mail.example.com");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Mail Delivery Method Changed/', $checkMsg->getText());
        $this->assertRegExp('/SMTP Server Changed/', $checkMsg->getText());
        $mailDeliverySelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );
        $mailDeliveryMethod = $mailDeliverySelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('SMTP', $mailDeliveryMethod);
        $smtpServer = $this->webDriver
            ->findElement(WebDriverBy::id('smtp_server_id'))
            ->getAttribute("value");
        $this->assertEquals('mail.example.com', $smtpServer);

        //Try a mail Server Change
        $this->webDriver->findElement(WebDriverBy::id('smtp_server_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('smtp_server_id'))
            ->sendKeys("smtp.example.com");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/SMTP Server Changed/', $checkMsg->getText());

        $smtpServer = $this->webDriver
            ->findElement(WebDriverBy::id('smtp_server_id'))
            ->getAttribute("value");
        $this->assertEquals('smtp.example.com', $smtpServer);

        //Try an invalid mail server address
        $this->webDriver->findElement(WebDriverBy::id('smtp_server_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('smtp_server_id'))
            ->sendKeys("smtp@example.com");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Invalid SMTP Server Name/', $checkMsg->getText());


        // Change the Option to None and clear the SMTP Server
        $mailDeliverySelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );
        $mailDeliverySelect->selectByVisibleText('None');
        $this->webDriver->findElement(WebDriverBy::id('smtp_server_id'))->clear();
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Mail Delivery Method Changed/', $checkMsg->getText());
        $mailDeliverySelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('mail_delivery_method'))
        );
        $mailDeliveryMethod = $mailDeliverySelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('None', $mailDeliveryMethod);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Login to the E-Mail Parameters Page and check the user can change the From
     * Address
     *
     * @group selenium
     * @group admin
     *
     * @depends testMailPageContents
     *
     * @return void
     */
    public function testMailPageEmailAddress()
    {

        // Open the Website and check the create account link is missing
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver->findElement(WebDriverBy::LinkText('Setup'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $this->webDriver->getTitle()
        );
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $checkPageName->getText()
        );

        // Move to the E-mail Setup page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('e-mail'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/e-mail/',
            $checkSelectedSection->getText()
        );

        // Check the email address is correct and Save it
        $emailAddressSave = $this->webDriver
            ->findElement(WebDriverBy::id('email_address_id'))
            ->getAttribute("value");
        $this->assertEquals(USEREMAIL, $emailAddressSave);

        // Put in a new Value update and check
        $this->webDriver->findElement(WebDriverBy::id('email_address_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('email_address_id'))
            ->sendKeys("test@example.com");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/E-Mail Address Changed/', $checkMsg->getText());
        $emailAddressSave = $this->webDriver
            ->findElement(WebDriverBy::id('email_address_id'))
            ->getAttribute("value");
        $this->assertEquals("test@example.com", $emailAddressSave);

        // Test with an invalid email address
        $this->webDriver->findElement(WebDriverBy::id('email_address_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('email_address_id'))
            ->sendKeys("test.example.com");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Invalid E-mail Address/', $checkMsg->getText());

        // Restore the origional e-mail address
        $this->webDriver->findElement(WebDriverBy::id('email_address_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('email_address_id'))
            ->sendKeys(USEREMAIL);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/E-Mail Address Changed/', $checkMsg->getText());
        $emailAddressSave = $this->webDriver
            ->findElement(WebDriverBy::id('email_address_id'))
            ->getAttribute("value");
        $this->assertEquals(USEREMAIL, $emailAddressSave);

        // Logout of the Website
        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Login to the User Authentication Page and test that the user can change the
     * SMTP User Name and Password
     *
     * @group selenium
     * @group admin
     *
     * @depends testMailPageContents
     *
     * @return void
     */
    public function testMailPageSMTPUserName()
    {

        // Open the Website and check the create account link is missing
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver->findElement(WebDriverBy::LinkText('Setup'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $this->webDriver->getTitle()
        );
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $checkPageName->getText()
        );

        // Move to the E-mail Setup page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('e-mail'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/e-mail/',
            $checkSelectedSection->getText()
        );

        // Try and set a username without a password
        $this->webDriver->findElement(WebDriverBy::id('smtp_user_name_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('smtp_user_name_id'))
            ->sendKeys("smtpuser");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/You must enter both the SMTP Username and Password/',
            $checkMsg->getText()
        );

        // Set a username and password
        $this->webDriver->findElement(WebDriverBy::id('smtp_user_name_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('smtp_user_name_id'))
            ->sendKeys("smtpuser");
        $this->webDriver->findElement(WebDriverBy::id('smtp_passwd_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('smtp_passwd_id'))
            ->sendKeys("smtpuser");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/SMTP User Name Changed/', $checkMsg->getText());
        $this->assertRegExp('/SMTP Password Changed/', $checkMsg->getText());
        $smtpUserName = $this->webDriver
            ->findElement(WebDriverBy::id('smtp_user_name_id'))
            ->getAttribute("value");
        $this->assertEquals('smtpuser', $smtpUserName);
        $smtpPasswd = $this->webDriver
            ->findElement(WebDriverBy::id('smtp_passwd_id'))
            ->getAttribute("value");
        $this->assertEquals('smtpuser', $smtpPasswd);

        // Change the Username
        $this->webDriver->findElement(WebDriverBy::id('smtp_user_name_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('smtp_user_name_id'))
            ->sendKeys("mailuser");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/SMTP User Name Changed/', $checkMsg->getText());
        $smtpUserName = $this->webDriver
            ->findElement(WebDriverBy::id('smtp_user_name_id'))
            ->getAttribute("value");
        $this->assertEquals('mailuser', $smtpUserName);

        // Change the password
        $this->webDriver->findElement(WebDriverBy::id('smtp_passwd_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('smtp_passwd_id'))
            ->sendKeys("smtppasswd");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/SMTP Password Changed/', $checkMsg->getText());
        $smtpPasswd = $this->webDriver
            ->findElement(WebDriverBy::id('smtp_passwd_id'))
            ->getAttribute("value");
        $this->assertEquals('smtppasswd', $smtpPasswd);

        // Change the Option to None and clear the SMTP Server
        $this->webDriver->findElement(WebDriverBy::id('smtp_user_name_id'))->clear();
        $this->webDriver->findElement(WebDriverBy::id('smtp_passwd_id'))->clear();
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/SMTP User Name Changed/', $checkMsg->getText());
        $this->assertRegExp('/SMTP Password Changed/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Login to the User Authentication Page and test that the user can change the
     * SMTP debug Parameter
     *
     * @group selenium
     * @group admin
     *
     * @depends testMailPageContents
     *
     * @return void
     */
    public function testMailPageSMTPDebug()
    {

        // Open the Website and check the create account link is missing
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver->findElement(WebDriverBy::LinkText('Setup'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $this->webDriver->getTitle()
        );
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $checkPageName->getText()
        );

        // Move to the E-mail Setup page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('e-mail'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/e-mail/',
            $checkSelectedSection->getText()
        );

        // Test that the SMTP Debug is set to No
        $smtpDebugSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('smtp_debug'))
        );
        $smtpDebug = $smtpDebugSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('No', $smtpDebug);

        // Change the Option to Yes
        $smtpDebugSelect->selectByVisibleText('Yes');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/SMTP Debug Changed/', $checkMsg->getText());
        $smtpDebugSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('smtp_debug'))
        );
        $smtpDebug = $smtpDebugSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('Yes', $smtpDebug);

        // Change the Option to back to No
        $smtpDebugSelect->selectByVisibleText('No');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/SMTP Debug Changed/', $checkMsg->getText());
        $smtpDebugSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('smtp_debug'))
        );
        $smtpDebug = $smtpDebugSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('No', $smtpDebug);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }
}
