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
 * Main Admin Page Functional Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/


class AdminMainTest extends TestCase
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
     * Test access to the Main Admin Page.
     *
     * @group selenium
     * @group admin
     *
     * @return null No return data
     */
    public function testAdminMainPage()
    {

         // Open the Website Login Page and check that it is the correct page
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enter the User Name and Password
        $usernameInput = $this->webDriver->findElement(
            WebDriverBy::name('username')
        );
        $usernameInput->click();
        $this->webDriver->getKeyboard()->sendKeys(USERNAME);
        $passwordInput = $this->webDriver->findElement(
            WebDriverBy::name('password')
        );
        $passwordInput->click();
        $this->webDriver->getKeyboard()->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());


        //Switch to the Admin Page
        $link = $this->webDriver->findElement(
            WebDriverBy::LinkText('Administration')
        );
        $link->click();

        // Check got to the Main Admin Page
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        $checkAdminPage = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#main-body')
        );
        $this->assertRegExp(
            '/This page is only accessible to empowered users/',
            $checkAdminPage->getText()
        );

        // Check the pagename is correct
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(WEBSITENAME . ': Admin', $checkPageName->getText());


        // Logout of the Website
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Logout'));
        $link->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }
}
