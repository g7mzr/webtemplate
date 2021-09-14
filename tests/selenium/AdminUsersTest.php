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

// Include the Test Database Connection details
require_once dirname(__FILE__) . '/../_data/database.php';

/**
 * Users Admin Functional Tests
 *
 **/
class AdminUsersTest extends TestCase
{

    /**
     * Remote Webdriver
     *
     * @var \RemoteWebDriver
     */
    protected $webDriver;

    /**
     *New user flag
     *
     * @var newuserflag
     */
    protected $deleteSeleniumUser;


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
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @throws \Exception If unable to connect to remote database.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        global $testdsn, $options;
        if ($this->deleteSeleniumUser == true) {
            // DELETE THE Test USER
            $conStr = sprintf(
                "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
                $testdsn["hostspec"],
                '5432',
                $testdsn["databasename"],
                $testdsn["username"],
                $testdsn["password"]
            );

            // Create the PDO object and Connect to the database
            try {
                $localconn = new \PDO($conStr);
            } catch (\Exception $e) {
                //print_r($e->getMessage());
                throw new \Exception('Unable to connect to the database');
            }
            //$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $localconn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
            $sql = "delete from users where user_name = 'selenium'";

            $localconn->query($sql);
            $localconn = null;
        }

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
     * Test access to the Admin Search Users Page
     *
     * @group selenium
     * @group users
     *
     * @return void
     */
    public function testSearchUsersPage()
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Check the pagename is correct
        $checkPageName = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#left'));
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $checkPageName->getText()
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Test that users can be searched for from the search users page
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     *
     * @return void
     */
    public function testSearchUsers()
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Click on Search Button with No Search Data.
        // Check we are on the right page
        // Check the admin and phpunit users exist
        // Then return to search page
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/admin/', $main_body->getText());
        $this->assertRegExp('/phpunit/', $main_body->getText());
        $this->webDriver
            ->findElement(WebDriverBy::linkText('find another user'))
            ->click();

