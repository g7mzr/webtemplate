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
require_once __DIR__ . '/../../config.php';

// Include the composer Autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Install Dependencies Class Unit Test
 *
 * @backupGlobals disabled
 *
 **/
class DependenciesTest extends TestCase
{

    /**
     * Dependencies Object
     *
     * @var\g7mzr\webtemplate\install\Dependencies Object
     */
    protected $object;


    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
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
     * Function to test creation of Dependencies class
     *
     * @group install
     * @group unittest
     *
     * @return void
     */
    public function testCreateClass()
    {
        $this->object = new\g7mzr\webtemplate\install\Dependencies();
        $this->expectOutputRegex("/Checking configuration:/");
        $this->object = null;
    }

    /**
     * Function to test the PHP Version check passess
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testPHPVersionPass()
    {
        $this->object = new\g7mzr\webtemplate\install\Dependencies();

        //  PHP Version Pass
        $this->object->checkPHP("5.6.1");
        $this->expectOutputRegex("/Ok: Found: /");
    }


    /**
     * Function to test the PHP Version check passess
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testPHPVersionFail()
    {
        $this->object = new\g7mzr\webtemplate\install\Dependencies();

        //  PHP Version Fail
        $this->object->checkPHP("10.6.1");
        $this->expectOutputRegex("/\(v10.6.1\)         Found: v/");
    }


    /**
     * Function to test the correct PHP Modules are installed
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testPHPModulesPass()
    {
        $this->object = new\g7mzr\webtemplate\install\Dependencies();

        //  PHP Modules Pass
        $result = $this->object->checkPHPModules(
            \g7mzr\webtemplate\install\DependenciesData::$phpModules
        );
        $this->expectOutputRegex("/Checking for POSIX                 Ok: Found/");
        $this->expectOutputRegex("/Checking for SESSION               Ok: Found/");
        $this->expectOutputRegex("/Checking for PDO                   Ok: Found/");
        $this->assertTrue($result);
        $result = $this->object->printErrorMsgs();
        $this->assertFalse($result);
    }


    /**
     * Function to test that php modules not installed fail
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testPHPModulesFail()
    {
        $this->object = new\g7mzr\webtemplate\install\Dependencies();

        //  PHP Modules Fail
        $testdata = array(
            'TEST' => array(
                'name' => 'test',
                'install' => "Install test as instructed by your version of PHP"
            )
        );
        $result = $this->object->checkPHPModules($testdata);
        $this->expectOutputRegex(
            "/Checking for TEST                  Extension not found/"
        );
        $this->assertFalse($result);

        $result = $this->object->printErrorMsgs();
        $this->assertTrue($result);
        $this->expectOutputRegex("/instructed by your version of PHP/");
    }

    /**
     * Function to test the other Modules are installed
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testOtherModulesPass()
    {
        $this->object = new\g7mzr\webtemplate\install\Dependencies();


        $testData = array(
            'SMARTY' => array(
                'classname'      => '\Smarty',
                'version'       => '3.1.29',
                'versionvar'    => "Smarty::SMARTY_VERSION",
                'versionsubstr' => 0,
                'install'       => 'Smarty: Install via composer'
            )
        );
        $result = $this->object->checkComposerModules($testData);
        $this->expectOutputRegex("/Checking for SMARTY/");
        $this->expectOutputRegex("/      Ok: Found: v/");
        $this->assertTrue($result);

        // Test printing error messages
        $result = $this->object->printErrorMsgs();
        $this->assertFalse($result);
    }


    /**
     * Function to test the other Modules are installed
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testOtherModulesPassAny()
    {
        $this->object = new\g7mzr\webtemplate\install\Dependencies();


        $testData = array(
            'SMARTY' => array(
                'classname'      => '\Smarty',
                'version'       => 'any',
                'versionvar'    => "Smarty::SMARTY_VERSION",
                'versionsubstr' => 0,
                'install'       => 'Smarty: Install via composer'
            )
        );
        $result = $this->object->checkComposerModules($testData);
        $this->expectOutputRegex("/Checking for SMARTY/");
        $this->expectOutputRegex("/      Ok: Found: v/");
        $this->assertTrue($result);

        // Test printing error messages
        $result = $this->object->printErrorMsgs();
        $this->assertFalse($result);
    }

    /**
     * Function to test correct message when module is not found
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testOtherModulesNotFound()
    {
        $this->object = new\g7mzr\webtemplate\install\Dependencies();


        $testData = array(
            'TEST' => array(
                'classname'      => '\test',
                'version'       => '3.1.19',
                'versionvar'    => "Smarty::SMARTY_VERSION",
                'versionsubstr' => 0,
                'install'       => 'Smarty: Install via composer'
            )
        );
        $result = $this->object->checkComposerModules($testData);
        $this->expectOutputRegex("/Module not found/");
        $this->assertFalse($result);

        // Test printing error messages
        $result = $this->object->printErrorMsgs();
        $this->assertTrue($result);
        $this->expectOutputRegex("/Smarty: Install via composer/");
    }

    /**
     * Function to test the other Modules are installed
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testOtherModulesWrongVersion()
    {
        $this->object = new\g7mzr\webtemplate\install\Dependencies();


        $testData = array(
            'SMARTY' => array(
                'classname'      => '\Smarty',
                'version'       => '30.1.19',
                'versionvar'    => "Smarty::SMARTY_VERSION",
                'versionsubstr' => 0,
                'install'       => 'Smarty: Install via composer'
            )
        );
        $result = $this->object->checkComposerModules($testData);
        $this->expectOutputRegex("/Checking for SMARTY \(v30.1.19\)     Found:/");
        $this->assertFalse($result);

        // Test printing error messages
        $result = $this->object->printErrorMsgs();
        $this->assertTrue($result);
        $this->expectOutputRegex("/Smarty: Install via composer/");
    }


    /**
     * Function to test correct message when module is not found
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testDBMSDriverNotFound()
    {
        global $installConfig;


        $this->object = new\g7mzr\webtemplate\install\Dependencies();

        $database = "test";
        $result = $this->object->checkDatabases(
            $database,
            \g7mzr\webtemplate\install\DependenciesData::$databases,
            $installConfig
        );
        $this->expectOutputRegex("/test Databases are not supported/");
        $this->assertFalse($result);
    }

    /**
     * Function to test correct message when the pdo driver is not supported by the
     * installed version of php
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testDBMSPDONotFound()
    {
        global $installConfig;

        // Set up dependancy class
        $this->object = new\g7mzr\webtemplate\install\Dependencies();

        // Set up database array to pass initial check but fail pdo test
        $databasearray = array(
            'test' => array(
                'phpdriver'     => 'test',
                'phpinstall'    => "Install php pgsql pdo driver",
                'dbversion'     => '9.3.0',
                'dbinstall'     => 'Get postgreSQL from http://www.postgresql.org/',
                'dbupgrade'     => 'Get postgreSQL from http://www.postgresql.org/',
                'templatedb'    => 'template1'
            )
        );


        $database = "test";


        $result = $this->object->checkDatabases(
            $database,
            $databasearray,
            $installConfig
        );
        $this->expectOutputRegex("/Install php pgsql pdo driver/");
        $this->assertFalse($result);
    }


    /**
     * Function to test correct message when the wrong version of the postgresql
     * database is installed
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testDBMSWrongVersion()
    {
        global $installConfig;

        // Set up dependancy class
        $this->object = new\g7mzr\webtemplate\install\Dependencies();

        // Set up database array to pass initial check but fail pdo test
        $databasearray = array(
            'pgsql' => array(
                'phpdriver'     => 'pgsql',
                'phpinstall'    => "Install php pgsql pdo driver",
                'dbversion'     => '99.3.0',
                'dbinstall'     => 'Get postgreSQL from http://www.postgresql.org/',
                'dbupgrade'     => 'Get postgreSQL from http://www.postgresql.org/',
                'templatedb'    => 'template1'
            )
        );


        $database = $installConfig['database_type'];

        $result = $this->object->checkDatabases(
            $database,
            $databasearray,
            $installConfig
        );
        $this->expectOutputRegex("/Database not configured correctly/");
        $this->assertFalse($result);
    }


    /**
     * Function to test correct message when  a valid version of the postgresql
     * database is installed
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testDBMSValidVersion()
    {
        global $installConfig;

        // Set up dependancy class
        $this->object = new\g7mzr\webtemplate\install\Dependencies();

        // Set up database array to pass initial check but fail pdo test
        $databasearray = array(
            'pgsql' => array(
                'phpdriver'     => 'pgsql',
                'phpinstall'    => "Install php pgsql pdo driver",
                'dbversion'     => '9.3.0',
                'dbinstall'     => 'Get postgreSQL from http://www.postgresql.org/',
                'dbupgrade'     => 'Get postgreSQL from http://www.postgresql.org/',
                'templatedb'    => 'template1'
            )
        );


        $database = $installConfig['database_type'];

        // Test Specific version
        $result = $this->object->checkDatabases(
            $database,
            $databasearray,
            $installConfig
        );
        $this->expectOutputRegex("/Checking for pgsql \(v9.3.0\)        Ok: Found/");
        $this->assertTrue($result);

        // Test Any version
        $databasearray['pgsql']['dbversion'] = 'any';
        $result = $this->object->checkDatabases(
            $database,
            $databasearray,
            $installConfig
        );
        $this->expectOutputRegex("/Checking for pgsql \(any\)           Ok: Found/");
        $this->assertTrue($result);
    }

    /**
     * Function to test correct message when  a valid version of the postgresql
     * database is installed
     *
     * @group install
     * @group unittest
     *
     * @depends testCreateClass
     *
     * @return void
     */
    public function testDBMSInValidDatabase()
    {
        global $installConfig;

        // Set up dependancy class
        $this->object = new\g7mzr\webtemplate\install\Dependencies();

        // Set up database array to pass initial check but fail pdo test
        $databasearray = array(
            'pgsql' => array(
                'phpdriver'     => 'pgsql',
                'phpinstall'    => "Install php pgsql pdo driver",
                'dbversion'     => '9.3.0',
                'dbinstall'     => 'Get postgreSQL from http://www.postgresql.org/',
                'dbupgrade'     => 'Get postgreSQL from http://www.postgresql.org/',
                'templatedb'    => 'template99'
            )
        );


        $database = $installConfig['database_type'];
        $installConfig['database_superuser_passwd'] = "dudpassword";
        // Test Specific version
        $result = $this->object->checkDatabases(
            $database,
            $databasearray,
            $installConfig
        );
        $this->expectOutputRegex("/Unable to switch DBManager to admin mode/");
        $this->assertFalse($result);
    }
}
