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
 * Admin Groups Functional Tests
 *
 **/
class AdminGroupsTest extends TestCase
{

    /**
     * Row number for group
     *
     * @var int
     */
    private static $rowNumber;

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
     * Test access to the Admin List Groups Page and that the 3 default groups
     * admin, editusers and edit groups are present.
     *
     * @group selenium
     * @group groups
     *
     * @return void
     */
    public function testListGroups()
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

        //Switch to the Admin Page
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Groups'));
        $link->click();

        // Check that we are on the Groups Page
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );

        // Check the pagename is correct
        $checkPageName = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#left')
        );
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $checkPageName->getText()
        );

        // Check that the admin Group Exists
        $main_body = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#main-body')
        );
        $this->assertRegExp('/admin/', $main_body->getText());

        // Check that the editusers Group Exists
        $this->assertRegExp('/editusers/', $main_body->getText());

        // Check that the editgroups Group Exists
        $this->assertRegExp('/editgroups/', $main_body->getText());

        // Test Finished so Logout of the Website
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Logout'));
        $link->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that a new group can be added to the database
     *
     * @group selenium
     * @group groups
     *
     * @depends testListGroups
     *
     * @return void
     */
    public function testAddGroup()
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

        //Switch to the Admin Page
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Groups'));
        $link->click();

        // Check that we are on the Groups Page
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );


        // Switch to the New Group page
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('New group'));
        $link->click();

        // Check that we are on the Groups Page
        $this->assertEquals(
            WEBSITENAME . ': New Group',
            $this->webDriver->getTitle()
        );


        // Add a new group to the database
        $groupNameIP = $this->webDriver->findElement(
            WebDriverBy::name('groupname')
        );
        $groupNameIP->click();
        $this->webDriver->getKeyboard()->sendKeys("selenium");
        $groupDescIP = $this->webDriver->findElement(
            WebDriverBy::name('description')
        );
        $groupDescIP->click();
        $this->webDriver->getKeyboard()->sendKeys("Selenium Test Group");
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();

        //Check the New group has been added
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::CssSelector('div#msg-box')
        );
        $this->assertRegExp('/selenium Created/', $checkMsg->getText());

        // Check the right group and that the group name edit box is disabled
        // Attribute is returned as a string ('true') if true and null other wise
        $groupname = $this->webDriver->findElement(WebDriverBy::name('groupname'));
        $this->assertRegExp('/selenium/', $groupname->getAttribute("value"));
        $this->assertEquals('true', $groupname->getAttribute("readOnly"));

        // Check Group is in the List
        $link =  $this->webDriver->findElement(
            WebDriverBy::LinkText('find another group')
        );
        $link->click();
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#main-body')
        );
        $this->assertRegExp('/Selenium Test Group/', $main_body->getText());


        $rows = $this->webDriver->findElements(WebDriverBy::cssSelector('table tr'));
        $rowNumber = count($rows);
        $rowdata = $this->webDriver->findElement(
            WebDriverBy::xpath("//table//tbody//tr[$rowNumber]//td[1]")
        )->getText();
        $this->assertEquals('selenium', $rowdata);

        // Test Finished so Logout of the Website
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Logout'));
        $link->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        self::$rowNumber = $rowNumber;
    }

    /**
     * Test that an existing group can be edited.
     *
     * @group selenium
     * @group groups
     *
     * @depends testAddGroup
     *
     * @return void
     */
    public function testEditGroup()
    {
        $rowNumber = self::$rowNumber;

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

        //Switch to the Admin Page
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Groups'));
        $link->click();

        // Check that we are on the Groups Page
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );

        //Switch to the Selenium Group Edit Page
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('selenium'));
        $link->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Group',
            $this->webDriver->getTitle()
        );

        // Check we have the right group and that the group name edit box is disabled
        // Attribute is returned as a string ('true') if true and null other wise
        $groupname = $this->webDriver->findElement(WebDriverBy::name('groupname'));
        $this->assertRegExp('/selenium/', $groupname->getAttribute("value"));
        $this->assertEquals('true', $groupname->getAttribute("readOnly"));

        // Test The Group Description and that it can be Changed.
        // Then Restore to the Default Value
        $groupDescription = $this->webDriver->findElement(
            WebDriverBy::name('description')
        );
        $this->assertRegExp(
            '/Selenium Test Group/',
            $groupDescription->getAttribute("value")
        );
        $tempDescription = $groupDescription->getAttribute("value");
        $groupDescription->clear();
        $groupDescription->click();
        $groupDescription->sendKeys('Selenium Users');
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp('/selenium Updated/', $checkMsg->getText());
        $this->assertRegExp('/Group Description Changed/', $checkMsg->getText());
        $groupDescription = $this->webDriver->findElement(
            WebDriverBy::name('description')
        );
        $this->assertRegExp(
            '/Selenium Users/',
            $groupDescription->getAttribute("value")
        );
        $groupDescription->clear();
        $groupDescription->sendKeys($tempDescription);
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp('/selenium Updated/', $checkMsg->getText());
        $this->assertRegExp('/Group Description Changed/', $checkMsg->getText());

        //Get the Check box details and ensure they are not selected
        $useforproduct = $this->webDriver->findElement(
            WebDriverBy::name('useforproduct')
        );
        $autogroup = $this->webDriver->findElement(
            WebDriverBy::name('autogroup')
        );
        $this->assertFalse($useforproduct->isSelected());
        $this->assertFalse($autogroup->isSelected());

        // Select userforproduct and autogroup not selected.
        // Then Restore to not selected
        $useforproduct->click();
        $this->assertTrue($useforproduct->isSelected());
        $this->assertFalse($autogroup->isSelected());
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
         $this->assertRegExp('/selenium Updated/', $checkMsg->getText());
        $this->assertRegExp('/Group Use For Product Changed/', $checkMsg->getText());
        $useforproduct = $this->webDriver->findElement(
            WebDriverBy::name('useforproduct')
        );
        $autogroup = $this->webDriver->findElement(
            WebDriverBy::name('autogroup')
        );
        $this->assertTrue($useforproduct->isSelected());
        $this->assertFalse($autogroup->isSelected());
        $link = $this->webDriver->findElement(
            WebDriverBy::LinkText("find another group")
        );
        $link->click();
        $rowdata = $this->webDriver->findElement(
            WebDriverBy::xpath("//table/tbody/tr[$rowNumber]/td[3]")
        )->getText();
        $this->assertEquals('*', $rowdata);
        $rowdata = $this->webDriver->findElement(
            WebDriverBy::xpath("//table/tbody/tr[$rowNumber]/td[4]")
        )->getText();
        $this->assertEquals(' ', $rowdata);
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('selenium'));
        $link->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Group',
            $this->webDriver->getTitle()
        );
        $useforproduct = $this->webDriver->findElement(
            WebDriverBy::name('useforproduct')
        );
        $autogroup = $this->webDriver->findElement(
            WebDriverBy::name('autogroup')
        );
        $useforproduct->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
         $this->assertRegExp('/selenium Updated/', $checkMsg->getText());
        $this->assertRegExp('/Group Use For Product Changed/', $checkMsg->getText());
        $useforproduct = $this->webDriver->findElement(
            WebDriverBy::name('useforproduct')
        );
        $autogroup = $this->webDriver->findElement(
            WebDriverBy::name('autogroup')
        );
        $this->assertFalse($useforproduct->isSelected());
        $this->assertFalse($autogroup->isSelected());


        // Select autogroup, usefor product fot selected.
        // Then Restore to not selected
        $autogroup->click();
        $this->assertFalse($useforproduct->isSelected());
        $this->assertTrue($autogroup->isSelected());
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
         $this->assertRegExp('/selenium Updated/', $checkMsg->getText());
        $this->assertRegExp('/Group Auto Membership Changed/', $checkMsg->getText());
        $useforproduct = $this->webDriver->findElement(
            WebDriverBy::name('useforproduct')
        );
        $autogroup = $this->webDriver->findElement(
            WebDriverBy::name('autogroup')
        );
        $this->assertFalse($useforproduct->isSelected());
        $this->assertTrue($autogroup->isSelected());
        $link = $this->webDriver->findElement(
            WebDriverBy::LinkText("find another group")
        );
        $link->click();
        $rowdata = $this->webDriver->findElement(
            WebDriverBy::xpath("//table/tbody/tr[$rowNumber]/td[3]")
        )->getText();
        $this->assertEquals(' ', $rowdata);
        $rowdata = $this->webDriver->findElement(
            WebDriverBy::xpath("//table/tbody/tr[$rowNumber]/td[4]")
        )->getText();
        $this->assertEquals('*', $rowdata);
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('selenium'));
        $link->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit Group',
            $this->webDriver->getTitle()
        );
        $useforproduct = $this->webDriver->findElement(
            WebDriverBy::name('useforproduct')
        );
        $autogroup = $this->webDriver->findElement(
            WebDriverBy::name('autogroup')
        );
        $autogroup->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp('/selenium Updated/', $checkMsg->getText());
        $this->assertRegExp('/Group Auto Membership Changed/', $checkMsg->getText());
        $useforproduct = $this->webDriver->findElement(
            WebDriverBy::name('useforproduct')
        );
        $autogroup = $this->webDriver->findElement(
            WebDriverBy::name('autogroup')
        );
        $this->assertFalse($useforproduct->isSelected());
        $this->assertFalse($autogroup->isSelected());


        // Check that an error is displayed in no changes are made and the save
        // button is clicked
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::cssSelector('div#msg-box')
        );
        $this->assertRegExp('/You have not made any changes/', $checkMsg->getText());


        // Test Finished so Logout of the Website
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Logout'));
        $link->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }



    /**
     * Test that a duplicate group cannot be added to the database
     *
     * @group selenium
     * @group groups
     *
     * @depends testAddGroup
     *
     * @return void
     */
    public function testAddDuplicateGroup()
    {
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

        //Switch to the Admin Page
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Groups'));
        $link->click();

        // Check that we are on the Groups Page
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );

        // Switch to the New Group page
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('New group'));
        $link->click();

        // Check that we are on the Groups Page
        $this->assertEquals(
            WEBSITENAME . ': New Group',
            $this->webDriver->getTitle()
        );

        // Add a new group to the database
        $groupNameIP = $this->webDriver->findElement(
            WebDriverBy::name('groupname')
        );
        $groupNameIP->click();
        $this->webDriver->getKeyboard()->sendKeys("selenium");
        $groupDescIP = $this->webDriver->findElement(
            WebDriverBy::name('description')
        );
        $groupDescIP->click();
        $this->webDriver->getKeyboard()->sendKeys("Selenium Test Group");
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();

        //Check the New group has been added
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::CssSelector('div#msg-box')
        );
        $this->assertRegExp(
            '/selenium already exists in the database/',
            $checkMsg->getText()
        );

        // Test Finished so Logout of the Website
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Logout'));
        $link->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that a new group can be added to the database
     *
     * @group selenium
     * @group groups
     *
     * @depends testAddGroup
     *
     * @return void
     */
    public function testDeleteGroup()
    {
        $rowNum = self::$rowNumber;

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

        //Switch to the Admin Page
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Groups'));
        $link->click();

        // Check that we are on the Groups Page
        $this->assertEquals(
            WEBSITENAME . ': List Groups',
            $this->webDriver->getTitle()
        );

        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table//tbody//tr[$rowNum]//td[1]"))
            ->getText();
        $this->assertEquals('selenium', $rowdata);

        $this->webDriver
            ->findElement(WebDriverBy::xpath("//table//tbody//tr[$rowNum]//td[6]/a"))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': Delete Group',
            $this->webDriver->getTitle()
        );
        $this->assertEquals(
            'selenium',
            $this->webDriver
                ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[2]"))
                ->getText()
        );
        $this->webDriver->findElement(WebDriverBy::id('delete'))->click();
        $checkMsg = $this->webDriver->findElement(
            WebDriverBy::CssSelector('div#msg-box')
        );
        $this->assertRegExp('/Deleted/', $checkMsg->getText());

        // Test Finished so Logout of the Website
        $link = $this->webDriver->findElement(WebDriverBy::LinkText('Logout'));
        $link->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }
}
