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

/**
 * Login/Logout Functional Tests
 *
 **/
class HomePageTest extends TestCase
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
     * Test access to the Main Page. and that the Website and Page Titles
     * are correct.
     *
     * @group selenium
     * @group login
     *
     * @return void
     */
    public function testTitle()
    {
         // Open the Website Login Page and check the page title is correct

        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Check the pagename is correct
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(WEBSITENAME . ': Login', $checkPageName->getText());

        // Check the Version Number is correct
        $checkVersion = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#right')
        );
        $this->assertRegExp('/' . VERSION . '/', $checkVersion->getText());
    }

    /**
     * Test that users can login and Logout of the Application
     *
     * @group selenium
     * @group login
     *
     * @return void
     */
    public function testLoginLogout()
    {

        // Open the Website Login Page and check that it is the correct page
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());


        // Enter the User Name and Password
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Logout of the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that users cannot login if they are:
     * Not Registered
     * Invalid Passwords
     * Invalid Username Format
     * Invalid Password format
     *
     * @group selenium
     * @group login
     *
     * @return void
     */
    public function testBadLogin()
    {
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Attempt to login with out entering a Username or Password
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());


        $checkLogin = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp(
            '/Invalid Username and password/',
            $checkLogin->getText()
        );
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Home'));
        $link->click();

        // Valid username and invalid password has been moved to testLoginWait

        // Attempt to login with an Invalid Username Valid Password
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys('runner');
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys(PASSWORD);

        // Attempt to login to the site and check it was unsuccessful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        $checkLogin = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp(
            '/Invalid Username and password/',
            $checkLogin->getText()
        );
        $this->webDriver->findElement(WebDriverBy::LinkText('Home'))->click();


        // Attempt to login with a Username that Fails Input Validation Checks
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys('runner!');
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys(PASSWORD);

        // Attempt to login to the site and check it was unsuccessful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        $checkLogin = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp(
            '/Invalid Username and password/',
            $checkLogin->getText()
        );
        $this->webDriver->findElement(WebDriverBy::LinkText('Home'))->click();


        // Attempt to login with a Password that Fails Input Validation Checks
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys('phpuNit1!');

        // Attempt to login to the site and check it was unsuccessful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        $checkLogin = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp(
            '/Invalid Username and password/',
            $checkLogin->getText()
        );
        $this->webDriver->findElement(WebDriverBy::LinkText('Home'))->click();
    }


    /**
     * Test that the 15 second wait period afet an unsuccessful login work
     *
     * @group seleniun
     * @group login
     *
     * @return void
     */
    public function testLoginWait()
    {
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Attempt to login with out entering a Username or Password
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys('phpuNit1');

        // Attempt to login to the site and check it was unsuccessful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        $checkLogin = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp(
            '/Invalid Username and password/',
            $checkLogin->getText()
        );
        $this->webDriver->findElement(WebDriverBy::LinkText('Home'))->click();


        //Attempt to login with a valid username and password within 15 seconds
        // and check it was unsucessful
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys(PASSWORD);

        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        $checkLogin = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp(
            '/Invalid Username and password/',
            $checkLogin->getText()
        );
        $this->webDriver->findElement(WebDriverBy::LinkText('Home'))->click();


        // Wait 15 seconds and try to login again
        // Enter the User Name and Password
        sleep(16);
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys(PASSWORD);

        // Login to the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        //Logout of the site and check it was successful
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Test that users cannot login to the Application if their account is disabled
     *
     * @group selenium
     * @group login
     *
     * @return void
     */
    public function testLockedAccount()
    {
         // Open the Website Login Page and check that it is the correct page
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Enter the User Name and Password
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys(LOCKEDUSERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys(LOCKEDPASSWORD);

        // Attemp to login to the site and check it was unsuccessful
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        $checkLogin = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp(
            '/Invalid Username and password/',
            $checkLogin->getText()
        );
    }


    /**
     * Test access to the Release Notes and that the Page Titl etc are correct.
     *
     * @group selenium
     * @group login
     *
     * @return void
     */
    public function testReleaseNotes()
    {
        // Open the Website Login Page and check the page title is correct
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Check the pagename is correct
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(WEBSITENAME . ': Login', $checkPageName->getText());

        //Switch to the Admin Page
        $this->webDriver
            ->findElement(WebDriverBy::LinkText('Release Notes'))
            ->click();

        // Check got to the Release Notes Page
        $this->assertEquals(
            WEBSITENAME . ': 1.0 Release Notes',
            $this->webDriver->getTitle()
        );

        // Check the pagename is correct
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(
            WEBSITENAME . ': 1.0 Release Notes',
            $checkPageName->getText()
        );

        // Check the Page Title is correct
        $h1 = $this->webDriver->findElement(WebDriverBy::cssSelector('h1'));
        $this->assertRegExp('/1.0 Release Notes/', $h1->getText());

        // Goe Back to the Home Page and check it was successful
        $this->webDriver->findElement(WebDriverBy::LinkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test access to the Release Notes and that the Page Title etc are correct.
     *
     * @group selenium
     * @group login
     *
     * @return void
     */
    public function testHelpFiles()
    {
        // Open the Website Login Page and check the page title is correct
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Check the pagename is correct
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(WEBSITENAME . ': Login', $checkPageName->getText());


        $handles = $this->webDriver->getWindowHandles();
        $loginwindowhandle = $handles[0];

        // Switch to the admin Page
        try {
            $this->webDriver
                ->findElement(WebDriverBy::LinkText('Using Web Database Skeleton'))
                ->click();
        } catch (NoSuchElementException $e) {
            $this->markTestSkipped("Documentation not Compiled");
            return;
        }


        // Only run this part of the test if the Doc Link is there.
        $handles = $this->webDriver->getWindowHandles();
        if (count($handles) != 2) {
            $this->fail("Failed to open second window");
        }

        if ($handles[0] == $loginwindowhandle) {
            $this->webDriver->switchTo()->window($handles[1]);
        } else {
            $this->webDriver->switchTo()->window($handles[0]);
        }

        $body = $this->webDriver->findElement(WebDriverBy::cssSelector('body'));
        $this->assertRegExp('/Chapter 5/', $body->getText());

        // Close the Help Window
        $this->webDriver->Close();
        $handles = $this->webDriver->getWindowHandles();
        if (count($handles) != 1) {
            $this->fail("Failed to close second window");
        }

        // Switch back to the main window and check we got there
        $this->webDriver->switchTo()->window($handles[0]);
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(WEBSITENAME . ': Login', $checkPageName->getText());
    }
}
