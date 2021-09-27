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
 * Authentication Parameters Functional Tests
 *
 **/
class AdminParametersAuthenticationTest extends TestCase
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
            if (
                $status == \PHPUnit\Runner\BaseTestRunner::STATUS_ERROR
                || $status == \PHPUnit\Runner\BaseTestRunner::STATUS_FAILURE
            ) {
                //Check that the browser is not htmlunit or that we are testing
                // the Help Files.
                if (
                    (BROWSER != "htmlunit")
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
     * Logon to the User Authentication Page and make sure the existing values are
     * correct.  Instructions for setting up the correct values are in the
     * Developers' Handbook
     *
     * @group selenium
     * @group admin
     *
     * @return void
     */
    public function testUserAuthenticationPageContents()
    {

        // Open the Website and Log in
        // Check that the login was sucessful
        // Move to the Authentications Settings Page
         // Open the Website Login Page and check that it is the correct page
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enter the User Name and Password
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        //Switch to the Edit Configuration Page
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

        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );

        // Check Create accounts = No
        $createAccSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('create_account'))
        );
        $createAccounts = $createAccSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('No', $createAccounts);

        // Check that new password = No
        $newPasswdSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('new_password'))
        );
        $newPassword = $newPasswdSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('No', $newPassword);

        // Check that password Strength = Letters and Numbers
        $newPasswdStrengthSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdstrength'))
        );
        $passwdStrength = $newPasswdStrengthSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Letters and Numbers', $passwdStrength);

        // Check that the User Regular Expression = /^[a-zA-Z0-9]{5,12}$/
        $regEx = $this->webDriver->findElement(WebDriverBy::id('regexp'));
        $this->assertEquals("/^[a-zA-Z0-9]{5,12}$/", $regEx->getAttribute("value"));

        // Check the User Regular Expression Description
        $RegExDesc = $this->webDriver->findElement(WebDriverBy::id('regexpdesc'));
        $this->assertRegExp(
            '/Must between 5 and 12 characters long and contain upper and lower/',
            $RegExDesc->getAttribute("value")
        );

        // Check Password Aging Policy = None
        $passwdAgeSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdage'))
        );
        $passwdAge = $passwdAgeSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('None', $passwdAge);

        // Check autocomplete = disabled
        $autoCompleteSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('autocomplete'))
        );
        $autoComplete = $autoCompleteSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Disabled', $autoComplete);

        // Check that the Autologout is set to Session
        $autoLogoutSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('autologout'))
        );
        $autoLogout = $autoLogoutSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Session', $autoLogout);


        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Login to the User Authentication Page and an error is reported if the page
     * is submitted with no changes
     *
     * @group selenium
     * @group admin
     *
     * @depends testUserAuthenticationPageContents
     *
     * @return void
     */
    public function testUserAuthenticationPageNoChanges()
    {

        // Open the Website and Log in
        // Check that the login was sucessful
        // Move to the Authentications Settings Page
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enter the User Name and Password
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        //Switch to the Edit Configuration Page
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

        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );

        // Check warning is displayed if page saved with no changes
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp('/You Have Not Made Any Changes/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Login to the User Authentication Page and test that the user can change the
     * Create Account Value.  Check that the link appears on the home page at the
     * appropriate time
     *
     * @group selenium
     * @group admin
     *
     * @depends testUserAuthenticationPageContents
     *
     * @return void
     */
    public function testUserAuthenticationPageCreateAccount()
    {

        // Open the Website and check the create account link is missing
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $checkCreateAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringNotContainsString(
            '[New Account]',
            $checkCreateAccount->getText()
        );


        // Enter the User Name and Password
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        //Switch to the Edit Configuration Page
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

        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );

        // Test that the New Account Option is set to No
        $createAccSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('create_account'))
        );
        $createAccounts = $createAccSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('No', $createAccounts);

        // Change the Option to YES
        $createAccSelect->selectByVisibleText("Yes");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp(
            '/Create Accounts Parameter Changed/',
            $checkMsg->getText()
        );
        $createAccSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('create_account'))
        );
        $createAccounts = $createAccSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('Yes', $createAccounts);

        // Logout of the Website and Check the Link exists
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        $checkCreateAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringContainsString(
            '[New Account]',
            $checkCreateAccount->getText()
        );

        // Log back in and Change the parameter back to No
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        //Switch to the Edit Configuration Page
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

        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );

        // Test that the New Account Option is set to Yes
        $createAccSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('create_account'))
        );
        $createAccounts = $createAccSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('Yes', $createAccounts);

        // Change the Option to YES
        $createAccSelect->selectByVisibleText("No");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp(
            '/Create Accounts Parameter Changed/',
            $checkMsg->getText()
        );
        $createAccSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('create_account'))
        );
        $createAccounts = $createAccSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('No', $createAccounts);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Check the link has been removed
        $checkCreateAccount = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringNotContainsString(
            '[New Account]',
            $checkCreateAccount->getText()
        );
    }


    /**
     * Login to the User Authentication Page and test that the user can change the
     * Request new password  Value.  Check that the link appears on the home page
     * at the appropriate time
     *
     * @group selenium
     * @group admin
     *
     * @depends testUserAuthenticationPageContents
     *
     * @return void
     */
    public function testUserAuthenticationPageNewPassword()
    {

        // Open the Website and check the create account link is missing
        $this->webDriver->get(URL);
        $checkNewPassword = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringNotContainsString(
            '[Forgot Your Password?]',
            $checkNewPassword->getText()
        );
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        //Switch to the Edit Configuration Page
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

        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );




        // Test that the New Account Option is set to No
        $newPasswdSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('new_password'))
        );
        $newPassword = $newPasswdSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('No', $newPassword);

        // Change the Option to YES
        $newPasswdSelect->selectByVisibleText("Yes");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/New Password Parameter Changed/',
            $checkMsg->getText()
        );
        $newPasswdSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('new_password'))
        );
        $newPassword = $newPasswdSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('Yes', $newPassword);

        // Logout of the Website and Check the Link exists
        $this->webDriver->findElement(WebDriverBy::linkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());


        $checkNewPassword = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringContainsString(
            '[Forgot Your Password?]',
            $checkNewPassword->getText()
        );

        // Log back in and Change the parameter back to No
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administration'))->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        //Switch to the Edit Configuration Page
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

        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );

        // Test that the New Account Option is set to Yes
        $newPasswdSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('new_password'))
        );
        $newPassword = $newPasswdSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('Yes', $newPassword);

        // Change the Option to No
        $newPasswdSelect->selectByVisibleText("No");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/New Password Parameter Changed/',
            $checkMsg->getText()
        );
        $newPasswdSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('new_password'))
        );
        $newPassword = $newPasswdSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('No', $newPassword);

        // Logout of the Website
        $this->webDriver->findElement(WebDriverBy::linkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Check the link has been removed
        $checkNewPassword = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringNotContainsString(
            '[Forgot Your Password?]',
            $checkNewPassword->getText()
        );
    }


    /**
     * Login to the User Authentication Page and test that the user can change the
     * password strength Value.
     *
     * @group selenium
     * @group admin
     *
     * @depends testUserAuthenticationPageContents
     *
     * @return void
     */
    public function testUserAuthenticationPagePasswordStrength()
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
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );

        // Test that the Password Strength Option is set to Letters and Numbers.
        $newPasswdStrengthSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdstrength'))
        );
        $passwdStrength = $newPasswdStrengthSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Letters and Numbers', $passwdStrength);

        // Change the Option to 'No Constraints'
        $newPasswdStrengthSelect->selectByVisibleText('No Constraints');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Password Strength has changed/', $checkMsg->getText());
        $newPasswdStrengthSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdstrength'))
        );
        $passwdStrength = $newPasswdStrengthSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('No Constraints', $passwdStrength);


        // Change the Option to 'Letters'
        $newPasswdStrengthSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdstrength'))
        );
        $newPasswdStrengthSelect->selectByVisibleText('Letters');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Password Strength has changed/', $checkMsg->getText());
        $newPasswdStrengthSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdstrength'))
        );
        $passwdStrength = $newPasswdStrengthSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Letters', $passwdStrength);


        // Change the Option to 'mixed Letters'
        $newPasswdStrengthSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdstrength'))
        );
        $newPasswdStrengthSelect->selectByVisibleText('Mixed Letters');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Password Strength has changed/', $checkMsg->getText());
        $newPasswdStrengthSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdstrength'))
        );
        $passwdStrength = $newPasswdStrengthSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Mixed Letters', $passwdStrength);


        // Change the Option to 'No Constraints
        $newPasswdStrengthSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdstrength'))
        );
        $newPasswdStrengthSelect
            ->selectByVisibleText('Letters, Numbers and Special Chars');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Password Strength has changed/', $checkMsg->getText());
        $newPasswdStrengthSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdstrength'))
        );
        $passwdStrength = $newPasswdStrengthSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Letters, Numbers and Special Chars', $passwdStrength);


        // Change the Option to 'Letters and Numbers'
        $newPasswdStrengthSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdstrength'))
        );
        $newPasswdStrengthSelect->selectByVisibleText('Letters and Numbers');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Password Strength has changed/', $checkMsg->getText());
        $newPasswdStrengthSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdstrength'))
        );
        $passwdStrength = $newPasswdStrengthSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Letters and Numbers', $passwdStrength);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Login to the User Authentication Page and test that the user can change the
     * Regular Expression used to validate user names
     *
     * @group selenium
     * @group admin
     *
     * @depends testUserAuthenticationPageContents
     *
     * @return void
     */
    public function testUserAuthenticationPageUserRegExp()
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
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );

        // Check that the User Regular Expression = /^[a-zA-Z0-9]{5,12}$/
        // And save it
        $saveRegEx = $this->webDriver
            ->findElement(WebDriverBy::id('regexp'))->getAttribute("value");
        $this->assertEquals("/^[a-zA-Z0-9]{5,12}$/", $saveRegEx);

        // Change the Regular Expression
        $this->webDriver->findElement(WebDriverBy::id('regexp'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('regexp'))->sendKeys("/^[a-zA-Z]{4,12}$/");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/User Name Regexp Parameter Changed/',
            $checkMsg->getText()
        );
        $regEx = $this->webDriver
            ->findElement(WebDriverBy::id('regexp'))->getAttribute("value");
        $this->assertEquals("/^[a-zA-Z]{4,12}$/", $regEx);

        // Test a bad Regular expression
        $this->webDriver->findElement(WebDriverBy::id('regexp'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('regexp'))
            ->sendKeys("/^[a-zA-Z0-9];{5,12}$/");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/User name Regular Expression contains invalid characters/',
            $checkMsg->getText()
        );

        // Restore the Regular Expression
        $this->webDriver->findElement(WebDriverBy::id('regexp'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('regexp'))
            ->sendKeys($saveRegEx);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/User Name Regexp Parameter Changed/',
            $checkMsg->getText()
        );
        $regEx = $this->webDriver
            ->findElement(WebDriverBy::id('regexp'))->getAttribute("value");
        $this->assertEquals("/^[a-zA-Z0-9]{5,12}$/", $regEx);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Login to the User Authentication Page and test that the user can change the
     * Regular Expression used to validate user names
     *
     * @group selenium
     * @group admin
     *
     * @depends testUserAuthenticationPageContents
     *
     * @return void
     */
    public function testUserAuthenticationPageRegExDescription()
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
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );

        // Save the original description and test it.
        $saveRegExDesc = $this->webDriver
            ->findElement(WebDriverBy::id('regexpdesc'))->getAttribute("value");
        $this->assertRegExp(
            '/Must between 5 and 12 characters long and contain upper and lower/',
            $saveRegExDesc
        );

        // Enter Test Data to the Description
        $this->webDriver->findElement(WebDriverBy::id('regexpdesc'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('regexpdesc'))
            ->sendKeys("This is test data. It will work.");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/User Name Regexp Description Parameter Changed/',
            $checkMsg->getText()
        );
        $regExDesc = $this->webDriver
            ->findElement(WebDriverBy::id('regexpdesc'))
            ->getAttribute("value");
        $this->assertRegExp(
            '/This is test data. It will work./',
            $regExDesc
        );

        // Enter Invalid Data
        $this->webDriver->findElement(WebDriverBy::id('regexpdesc'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('regexpdesc'))
            ->sendKeys("This is test data; It will not work.");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/User name Regular Expression Description contains invalid characters/',
            $checkMsg->getText()
        );

        // Restore the original data
        $this->webDriver->findElement(WebDriverBy::id('regexpdesc'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('regexpdesc'))
            ->sendKeys($saveRegExDesc);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/User Name Regexp Description Parameter Changed/',
            $checkMsg->getText()
        );
        $regExDesc = $this->webDriver
            ->findElement(WebDriverBy::id('regexpdesc'))
            ->getAttribute("value");
        $this->assertRegExp(
            '/Must between 5 and 12 characters long and contain upper and lower/',
            $regExDesc
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Login to the User Authentication Page and test that the user can change the
     * Password Aging Policy
     *
     * @group selenium
     * @group admin
     *
     * @depends testUserAuthenticationPageContents
     *
     * @return void
     */
    public function testUserAuthenticationPagePasswordAging()
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
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );

        // Check Password Agining Policy = None
        $passwdAgeSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdage'))
        );
        $passwdAge = $passwdAgeSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('None', $passwdAge);

        // Save at 60 Days
        $passwdAgeSelect->selectByVisibleText("60 Days");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Password Ageing has changed/', $checkMsg->getText());
        $passwdAgeSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdage'))
        );
        $passwdAge = $passwdAgeSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('60 Days', $passwdAge);

        // Save at 90 Days
        $passwdAgeSelect->selectByVisibleText("90 Days");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Password Ageing has changed/', $checkMsg->getText());
        $passwdAgeSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdage'))
        );
        $passwdAge = $passwdAgeSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('90 Days', $passwdAge);

        // Save at 180 Days
        $passwdAgeSelect->selectByVisibleText("180 Days");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Password Ageing has changed/', $checkMsg->getText());
        $passwdAgeSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdage'))
        );
        $passwdAge = $passwdAgeSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('180 Days', $passwdAge);

        // Save at None
        $passwdAgeSelect->selectByVisibleText("None");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Password Ageing has changed/', $checkMsg->getText());
        $passwdAgeSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('passwdage'))
        );
        $passwdAge = $passwdAgeSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('None', $passwdAge);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Login to the User Authentication Page and test that the user can change the
     * Regular Expression used to validate user names
     *
     * @group selenium
     * @group admin
     *
     * @depends testUserAuthenticationPageContents
     *
     * @return void
     */
    public function testUserAuthenticationPageAutoComplete()
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
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );

        // Check autocomplete = disabled
        $autoCompleteSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('autocomplete'))
        );
        $autoComplete = $autoCompleteSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Disabled', $autoComplete);

        $autoCompleteSelect->selectByVisibleText("Enabled");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Autocomplete has changed/', $checkMsg->getText());
        $autoCompleteSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('autocomplete'))
        );
        $autoComplete = $autoCompleteSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Enabled', $autoComplete);

        $autoCompleteSelect->selectByVisibleText("Disabled");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Autocomplete has changed/', $checkMsg->getText());
        $autoCompleteSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('autocomplete'))
        );
        $autoComplete = $autoCompleteSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Disabled', $autoComplete);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }



    /**
     * Login to the User Authentication Page and test that the user can change the
     * Auto Logout Policy
     *
     * @group selenium
     * @group admin
     *
     * @depends testUserAuthenticationPageContents
     *
     * @return void
     */
    public function testUserAuthenticationPageAutoLogout()
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
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('User Authentication'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );

        // Check Auto Logout Policy equals Session
        $autoLogoutSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('autologout'))
        );
        $autoLogout = $autoLogoutSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Session', $autoLogout);


        // Save at 10 Minutes
        $autoLogoutSelect->selectByVisibleText("10 Minutes");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Auto Logout has changed/', $checkMsg->getText());
        $autoLogoutSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('autologout'))
        );
        $autoLogout = $autoLogoutSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('10 Minutes', $autoLogout);


        // Save at 20 Minutes
        $autoLogoutSelect->selectByVisibleText("20 Minutes");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Auto Logout has changed/', $checkMsg->getText());
        $autoLogoutSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('autologout'))
        );
        $autoLogout = $autoLogoutSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('20 Minutes', $autoLogout);


        // Save at 30 Minutes
        $autoLogoutSelect->selectByVisibleText("30 Minutes");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Auto Logout has changed/', $checkMsg->getText());
        $autoLogoutSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('autologout'))
        );
        $autoLogout = $autoLogoutSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('30 Minutes', $autoLogout);


        // Save Never
        $autoLogoutSelect->selectByVisibleText("Never");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Auto Logout has changed/', $checkMsg->getText());
        $autoLogoutSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('autologout'))
        );
        $autoLogout = $autoLogoutSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Never', $autoLogout);

        // Save at Session
        $autoLogoutSelect->selectByVisibleText("Session");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Auto Logout has changed/', $checkMsg->getText());
        $autoLogoutSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('autologout'))
        );
        $autoLogout = $autoLogoutSelect
            ->getFirstSelectedOption()->getText();
        $this->assertEquals('Session', $autoLogout);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }
}
