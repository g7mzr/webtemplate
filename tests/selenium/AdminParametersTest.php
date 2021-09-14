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
 * About Page Functional Tests
 *
 * Check that the Admin Parameters page can be accessed by a user with the
 * correct permissions
 *
 **/
class AdminParametersTest extends TestCase
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
     * Test access to the Setup Page and Rotate round all Four Pages just to check
     * that it works.
     *
     * @group selenium
     * @group admin

     * @return void
     */
    public function testAdminSetupPage()
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


        // Check we are on the the Required Settings Page.
        $checkSelectedSection = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('td.selected_section'));
        $this->assertRegExp('/Required Settings/', $checkSelectedSection->getText());
        $checkBodyText = $this->webDriver
            ->findElement(WebDriverBy::xpath("//div[@id='main-body']/table/tbody"))
            ->getText();
        $this->assertRegExp('/URL Base/', $checkBodyText);

        // Move to the Administrative Policys Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Administrative Policys'))
            ->click();
        $checkSelectedSection = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('td.selected_section'));
        $this->assertRegExp(
            '/Administrative Policys/',
            $checkSelectedSection->getText()
        );
        $checkBodyText = $this->webDriver
            ->findElement(WebDriverBy::xpath("//div[@id='main-body']/table/tbody"))
            ->getText();
        $this->assertRegExp('/Logging Level/', $checkBodyText);

        // Move to the User Authentication Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('User Authentication'))
            ->click();
        $checkSelectedSection = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('td.selected_section'));
        $this->assertRegExp(
            '/User Authentication/',
            $checkSelectedSection->getText()
        );
        $checkBodyText = $this->webDriver
            ->findElement(WebDriverBy::xpath("//div[@id='main-body']/table/tbody"))
            ->getText();
        $this->assertRegExp('/Create Accounts/', $checkBodyText);

        // Move to the Email Page
        $this->webDriver->findElement(WebDriverBy::linkText('e-mail'))->click();
        $checkSelectedSection = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('td.selected_section'));
        $this->assertRegExp('/e-mail/', $checkSelectedSection->getText());
        $checkBodyText = $this->webDriver
            ->findElement(WebDriverBy::xpath("//div[@id='main-body']/table/tbody"))
            ->getText();
        $this->assertRegExp('/Mail Delivery Method/', $checkBodyText);

        // Move back to the Required Settings Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Required Settings'))
            ->click();
        $checkSelectedSection = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('td.selected_section'));
        $this->assertRegExp('/Required Settings/', $checkSelectedSection->getText());
        $checkBodyText = $this->webDriver
            ->findElement(WebDriverBy::xpath("//div[@id='main-body']/table/tbody"))
            ->getText();
        $this->assertRegExp('/URL Base/', $checkBodyText);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }
}
