<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\unittest;

use PHPUnit\Framework\TestCase;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

// include the test database connection configuration
require_once dirname(__FILE__) .'/../_data/database.php';

/**
 * UserPrefClass Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class EditUserPrefClassTest extends TestCase
{
    /**
     * User Prefs Object
     *
     * @var UserPrefClass
     */
    protected $object;

    /**
     * MDB2 Database Connection Object
     *
     * @var MDB2 Database Connection Object
     *
     * @access protected
     */
    protected $object2;

    /**
     * Valid Database Connection Flag
     *
     * @var Valid Database connection
     *
     * @access protected
     */
    protected $databaseconnection;

    /**
     * MOCK Database Connection
     *
     * @var Mock MDB2 Connection
     *
     * @access protected
     */
    protected $mockDB;

    /**
     * User Pref Object Using the Mock Connection
     *
     * @var UserPrefClass
     *
     * @access protected
     */
    protected $mockPrefClass;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        global $testdsn, $sitePreferences;

        // Check that we can connect to the database
        $this->object2 = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($this->object2)) {
            $this->databaseconnection = true;
        } else {
            $this->databaseconnection = false;
        }

        // Set up the site preferences
        $sitePreferences['theme']['value'] = 'Dusk';
        $sitePreferences['theme']['enabled'] = true;
        $sitePreferences['zoomtext']['value'] = true;
        $sitePreferences['zoomtext']['enabled'] = true;
        $sitePreferences['displayrows']['value'] = '2';
        $sitePreferences['displayrows']['enabled'] = true;

        // Create a new User Object
        $this->object = new \webtemplate\users\EditUserPref($this->object2, '2');

        $testdsn['phptype'] = 'mock';
        $this->mockDB = \webtemplate\db\DB::load($testdsn);
        $this->mockPrefClass = new \webtemplate\users\EditUserPref(
            $this->mockDB,
            '2'
        );
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
        if ($this->databaseconnection === true) {
            $this->object2->disconnect();
        }
    }

    /**
     * Test that the last message created by the Class is available
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testGetLastMsg()
    {
        $localStr = $this->object->getLastMsg();
        $this->assertEquals('', $localStr);
    }


    /**
     * Test that the existing preferences can be loaded
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testloadPreferences()
    {
        global $sitePreferences;

        if ($this->databaseconnection == true) {
            // Got   Preferences;
            $rootDir = dirname(__FILE__) . "/../_data";
            $result = $this->object->loadPreferences($rootDir, $sitePreferences);
            $this->assertTrue($result);

            // Load an empty Directory
            $rootDir = __DIR__ . "/../_data/stylefail";
            $result = $this->object->loadPreferences($rootDir, $sitePreferences);
            $this->assertFalse($result);
            $this->assertContains(
                "No Theme have been installed",
                $this->object->getLastMsg()
            );

            // Load a Directory that does not exits
            $rootDir = __DIR__;
            $result = $this->object->loadPreferences($rootDir, $sitePreferences);
            $this->assertFalse($result);
            $this->assertContains(
                "Unable to Open Style Directory",
                $this->object->getLastMsg()
            );
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that the installed themes (Directories in the Sytle folder can be
     * identified
     *
     * @group unittest
     * @group users
     *
     * @depends testloadPreferences
     *
     * @return null
     */
    public function testgetInstalledThemes()
    {
        global $sitePreferences;

        if ($this->databaseconnection == true) {
            // Get   Preferences;
            $rootDir = dirname(__FILE__) ."/../_data";
            $result = $this->object->loadPreferences($rootDir, $sitePreferences);
            if ($result) {
                $themeArray = $this->object->getInstalledThemes();
                $this->assertEquals('default', $themeArray[0]['name']);
                $this->assertTrue($themeArray[0]['selected']);
                $this->assertEquals('Dusk (Site Default)', $themeArray[0]['title']);
            } else {
                $this->fail("Unable to load Preferences");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that Zomm Prefernces Array for the SELECT Form element can be created
     *
     * @group unittest
     * @group users
     *
     * @depends testloadPreferences
     *
     * @return null
     */
    public function testZoomTextOptions()
    {
        global $sitePreferences;

        if ($this->databaseconnection == true) {
            // Get   Preferences;
            $rootDir = dirname(__FILE__) ."/../_data";
            $result = $this->object->loadPreferences($rootDir, $sitePreferences);
            if ($result) {
                $zoomTextArray = $this->object->getZoomTextOptions();
                $this->assertEquals('default', $zoomTextArray[0]['name']);
                $this->assertTrue($zoomTextArray[0]['selected']);
                $this->assertEquals('On (Site Default)', $zoomTextArray[0]['title']);
            } else {
                $this->fail("Unable to load Preferences");
            }

            // Load with ZOOM TEXT equal to False
                        // Load with Zoom Text = false
            $sitePreferences['zoomtext']['value'] = false;
            $rootDir = dirname(__FILE__) . "/../_data";
            $result = $this->object->loadPreferences($rootDir, $sitePreferences);
            if ($result) {
                $zoomTextArray = $this->object->getZoomTextOptions();
                $this->assertEquals('default', $zoomTextArray[0]['name']);
                $this->assertTrue($zoomTextArray[0]['selected']);
                $this->assertEquals(
                    'Off (Site Default)',
                    $zoomTextArray[0]['title']
                );
            } else {
                $this->fail("Unable to load Preferences");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that Display Rows Array for the SELECT element in the Form can be
     * created
     *
     * @group unittest
     * @group users
     *
     * @depends testloadPreferences
     *
     * @return null
     */
    public function testgetDisplayRowsOptions()
    {
        global $sitePreferences;

        if ($this->databaseconnection == true) {
            // Get   Preferences;
            $rootDir = dirname(__FILE__) ."/../_data";
            $result = $this->object->loadPreferences($rootDir, $sitePreferences);
            if ($result) {
                $displayRowsArray = $this->object->getDisplayRowsOptions();
                $this->assertEquals('default', $displayRowsArray[0]['name']);
                $this->assertTrue($displayRowsArray[0]['selected']);
                $this->assertEquals(
                    '20 (Site Default)',
                    $displayRowsArray[0]['title']
                );
            } else {
                $this->fail("Unable to load Preferences");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that the Preferences Chosen by the user can be validated
     *
     * @group unittest
     * @group users
     *
     * @depends testloadPreferences
     *
     * @return null
     */
    public function testValidateUserPreferences()
    {
        global $sitePreferences;

        // Get Preferences;
        $rootDir = dirname(__FILE__) ."/../_data";
        $result = $this->object->loadPreferences($rootDir, $sitePreferences);
        if ($result) {
            $inputArray = array();
            // Test with no variables set
            $result = $this->object->validateUserPreferences($inputArray);
            $this->assertTrue($result);

            // Test with all Variables Set okay
            $inputArray['theme'] = 'default';
            $inputArray['zoom_textareas'] = 'default';
            $inputArray['display_rows'] = 'default';
            $result = $this->object->validateUserPreferences($inputArray);
            $this->assertTrue($result);

            // Test with invalid theme
            $inputArray['theme'] = 'purple';
            $inputArray['zoom_textareas'] = 'default';
            $inputArray['display_rows'] = 'default';
            $result = $this->object->validateUserPreferences($inputArray);
            $this->assertFalse($result);
            $msg = $this->object->getLastMsg();
            $this->assertContains('The chosen theme', $msg);

            // Test with invalid zoom Text Areas
            $inputArray['theme'] = 'Dusk';
            $inputArray['zoom_textareas'] = 'run';
            $inputArray['display_rows'] = 'default';
            $result = $this->object->validateUserPreferences($inputArray);
            $this->assertFalse($result);
            $msg = $this->object->getLastMsg();
            $this->assertContains('The ZoomTextArea', $msg);

            // Test with invalid Display Rows
            $inputArray['theme'] = 'Dusk';
            $inputArray['zoom_textareas'] = 'on';
            $inputArray['display_rows'] = '6';
            $result = $this->object->validateUserPreferences($inputArray);
            $this->assertFalse($result);
            $msg = $this->object->getLastMsg();
            $this->assertContains('display rows', $msg);
        } else {
            $this->fail("Unable to load Preferences");
        }
    }

     /**
     * Test that changes made by the user can be confirmed
     *
     * @group unittest
     * @group users
     *
     * @depends testloadPreferences
     * @depends testValidateUserPreferences
     *
     * @return null
     */
    public function testCheckUserPreferencesUpdated()
    {
        global $sitePreferences;

        // Get Preferences;
        $rootDir = dirname(__FILE__) ."/../_data";
        $result = $this->object->loadPreferences($rootDir, $sitePreferences);
        if ($result) {
            // Create input array
            $inputArray = array();

            // Test with no input variables
            $result = $this->object->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->object->checkUserPreferencesChanged();
                $this->assertFalse($result);
            } else {
                $this->fail("Failed to Validate Preferences");
            }

            // Test with no Changes
            $inputArray['theme'] = 'default';
            $inputArray['zoom_textareas'] = 'default';
            $inputArray['display_rows'] = 'default';
            $result = $this->object->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->object->checkUserPreferencesChanged();
                $this->assertFalse($result);
            } else {
                $this->fail("Failed to Validate Preferences");
            }

            // Test with Theme updated
            $inputArray['theme'] = 'Dusk';
            $inputArray['zoom_textareas'] = 'default';
            $inputArray['display_rows'] = 'default';
            $result = $this->object->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->object->checkUserPreferencesChanged();
                $this->assertTrue($result);
                $msg = $this->object->getLastMsg();
                $this->assertContains('Theme Updated', $msg);
            } else {
                $this->fail("Failed to Validate Preferences");
            }

            // Test with Zoom Text Areas updated as On
            $inputArray['theme'] = 'default';
            $inputArray['zoom_textareas'] = 'on';
            $inputArray['display_rows'] = 'default';
            $result = $this->object->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->object->checkUserPreferencesChanged();
                $this->assertTrue($result);
                $msg = $this->object->getLastMsg();
                $this->assertContains('Text Area Zoom Updated', $msg);
            } else {
                $this->fail("Failed to Validate Preferences");
            }


            // Test with Zoom Text Areas updated as On
            $inputArray['theme'] = 'default';
            $inputArray['zoom_textareas'] = 'off';
            $inputArray['display_rows'] = 'default';
            $result = $this->object->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->object->checkUserPreferencesChanged();
                $this->assertTrue($result);
                $msg = $this->object->getLastMsg();
                $this->assertContains('Text Area Zoom Updated', $msg);
            } else {
                $this->fail("Failed to Validate Preferences");
            }



            // Test with Theme updated
            $inputArray['theme'] = 'default';
            $inputArray['zoom_textareas'] = 'default';
            $inputArray['display_rows'] = '4';
            $result = $this->object->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->object->checkUserPreferencesChanged();
                $this->assertTrue($result);
                $msg = $this->object->getLastMsg();
                $this->assertContains('Number of Data Rows', $msg);
            } else {
                $this->fail("Failed to Validate Preferences");
            }

            // Test with no input variables
            $inputArray2 = array();
            $result = $this->object->validateUserPreferences($inputArray2);
            if ($result) {
                $result = $this->object->checkUserPreferencesChanged();
                $this->assertFalse($result);
            } else {
                $this->fail("Failed to Validate Preferences");
            }
        } else {
            $this->fail("Unable to load Preferences");
        }
    }



     /**
     * Test that the Users Chosen preferences can be saved
     *
     * @group unittest
     * @group users
     *
     * @depends testloadPreferences
     * @depends testValidateUserPreferences
     * @depends testCheckUserPreferencesUpdated
     *
     * @return null
     */
    public function testSaveUserPreferences()
    {
        global $sitePreferences;

        // Get Preferences;
        $rootDir = dirname(__FILE__) ."/../_data";
        $result = $this->object->loadPreferences($rootDir, $sitePreferences);
        if ($result) {
            // Try and save preferences before validation etc
            $result = $this->object->saveUserPreferences();
            $this->assertFalse($result);
            $msg = $this->object->getLastMsg();
            $this->assertContains('No Changes Made.', $msg);

            //Create input array
            $inputArray = array();
            $inputArray['theme'] = 'Dusk';
            $inputArray['zoom_textareas'] = 'off';
            $inputArray['display_rows'] = '2';

            // Test with no input variables
            $result = $this->object->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->object->checkUserPreferencesChanged();
                if ($result) {
                    $result = $this->object->saveUserPreferences();
                    $this->assertTrue($result);
                } else {
                    $this->fail("Failed checking user preferences had changed");
                }
            } else {
                $this->fail("Failed to Validate Preferences");
            }
            $this->object->updatePreferences();



            $inputArray = array();
            $inputArray['theme'] = 'Dusk';
            $inputArray['zoom_textareas'] = 'on';
            $inputArray['display_rows'] = '2';

            // Test with no input variables
            $result = $this->object->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->object->checkUserPreferencesChanged();
                if ($result) {
                    $result = $this->object->saveUserPreferences();
                    $this->assertTrue($result);
                } else {
                    $this->fail("Failed checking user preferences had changed");
                }
            } else {
                $this->fail("Failed to Validate Preferences");
            }
            $this->object->updatePreferences();





            $inputArray['theme'] = 'default';
            $inputArray['zoom_textareas'] = 'default';
            $inputArray['display_rows'] = 'default';

            // Test with no input variables
            $result = $this->object->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->object->checkUserPreferencesChanged();
                if ($result) {
                    $result = $this->object->saveUserPreferences();
                    $this->assertTrue($result);
                } else {
                    $this->fail("Failed checking user preferences had changed");
                }
            } else {
                $this->fail("Failed to Validate Preferences");
            }
        } else {
            $this->fail("Unable to load Preferences");
        }
    }



    /**
     * Mock Database Driver
     * Test that Preferences cannot be saved without Validation
     *
     * @group unittest
     * @group users
     *
     * @depends testloadPreferences
     * @depends testValidateUserPreferences
     * @depends testCheckUserPreferencesUpdated
     *
     * @return null
     */
    public function testMockSaveUserPreferencesNoValidation()
    {
        global $sitePreferences;

        // Get Preferences;
        $rootDir = dirname(__FILE__) ."/../_data";
        $result = $this->mockPrefClass->loadPreferences($rootDir, $sitePreferences);
        if ($result) {
            // Try and save preferences before validation etc
            $result = $this->mockPrefClass->saveUserPreferences();
            $this->assertFalse($result);
            $msg = $this->mockPrefClass->getLastMsg();
            $this->assertContains('No Changes Made.', $msg);
        } else {
            $this->fail("Mock Driver: Unable to load Preferences");
        }
    }



    /**
     * Mock Database Driver
     * Test that The Class deals with a Transaction Failure
     *
     * @group unittest
     * @group users
     *
     * @depends testloadPreferences
     * @depends testValidateUserPreferences
     * @depends testCheckUserPreferencesUpdated
     *
     * @return null
     */
    public function testMockSaveUserPreferencesTransactionFail()
    {
        global $sitePreferences;

        // Get Preferences;
        $rootDir = dirname(__FILE__) ."/../_data";
        $result = $this->mockPrefClass->loadPreferences($rootDir, $sitePreferences);
        if ($result) {
            //Create input array
            $inputArray = array();
            $inputArray['theme'] = 'Dusk';
            $inputArray['zoom_textareas'] = 'off';
            $inputArray['display_rows'] = '2';

            // Fail Transaction Test
            $result = $this->mockPrefClass->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->mockPrefClass->checkUserPreferencesChanged();
                if ($result) {
                    $result = $this->mockPrefClass->saveUserPreferences();
                    $this->assertFalse($result);
                } else {
                    $this->fail("Failed checking user preferences had changed");
                }
            } else {
                $this->fail("Failed to Validate Preferences");
            }
        } else {
            $this->fail("Mock Driver: Unable to load Preferences");
        }
    }

    /**
     * Mock Database Driver
     * Test that
     *
     * @group unittest
     * @group users
     *
     * @depends testloadPreferences
     * @depends testValidateUserPreferences
     * @depends testCheckUserPreferencesUpdated
     *
     * @return null
     */
    public function testMockSaveUserPreferences()
    {
        global $sitePreferences;

        // Get Preferences;
        $rootDir = dirname(__FILE__) ."/../_data";
        $result = $this->mockPrefClass->loadPreferences($rootDir, $sitePreferences);
        if ($result) {
            //Create input array
            $inputArray = array();
            $inputArray['theme'] = 'Dusk';
            $inputArray['zoom_textareas'] = 'off';
            $inputArray['display_rows'] = '2';

            // Pass Start Transaction Test
            $functions = array(
                'saveUserPreferences' => array(
                    'starttransaction' => true

                )
            );
            $data = array();
            $this->mockDB->control($functions, $data);
            $result = $this->mockPrefClass->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->mockPrefClass->checkUserPreferencesChanged();
                if ($result) {
                    $result = $this->mockPrefClass->saveUserPreferences();
                    $this->assertFalse($result);
                } else {
                    $this->fail("Failed checking user preferences had changed");
                }
            } else {
                $this->fail("Failed to Validate Preferences");
            }

            // PASS THE DELETE TEST
            $functions['saveUserPreferences']['delete'] = 'userprefs';
            $this->mockDB->control($functions, $data);
            $result = $this->mockPrefClass->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->mockPrefClass->checkUserPreferencesChanged();
                if ($result) {
                    $result = $this->mockPrefClass->saveUserPreferences();
                    $this->assertFalse($result);
                } else {
                    $this->fail("Failed checking user preferences had changed");
                }
            } else {
                $this->fail("Failed to Validate Preferences");
            }

            // Rerun with Zoom TEXT ON
            $inputArray = array();
            $inputArray['theme'] = 'Dusk';
            $inputArray['zoom_textareas'] = 'on';
            $inputArray['display_rows'] = '2';
            $result = $this->mockPrefClass->validateUserPreferences($inputArray);
            if ($result) {
                $result = $this->mockPrefClass->checkUserPreferencesChanged();
                if ($result) {
                    $result = $this->mockPrefClass->saveUserPreferences();
                    $this->assertFalse($result);
                } else {
                    $this->fail("Failed checking user preferences had changed");
                }
            } else {
                $this->fail("Failed to Validate Preferences");
            }
        } else {
            $this->fail("Mock Driver: Unable to load Preferences");
        }
    }




















































































    /**
     * Test that any new theme that the USer choses is used to display the
     * comfirmation page
     *
     * @group unittest
     * @group users
     *
     * @depends testloadPreferences
     * @depends testValidateUserPreferences
     * @depends testCheckUserPreferencesUpdated
     *
     * @return null
     */
    public function testGetNewTheme()
    {
        global $sitePreferences;

        // Run without Loading and updating Preferences
        $localStr = $this->object->getNewTheme($sitePreferences['theme']['value']);
        $this->assertEquals('Dusk', $localStr);

        // Set Up Preferences for Test
        $rootDir = dirname(__FILE__) ."/../_data";
        $result = $this->object->loadPreferences($rootDir, $sitePreferences);
        if ($result) {
            // Run using Default Input
            $inputArray['theme'] = 'default';
            $inputArray['zoom_textareas'] = 'default';
            $inputArray['display_rows'] = 'default';

            $result = $this->object->validateUserPreferences($inputArray);
            if ($result) {
                $localStr = $this->object->getNewTheme(
                    $sitePreferences['theme']['value']
                );
                $this->assertEquals('Dusk', $localStr);
            } else {
                $this->fail("Failed to Validate Preferences");
            }

            // Run using Blue Theme
            $inputArray['theme'] = 'Blue';
            $inputArray['zoom_textareas'] = 'default';
            $inputArray['display_rows'] = 'default';

            $result = $this->object->validateUserPreferences($inputArray);
            if ($result) {
                $localStr = $this->object->getNewTheme(
                    $sitePreferences['theme']['value']
                );
                $this->assertEquals('Blue', $localStr);
            } else {
                $this->fail("Failed to Validate Preferences");
            }
        } else {
            $this->fail("Unable to load Preferences");
        }
    }
}
