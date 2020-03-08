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
 * Settings Parameters Functional Tests
 *
 **/
class AdminParametersSettingsTest extends TestCase
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
     * Logon to the Required Settings Page and make sure the existing values are
     * correct.  Instructions for setting up the correct values are in the
     * Developers' Handbook
     *
     * @group selenium
     * @group admin
     *
     * @return void
     */
    public function testRequiredSettingPageContents()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // Check the URL BASE  is correct
        $urlbase = $this->webDriver->findElement(WebDriverBy::id('url_base_id'));
        $this->assertEquals(URL, $urlbase->getAttribute("value"));

        // Check the maintainer  is correct
        $maintainer = $this->webDriver
            ->findElement(WebDriverBy::id('maintainer_id'));
        $this->assertEquals(USEREMAIL, $maintainer->getAttribute("value"));

        // Check the documentation base is correct
        $docbase = $this->webDriver->findElement(WebDriverBy::id('doc_baseurl_id'));
        $this->assertEquals('docs/en/html/', $docbase->getAttribute("value"));

        // Check the cookie domain is correct
        $cookiedomain = $this->webDriver
            ->findElement(WebDriverBy::id('cookie_domain_id'));
        $this->assertEquals('', $cookiedomain->getAttribute("value"));

        // Check the cookie path is correct
        $cookiepath = $this->webDriver
            ->findElement(WebDriverBy::id('cookie_path_id'));
        $this->assertEquals('/', $cookiepath->getAttribute("value"));

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Logon to the Required Settings Page and test with no changes
     *
     * @group selenium
     * @group admin
     *
     * @depends testRequiredSettingPageContents
     *
     * @return void
     */
    public function testRequiredSettingNoChanges()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // No Changes Made
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'))
            ->getText();
        $this->assertRegExp('/You Have Not Made Any Changes/', $checkMsg);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }




    /**
     * Logon to the Required Settings Page
     * Backup the existing data
     * Test if the Application URL can be changed
     * restore the original data
     *
     * @group selenium
     * @group admin
     *
     * @depends testRequiredSettingPageContents
     *
     * @return void
     */
    public function testRequiredSettingURL()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // Backup the original URL Data
        $urlbasetext = $this->webDriver
            ->findElement(WebDriverBy::id('url_base_id'))
            ->getAttribute("value");

        // Update URL Base
        $this->webDriver->findElement(WebDriverBy::id('url_base_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('url_base_id'))
            ->sendKeys('http://www.example.com');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Base URL Changed/', $checkMsg->getText());

        // Check that the NEW Values are all displayed
        $urlbase = $this->webDriver->findElement(WebDriverBy::id('url_base_id'));
        $this->assertEquals(
            'http://www.example.com',
            $urlbase->getAttribute("value")
        );

        // Restore the Origional Data
        $this->webDriver->findElement(WebDriverBy::id('url_base_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('url_base_id'))
            ->sendKeys($urlbasetext);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Base URL Changed/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Logon to the Required Settings Page a
     * Backup the existing data
     * Test if the maintainers details can be changed
     * restore the original data
     *
     * @group selenium
     * @group admin
     *
     * @depends testRequiredSettingPageContents
     *
     * @return void
     */
    public function testRequiredSettingMaintainer()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // Backup the original form contents
        $maintainertext = $this->webDriver
            ->findElement(WebDriverBy::id('maintainer_id'))
            ->getAttribute("value");

        // Update the Maintainer
        $this->webDriver->findElement(WebDriverBy::id('maintainer_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('maintainer_id'))
            ->sendKeys('testuser@example.com');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Maintainer\'s Address Changed/', $checkMsg->getText());

        // Check that the NEW Values are all displayed
        $maintainer = $this->webDriver
            ->findElement(WebDriverBy::id('maintainer_id'));
        $this->assertEquals(
            'testuser@example.com',
            $maintainer->getAttribute("value")
        );

        $this->webDriver->findElement(WebDriverBy::id('maintainer_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('maintainer_id'))
            ->sendKeys($maintainertext);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Maintainer\'s Address Changed/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Logon to the Required Settings Page
     * Backup the existing data
     * Test if the documentation base can be changed
     * restore the original data
     *
     * @group selenium
     * @group admin
     *
     * @depends testRequiredSettingPageContents
     *
     * @return void
     */
    public function testRequiredSettingDocbase()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // Backup the origional form contents
        $docbasetext = $this->webDriver
            ->findElement(WebDriverBy::id('doc_baseurl_id'))
            ->getAttribute("value");

        // Update the docbase URL
        $this->webDriver->findElement(WebDriverBy::id('doc_baseurl_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('doc_baseurl_id'))
            ->sendKeys('docs/en/');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Docs Base URL Changed/', $checkMsg->getText());

        // Check that the NEW Values are all displayed
        $docbase = $this->webDriver->findElement(WebDriverBy::id('doc_baseurl_id'));
        $this->assertEquals('docs/en/', $docbase->getAttribute("value"));

        // Restore the Original Data
        $this->webDriver->findElement(WebDriverBy::id('doc_baseurl_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('doc_baseurl_id'))
            ->sendKeys($docbasetext);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Docs Base URL Changed/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Logon to the Required Settings Page
     * Backup the existing data
     * Test if the Cookie Domain can be changed
     * restore the original data
     *
     * @group selenium
     * @group admin
     *
     * @depends testRequiredSettingPageContents
     *
     * @return void
     */
    public function testRequiredSettingCookieDomain()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // Backup the original form contents
        $cookiedomaintext = $this->webDriver
            ->findElement(WebDriverBy::id('cookie_domain_id'))
            ->getAttribute("value");

        // Update the cookie Domain URL
        $this->webDriver->findElement(WebDriverBy::id('cookie_domain_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('cookie_domain_id'))
            ->sendKeys(COOKIEDOMAIN);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Cookie Domain Changed/', $checkMsg->getText());

        // Check that the NEW Values are all displayed
        $cookiedomain = $this->webDriver
            ->findElement(WebDriverBy::id('cookie_domain_id'));
        $this->assertEquals(COOKIEDOMAIN, $cookiedomain->getAttribute("value"));

        // Restore the Original Data
        $this->webDriver->findElement(WebDriverBy::id('cookie_domain_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('cookie_domain_id'))
            ->sendKeys($cookiedomaintext);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Cookie Domain Changed/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Logon to the Required Settings Page
     * Backup the existing data
     * Test if the cookie path can be changed
     * restore the original data
     *
     * @group selenium
     * @group admin
     *
     * @depends testRequiredSettingPageContents
     *
     * @return void
     */
    public function testRequiredSettingCookiePath()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // Backup the original form contents
        $cookiepathtext = $this->webDriver
            ->findElement(WebDriverBy::id('cookie_path_id'))
            ->getAttribute("value");

        // Update the cookie Path
        $this->webDriver->findElement(WebDriverBy::id('cookie_path_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('cookie_path_id'))
            ->sendKeys('/test/');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Cookie Path Changed/', $checkMsg->getText());

        // Check that the NEW Values are all displayed
        $cookiepath = $this->webDriver
            ->findElement(WebDriverBy::id('cookie_path_id'));
        $this->assertEquals('/test/', $cookiepath->getAttribute("value"));

        // Restore the Original Data
        $this->webDriver->findElement(WebDriverBy::id('cookie_path_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('cookie_path_id'))
            ->sendKeys($cookiepathtext);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Cookie Path Changed/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Logon to the Required Settings Page
     * Backup the existing data
     * Test that warings are issued if invalid URL data is submitted
     * restore the original data
     *
     * @group selenium
     * @group admin
     *
     * @depends testRequiredSettingPageContents
     *
     * @return void
     */
    public function testRequiredSettingPageInvalidDataURL()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // Backup the origional form contents
        $urlbasetext = $this->webDriver
            ->findElement(WebDriverBy::id('url_base_id'))
            ->getAttribute("value");

        // test and Invalid Base URL
        $this->webDriver->findElement(WebDriverBy::id('url_base_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('url_base_id'))
            ->sendKeys('httpd://www.example.com');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Invalid URL Base/', $checkMsg->getText());
        $this->webDriver->findElement(WebDriverBy::id('url_base_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('url_base_id'))
            ->sendKeys($urlbasetext);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Logon to the Required Settings Page
     * Backup the existing data
     * Test that warings are issued if invalid maintainer data is submitted
     * restore the original data
     *
     * @group selenium
     * @group admin
     *
     * @depends testRequiredSettingPageContents
     *
     * @return void
     */
    public function testRequiredSettingPageInvalidDataMaintainer()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // Backup the original form contents
        $maintainertext = $this->webDriver
            ->findElement(WebDriverBy::id('maintainer_id'))
            ->getAttribute("value");

        // Test an invalid Maintainer's e-mail address
        $this->webDriver->findElement(WebDriverBy::id('maintainer_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('maintainer_id'))
            ->sendKeys('testuserexample.com');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Invalid Maintainer\'s Email Address/',
            $checkMsg->getText()
        );
        $this->webDriver->findElement(WebDriverBy::id('maintainer_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('maintainer_id'))
            ->sendKeys($maintainertext);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }
    /**
     * Logon to the Required Settings Page
     * Backup the existing data
     * Test that warings are issued if invalid Docbase data is submitted
     * restore the original data
     *
     * @group selenium
     * @group admin
     *
     * @depends testRequiredSettingPageContents
     *
     * @return void
     */
    public function testRequiredSettingPageInvalidDataDocBase()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // Backup the origional form contents
        $docbasetext = $this
            ->webDriver->findElement(WebDriverBy::id('doc_baseurl_id'))
            ->getAttribute("value");

        // test an invalid Documentation base
        $this->webDriver->findElement(WebDriverBy::id('doc_baseurl_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('doc_baseurl_id'))
            ->sendKeys('docs/en/html');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Invalid Document Path/', $checkMsg->getText());
        $this->webDriver->findElement(WebDriverBy::id('doc_baseurl_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('doc_baseurl_id'))
            ->sendKeys($docbasetext);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Logon to the Required Settings Page
     * Backup the existing data
     * Test that warings are issued if an invalid Cookie Domain is submitted
     * restore the original data
     *
     * @group selenium
     * @group admin
     *
     * @depends testRequiredSettingPageContents
     *
     * @return void
     */
    public function testRequiredSettingPageInvalidDataCookieDomain()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // Backup the origional form contents
        $cookiedomaintext = $this->webDriver
            ->findElement(WebDriverBy::id('cookie_domain_id'))
            ->getAttribute("value");

        // test an invalid Cookie Domain
        $this->webDriver->findElement(WebDriverBy::id('cookie_domain_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('cookie_domain_id'))
            ->sendKeys('test');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Invalid Cookie Domain/', $checkMsg->getText());
        $this->webDriver->findElement(WebDriverBy::id('cookie_domain_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('cookie_domain_id'))
            ->sendKeys($cookiedomaintext);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Logon to the Required Settings Page
     * Backup the existing data
     * Test that warings are issued if  Cookie Path data is submitted
     * restore the original data
     *
     * @group selenium
     * @group admin
     *
     * @depends testRequiredSettingPageContents
     *
     * @return void
     */
    public function testRequiredSettingPageInvalidDataCookiePath()
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

        $checkSelectedSection = $this->webDriver->findElement(
            WebDriverBy::cssSelector('td.selected_section')
        );
        $this->assertRegExp(
            '/Required Settings/',
            $checkSelectedSection->getText()
        );

        // Backup the origional form contents
        $cookiepath = $this->webDriver
            ->findElement(WebDriverBy::id('cookie_path_id'))
            ->getAttribute("value");

        // Test an invalid Cookie path
        $this->webDriver->findElement(WebDriverBy::id('cookie_path_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('cookie_path_id'))
            ->sendKeys('te st');
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Invalid Cookie Path/', $checkMsg->getText());
        $this->webDriver->findElement(WebDriverBy::id('cookie_path_id'))->clear();
        $this->webDriver
            ->findElement(WebDriverBy::id('cookie_path_id'))
            ->sendKeys($cookiepath);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }
}
