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

require_once __DIR__ . '/../../vendor/autoload.php';
use \org\bovigo\vfs\vfsStream;
use \org\bovigo\vfs\vfsStreamDirectory;
use \org\bovigo\vfs\vfsStreamWrapper;

/**
 * Parameters Class Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class FileManagerTest extends TestCase
{
    /**
     * Vertual file system mikey179/vfsStream Object
     *
     * @var mikey179/vfsStream
     */
    protected $vsffilestream;

    /**
     * \webtemplate\install\FileManager Object
     *
     * @var \webtemplate\install\FileManager
     */
    protected $filemanager;

    /**
     * Root directory name
     *
     * @var string
     */
    protected $rootdir = "webtemplate";

    /**
     * This function is called prior to any tests being run.
     * Its prpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        $this->vsffilestream = vfsStream::setup($this->rootdir);
        $this->filemanager = new \webtemplate\install\FileManager(
            vfsStream::url($this->rootdir)
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
    }

    /**
     * Test that the correct error message is received if no config.php is present
     *
     * @group unittest
     * @group install
     *
     * @return null
     */
    public function testcheckInstallConfigNoFile()
    {
        $testresult = $this->filemanager->checkInstallConfig($installConfig);
        if (\webtemplate\general\General::isError($testresult)) {
            $this->assertContains(
                'Please copy config.php.dist',
                $testresult->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": Error testing for a config file");
        }
    }


    /**
     * Test that the correct error message is received if no config.php is present
     *
     * @group unittest
     * @group install
     *
     * @return null
     */
    public function testcheckInstallConfigEmptyFile()
    {
        // Set the config file name
        $configfile = vfsStream::url($this->rootdir . "/config.php");

        // Copy a blank config file to the vfsStream
        copy(
            __DIR__ . '/../../config.php.dist',
            $configfile
        );

        if (file_exists($configfile)) {
            include $configfile;
        }


        $testresult = $this->filemanager->checkInstallConfig($installConfig);
        if (\webtemplate\general\General::isError($testresult)) {
            $this->assertContains(
                'Please update config.php to match your installation',
                $testresult->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": Error Testing for an empty config file");
        }
    }

    /**
     * Test that a correctly populated config file is available
     *
     * @group unittest
     * @group install
     *
     * @return null
     */
    public function testcheckInstallConfig()
    {
        // Set the config file name
        $configfile = vfsStream::url($this->rootdir . "/config.php");

        // Copy a blank config file to the vfsStream
        copy(
            __DIR__ . '/../../config.php',
            $configfile
        );

        if (file_exists($configfile)) {
            include $configfile;
        }


        $testresult = $this->filemanager->checkInstallConfig($installConfig);
        if (!\webtemplate\general\General::isError($testresult)) {
            $this->assertTrue($testresult);
        } else {
            $this->fail(__FUNCTION__ . ": Error Testing for a config file");
        }
    }


    /**
     * Test that a local.conf file can be created.
     *
     * @group unittest
     * @group install
     *
     * @return null
     */
    public function testcreateLocalConfig()
    {
        // Set the config file name
        $configfile = vfsStream::url($this->rootdir . "/config.php");

        // Copy a blank config file to the vfsStream
        copy(
            __DIR__ . '/../../config.php',
            $configfile
        );

        if (file_exists($configfile)) {
            include $configfile;
        }

        // This should fail as the directory does not exist to put the file in.
        $testresult = $this->filemanager->createLocalConf($installConfig);
        if (\webtemplate\general\General::isError($testresult)) {
            $this->assertContains(
                'Error creating local.conf',
                $testresult->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": Error testing failure");
        }

        // From here on in the file should either be created or aknowledge that it
        // already exists.
        $this->vsffilestream->addChild(vfsStream::newDirectory('configs'));

        // test to see if a local config file can be created
        $testresult = $this->filemanager->createLocalConf($installConfig);
        if (!\webtemplate\general\General::isError($testresult)) {
            $this->assertTrue($testresult);
            $this->assertFileEquals(
                __DIR__ .'/../../configs/local.conf',
                vfsStream::url($this->rootdir . "/configs/local.conf")
            );
        } else {
            $this->fail(__FUNCTION__ . ": Error creating a Local Config File");
        }

        // Check that we will not over write an existing file
        $testresult = $this->filemanager->createLocalConf($installConfig);
        if (!\webtemplate\general\General::isError($testresult)) {
            $this->assertFalse($testresult);
        } else {
            $this->fail(__FUNCTION__ . ": Error creating a Local Config File");
        }
    }

    /**
     * Test that a local.conf file can be created.
     *
     * @group unittest
     * @group install
     *
     * @return null
     */
    public function testcreateTestConf()
    {
        // Set the config file name
        $configfile = vfsStream::url($this->rootdir . "/config.php");

        // Copy a blank config file to the vfsStream
        copy(
            __DIR__ . '/../../config.php',
            $configfile
        );

        if (file_exists($configfile)) {
            include $configfile;
        }

        // Test with the incorrect directory structure for failure
        $testresult = $this->filemanager->createTestConf($installConfig);
        if (\webtemplate\general\General::isError($testresult)) {
            $this->assertContains(
                'Error creating tests/_data/database.php',
                $testresult->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": Error testing failure");
        }


        // Create the directory structure so the tests will pass.
        $this->vsffilestream->addChild(vfsStream::newDirectory('tests'));
        $this->vsffilestream->addChild(vfsStream::newDirectory('tests/_data'));

        $testConfig = vfsStream::url($this->rootdir . "/tests/_data/database.php");

        // test to see if a local config file can be created
        $testresult = $this->filemanager->createTestConf($installConfig);
        if (!\webtemplate\general\General::isError($testresult)) {
            $this->assertTrue($testresult);
        } else {
            $this->fail(__FUNCTION__ . ": Error creating a Local Config File");
        }

        if (file_exists($testConfig)) {
            include $testConfig;
            $this->assertEquals(
                $installConfig['database_type'],
                $testdsn["phptype"]
            );
            $this->assertEquals(
                $installConfig['database_host'],
                $testdsn["hostspec"]
            );
            $this->assertEquals(
                $installConfig['database_name'],
                $testdsn["database"]
            );
            $this->assertEquals(
                $installConfig['database_user'],
                $testdsn["username"]
            );
            $this->assertEquals(
                $installConfig['database_user_passwd'],
                $testdsn["password"]
            );
        }
    }


    /**
     * Test the parameters.php file can be created
     *
     * @group unittest
     * @group install
     *
     * @return null
     */
    public function testcreateParameters()
    {
        $parametersfile = vfsStream::url($this->rootdir . "/configs/parameters.php");
        // Test with the configs directory missing.  It should report back false
        $result = $this->filemanager->createParameters($parameters);
        $this->assertFalse($result);

        // Test with the configs directory existing.  It should report back true
        $this->vsffilestream->addChild(vfsStream::newDirectory('configs'));
        $result = $this->filemanager->createParameters($parameters);
        $this->assertTrue($result);
        include $parametersfile;
        $this->assertEquals(
            '1',
            $parameters['admin']['logging']
        );
        $this->assertEquals(
            'none',
            $parameters['email']['smtpdeliverymethod']
        );

        // Test for an update
        $parameters['email']['smtpdeliverymethod'] = "smtp";
        $result = $this->filemanager->createParameters($parameters);
        $this->assertTrue($result);
        include $parametersfile;
        $this->assertEquals(
            '1',
            $parameters['admin']['logging']
        );
        $this->assertEquals(
            'smtp',
            $parameters['email']['smtpdeliverymethod']
        );

        // Test that an error occurs if the file cannot be opened for writing
        chmod($parametersfile, 0444);
        $result = $this->filemanager->createParameters($parameters);
        $this->assertFalse($result);
    }

    /**
     * Test the preferences.php file can be created
     *
     * @group unittest
     * @group install
     *
     * @return null
     */
    public function testcreatePreferences()
    {
        $preferencesfile = vfsStream::url(
            $this->rootdir . "/configs/preferences.php"
        );
        // Test with the configs directory missing.  It should report back false
        $result = $this->filemanager->createPreferences($sitePreferences);
        $this->assertFalse($result);

        // Test with the configs directory existing.  It should report back true
        $this->vsffilestream->addChild(vfsStream::newDirectory('configs'));
        $result = $this->filemanager->createPreferences($sitePreferences);
        $this->assertTrue($result);
        include $preferencesfile;



        $this->assertEquals(
            'Dusk',
            $sitePreferences['theme']['value']
        );
        $this->assertTrue($sitePreferences['theme']['enabled']);

        // Test for an update
        $sitePreferences['theme']['value'] = 'Blue';
        $sitePreferences['theme']['enabled'] = false;

        $result = $this->filemanager->createPreferences($sitePreferences);
        $this->assertTrue($result);
        include $preferencesfile;

        $this->assertEquals(
            'Blue',
            $sitePreferences['theme']['value']
        );
        $this->assertFalse($sitePreferences['theme']['enabled']);

        // Test that an error occurs if the file cannot be opened for writing
        chmod($preferencesfile, 0444);
        $result = $this->filemanager->createPreferences($sitePreferences);
        $this->assertFalse($result);
    }


    /**
     * Test the preferences.php file can be created
     *
     * @group unittest
     * @group install
     *
     * @return null
     */
    public function testdeleteCompiledTemplates()
    {
        // Create the Template directory and a test file
        $this->vsffilestream->addChild(vfsStream::newDirectory('templates_c'));
        touch(vfsStream::url('webtemplate/templates_c/template.php'));
        touch(vfsStream::url('webtemplate/templates_c/.gitignore'));


        // Confirm files exist
        $this->assertTrue(
            file_exists(vfsStream::url('webtemplate/templates_c/template.php'))
        );
        $this->assertTrue(
            file_exists(vfsStream::url('webtemplate/templates_c/.gitignore'))
        );

        // Run the template deletion function
        $this->filemanager->deleteCompiledTemplates();

        // Confirm the PHP file has gone
        $this->assertFalse(
            file_exists(vfsStream::url('webtemplate/templates_c/template.php'))
        );

        // Confirm .gitignore still exists
        $this->assertTrue(
            file_exists(vfsStream::url('webtemplate/templates_c/.gitignore'))
        );
    }

    /**
     * Test the setPermissions function
     *
     * @group unittest
     * @group install
     *
     * @return null
     */
    public function testsetPermissions()
    {
        $structure = array(
            'base' => array(
                "docs" => array(
                    "en" => array(
                        "xml" => array(
                            "test1.xml" => "xml_test1.xml",
                            "test2.xml" => "xml_test2.xml"
                        ),

                        "html" => array(
                            "index.html" => "An HTML File"
                        )
                    ),
                    "developer" => array(
                        "develop_xml" => array(
                            "test1.xml" => "develop_test1.xml",
                            "test2.xml" => "develop_test2.xml"
                        ),
                        "restapi_xml" => array(
                            "test1.xml" => "develop_test1.xml",
                            "test2.xml" => "develop_test2.xml"
                        ),
                    ),
                    "makedocs.php" => "Make docs text"
                ),
                "index.php" => "The main file",
                "editusers.php" => "Another PHP File"

            ),
            "configs" => array(
                "parameters.php" => "Parameters File",
                "preferences.php" => "Preferences File"
            ),
            "cache" => array(
                "cacheFile.php" => " A Cache file"
            ),
            "logs" => array(
                "weekly.log" => " Alog File"
            ),
            "templates" => array(
                "en" => array(
                    "main.tpl" => " Some Text in the template File",
                    "layout.tpl" => "The main layout",
                    "admin" => array(
                        "editprefs.tpl" => "The editprefs template"
                    ),
                    "users" => array(
                        "editusers.tpl" => "The edit users template"
                    )
                )
            ),
            "templates_c" =>array(
                "template1.php" => " Some Text"
            ),
            "includes" => array(
                "autoloader.php" => "The class autoloader",
                "admin" => array(
                    "editprefs.php" => "The edit prefs class",
                    "editsettings.php" => "The edit params class"
                ),
                "users" => array(
                    "editusers.php" => "EditUsers Class"
                )
            ),
            "tests" => array(
                "classtest.php" => "The unit test file"
            ),
            "install.php" => "Some Text in the file",
            "config.php"  => "The config file"
        );
        vfsStream::create($structure, $this->vsffilestream);

        // Set Up the Webserver group incorrectly and test for error message
        $installConfig = array();
        $installConfig['webservergroup'] = 'wwwrun';

        $result = $this->filemanager->setPermissions($installConfig);
        if (\webtemplate\general\General::isError($result)) {
            $this->assertContains(
                'Invalid Web Server Group',
                $result->getMessage()
            );
        } else {
            $this->fail(__FUNCTION__ . ": Error checking webserver group");
        }

        // Set up the correct Webserver Group
        $installConfig['webservergroup'] = 'www';
        $result = $this->filemanager->setPermissions($installConfig);
        $this->assertTrue($result);

        // Check Directories
        $this->assertEquals(
            0770,
            $this->vsffilestream->getChild('templates_c')->getPermissions()
        );


        // Check Files
        // Index.html from docs
        $this->assertEquals(
            0640,
            $this->vsffilestream
                ->getChild('base/docs/en/html/index.html')
                ->getPermissions()
        );

        $this->assertEquals(
            0600,
            $this->vsffilestream
                ->getChild('base/docs/en/xml/test1.xml')
                ->getPermissions()
        );

        // Templates files
        $this->assertEquals(
            0640,
            $this->vsffilestream
                ->getChild('templates/en/main.tpl')
                ->getPermissions()
        );
        $this->assertEquals(
            0640,
            $this->vsffilestream
                ->getChild('templates/en/users/editusers.tpl')
                ->getPermissions()
        );
    }
}
