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

// Load the Test Database Configuration File
require_once dirname(__FILE__) . '/../_data/database.php';

/**
 * Configure Class Unit Tests
 *
 **/
class ConfigureClassTest extends TestCase
{
    /**
     * Blank Class Object
     *
     * @var\g7mzr\webtemplate\config\Configure
     */
    protected $object;


    /**
     * Database Driver Class
     *
     * @var \g7mzr\db\interfaces\InterfaceDatabaseDriver
     */
    protected $db;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        /**
         * Database object is currently not used.  Disable from tests
         */
        //global $testdsn, $options;

        // Create a database object
        global $testdsn;
        // Check that we can connect to the database
        try {
            $this->db = new \g7mzr\db\DBManager(
                $testdsn,
                $testdsn['username'],
                $testdsn['password']
            );
            $setresult = $this->db->setMode("datadriver");
            if (!\g7mzr\db\common\Common::isError($setresult)) {
                $this->databaseconnection = true;
            } else {
                $this->databaseconnection = false;
                echo $setresult->getMessage();
            }
        } catch (Exception $ex) {
            echo $ex->getMessage();
            $this->databaseconnection = false;
        }

        //$this->db = null;

        // Create configuration Object
        $this->object = new\g7mzr\webtemplate\config\Configure($this->db->getDataDriver());
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }

    /**
     * Test Configuration Items can be read
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testRead()
    {
        // Check we can get both arrays
        $resultarray = $this->object->read();
        $this->assertTrue(is_array($resultarray));
        $this->assertEquals(
            "none",
            $resultarray['parameters']["email"]["smtpdeliverymethod"]
        );
        $this->assertEquals(
            "Dusk",
            $resultarray['preferences']["theme"]["value"]
        );

        // Check we can read the Parameter aarray
        $parameters = $this->object->read('param');
        $this->assertTrue(is_array($parameters));
        $this->assertEquals("none", $parameters["email"]["smtpdeliverymethod"]);

        // Check that we can read the Preferences array
        $preferences = $this->object->read('pref');
        $this->assertTrue(is_array($preferences));
        $this->assertEquals("Dusk", $preferences['theme']['value']);

        // Check we can get one item from the parameters array
        $cookiepath = $this->object->read('param.cookiepath');
        $this->assertEquals("/", $cookiepath);
        $smtpdeliverymethod = $this->object->read("param.email.smtpdeliverymethod");
        $this->assertEquals("none", $smtpdeliverymethod);

        // check hat we can get one item from the preferences array
        $theme = $this->object->read('pref.theme.value');
        $this->assertEquals("Dusk", $theme);
        $themeEnabled = $this->object->read('pref.theme.enabled');
        $this->assertTrue($themeEnabled);

        // Check we can pull a sub array from the parameter array
        $email = $this->object->read("param.email");
        $this->assertTrue(is_array($email));
        $this->assertEquals("none", $email["smtpdeliverymethod"]);

        // Test that we can pull a sub array from the preferences array
        $themedata = $this->object->read('pref.theme');
        $this->assertEquals("Dusk", $themedata['value']);
        $this->assertTrue($themedata['enabled']);

        // Test we return Null if parameter does not exist
        $email = $this->object->read("param.email.test");
        $this->assertEquals("", $email);

        // Test for error message if param or pref not first item
        $msg = "fail";
        try {
            $result = $this->object->read('fail');
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }
        $this->assertStringContainsString(
            "Invalid Argument fail, should be param or pref",
            $msg
        );
    }

     /**
     * Test an error is thrown if the read path is not a string
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testReadPathNotString()
    {
        $msg = "fail";
        try {
            $smtpdeliverymethod = $this
                ->object->read(123344);
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }

        $this->assertStringContainsString(
            "should be a dot seperated path",
            $msg
        );
    }

    /**
     * Test an error is thrown if the read path is too long
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testReadPathTooLong()
    {
        $msg = "fail";
        try {
            $smtpdeliverymethod = $this
                ->object->read("param.email.smtp.delivery.method");
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }

        $this->assertStringContainsString(
            "path must contain less than 5 items",
            $msg
        );
    }


    /**
     * Test Parameter Items can be Written useing the write() function
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testWriteParameters()
    {
        // Check that the existing value is correct
        $smtpdeliverymethod = $this->object->read("param.email.smtpdeliverymethod");
        if ($smtpdeliverymethod != "none") {
            $this->fail("Prerequisites  for Param write test one are not set.");
        }

        // Update the smtpdeliverymethod and check it is updated.
        $this->object->write("param.email.smtpdeliverymethod", "test");
        $smtpdeliverymethod = $this->object->read("param.email.smtpdeliverymethod");
        $this->assertEquals("test", $smtpdeliverymethod);

        // Test a Value does not exist. One dimensional array
        $value = $this->object->read("param.system");
        if ($value !== null) {
            $this->fail("Prerequisites for Param write test two are not set.");
        }

        // Create the value and check it has been stored.  One dimensional array
        $this->object->write("param.system", "test");
        $value = $this->object->read("param.system");
        $this->assertEquals("test", $value);

        // Test a Value does not exist. Three dimensional array
        $value = $this->object->read("param.user.name.first");
        if ($value !== null) {
            $this->fail("Prerequisites for Param write test three are not set.");
        }

        // Create the value and check it has been stored.  Three dimensional array
        $this->object->write("param.user.name.first", "test");
        $value = $this->object->read("param.user.name.first");
        $this->assertEquals("test", $value);


        // Test for error message if param or pref not first item
        $msg = "fail";
        try {
            $result = $this->object->write('fail.user.name.first', 'test');
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }
        $this->assertStringContainsString(
            "Invalid Argument fail, should be param or pref",
            $msg
        );
    }

    /**
     * Test Preferences Items can be Written useing the write() function
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testWritePreferences()
    {
        // Preference Write Test one.  Check that one Item can be updated.
        $theme = $this->object->read('pref.theme.value');
        if ($theme != 'Dusk') {
            $this->fail("Prerequisites for Pref write test one are not set.");
        }
        $this->object->write('pref.theme.value', 'Blue');
        $newTheme = $this->object->read('pref.theme.value');
        $this->assertEquals("Blue", $newTheme);

        // Preference Write Test two One dimensional array
        $testTwoValue = $this->object->read("pref.system");
        if ($testTwoValue !== null) {
            $this->fail("Prerequisites for Prefwrite test two are not set.");
        }

        // Create the value and check it has been stored.  One dimensional array
        $this->object->write("pref.system", "test");
        $testTwoNewValue = $this->object->read("pref.system");
        $this->assertEquals("test", $testTwoNewValue);

        // Test a Value does not exist. Three dimensional array
        $value = $this->object->read("pref.user.name.first");
        if ($value !== null) {
            $this->fail("Prerequisites for Pref write test three are not set.");
        }

        // Create the value and check it has been stored.  Three dimensional array
        $this->object->write("pref.user.name.first", "test");
        $value = $this->object->read("pref.user.name.first");
        $this->assertEquals("test", $value);

        // Test for error message if param or pref not first item
        $msg = "fail";
        try {
            $result = $this->object->write('fail.user.name.first', 'test');
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }
        $this->assertStringContainsString(
            "Invalid Argument fail, should be param or pref",
            $msg
        );
    }

    /**
     * Test an error is thrown if the write path is not a string
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testWritePathNotString()
    {
        $msg = "fail";
        try {
            $smtpdeliverymethod = $this
                ->object->write(123344, 1);
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }

        $this->assertStringContainsString(
            "should be a dot seperated path",
            $msg
        );
    }

    /**
     * Test an error is thrown if the write path is too long
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testWritePathTooLong()
    {
        // Parameter test
        $msg = "fail";
        try {
            $smtpdeliverymethod = $this
                ->object->write("param.email.smtp.delivery.method", "test");
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }

        $this->assertStringContainsString(
            "path must contain less than 5 items",
            $msg
        );

        // Preference Test
        $msg = "fail";
        try {
            $smtpdeliverymethod = $this
                ->object->write("pref.email.smtp.delivery.method", "test");
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }

        $this->assertStringContainsString(
            "path must contain less than 5 items",
            $msg
        );
    }

    /**
     * Test the check function to see if a parameter exists or not.
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testCheckParameter()
    {
        // Check Parameter exists
        $parameterExists = $this->object->check("param.email.smtpdeliverymethod");
        $this->assertTrue($parameterExists);

        // Check parameter does not exist
        $parameterDoesNotExist = $this->object->check("param.dummy");
        $this->assertFalse($parameterDoesNotExist);

        // Check empty variable sent
        $empty = $this->object->check("");
        $this->assertFalse($empty);
    }

    /**
     * Test the check function to see if a preference exists or not.
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testCheckPreference()
    {
        // Check Preference exists
        $preferenceexists = $this->object->check('pref.theme.value');
        $this->assertTrue($preferenceexists);

        // Check Preference does not exist
        $preferencedoesnotexists = $this->object->check('pref.theme.dummy');
        $this->assertFalse($preferencedoesnotexists);

        // Check empty variable sent
        $empty = $this->object->check("");
        $this->assertFalse($empty);
    }

    /**
     * Test the delete function on a single parameter
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testDeleteParameter()
    {
        //Check that the parameter exists prior to deletion
        $checkExists = $this->object->check("param.email.smtpdeliverymethod");
        if ($checkExists == false) {
            $this->fail("Prerequisites for parameter delete test are not set");
        }

        $deleteResult = $this->object->delete("param.email.smtpdeliverymethod");
        $this->assertTrue($deleteResult);
        $checkDeleted = $this->object->check("param.email.smtpdeliverymethod");
        $this->assertFalse($checkDeleted);

        // Test for error message if param or pref not first item
        $msg = "fail";
        try {
            $result = $this->object->delete('fail');
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }
        $this->assertStringContainsString(
            "Invalid Argument fail, should be param or pref",
            $msg
        );
    }

    /**
     * Test the delete function on an array
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testDeleteParameterArray()
    {
        //Check that the parameter exists prior to deletion
        $checkExists = $this->object->check("param.email");
        if ($checkExists == false) {
            $this->fail("Prerequisites for parameter delete array test are not set");
        }

        $deleteResult = $this->object->delete("param.email");
        $this->assertTrue($deleteResult);
        $checkDeleted = $this->object->check("param.email");
        $this->assertFalse($checkDeleted);
    }

    /**
     * Test an error is thrown if the delete path is too long
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testDeleteParameterPathTooLong()
    {
        // Test fail on a parameter
        $msg = "fail";
        try {
            $smtpdeliverymethod = $this
                ->object->delete("param.email.smtp.delivery.method");
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }

        $this->assertStringContainsString(
            "path must contain less than 5 items",
            $msg
        );
    }

    /**
     * Test deleteing an Three dimensional array.  The array needs to be created as
     * part of the test
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testDeleteItemfrom3dimensionalArray()
    {
        // Check Parameter exists
        $parameterExists = $this->object->check("param.email.smtp.deliverymethod");
        if ($parameterExists  === true) {
            $this->fail("Prerequisites for parameter delete array test are not set");
        }

        $this->object->write("param.email.smtp.deliverymethod", "test");
        $parameterCreated = $this->object->check("param.email.smtp.deliverymethod");
        if ($parameterCreated  === false) {
            $this->fail("Unable to create arry for test");
        }

        // Delete item and make sure it has gone
        $result = $this->object->delete("param.email.smtp.deliverymethod");
        $this->assertTrue($result);
        $parameterDeleted = $this->object->check("param.email.smtp.deliverymethod");
        $this->assertFalse($parameterDeleted);
    }

    /**
     * Test deleteing an Invalid Parameter
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testDeleteInvalidParameter()
    {
        $result = $this->object->delete("param.test.fail");
        $this->assertFalse($result);
    }



    /**
     * Test the delete function on a single parameter
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testDeletePreference()
    {
        //Check that the parameter exists prior to deletion
        $checkExists = $this->object->check("pref.theme.value");
        if ($checkExists == false) {
            $this->fail("Prerequisites for prefrence delete test are not set");
        }

        $deleteResult = $this->object->delete("pref.theme.value");
        $this->assertTrue($deleteResult);
        $checkDeleted = $this->object->check("pref.theme.value");
        $this->assertFalse($checkDeleted);

        // Test for error message if param or pref not first item
        $msg = "fail";
        try {
            $result = $this->object->delete('fail');
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }
        $this->assertStringContainsString(
            "Invalid Argument fail, should be param or pref",
            $msg
        );
    }

    /**
     * Test the delete function on an array
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testDeletePreferenceArray()
    {
        //Check that the parameter exists prior to deletion
        $checkExists = $this->object->check("pref.theme");
        if ($checkExists == false) {
            $this->fail(
                "Prerequisites for preference delete array test are not set"
            );
        }

        $deleteResult = $this->object->delete("pref.theme");
        $this->assertTrue($deleteResult);
        $checkDeleted = $this->object->check("pref.theme");
        $this->assertFalse($checkDeleted);
    }

    /**
     * Test an error is thrown if the delete path is too long
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testDeletePreferencePathTooLong()
    {
        // Test fail on a parameter
        $msg = "fail";
        try {
            $smtpdeliverymethod = $this
                ->object->delete("pref.theme.value.one.two");
        } catch (\Throwable $ex) {
            $msg = $ex->getMessage();
        }

        $this->assertStringContainsString(
            "path must contain less than 5 items",
            $msg
        );
    }

    /**
     * Test deleting an Invalid Preference
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testDeleteInvalidPref()
    {
        $result = $this->object->delete("pref.test.fail");
        $this->assertFalse($result);
    }


    /**
     * Test that the parameters can be reloaded into the class
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testReloadParams()
    {
        $checksetup = $this->object->read('param.users.autocomplete');
        if ($checksetup !== false) {
            $this->fail("Prerequisites for reload test are not set correctly");
        }

        $parameters = $this->object->read('param');
        $parameters['users']['autocomplete'] = true;
        $this->object->reloadParams($parameters);
        $checkreload = $this->object->read('param.users.autocomplete');
        $this->assertTrue($checkreload);
    }


    /**
     * Test that the preferences can be reloaded into the class
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testReloadPrefs()
    {
        $checksetup = $this->object->read('pref.theme.enabled');
        if ($checksetup !== true) {
            $this->fail("Prerequisites for prefernce reload test are not set");
        }

        $preferences = $this->object->read('pref');
        $preferences['theme']['enabled'] = false;
        $this->object->reloadPref($preferences);
        $checkreload = $this->object->read('pref.theme.enabled');
        $this->assertFalse($checkreload);
    }


    /**
     * Test that the configuration parameters can be saved in a file
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testSaveParams()
    {
        $filesaved = $this->object->saveParams();
        $this->assertTrue($filesaved);
    }


    /**
     * Test that the configuration preferences are saved to the database
     *
     * @group unittest
     * @group config
     *
     * @return void
     */
    public function testSavePref()
    {
        $filesaved = $this->object->savePrefs();
        $this->assertTrue($filesaved);
    }
}
