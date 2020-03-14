<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Unit Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\unittest;

use PHPUnit\Framework\TestCase;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

/**
 * Preferences Class Unit Tests
 *
 **/
class PreferencesTest extends TestCase
{
    /**
     * Preference Class Object
     *
     * @var\g7mzr\webtemplate\admin\Preferences
     */

    protected $object;
    /**
     * Configuration Object
     *
     * @var\g7mzr\webtemplate\config\Configure
     */
    protected $confobj;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {

        // Create configuration Object
        $configDir = __DIR__ . "/../../configs";
        $this->confobj = new\g7mzr\webtemplate\config\Configure($configDir);

        $this->confobj->write('pref.theme.value', 'Dusk');
        $this->confobj->write('pref.theme.enabled', true);
        $this->confobj->write('pref.zoomtext.value', true);
        $this->confobj->write('pref.zoomtext.enabled', true);
        $this->confobj->write('pref.displayrows.value', '2');
        $this->confobj->write('pref.displayrows.enabled', true);


        $this->object = new\g7mzr\webtemplate\admin\Preferences($this->confobj);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        $configDir = dirname(dirname(__FILE__)) . "/_data";
        $filename = $configDir . '/preferences.php';
        //if (file_exists($filename)) {
        //    unlink($filename);
        //}
    }


    /**
     * Test no message exists when the class is created
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testGetLastMsg()
    {
        $localStr = $this->object->getLastMsg();
        $this->assertEquals('', $localStr);
    }

    /**
     * Test that the theme CSS file can be loaded
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testLoadThemes()
    {
        $rootDir = dirname(dirname(__FILE__)) . "/_data";
        $result = $this->object->loadThemes($rootDir);
        $this->assertTrue($result);

        $rootDir = dirname(dirname(__FILE__)) . "/_data/stylefail";
        $result = $this->object->loadThemes($rootDir);
        $this->assertFalse($result);
        $localStr = $this->object->getLastMsg();
        $this->assertEquals('No Theme have been installed', $localStr);

        $rootDir = dirname(dirname(__FILE__)) . "/_data/stylefail/style";
        $result = $this->object->loadThemes($rootDir);
        $this->assertFalse($result);
        $localStr = $this->object->getLastMsg();
        $this->assertEquals('Unable to Open Style Directory', $localStr);
    }

    /**
     * Test that the Default Theme can be set
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testSetDefaultThemes()
    {
        $rootDir = dirname(dirname(__FILE__)) . "/_data";
        $result = $this->object->loadThemes($rootDir);
        if ($result == true) {
            $result = $this->object->setDefaultThemes();
            $this->assertTrue($result);
        } else {
            $this->fail("Unable to load themes: " . $this->object->GetLastMsg());
        }
    }

    /**
     * Test the list of installed themes can be created
     *
     * @group unittest
     * @group admin
     *
     * @depends testLoadThemes
     * @depends testSetDefaultThemes
     *
     * @return void
     */
    public function testGetThemes()
    {
        $duskFound = false;
        $rootDir = dirname(dirname(__FILE__)) . "/_data";
        $result = $this->object->loadThemes($rootDir);
        if ($result == true) {
            $result = $this->object->setDefaultThemes();
            if ($result == true) {
                $resultArray = $this->object->getThemes();
                foreach ($resultArray as $value) {
                    if (($value['name'] == 'Dusk') and ($value['selected'])) {
                        $duskFound = true;
                    }
                }
                $this->assertTrue($duskFound);
            } else {
                $this->fail("Unable to load themes: " . $this->object->GetLastMsg());
            }
        } else {
            $this->fail("Error: " . $this->object->GetLastMsg());
        }
    }


    /**
     * Test that the current preferences can be returned to the application
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testGetCurrentPreferences()
    {
        $preferences = $this->object->GetCurrentPreferences();
        $this->assertEquals('Dusk', $preferences['theme']['value']);
        $this->assertTrue($preferences['theme']['enabled']);
        $this->assertTrue($preferences['zoomtext']['value']);
        $this->assertTrue($preferences['zoomtext']['enabled']);
        $this->assertEquals('2', $preferences['displayrows']['value']);
        $this->assertTrue($preferences['displayrows']['enabled']);
        $this->assertTrue($preferences['displayrows']['enabled']);
    }


    /**
     * Test that the the preferences input by the user can be validated
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testvalidatePreferences()
    {
        $rootDir = dirname(dirname(__FILE__)) . "/_data";
        // At present on the theme can cause a failure.
        // Other inputs have non validation default values
        $inputArray['theme'] = 'Dusk';
        $inputArray['theme-enabled'] = 'on';
        $inputArray['zoom_textareas'] = 'on';
        $inputArray['zoom_textareas_enabled'] = 'on';
        $inputArray['display_rows'] = '2';
        $inputArray['display_rows_enabled'] = 'on';
        $result = $this->object->loadThemes($rootDir);
        if ($result == true) {
            $result = $this->object->validatePreferences($inputArray);
            $this->assertTrue($result);
            $inputArray['theme'] = 'Yellow';
            $inputArray['display_rows'] = '6'; // Out of range
            $result = $this->object->validatePreferences($inputArray);
            $this->assertFalse($result);
        } else {
            $this->fail("Failed to Load Themes for the test");
        }
    }


    /**
     * Test that Preference file can be saved
     *
     * @group unittest
     * @group admin
     *
     * @depends testLoadThemes
     * @depends testvalidatePreferences
     *
     * @return void
     */
    public function testSavePrefFile()
    {
        // Test using Default Data
        $configDir = dirname(__FILE__) . "/../_data";
        $filesaved = $this->object->savePrefFile($configDir);
        $this->assertTrue($filesaved);
        $filename = $configDir . '/preferences.json';
        $expectedfilename = $configDir . '/preferences_test_file.json';
        $this->assertFileExists($filename);

        $this->assertFileEquals($expectedfilename, $filename);

        // test Using NEw Data
        $rootDir = dirname(dirname(__FILE__)) . "/_data";
        $inputArray['theme'] = 'Dusk';
        $result = $this->object->loadThemes($rootDir);
        if ($result == true) {
            $result = $this->object->validatePreferences($inputArray);
            if ($result == true) {
                $filesaved = $this->object->savePrefFile($configDir);
                $this->assertTrue($filesaved);
            } else {
                $this->fail("Failed to Validate Theme for the test");
            }
        } else {
            $this->fail("Failed to Load Themes for the test");
        }

        // Faile to save
        $configDir = dirname(__FILE__) . "/../data";
        $filesaved = $this->object->savePrefFile($configDir);
        $this->assertFalse($filesaved);
    }


