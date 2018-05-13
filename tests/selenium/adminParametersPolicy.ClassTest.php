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

require_once "constants.php";

/**
 * Policy Parameters Functional Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/


class AdminParametersPolicyTest extends TestCase
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
     * @return null No return data
     */
    protected function setUp()
    {

        // Load the Webdriver from constants.php
        $this->webDriver = MyWebDriver::load();
    }

    /**
     * Function to close the Webdriver after each test is complete
     *
     * @return null no return data
     */
    public function tearDown()
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
     * Logon to the Administrative Policy Page and make sure the existing values are
     * correct.  Instructions for setting up the correct values are in the
     * Developers' Handbook
     *
     * @group selenium
     * @group admin
     *
     * @return null No return data
     */
    public function testAdminPolicyPageContents()
    {

        // Open the Website and Log in
        // Check that the login was sucessful
        // Move to the Required Settings Page
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

        // Move to the Administrative Polices page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administrative Policys'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Administrative Policys/',
            $checkSelectedSection->getText()
        );

        // Check the Correct Logging Level is Set
        $loggingLevelSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logging'))
        );
        $loggingLevel = $loggingLevelSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Errors', $loggingLevel);

        // Check the Correct Log rotate Level is Set
        $logRotateSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logrotate'))
        );
        $logRotate = $logRotateSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Weekly', $logRotate);

        // Check the option to open links in new windows is set
        $newWindowSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('new_window'))
        );
        $newWindow = $newWindowSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Yes', $newWindow);

        // Check the max number of records to display is set
        $maxRecordsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('max_records'))
        );
        $maxRecords = $maxRecordsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('200', $maxRecords);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Login to the Administrative Policy Page and test that that an error is issued
     * if the page is submitted with no changes
     *
     * @group selenium
     * @group admin
     *
     * @depends testAdminPolicyPageContents
     *
     * @return null No return data
     */
    public function testAdminPolicyPageNoChanges()
    {

        // Open the Website and Log in
        // Check that the login was sucessful
        // Move to the Required Settings Page
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

        // Move to the Administrative Polices page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administrative Policys'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Administrative Policys/',
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
     * Login to the Administrative Policy Page and test that the user can change the
     * Logging Value
     *
     * @group selenium
     * @group admin
     *
     * @depends testAdminPolicyPageContents
     *
     * @return null No return data
     */
    public function testAdminPolicyPageLogging()
    {

        // Open the Website and Log in
        // Check that the login was sucessful
        // Move to the Required Settings Page
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

        // Move to the Administrative Polices page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Administrative Policys'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Administrative Policys/',
            $checkSelectedSection->getText()
        );

        // Create the initial Select Object
        $loggingLevelSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logging'))
        );

        // Test that Each of the Logging Values can be selected
        // NONE
        $loggingLevelSelect->selectByVisibleText('None');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Logging Level Parameter Changed/',
            $checkMsg->getText()
        );
        $loggingLevelSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logging'))
        );
        $logging = $loggingLevelSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('None', $logging);

        // ERRORS
        $loggingLevelSelect->selectByVisibleText('Errors');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Logging Level Parameter Changed/',
            $checkMsg->getText()
        );
        $loggingLevelSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logging'))
        );
        $logging = $loggingLevelSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('Errors', $logging);

        // WARNINGS
        $loggingLevelSelect->selectByVisibleText('Warnings');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Logging Level Parameter Changed/',
            $checkMsg->getText()
        );
        $loggingLevelSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logging'))
        );
        $logging = $loggingLevelSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('Warnings', $logging);

        // INFORMATION
        $loggingLevelSelect->selectByVisibleText('Information');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Logging Level Parameter Changed/',
            $checkMsg->getText()
        );
        $loggingLevelSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logging'))
        );
        $logging = $loggingLevelSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('Information', $logging);

        // DEBUG
        $loggingLevelSelect->selectByVisibleText('Debug');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Logging Level Parameter Changed/',
            $checkMsg->getText()
        );
        $loggingLevelSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logging'))
        );
        $logging = $loggingLevelSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('Debug', $logging);

        // DEBUG
        $loggingLevelSelect->selectByVisibleText('Trace');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Logging Level Parameter Changed/',
            $checkMsg->getText()
        );
        $loggingLevelSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logging'))
        );
        $logging = $loggingLevelSelect->getFirstSelectedOption()->getText();
        $this->assertEquals('Trace', $logging);

        // Restore the Origional Values
        $loggingLevelSelect->selectByVisibleText('Errors');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Logging Level Parameter Changed/',
            $checkMsg->getText()
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Login to the Administrative Policy Page and test that the user can change the
     * Log Rotate value
     *
     * @group selenium
     * @group admin
     *
     * @depends testAdminPolicyPageContents
     *
     * @return null No return data
     */
    public function testAdminPolicyPageLogRotate()
    {

        // Open the Website and Log in
        // Check that the login was sucessful
        // Move to the Required Settings Page
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
            ->findElement(WebDriverBy::LinkText('Administrative Policys'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Administrative Policys/',
            $checkSelectedSection->getText()
        );


        // Create the initial SELECT Object
        $logRotateSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logrotate'))
        );

        // Test that Each of the Log Rotate values can be chosen
        // DAILY
        $logRotateSelect->selectByVisibleText('Daily');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Log Rotate Parameter Changed/', $checkMsg->getText());
        $logRotateSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logrotate'))
        );
        $logRotate = $logRotateSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Daily', $logRotate);

        // WEEKLY
        $logRotateSelect->selectByVisibleText('Weekly');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Log Rotate Parameter Changed/', $checkMsg->getText());
        $logRotateSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logrotate'))
        );
        $logRotate = $logRotateSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Weekly', $logRotate);

        // MONTHLY
        $logRotateSelect->selectByVisibleText('Monthly');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Log Rotate Parameter Changed/', $checkMsg->getText());
        $logRotateSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('logrotate'))
        );
        $logRotate = $logRotateSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Monthly', $logRotate);

        // Restore the Origional Values
        $logRotateSelect->selectByVisibleText('Weekly');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Log Rotate Parameter Changed/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Login to the Administrative Policy Page and test that the user can change the
     * New Page value.
     *
     * @group selenium
     * @group admin
     *
     * @depends testAdminPolicyPageContents
     *
     * @return null No return data
     */
    public function testAdminPolicyPageNewWindow()
    {

        // Open the Website and Log in
        // Check that the login was sucessful
        // Move to the Required Settings Page
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
            ->findElement(WebDriverBy::LinkText('Administrative Policys'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Administrative Policys/',
            $checkSelectedSection->getText()
        );

        // Initial SELECT Object
        $newWindowSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('new_window'))
        );

        // Test that the No option for New Windows can be selected
        $newWindowSelect->selectByVisibleText('No');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/New Window Parameter Changed/', $checkMsg->getText());
        $newWindowSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('new_window'))
        );
        $newWindow = $newWindowSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('No', $newWindow);

        // Restore the Origional Values
        $newWindowSelect->selectByVisibleText('Yes');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/New Window Parameter Changed/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Login to the Administrative Policy Page and test that the user can change the
     * Max numver of records to be returned value
     *
     * @group selenium
     * @group admin
     *
     * @depends testAdminPolicyPageContents
     *
     * @return null No return data
     */
    public function testAdminPolicyPageMaxRecords()
    {

        // Open the Website and Log in
        // Check that the login was sucessful
        // Move to the Required Settings Page
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
            ->findElement(WebDriverBy::LinkText('Administrative Policys'))->click();
        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Administrative Policys/',
            $checkSelectedSection->getText()
        );

        // Initial SELECT Object
        $maxRecordsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('max_records'))
        );

        // test the the maximum number of records that can be returned can be
        // changed
        // 100
        $maxRecordsSelect->selectByVisibleText("100");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Max Records Parameter Changed/', $checkMsg->getText());
        $maxRecordsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('max_records'))
        );
        $maxRecords = $maxRecordsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('100', $maxRecords);

        // 200
        $maxRecordsSelect->selectByVisibleText("200");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Max Records Parameter Changed/', $checkMsg->getText());
        $maxRecordsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('max_records'))
        );
        $maxRecords = $maxRecordsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('200', $maxRecords);

        //300
        $maxRecordsSelect->selectByVisibleText("300");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Max Records Parameter Changed/', $checkMsg->getText());
        $maxRecordsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('max_records'))
        );
        $maxRecords = $maxRecordsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('300', $maxRecords);

        // 400
        $maxRecordsSelect->selectByVisibleText("400");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Max Records Parameter Changed/', $checkMsg->getText());
        $maxRecordsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('max_records'))
        );
        $maxRecords = $maxRecordsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('400', $maxRecords);

        // 500
        $maxRecordsSelect->selectByVisibleText("500");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Max Records Parameter Changed/', $checkMsg->getText());
        $maxRecordsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('max_records'))
        );
        $maxRecords = $maxRecordsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('500', $maxRecords);

        // Restore the Original Values
        $maxRecordsSelect->selectByVisibleText("200");
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Max Records Parameter Changed/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }
}