        // Search with a username this time
        $searchTypeSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::name('searchtype'))
        );
        $searchType = $searchTypeSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('User Name', $searchType);
        $searchInput = $this->webDriver->findElement(WebDriverBy::name('searchstr'));
        $searchInput->sendKeys(USERNAME);
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringNotContainsString('/admin/', $main_body->getText());
        $this->assertRegExp('/phpunit/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());
        $this->webDriver
            ->findElement(WebDriverBy::linkText('find another user'))
            ->click();

        // Search by Real Name
        $searchTypeSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::name('searchtype'))
        );
        $searchType = $searchTypeSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('User Name', $searchType);
        $searchTypeSelect->selectByVisibleText('Real Name');
        $searchInput = $this->webDriver->findElement(WebDriverBy::name('searchstr'));
        $searchInput->sendKeys(USERREALNAME);
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringNotContainsString('/admin/', $main_body->getText());
        $this->assertRegExp('/phpunit/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());
        $this->webDriver
            ->findElement(WebDriverBy::linkText('find another user'))
            ->click();

        // Search by email
        $searchTypeSelect = new WebDriverSelect(
            $this->webDriver->findElement(WebDriverBy::name('searchtype'))
        );
        $searchType = $searchTypeSelect
            ->getFirstSelectedOption()
            ->getText();
        $this->assertEquals('User Name', $searchType);
        $searchTypeSelect->selectByVisibleText('E-Mail');
        $searchInput = $this->webDriver->findElement(WebDriverBy::name('searchstr'));
        $searchInput->sendKeys(USEREMAIL);
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertStringNotContainsString('/admin/', $main_body->getText());
        $this->assertRegExp('/phpunit/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());
        $this->webDriver
            ->findElement(WebDriverBy::linkText('find another user'))
            ->click();

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test searches for invalid users fail
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     *
     * @return void
     */
    public function testSearchInvalidUser()
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Search for a user that soes not exist.
        // Check that an error msg is received
        $searchInput = $this->webDriver->findElement(WebDriverBy::name('searchstr'));
        $searchInput->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Search Error',
            $this->webDriver->getTitle()
        );
        $error_msg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#error_msg'));
        $this->assertRegExp('/Not Found/', $error_msg->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that a new user can be added.  At this point the user is not assigned to
     * any groups that is covered by testEditUser.
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     *
     * @return void
     */
    public function testAddUser()
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

        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Switch to Add user and add the user.
        $this->webDriver
            ->findElement(WebDriverBy::linkText('add a new user'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': New User',
            $this->webDriver->getTitle()
        );
        $checkPageName = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#left'));
        $this->assertEquals(WEBSITENAME . ': New User', $checkPageName->getText());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys('selenium');
        $this->webDriver
            ->findElement(WebDriverBy::name('realname'))
            ->sendKeys('Selenium User');
        $this->webDriver
            ->findElement(WebDriverBy::name('passwd'))
            ->sendKeys('Selenium1');
        $this->webDriver
            ->findElement(WebDriverBy::name('useremail'))
            ->sendKeys('selenium@example.com');
        $this->webDriver
            ->findElement(WebDriverBy::name('userenabled'))
            ->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User Created selenium/', $checkMsg->getText());

        // Check that the username exists in the checkbox and it is readonly
        $username = $this->webDriver->findElement(WebDriverBy::name('username'));
        $this->assertRegExp('/selenium/', $username->getAttribute("value"));
        $this->assertEquals('true', $username->getAttribute("readOnly"));

        // Go back to the list and check user is also there
        $this->webDriver
            ->findElement(WebDriverBy::linkText('find another user'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that duplicate users cannot be added
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     * @depends testAddUser
     *
     * @return void
     */
    public function testAddDuplicateUser()
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Switch to Add user and add the duplicate user.
        $this->webDriver
            ->findElement(WebDriverBy::linkText('add a new user'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': New User',
            $this->webDriver->getTitle()
        );

        // Add the user details
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))
            ->sendKeys('selenium');
        $this->webDriver
            ->findElement(WebDriverBy::name('realname'))
            ->sendKeys('Selenium User');
        $this->webDriver
            ->findElement(WebDriverBy::name('passwd'))
            ->sendKeys('Selenium1');
        $this->webDriver
            ->findElement(WebDriverBy::name('useremail'))
            ->sendKeys('selenium@example.com');
        $this->webDriver
            ->findElement(WebDriverBy::name('userenabled'))
            ->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/User selenium exists in the database/',
            $checkMsg->getText()
        );

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that users can be disabled
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     * @depends testAddUser
     *
     * @return void
     */
    public function testDisableUser()
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Search for the user and check account is not locked
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[1]"))
            ->getText();
        $this->assertEquals('selenium', $rowdata);
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[6]"))
            ->getText();
        $this->assertEquals(' ', $rowdata);

        // Edit the user to disable them
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();

        // As this is the first time on this page check the names are right
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );
        $checkPageName = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#left'));
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $checkPageName->getText()
        );


        $userenabled = $this->webDriver
            ->findElement(WebDriverBy::name("userenabled"));
        $this->assertTrue($userenabled->isSelected());
        $userenabled->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/User Enabled Status Changed/', $checkMsg->getText());
        $userenabled = $this->webDriver
            ->findElement(WebDriverBy::name("userenabled"));
        $this->assertFalse($userenabled->isSelected());

        // Go back to the list and check user is shown as disabled there
        $this->webDriver
            ->findElement(WebDriverBy::linkText('find another user'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[1]"))
            ->getText();
        $this->assertEquals('selenium', $rowdata);
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[6]"))
            ->getText();
        $this->assertEquals('Yes', $rowdata);

        // Check you can re-enable the user
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );
        $userenabled = $this->webDriver
            ->findElement(WebDriverBy::name("userenabled"));
        $this->assertFalse($userenabled->isSelected());
        $userenabled->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/User Enabled Status Changed/', $checkMsg->getText());
        $userenabled = $this->webDriver
            ->findElement(WebDriverBy::name("userenabled"));
        $this->assertTrue($userenabled->isSelected());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }


    /**
     * Test that users can have their realname edited
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     * @depends testAddUser
     *
     * @return void
     */
    public function testEditRealName()
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Search for the user and check account is not locked
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[1]"))
            ->getText();
        $this->assertEquals('selenium', $rowdata);
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[2]"))
            ->getText();
        $this->assertEquals('Selenium User', $rowdata);

        // Edit the user to change their name
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );

        // Check that the username exists in the edit
        $realname = $this->webDriver->findElement(WebDriverBy::name('realname'));
        $this->assertRegExp('/Selenium User/', $realname->getAttribute("value"));
        $realname->clear();
        $realname->sendKeys('User Selenium');
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/Real Name Changed/', $checkMsg->getText());
        $realname = $this->webDriver->findElement(WebDriverBy::name('realname'));
        $this->assertRegExp('/User Selenium/', $realname->getAttribute("value"));

        // Go back to the list and check the realname has changed
        $this->webDriver
            ->findElement(WebDriverBy::linkText('find another user'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[1]"))
            ->getText();
        $this->assertEquals('selenium', $rowdata);
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[2]"))
            ->getText();
        $this->assertEquals('User Selenium', $rowdata);

        // Change the Real Name Back
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (User Selenium)',
            $this->webDriver->getTitle()
        );

        // Check that the username exists in the edit
        $realname = $this->webDriver->findElement(WebDriverBy::name('realname'));
        $this->assertRegExp('/User Selenium/', $realname->getAttribute("value"));
        $realname->clear();
        $realname->sendKeys('Selenium User');
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/Real Name Changed/', $checkMsg->getText());
        $realname = $this->webDriver->findElement(WebDriverBy::name('realname'));
        $this->assertRegExp('/Selenium User/', $realname->getAttribute("value"));

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that users can have their e-mail address edited
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     * @depends testAddUser
     *
     * @return void
     */
    public function testEditEMail()
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Search for the user and check account is not locked
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[1]"))
            ->getText();
        $this->assertEquals('selenium', $rowdata);
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[3]"))
            ->getText();
        $this->assertEquals('selenium@example.com', $rowdata);

        // Edit the user to change their name
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );

        // Check that the username exists in the edit
        $useremail = $this->webDriver->findElement(WebDriverBy::name('useremail'));
        $this->assertRegExp(
            '/selenium@example.com/',
            $useremail->getAttribute("value")
        );
        $useremail->clear();
        $useremail->sendKeys('selenium2@example.com');
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/E-Mail Address Changed/', $checkMsg->getText());
        $useremail = $this->webDriver->findElement(WebDriverBy::name('useremail'));
        $this->assertRegExp(
            '/selenium2@example.com/',
            $useremail->getAttribute("value")
        );

        // Go back to the list and check the realname has changed
        $this->webDriver
            ->findElement(WebDriverBy::linkText('find another user'))
            ->click();
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[1]"))
            ->getText();
        $this->assertEquals('selenium', $rowdata);
        $rowdata = $this->webDriver
            ->findElement(WebDriverBy::xpath("//table/tbody/tr[2]/td[3]"))
            ->getText();
        $this->assertEquals('selenium2@example.com', $rowdata);

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that users' email can be disabled
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     * @depends testAddUser
     *
     * @return void
     */
    public function testDisableUsersEmail()
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Search for the user and check account is not locked
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());

        // Edit the user to disable them
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();

        // As this is the first time on this page check the names are right
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );

        // Disable the users e-mail and check it is disabled
        $emaildisabled = $this->webDriver
            ->findElement(WebDriverBy::name("userdisablemail"));
        $this->assertFalse($emaildisabled->isSelected());
        $emaildisabled->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/Disable E-Mail Changed/', $checkMsg->getText());
        $emaildisabled = $this->webDriver
            ->findElement(WebDriverBy::name("userdisablemail"));
        $this->assertTrue($emaildisabled->isSelected());

        // re-Enable the Users E-mail
        $emaildisabled = $this->webDriver
            ->findElement(WebDriverBy::name("userdisablemail"));
        $this->assertTrue($emaildisabled->isSelected());
        $emaildisabled->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/Disable E-Mail Changed/', $checkMsg->getText());
        $emaildisabled = $this->webDriver
            ->findElement(WebDriverBy::name("userdisablemail"));
        $this->assertFalse($emaildisabled->isSelected());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that users can be added to the admin group
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     * @depends testAddUser
     *
     * @return void
     */
    public function testAdminGroup()
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Search for the user and move to the edit user page
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());

        // Edit the user to disable them
        $this->webDriver
            ->findElement(WebDriverBy::linkText('selenium'))
            ->click();

        // As this is the first time on this page check the names are right
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );

        // Add the User to the Admin Group
        $admin = $this->webDriver->findElement(WebDriverBy::name("admin"));
        $this->assertFalse($admin->isSelected());
        $admin->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/Added to group: admin/', $checkMsg->getText());
        $admin = $this->webDriver->findElement(WebDriverBy::name("admin"));
        $this->assertTrue($admin->isSelected());


        // Move away from the edit user page and back again
        $this->webDriver
            ->findElement(WebDriverBy::linkText('find another user'))
            ->click();
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());

        // Edit the user to disable them
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();

        // As this is the first time on this page check the names are right
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );

        // Remove the user from the Admin Group
        $admin = $this->webDriver->findElement(WebDriverBy::name("admin"));
        $this->assertTrue($admin->isSelected());
        $admin->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/Removed from group: admin/', $checkMsg->getText());
        $admin = $this->webDriver->findElement(WebDriverBy::name("admin"));
        $this->assertFalse($admin->isSelected());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that users can be added to the editusers group
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     * @depends testAddUser
     *
     * @return void
     */
    public function testEditUsersGroup()
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Search for the user and move to the edit user page
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());

        // Edit the user
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();

        // As this is the first time on this page check the names are right
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );

        // Add the User to the Admin Group
        $editusers = $this->webDriver->findElement(WebDriverBy::name("editusers"));
        $this->assertFalse($editusers->isSelected());
        $editusers->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/Added to group: editusers/', $checkMsg->getText());
        $editusers = $this->webDriver->findElement(WebDriverBy::name("editusers"));
        $this->assertTrue($editusers->isSelected());

        // Move away from the edit user page and back again
        $this->webDriver
            ->findElement(WebDriverBy::linkText('find another user'))
            ->click();
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());

        // Edit the user to disable them
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();

        // As this is the first time on this page check the names are right
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );

        // Remove the user from the Admin Group
        $editusers = $this->webDriver->findElement(WebDriverBy::name("editusers"));
        $this->assertTrue($editusers->isSelected());
        $editusers->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/Removed from group: editusers/', $checkMsg->getText());
        $editusers = $this->webDriver->findElement(WebDriverBy::name("editusers"));
        $this->assertFalse($editusers->isSelected());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that users can be added to the editgroups group
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     * @depends testAddUser
     *
     * @return void
     */
    public function testEditGroupsGroup()
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Search for the user and move to the edit user page
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());

        // Edit the user to disable them
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();

        // As this is the first time on this page check the names are right
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );

        // Add the User to the Edit Group Group
        $editgroups = $this->webDriver->findElement(WebDriverBy::name("editgroups"));
        $this->assertFalse($editgroups->isSelected());
        $editgroups->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/Added to group: editgroups/', $checkMsg->getText());
        $editgroups = $this->webDriver->findElement(WebDriverBy::name("editgroups"));
        $this->assertTrue($editgroups->isSelected());

        // Move away from the edit user page and back again
        $this->webDriver
            ->findElement(WebDriverBy::linkText('find another user'))
            ->click();
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());

        // Edit the user to remove them from the edit group group
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();

        // As this is the first time on this page check the names are right
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );

        // Remove the user from the Admin Group
        $editgroups = $this->webDriver->findElement(WebDriverBy::name("editgroups"));
        $this->assertTrue($editgroups->isSelected());
        $editgroups->click();
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp(
            '/Removed from group: editgroups/',
            $checkMsg->getText()
        );
        $editgroups = $this->webDriver->findElement(WebDriverBy::name("editgroups"));
        $this->assertFalse($editgroups->isSelected());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that users' Password can be changed by the administrator. This involves
     * the following steps:
     * 1.  Log the selenium user in using the original password
     * 2.  Log the admin in and change selenium's password
     * 3.  Try to log selenium in on the original password.  This will not work.
     * 4.  Log selenium in using the new password.  This will work
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     * @depends testAddUser
     *
     * @return void
     */
    public function testChangePassword()
    {
        // Test that the Selenium User Can log in and out
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys('selenium');
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys('Selenium1');
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Log in as the Admin User
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Search for the Selenium user and change the password
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());

        // Edit the user to change their Password
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );

        // Check that the username exists in the edit
        $userpasswd = $this->webDriver->findElement(WebDriverBy::name('passwd'));
        $userpasswd->clear();
        $userpasswd->sendKeys('Selenium345');
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/User changed selenium/', $checkMsg->getText());
        $this->assertRegExp('/Password Changed/', $checkMsg->getText());

        //Log the Admin out
        $this->webDriver->findElement(WebDriverBy::linkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());

        // Login the Selenium User Again with the Old Password
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys('selenium');
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys('Selenium1');
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $checkLogin = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp(
            '/Invalid Username and password/',
            $checkLogin->getText()
        );

        // Login the Selenium User Again with the New Password
        // Wait for Password Time out
        sleep(16);
        $this->webDriver->get(URL);
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
        $this->webDriver
            ->findElement(WebDriverBy::name('username'))->sendKeys('selenium');
        $this->webDriver
            ->findElement(WebDriverBy::name('password'))->sendKeys('Selenium345');
        $this->webDriver->findElement(WebDriverBy::name('Login_Button'))->click();
        $this->assertEquals(WEBSITENAME . ': Home', $this->webDriver->getTitle());

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }

    /**
     * Test that and error message is displayed if no changes are made prior to
     * the save button being clicked
     *
     * @group selenium
     * @group users
     *
     * @depends testSearchUsersPage
     * @depends testSearchUsers
     * @depends testAddUser
     *
     * @return void
     */
    public function testNoChanges()
    {
        // Login as the Admin User and Change the Password
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


        // Switch to the Search Users Page
        $link = $this->webDriver
            ->findElement(WebDriverBy::linkText('Users'))
            ->click();

        // Check that we are on the Default Preferences Page
        $this->assertEquals(
            WEBSITENAME . ': Search Users',
            $this->webDriver->getTitle()
        );

        // Search for the Selenium user and submit the page with no changes
        $this->webDriver
            ->findElement(WebDriverBy::name('searchstr'))
            ->sendKeys('selenium');
        $this->webDriver->findElement(WebDriverBy::id('search_button'))->click();
        $this->assertEquals(
            WEBSITENAME . ': List Users',
            $this->webDriver->getTitle()
        );
        $main_body = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#main-body'));
        $this->assertRegExp('/selenium/', $main_body->getText());
        $this->assertRegExp('/1 users found/', $main_body->getText());

        // Edit the user to change their Password
        $this->webDriver->findElement(WebDriverBy::linkText('selenium'))->click();
        $this->assertEquals(
            WEBSITENAME . ': Edit User: selenium (Selenium User)',
            $this->webDriver->getTitle()
        );

        // Check that the username exists in the edit
        $this->webDriver->findElement(WebDriverBy::name('save'))->click();
        $checkMsg = $this->webDriver
            ->findElement(WebDriverBy::cssSelector('div#msg-box'));
        $this->assertRegExp('/You have not made any changes/', $checkMsg->getText());

        // Finished with this user so approve deletion by tearDown task
        $this->deleteSeleniumUser = true;

        // Test Finished so Logout of the Website
        $this->webDriver->findElement(WebDriverBy::LinkText('Logout'))->click();
        $this->assertEquals(WEBSITENAME . ': Login', $this->webDriver->getTitle());
    }
}