    /**
     * Test that the Check Preferences have changed function works
     *
     * @group unittest
     * @group admin
     *
     * @return void
     */
    public function testcheckPreferencesChanged()
    {

        // No Change
        $this->confobj->write('pref.theme.value', 'Dusk');
        $this->confobj->write('pref.theme.enabled', true);
        $this->confobj->write('pref.zoomtext.value', true);
        $this->confobj->write('pref.zoomtext.enabled', true);
        $this->confobj->write('pref.displayrows.value', '2');
        $this->confobj->write('pref.displayrows.enabled', true);

        $result = $this->object->checkPreferencesChanged();
        $this->assertFalse($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertEquals('', $localStr);


        $this->confobj->write('pref.theme.value', 'Blue');
        $this->confobj->write('pref.theme.enabled', true);
        $this->confobj->write('pref.zoomtext.value', true);
        $this->confobj->write('pref.zoomtext.enabled', true);
        $this->confobj->write('pref.displayrows.value', '2');
        $this->confobj->write('pref.displayrows.enabled', true);

        $result = $this->object->checkPreferencesChanged();
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString('Theme Updated', $localStr);


        // Theme Enabled Changed
        $this->confobj->write('pref.theme.value', 'Dusk');
        $this->confobj->write('pref.theme.enabled', false);
        $this->confobj->write('pref.zoomtext.value', true);
        $this->confobj->write('pref.zoomtext.enabled', true);
        $this->confobj->write('pref.displayrows.value', '2');
        $this->confobj->write('pref.displayrows.enabled', true);

        $result = $this->object->checkPreferencesChanged();
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString('Theme Enabled Updated', $localStr);



        // Zoom Text Changed
        $this->confobj->write('pref.theme.value', 'Dusk');
        $this->confobj->write('pref.theme.enabled', true);
        $this->confobj->write('pref.zoomtext.value', false);
        $this->confobj->write('pref.zoomtext.enabled', true);
        $this->confobj->write('pref.displayrows.value', '2');
        $this->confobj->write('pref.displayrows.enabled', true);

        $result = $this->object->checkPreferencesChanged();
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString('Zoom Text Areas Updated', $localStr);


        //Zoom Text Enabled Changed
        $this->confobj->write('pref.theme.value', 'Dusk');
        $this->confobj->write('pref.theme.enabled', true);
        $this->confobj->write('pref.zoomtext.value', true);
        $this->confobj->write('pref.zoomtext.enabled', false);
        $this->confobj->write('pref.displayrows.value', '2');
        $this->confobj->write('pref.displayrows.enabled', true);

        $result = $this->object->checkPreferencesChanged();
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString('Zoom Text Areas Enabled Updated', $localStr);


        // Display rows changed
        $this->confobj->write('pref.theme.value', 'Dusk');
        $this->confobj->write('pref.theme.enabled', true);
        $this->confobj->write('pref.zoomtext.value', true);
        $this->confobj->write('pref.zoomtext.enabled', true);
        $this->confobj->write('pref.displayrows.value', '5');
        $this->confobj->write('pref.displayrows.enabled', true);

        $result = $this->object->checkPreferencesChanged();
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString('Display Rows Updated', $localStr);



        // Display rows enabled changed
        $this->confobj->write('pref.theme.value', 'Dusk');
        $this->confobj->write('pref.theme.enabled', true);
        $this->confobj->write('pref.zoomtext.value', true);
        $this->confobj->write('pref.zoomtext.enabled', true);
        $this->confobj->write('pref.displayrows.value', '2');
        $this->confobj->write('pref.displayrows.enabled', false);

        $result = $this->object->checkPreferencesChanged();
        $this->assertTrue($result);
        $localStr = $this->object->GetLastMsg();
        $this->assertStringContainsString('Display Rows Enabled Updated', $localStr);
    }
}
