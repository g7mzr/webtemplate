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
 * About Page Functional Tests
 * Check that the About page can be accessed by a user with the correct permissions
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/


class AdminPreferencesTest extends TestCase
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
     * Test access to the Preferences Administration Page
     *
     * @group selenium
     * @group admin
     *
     * @return null No return data
     */
    public function testAdminPrefsPage()
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
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        // Switch to the About Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp(
            '/This lets you edit the default preferences values/',
            $main_body->getText()
        );


        // Check the pagename is correct
        $checkPageName = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#left'));
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $checkPageName->getText()
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }



    /**
     * Test The functionality of Theme Preference Enabled Checkbox
     *
     * @group selenium
     * @group admin
     * @group test
     *
     * @return null No return data
     */
    public function testThemeEnabled()
    {

        // Open the Website and Log in ans swith to the DefaulT Preferences Page
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
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        // Switch to the About Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        //   THEME TESTS:
        //   Disable user selectable theme.
        $theme_enabled = $this->
            webDriver->findElement(WebDriverBy::id("theme-enabled"));
        if ($theme_enabled->isSelected()  == true) {
            $theme_enabled->click();
        }
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check the theme has been disabled.
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Theme Enabled Updated/', $checkMsg->getText());
        $theme_enabled = $this->webDriver
            ->findElement(WebDriverBy::id("theme-enabled"));
        $this->assertFalse($theme_enabled->isSelected());


        // Switch to Preference Page to check that THEME has been Disabled
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Preferences'));
        $link->click();
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Check that theme option has been removed but that the other options
        // remain
        $this->assertNotContains(
            'general appearance',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertContains(
            'Zoom textareas large',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertContains(
            'Search result lines to display',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );


        // Switch back to the Preferences Setup Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        // Restore Theme
        $theme_enabled = $this->webDriver
            ->findElement(WebDriverBy::id("theme-enabled"));
        if ($theme_enabled->isSelected()  == false) {
            $theme_enabled->click();
        }

        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check the theme has been enabled
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Theme Enabled Updated/', $checkMsg->getText());
        $theme_enabled = $this->webDriver
            ->findElement(WebDriverBy::id("theme-enabled"));
        $this->assertTrue($theme_enabled->isSelected());

        // Switch to Preference Page to check that THEME has been Enabled
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Preferences'));
        $link->click();
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Need to check that app Preferences are shown in case there is a fault that
        // stops some displaying when they should
        $this->assertContains(
            'general appearance',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertContains(
            'Zoom textareas large',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertContains(
            'Search result lines to display',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Test default theme Can be Changed
     *
     * @group selenium
     * @group admin
     *
     * @return null No return data
     */
    public function testThemeValue()
    {

        // Open the Website and Log in ans swith to the DefaulT Preferences Page
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
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        // Switch to the About Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        //   THEME TESTS:
        // CHECK THAT THE DEFAULT THEME CAN BE CHANGED
        // Currently only One Theme is installed No no Changes Can be made
        $themesSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('theme'))
        );
        $selectedTheme = $themesSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Dusk', $selectedTheme);

        //Change to a Particular Value (Dusk) and check correct
        $themesSelect->selectByVisibleText("Dusk");
        $selectedTheme = $themesSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Dusk', $selectedTheme);

        // Submit the General Preferences with Theme Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/You have not Made any Changes/',
            $checkMsg->getText()
        );
        $themesSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('theme'))
        );
        $selectedTheme = $themesSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Dusk', $selectedTheme);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }



    /**
     * Test The functionality of Textarea Preference Enabled Checkbox
     *
     * @group selenium
     * @group admin
     *
     * @return null No return data
     */
    public function testZoomTextAreasEnabled()
    {

        // Open the Website and Log in ans swith to the DefaulT Preferences Page
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
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        // Switch to the About Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        // ZOOM TEXT AREA TESTS:
        // Disable user selectable zoom text area.
        $zoom_textarea_enabled = $this->webDriver
            ->findElement(WebDriverBy::id("zoom_textareas_enabled"));
        if ($zoom_textarea_enabled->isSelected()  == true) {
            $zoom_textarea_enabled->click();
        }
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check the zoom text area has been disabled.
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Zoom Text Areas Enabled Updated/',
            $checkMsg->getText()
        );
        $zoom_textarea_enabled = $this->webDriver
            ->findElement(WebDriverBy::id("zoom_textareas_enabled"));
        $this->assertFalse($zoom_textarea_enabled->isSelected());


        // Switch to Preference Page to check that Tzoom text area has been Disabled
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Preferences'));
        $link->click();
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Need to check that app Preferences are shown in case there is a fault that
        // stops some displaying when they should
        $this->assertContains(
            'general appearance',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertNotContains(
            'Zoom textareas large',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertContains(
            'Search result lines to display',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );

        // Switch back to the Preferences Setup Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'));
        $link->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'));
        $link->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        // Restore Zoom TextArea Enabled
        $zoom_textarea_enabled = $this->webDriver
            ->findElement(WebDriverBy::id("zoom_textareas_enabled"));
        if ($zoom_textarea_enabled->isSelected()  == false) {
            $zoom_textarea_enabled->click();
        }

        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check the Zoom Text Areas has been enabled
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Zoom Text Areas Enabled Updated/',
            $checkMsg->getText()
        );
        $zoom_textarea_enabled = $this->webDriver
            ->findElement(WebDriverBy::id("zoom_textareas_enabled"));
        $this->assertTrue($zoom_textarea_enabled->isSelected());


        // Switch to Preference Page to check that TZoom Text Area has been Enabled
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Preferences'));
        $link->click();
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Need to check that app Preferences are shown in case there is a fault that
        // stops some displaying when they should
        $this->assertContains(
            'general appearance',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertContains(
            'Zoom textareas large',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertContains(
            'Search result lines to display',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }




    /**
     * Test the Textarea Value can be changed
     *
     * @group selenium
     * @group admin
     *
     * @return null No return data
     */
    public function testZoomTextAreasValue()
    {

        // Open the Website and Log in ans swith to the DefaulT Preferences Page
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
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        // Switch to the About Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        // ZOOM TEXT AREA TESTS:
        // Check that the Zoom Text Area Default Value Can be Set to Off
        $ZTASelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('zoom_textareas'))
        );
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('On', $selectedZTA);

        //Change to a Particular Value (Dusk) and check correct
        $ZTASelect->selectByVisibleText("Off");
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Off', $selectedZTA);

        // Submit the General Preferences with Theme Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Zoom Text Areas Updated/', $checkMsg->getText());
        $ZTASelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('zoom_textareas'))
        );
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Off', $selectedZTA);

        // Switch to Users Preference Page to See that Default Value
        // Has Changed to OFF
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Preferences'));
        $link->click();
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Check that Site Default is now Off
        $ZTASelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('zoom_textareas'))
        );
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Off (Site Default)', $selectedZTA);


        // Switch back to the Preferences Setup Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'));
        $link->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'));
        $link->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        // Check that the Zoom Text Area Default Value Can be Set back to On
        $ZTASelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('zoom_textareas'))
        );
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('Off', $selectedZTA);

        //Change to a Particular Value (Dusk) and check correct
        $ZTASelect->selectByVisibleText("On");
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('On', $selectedZTA);

        // Submit the General Preferences with Theme Change
        // Check Message and Value on the "Select"
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Zoom Text Areas Updated/', $checkMsg->getText());
        $ZTASelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('zoom_textareas'))
        );
        $selectedZTA = $ZTASelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('On', $selectedZTA);

        // Switch to User's Preference Page to See that Zoom Text Area has
        // been set back to a Default of On
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Preferences'));
        $link->click();
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Check that Site Default is now Off
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
     * Test The functionality of Theme Preference Enabled Checkbox
     *
     * @group selenium
     * @group admin
     *
     * @return null No return data
     */
    public function testDisplayLinesEnabled()
    {

        // Open the Website and Log in ans swith to the DefaulT Preferences Page
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
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        // Switch to the About Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        // ZOOM TEXT AREA TESTS:
        // Disable user selectable zoom text area.
        $display_rows_enabled = $this->webDriver
            ->findElement(WebDriverBy::id("display_rows_enabled"));
        if ($display_rows_enabled->isSelected()  == true) {
            $display_rows_enabled->click();
        }
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check the zoom text area has been disabled.
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Display Rows Enabled Updated/', $checkMsg->getText());
        $display_rows_enabled = $this->webDriver
            ->findElement(WebDriverBy::id("display_rows_enabled"));
        $this->assertFalse($display_rows_enabled->isSelected());

        // Switch to Preference Page to check that Tzoom text area has been Disabled
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Preferences'));
        $link->click();
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Need to check that app Preferences are shown in case there is a fault that
        // stops some displaying when they should
        $this->assertContains(
            'general appearance',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertContains(
            'Zoom textareas large',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertNotContains(
            'Search result lines to display',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );

        // Switch back to the Preferences Setup Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'));
        $link->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'));
        $link->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        // Restore Zoom TextArea Enabled
        $display_rows_enabled = $this->webDriver
            ->findElement(WebDriverBy::id("display_rows_enabled"));
        if ($display_rows_enabled->isSelected()  == false) {
            $display_rows_enabled->click();
        }

        $this->webDriver->findElement(WebDriverBy::id('update'))->click();

        // Check the Zoom Text Areas has been enabled
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Display Rows Enabled Updated/', $checkMsg->getText());
        $display_rows_enabled = $this->webDriver
            ->findElement(WebDriverBy::id("zoom_textareas_enabled"));
        $this->assertTrue($display_rows_enabled->isSelected());

        // Switch to Preferences and check all options displayed
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Preferences'));
        $link->click();
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Need to check that app Preferences are shown in case there is a fault that
        // stops some displaying when they should
        $this->assertContains(
            'general appearance',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertContains(
            'Zoom textareas large',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );
        $this->assertContains(
            'Search result lines to display',
            $this->webDriver
                ->findElement(WebDriverBy::cssSelector('body'))->getText()
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Test the Number of Display Line Values Can be Changed.
     *
     * @group selenium
     * @group admin
     *
     * @return null No return data
     */
    public function testDisplayLinesValue()
    {

        // Open the Website and Log in ans swith to the DefaulT Preferences Page
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
            ->findElement(WebDriverBy::linkText('Administration'))
            ->click();

        // Check got to the Main Admin Page
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());

        // Switch to the About Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        // Display Rows TESTS:
        // Check the Display Rows default Value is 20
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('20', $displayRows);

        //Change to a Particular Value (10) and check correct then submit and check
        $rowsSelect ->selectByVisibleText('10');
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('10', $displayRows);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Display Rows Updated/', $checkMsg->getText());
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('10', $displayRows);

        //Change to a Particular Value (30) and check correct
        $rowsSelect ->selectByVisibleText('30');
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('30', $displayRows);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Display Rows Updated/', $checkMsg->getText());
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('30', $displayRows);

        //Change to a Particular Value (40) and check correct
        $rowsSelect ->selectByVisibleText('40');
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('40', $displayRows);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Display Rows Updated/', $checkMsg->getText());
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('40', $displayRows);

        //Change to a Particular Value (50) and check correct
        $rowsSelect ->selectByVisibleText('50');
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('50', $displayRows);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Display Rows Updated/', $checkMsg->getText());
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('50', $displayRows);

        // Switch to Users Preference Page to See that Default Value
        // Has Changed to 50
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Preferences'));
        $link->click();
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Check that Site Default is now Off
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('50 (Site Default)', $displayRows);

        // Switch back to the Preferences Setup Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Administration'));
        $link->click();
        $this->assertEquals(WEBSITENAME . ': Admin', $this->webDriver->getTitle());
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Site Preferences'));
        $link->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Default Preferences',
            $this->webDriver->getTitle()
        );

        // Check that the Display Rows  Default Value Can be Set back to 20
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('50', $displayRows);
        $rowsSelect ->selectByVisibleText('20');
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('20', $displayRows);
        $this->webDriver->findElement(WebDriverBy::id('update'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/Display Rows Updated/', $checkMsg->getText());
        $rowsSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::id('display_rows'))
        );
        $displayRows = $rowsSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('20', $displayRows);

        // Switch to User's Preference Page to See that Display rows has
        // been set back to a Default of 2-
        $link = $this->webDriver->findElement(WebDriverBy::linkText('Preferences'));
        $link->click();
        $h2 = $this->webDriver->findElement(WebDriverBy::cssSelector('h2'));
        $this->assertRegExp('/General Preferences/', $h2->getText());

        // Check that Site Default is now Off
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
}
