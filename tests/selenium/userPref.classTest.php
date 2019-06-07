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
 * User Preferences Functional Tests
 *
 **/
class UserprefTest extends TestCase
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
     * Test the user can access their General Preference Page check the right error
     * message is received if the form is input when no changes are made
     *
     * @group selenium
     * @group users
     *
     * @return void
     */
    public function testGenPrefNoChange()
    {

        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Preferences'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(
            WEBSITENAME . ': User Preferences: phpunit',
            $this->webDriver->getTitle()
        );

        $h2 = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp(
            '/General Preferences/',
            $h2->getText()
        );

        $checkPageName = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#left'));
        $this->assertEquals(
            WEBSITENAME . ': User Preferences: phpunit',
            $checkPageName->getText()
        );

        // Submit the General Preferences with no changes
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/No changes have been made/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Test the user can access their General Preference Page and change the
     * the selected CSS pages.
     *
     * @group selenium
     * @group users
     *
     * @return void
     */
    public function testGenPrefTheme()
    {

        // Open the Website and Log in and check that the login was sucessful
        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Preferences'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(
            WEBSITENAME . ': User Preferences: phpunit',
            $this->webDriver->getTitle()
        );

        /**
         * CHANGES TO THEME OPTIONS
         * Options are Dusk (Default) and Dusk
         * Restore to Default Value after Tests Complete.
         */
        // Check the Default Value
        $themesSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('theme'))
        );
        $selectedTheme = $themesSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Dusk (Site Default)', $selectedTheme);

        //Change to a Particular Value (Dusk) and check correct
        $themesSelect->selectByVisibleText('Dusk');
        $selectedTheme = $themesSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Dusk', $selectedTheme);

        // Submit the General Preferences with Theme Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Theme Updated/', $checkMsg->getText());
        $themesSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('theme'))
        );
        $selectedTheme = $themesSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Dusk', $selectedTheme);

        // Restore Defaults
        $themesSelect->selectByVisibleText('Dusk (Site Default)');
        $selectedTheme = $themesSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Dusk (Site Default)', $selectedTheme);


        // Submit the General Preferences with Theme Change
        // Check Message and Value on the "Select"
         $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Theme Updated/', $checkMsg->getText());
        $themesSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('theme'))
        );
        $selectedTheme = $themesSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Dusk (Site Default)', $selectedTheme);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test the user can access their General Preference Page and change the
     * Wither Textareas are enlaged when they are selected and shrunk when
     * deselected
     *
     * @group selenium
     * @group users
     *
     * @return void
     */
    public function testGenPrefTextArea()
    {

        // Open the Website and Log in and check that the login was sucessful
        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Preferences'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(
            WEBSITENAME . ': User Preferences: phpunit',
            $this->webDriver->getTitle()
        );

        /**
         * CHANGES TO THE ZOOM TEXT AREA OPTIONS
         * Options are On (Default), On and Off
         * Restore to Default after Tests Complete
         */
        // Check the Default Value
        $ZTASelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('zoom_textareas'))
        );
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('On (Site Default)', $selectedZTA);

        //Change to a Particular Value (On) and check correct
        $ZTASelect->selectByVisibleText('On');
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('On', $selectedZTA);

        // Submit the General Preferences with Zoom Text Areas Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Text Area Zoom Updated/', $checkMsg->getText());
        $ZTASelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('zoom_textareas'))
        );
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('On', $selectedZTA);

        //Change to a Particular Value (Off) and check correct
        $ZTASelect->selectByVisibleText('Off');
        $ZTASelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('zoom_textareas'))
        );
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Off', $selectedZTA);

        // Submit the General Preferences with Zoom Text Areas Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Text Area Zoom Updated/', $checkMsg->getText());
        $ZTASelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('zoom_textareas'))
        );
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Off', $selectedZTA);

        //Change to a Particular Value (Default) and check correct
        $ZTASelect->selectByVisibleText('On (Site Default)');
        $ZTASelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('zoom_textareas'))
        );
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('On (Site Default)', $selectedZTA);

        // Submit the General Preferences with Zoom Text Areas Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Text Area Zoom Updated/', $checkMsg->getText());
        $ZTASelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('zoom_textareas'))
        );
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('On (Site Default)', $selectedZTA);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test the user can access their General Preference Page and change the
     * the number or records to be reyruned in a Search
     *
     * @group selenium
     * @group users
     *
     * @return void
     */
    public function testGenPrefRows()
    {
        // Open the Website and Log in and check that the login was sucessful
        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Preferences'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(
            WEBSITENAME . ': User Preferences: phpunit',
            $this->webDriver->getTitle()
        );

        /**
         * CHANGES TO THE DISPLAY ROWS OPTIONS
         * Options are 20 (Site Default), 10, 20, 30, 40 and 50
         * Restore to Default after Tests Complete
         */
        // Check the Default Value
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('20 (Site Default)', $displayRows);

        //Change to a Particular Value (10) and check correct
        $rowsSelect->selectByVisibleText('10');
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('10', $displayRows);

        // Submit the General Preferences with Zoom Text Areas Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Number of Data Rows to display/',
            $checkMsg->getText()
        );
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('10', $displayRows);

        //Change to a Particular Value (20) and check correct
        $rowsSelect->selectByVisibleText('20');
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('20', $displayRows);

        // Submit the General Preferences with Zoom Text Areas Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Number of Data Rows to display/',
            $checkMsg->getText()
        );
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('20', $displayRows);

        //Change to a Particular Value (30) and check correct
        $rowsSelect->selectByVisibleText('30');
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('30', $displayRows);

        // Submit the General Preferences with Zoom Text Areas Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Number of Data Rows to display/',
            $checkMsg->getText()
        );
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('30', $displayRows);

        //Change to a Particular Value (40) and check correct
        $rowsSelect->selectByVisibleText('40');
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('40', $displayRows);

        // Submit the General Preferences with Zoom Text Areas Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Number of Data Rows to display/',
            $checkMsg->getText()
        );
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('40', $displayRows);

        //Change to a Particular Value (50) and check correct
        $rowsSelect->selectByVisibleText('50');
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('50', $displayRows);

        // Submit the General Preferences with Zoom Text Areas Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Number of Data Rows to display/',
            $checkMsg->getText()
        );
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('50', $displayRows);

        //Change to the Default and check correct
        $rowsSelect->selectByVisibleText('20 (Site Default)');
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('20 (Site Default)', $displayRows);

        // Submit the General Preferences with Zoom Text Areas Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Number of Data Rows to display/',
            $checkMsg->getText()
        );
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('20 (Site Default)', $displayRows);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test access to the Users Account Preferences and check the current data is
     * correct
     *
     * @group selenium
     * @group users
     *
     * @return void
     */
    public function testAccountCurrentData()
    {

        // Open the Website and Log in and check that the login was sucessful
        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Preferences'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(
            WEBSITENAME . ': User Preferences: phpunit',
            $this->webDriver->getTitle()
        );

        // Switch to the Account Tab
        $permissions = $this->webDriver->findElement(WebDriverBy::id("tab_account"));
        $permissions->click();

        //Check we are on the Permissions tab
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/Name and Password/', $h2->getText());

        // Check the User information for phpunit is correct
        $realname = $this->webDriver->findElement(WebDriverBy::id('realname'));
        $this->assertRegExp('/Phpunit User/', $realname->getAttribute("value"));

        $email = $this->webDriver->findElement(WebDriverBy::id('useremail'));
        $this->assertRegExp('/phpunit@example.com/', $email->getAttribute("value"));

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test access to the Users Account Preferences and check that data can only
     * be saved if the current password is input.
     *
     * @group selenium
     * @group users
     *
     * @depends testAccountCurrentData
     *
     * @return void
     */
    public function testAccountInvalidPasswd()
    {

        // Open the Website and Log in and check that the login was sucessful
        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Preferences'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(
            WEBSITENAME . ': User Preferences: phpunit',
            $this->webDriver->getTitle()
        );

        // Switch to the Account Tab
        $permissions = $this->webDriver->findElement(WebDriverBy::id("tab_account"));
        $permissions->click();

        //Check we are on the Permissions tab
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/Name and Password/', $h2->getText());

        // Check for no current password
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Invalid Current Password/', $checkMsg->getText());

        // Check for wrong current password
        $this->webDriver
            ->findElement(WebDriverBy::name('current_password'))
            ->sendKeys('phpUnit41');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Invalid Current Password/', $checkMsg->getText());

        //Update User Data
        // Enter Current Password and check update works
        $this->webDriver
            ->findElement(WebDriverBy::name('current_password'))
            ->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Preferences Updated/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test access to the Users Account Preferences and Change the Users password.
     * test for different input values and invalid passwords
     *
     * @group selenium
     * @group users
     *
     * @depends testAccountInvalidPasswd
     *
     * @return void
     */
    public function testAccountChangePasswd()
    {

        // Open the Website and Log in and check that the login was sucessful
        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Preferences'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(
            WEBSITENAME . ': User Preferences: phpunit',
            $this->webDriver->getTitle()
        );

        // Switch to the Account Tab

        $permissions = $this->webDriver->findElement(WebDriverBy::id("tab_account"));
        $permissions->click();

        //Check we are on the Permissions tab
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/Name and Password/', $h2->getText());

        //Change the Password
        // Put in the Original Password
        $this->webDriver
            ->findElement(WebDriverBy::name('current_password'))
            ->sendKeys(PASSWORD);

        // Input the the new Password in both boxes
        $this->webDriver
            ->findElement(WebDriverBy::name('passwd'))
            ->sendKeys("TestPasswd2");
        $this->webDriver
            ->findElement(WebDriverBy::name('passwd2'))
            ->sendKeys("TestPasswd2");

        // Update the Password
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Preferences Updated/', $checkMsg->getText());

        //RESTORE THE PASSWORD
        // Put in the Original Password
        $this->webDriver
            ->findElement(WebDriverBy::name('current_password'))
            ->sendKeys("TestPasswd2");

        // Input the the new Password in both boxes
        $this->webDriver
            ->findElement(WebDriverBy::name('passwd'))
            ->sendKeys(PASSWORD);
        $this->webDriver
            ->findElement(WebDriverBy::name('passwd2'))
            ->sendKeys(PASSWORD);

        // Update the Password
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Preferences Updated/', $checkMsg->getText());

        // TEST WITH AN INVALID PASSWORD
        // Put in the Original Password
        $this->webDriver
            ->findElement(WebDriverBy::name('current_password'))
            ->sendKeys(PASSWORD);

        // Input the the new Password in both boxes
        $this->webDriver
            ->findElement(WebDriverBy::name('passwd'))
            ->sendKeys("testpasswd");
        $this->webDriver
            ->findElement(WebDriverBy::name('passwd2'))
            ->sendKeys("testpasswd");

        // Update the Password
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Invalid Password/', $checkMsg->getText());

        // TEST WITH DIFFERENT PASSWORDS
        // Put in the Original Password
        $this->webDriver
            ->findElement(WebDriverBy::name('current_password'))
            ->sendKeys(PASSWORD);

        // Input the the new Password in both boxes
        $this->webDriver
            ->findElement(WebDriverBy::name('passwd'))
            ->sendKeys("TestPasswd2");
        $this->webDriver
            ->findElement(WebDriverBy::name('passwd2'))
            ->sendKeys("Testpasswd2");

        // Update the Password
         $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Passwords are not the sam/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test access to the Users Account Preferences and Change their Name and E-Mail
     * Address.  Check for Invalid Names and E-Mail Addresses
     *
     * @group selenium
     * @group users
     *
     *  @depends testAccountInvalidPasswd
     *
     * @return void
     */
    public function testAccountChangeDetails()
    {

        // Open the Website and Log in and check that the login was sucessful
        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Preferences'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(
            WEBSITENAME . ': User Preferences: phpunit',
            $this->webDriver->getTitle()
        );

        // Switch to the Account Tab
        $permissions = $this->webDriver->findElement(WebDriverBy::id("tab_account"));
        $permissions->click();

        //Check we are on the Permissions tab
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/Name and Password/', $h2->getText());

        // CHANGE THE REALNAME AND EMAIL
        // GET THE LINKS TO THE INPUT BOXES and SAVE THE ORIGINAL DATA
        $realname = $this->webDriver->findElement(WebDriverBy::id('realname'));
        $email = $this->webDriver->findElement(WebDriverBy::id('useremail'));

        // Save the Real Data
        $realnamevalue = $realname->getAttribute("value");
        $emailvaluse   = $email->getAttribute("value");

        // PUT NEW VALUES IN AND SAVE
        $realname->clear();
        $email->clear();
        $realname->sendKeys("Test User");
        $email->sendKeys("testuser@example.com");
        $this->webDriver
            ->findElement(WebDriverBy::name('current_password'))
            ->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Preferences Updated/', $checkMsg->getText());

        //Check Update TOOK PLACE
        $realname = $this->webDriver->findElement(WebDriverBy::id('realname'));
        $this->assertRegExp('/Test User/', $realname->getAttribute("value"));

        $email = $this->webDriver->findElement(WebDriverBy::id('useremail'));
        $this->assertRegExp('/testuser@example.com/', $email->getAttribute("value"));

        //RESTORE THE ORIGIONAL DATA
        $realname = $this->webDriver->findElement(WebDriverBy::id('realname'));
        $email = $this->webDriver->findElement(WebDriverBy::id('useremail'));

        // Save the Real Data
        $realname->clear();
        $email->clear();
        $realname->sendKeys($realnamevalue);
        $email->sendKeys($emailvaluse);
        $this->webDriver
            ->findElement(WebDriverBy::name('current_password'))
            ->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Preferences Updated/', $checkMsg->getText());

        //Check the original data is back
        $realname = $this->webDriver->findElement(WebDriverBy::id('realname'));
        $this->assertRegExp('/Phpunit User/', $realname->getAttribute("value"));

        $email = $this->webDriver->findElement(WebDriverBy::id('useremail'));
        $this->assertRegExp('/phpunit@example.com/', $email->getAttribute("value"));

        // Test for an invalid Real Name
        $realname = $this->webDriver->findElement(WebDriverBy::id('realname'));
        $realname->clear();
        $realname->sendKeys("Phpunit USER!");
        $this->webDriver
            ->findElement(WebDriverBy::name('current_password'))
            ->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Invalid Real Name/', $checkMsg->getText());

        // TEST FOR AN INVALID E_MAIL ADDRESS
        $email = $this->webDriver->findElement(WebDriverBy::id('useremail'));
        $email->clear();
        $email->sendKeys("phpunit#example.com");
        $this->webDriver
            ->findElement(WebDriverBy::name('current_password'))
            ->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Invalid Email Address/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test access to the Users Permissions Preferences Page
     *
     * @group selenium
     * @group users
     *
     * @return void
     */
    public function testPermissions()
    {

        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Preferences'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(
            WEBSITENAME . ': User Preferences: phpunit',
            $this->webDriver->getTitle()
        );

        // Switch to the Permissions TAB
        $this->webDriver->findElement(WebDriverBy::id("tab_permissions"))->click();

        //Check we are on the Permissions tab
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/Permissions/', $h2->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that the user can move around the Preferences Pages using the
     * Hyperlinks rather than the Clickable areas
     *
     * @group selenium
     * @group users
     *
     * @return void
     */
    public function testLinks()
    {

        // Open the Website and Log in and check that the login was sucessful
        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Switch to the Admin Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Preferences'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(
            WEBSITENAME . ': User Preferences: phpunit',
            $this->webDriver->getTitle()
        );

        // Switch using the Links
        // Switch to the Accounts TAB
        $this->webDriver
            ->findElement(WebDriverBy::linkText("Name and Password"))
            ->click();

        //Check we are on the Accounts tab
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/Name and Password/', $h2->getText());


        // PERMISSIONS TAB
        // Switch to the Permissions TAB
        $this->webDriver->findElement(WebDriverBy::linkText("Permissions"))->click();

        //Check we are on the Accounts tab
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/Permissions/', $h2->getText());

        // GENERAL PREFERENCES TAB
        // Switch to the General Preferences TAB
        $this->webDriver
            ->findElement(WebDriverBy::linkText("General Preferences"))
            ->click();

        //Check we are on the Accounts tab
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Switch using the tab ids
        // Switch to the Accounts TAB
        $this->webDriver
            ->findElement(WebDriverBy::id("tab_account"))
            ->click();

        //Check we are on the Accounts tab
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/Name and Password/', $h2->getText());


        // PERMISSIONS TAB
        // Switch to the Permissions TAB
        $this->webDriver->findElement(WebDriverBy::id("tab_permissions"))->click();

        //Check we are on the Accounts tab
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/Permissions/', $h2->getText());

        // GENERAL PREFERENCES TAB
        // Switch to the General Preferences TAB
        $this->webDriver->findElement(WebDriverBy::id("tab_settings"))->click();

        //Check we are on the Accounts tab
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }
}
