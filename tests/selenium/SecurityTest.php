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
 * Security Functional Tests
 *
 * Check that users can only access pages that they have permission to.
 *
 **/
class SecurityTest extends TestCase
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
     * Test to ensure the user with no Permissions does not have the
     * adminstration link
     *
     * @group selenium
     * @group security

     * @return void
     */
    public function testNoPermissions()
    {

        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys(SECNONEUSERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys(SECNONEPASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());


        // Check the Adminstration Link is missing on the header menu bar
        $menuBarText = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#menu_bar'))
            ->getText();
        $this->assertStringNotContainsString('Administration', $menuBarText);

        // Check the Adminstration Link is missing on the footer menu bar
        $menuBarText = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#footer_menu'))
            ->getText();
        $this->assertStringNotContainsString('Administration', $menuBarText);

        // Try to Open Each Page without Using the Menu
        // Go back to the Home Page and test editsettings.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editsettings.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Go back to the Home Page and test editconfig.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editconfig.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Go back to the Home Page and test about.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'about.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Go back to the Home Page and test editusers.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editusers.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Go back to the Home Page and test editgroups.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editgroups.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test to ensure the user with edituser permissions can see the Adminstration
     * Link and Users Menu on the Admin Page and access editusers.php only
     *
     * @group selenium
     * @group security

     * @return void
     */
    public function testUserPermissions()
    {

        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys(SECUSERUSERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys(SECUSERPASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());


        // Check the Adminstration Link is missing on the header menu bar
        $menuBarText = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#menu_bar'))
            ->getText();
        $this->assertStringContainsString('Administration', $menuBarText);

        // Check the Adminstration Link is missing on the footer menu bar
        //$menuBarText = $this->webDriver
        //    ->findElement(WebDriverBy::cssSelector('div#footer_menu'))
        //    ->getText();
        //$this->assertStringContainsString('Administration', $menuBarText);

        // Switch to the Admin Page to check we are there
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());


        // Check that only the Users Menu is present
        $mainBodyText = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'))
            ->getText();
        $this->assertStringNotContainsString('Site Preferences', $mainBodyText);
        $this->assertStringNotContainsString('Setup', $mainBodyText);
        $this->assertStringNotContainsString('About', $mainBodyText);
        $this->assertStringContainsString('Users', $mainBodyText);
        $this->assertStringNotContainsString('Groups', $mainBodyText);

        //Check that the user can switch to the Users Search Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editconfig.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Go back to the Home Page and test about.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'about.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Go back to the Home Page and test editusers.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editusers.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Go back to the Home Page and test editgroups.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editgroups.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );



        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test to ensure the user with editgroupsroups permissions can see the
     * Administration Link and Groups Menu on the Admin Page and access
     * editgroups.php only
     *
     * @group selenium
     * @group security

     * @return void
     */
    public function testGroupsPermissions()
    {
        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys(SECGROUPSUSERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys(SECGROUPSPASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());


        // Check the Adminstration Link is missing on the header menu bar
        $menuBarText = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#menu_bar'))
            ->getText();
        $this->assertStringContainsString('Administration', $menuBarText);

        // Check the Adminstration Link is missing on the footer menu bar
        //$menuBarText = $this->webDriver
        //    ->findElement(WebDriverBy::cssSelector('div#footer_menu'))
        //    ->getText();
        //$this->assertStringContainsString('Administration', $menuBarText);

        // Switch to the Admin Page to check we are there
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());


        // Check that only the Users Menu is present
        $mainBodyText = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'))
            ->getText();
        $this->assertStringNotContainsString('Site Preferences', $mainBodyText);
        $this->assertStringNotContainsString('Setup', $mainBodyText);
        $this->assertStringNotContainsString('About', $mainBodyText);
        $this->assertStringNotContainsString('Users', $mainBodyText);
        $this->assertStringContainsString('Groups', $mainBodyText);

        //Check that the user can switch to the Groups Search Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Groups'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );

        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editconfig.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Go back to the Home Page and test about.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'about.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Go back to the Home Page and test editusers.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editusers.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Go back to the Home Page and test editgroups.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editgroups.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test to ensure the user with editusers and editgroupsroups permissions can
     * see the Administration Link and Groups Menu on the Admin Page and access
     * editusers.php and editgroups.php only
     *
     * @group selenium
     * @group security

     * @return void
     */
    public function testUsersGroupsPermissions()
    {
        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys(SECBOTHUSERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys(SECBOTHPASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());


        // Check the Adminstration Link is missing on the header menu bar
        $menuBarText = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#menu_bar'))
            ->getText();
        $this->assertStringContainsString('Administration', $menuBarText);

        // Check the Adminstration Link is missing on the footer menu bar
        //$menuBarText = $this->webDriver
        //    ->findElement(WebDriverBy::cssSelector('div#footer_menu'))
        //    ->getText();
        //$this->assertStringContainsString('Administration', $menuBarText);

        // Switch to the Admin Page to check we are there
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());


        // Check that only the Users Menu is present
        $mainBodyText = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'))
            ->getText();
        $this->assertStringNotContainsString('Site Preferences', $mainBodyText);
        $this->assertStringNotContainsString('Setup', $mainBodyText);
        $this->assertStringNotContainsString('About', $mainBodyText);
        $this->assertStringContainsString('Users', $mainBodyText);
        $this->assertStringContainsString('Groups', $mainBodyText);

        //Check that the user can switch to the Groups Search Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Groups'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );

        //Check that the user can switch to the Users Search Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editconfig.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Go back to the Home Page and test about.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'about.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Authorisation Required',
            $this->webDriver->getTitle()
        );
        $body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg.throw_error'));
        $this->assertRegExp(
            '/You are not authorised to access this page/',
            $body->getText()
        );

        // Go back to the Home Page and test editusers.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editusers.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Go back to the Home Page and test editgroups.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editgroups.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test to ensure the user with admin permissions can see the Administration Link
     * all the options on the Admin Page and access all modules.
     *
     * @group selenium
     * @group security

     * @return void
     */
    public function testAdminPermissions()
    {

        // Open the Website and Log in and check that the login was sucessful
        // Open the Website and Log in and check that the login was sucessful
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys(USERNAME);
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))
            ->sendKeys(PASSWORD);
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());


        // Check the Adminstration Link is missing on the header menu bar
        $menuBarText = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#menu_bar'))
            ->getText();
        $this->assertStringContainsString('Administration', $menuBarText);

        // Check the Adminstration Link is missing on the footer menu bar
        //$menuBarText = $this->webDriver
        //    ->findElement(WebDriverBy::cssSelector('div#footer_menu'))
        //    ->getText();
        //$this->assertStringContainsString('Administration', $menuBarText);

        // Switch to the Admin Page to check we are there
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());


        // Check that only the Users Menu is present
        $mainBodyText = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'))
            ->getText();
        $this->assertStringContainsString('Site Preferences', $mainBodyText);
        $this->assertStringContainsString('Setup', $mainBodyText);
        $this->assertStringContainsString('About', $mainBodyText);
        $this->assertStringContainsString('Users', $mainBodyText);
        $this->assertStringContainsString('Groups', $mainBodyText);

        // Test Access by calling the modules via the menu
        //Check that the user can switch to the Site Preferences Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        // Check that the user can switch to the Setup Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Setup'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $this->webDriver->getTitle()
        );

        //Check that the user can switch to the List Groups Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::linkText('About'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': About',
            $this->webDriver->getTitle()
        );

        // Check that the user can switch to the Users Search Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        //Check that the user can switch to the List Groups Page
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::linkText('Groups'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );

        // Test Access by calling the modules directly.
        // Go back to the Home Page and test editsettings.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editsettings.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        // Go back to the Home Page and test editconfig.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editconfig.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Edit Configuration',
            $this->webDriver->getTitle()
        );

        // Go back to the Home Page and test about.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'about.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': About',
            $this->webDriver->getTitle()
        );

        // Go back to the Home Page and test editusers.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editusers.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Go back to the Home Page and test editgroups.php
        $this->webDriver->findElement(WebDriverBy::linkText('Home'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $testPage = URL . 'editgroups.php';
        $this->webDriver->get($testPage);
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }
}
