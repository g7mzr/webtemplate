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

namespace webtemplate\unittest;

use PHPUnit\Framework\TestCase;

// Include the Class Autoloader
require_once __DIR__ . '/../../includes/global.php';

/**
 * Local Validate Class Unit Tests
 *
 **/
class LocalValidateTest extends TestCase
{
    /**
     * Local Validate Object
     *
     * @var \webtemplate\general\LocalValidate
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
        $this->object = new \webtemplate\general\LocalValidate();
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
     * Test that the Record number contains numbers and is between 1 and 99999
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testDbid()
    {
        $this->assertTrue($this->object->dbid('1'));
        $this->assertTrue($this->object->dbid('10'));
        $this->assertFalse($this->object->dbid('111111'));
    }

    /**
     * Test that the username is between 5 and 12 characters long and contains
     * only letters and numbers.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testUsername()
    {
        $regexp = '/^[a-zA-Z0-9]{5,12}$/';
        $this->assertTrue($this->object->username('name1', $regexp));
        $this->assertTrue($this->object->username('name234', $regexp));
        $this->assertFalse($this->object->username('name', $regexp));
        $this->assertFalse($this->object->username('name123456789', $regexp));
    }

    /**
     * Test that the regular expression only contains valid characters
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testRegexp()
    {
        $this->assertTrue($this->object->regexp('/^[a-zA-Z0-9]{5,12}$/'));
        $this->assertFalse($this->object->regexp('/^[a-zA-Z0-9];{5,12}$/'));
    }

    /**
     * Test that the users real name is between 2 and 60 characters long without
     * invalid characters
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testRealname()
    {
        $this->assertTrue($this->object->realname('First Surname'));
        $this->assertFalse($this->object->realname('Fi'));
        $this->assertFalse($this->object->realname('First;'));
    }

    /**
     * Test Password is between 8 and 20 characters long.
     * It can contain upper and lower case letters and numbers
     * It must contain one of each
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testPassword()
    {
        global $parameters;

        // Test no constraints
        $strength = '1';
        $this->assertTrue($this->object->password('Passwor1', $strength));
        $this->assertTrue(
            $this->object->password('Password1Password2aa', $strength)
        );
        $this->assertTrue($this->object->password('Password', $strength));
        $this->assertTrue($this->object->password('Password!', $strength));
        $this->assertTrue($this->object->password('password1', $strength));
        $this->assertFalse($this->object->password('Passw1', $strength));

        // Test lower letters only
        $strength = '2';
        $this->assertTrue($this->object->password('password', $strength));
        $this->assertTrue($this->object->password('passwordpasswordaa', $strength));
        $this->assertFalse($this->object->password('Passw1', $strength));
        $this->assertFalse($this->object->password('Password', $strength));
        $this->assertFalse($this->object->password('password1', $strength));

        // Test Upper and Lowercase letters
        $strength = '3';
        $this->assertTrue($this->object->password('Passworda', $strength));
        $this->assertTrue($this->object->password('PasswordPasswordaa', $strength));
        $this->assertFalse($this->object->password('Passw', $strength));
        $this->assertFalse($this->object->password('Password1', $strength));

        //Test Upper and lower case letters and numbers
        $strength = '4';
        $this->assertTrue($this->object->password('Passwor1', $strength));
        $this->assertTrue(
            $this->object->password('Password1Password2aa', $strength)
        );
        $this->assertFalse($this->object->password('Passw1', $strength));
        $this->assertFalse($this->object->password('Password', $strength));
        $this->assertFalse($this->object->password('password1', $strength));
        $this->assertFalse($this->object->password('password1!', $strength));

        // Test upper and lower case letters. numbers and special characters
        $strength = '5';
        $this->assertTrue($this->object->password('Passwor1!', $strength));
        $this->assertTrue(
            $this->object->password('Password1Password2a$', $strength)
        );
        $this->assertFalse($this->object->password('Passw1', $strength));
        $this->assertFalse($this->object->password('Password', $strength));
        $this->assertFalse($this->object->password('password1', $strength));
    }

    /**
     * Test e-mail addresses are valid
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testEmail()
    {
        $this->assertTrue($this->object->email('user@example.com'));
        $this->assertTrue($this->object->email('user.name@example.com'));
        $this->assertFalse($this->object->email('user@example'));
    }

    /**
     * Test URLs are valid
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testUrl()
    {
        $this->assertTrue($this->object->url('http://example.com'));
        $this->assertTrue($this->object->url('https://example.com'));
        $this->assertTrue($this->object->url('ftp://example.com'));
        //$this->assertTrue($this->object->url('http://10.10.10.10'));
        $this->assertTrue($this->object->url('http://example.com:8080'));
        $this->assertTrue($this->object->url('http://example.com/directory'));
        $this->assertTrue($this->object->url('http://example.com/directory/file'));
        $this->assertTrue(
            $this->object->url('http://example.com/directory/file.ext')
        );
        $this->assertFalse($this->object->url('http://example/directory'));
    }

    /**
     * Test that the directory path works
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testPath()
    {
        $this->assertTrue($this->object->path('/path'));
        $this->assertTrue($this->object->path('/path/'));
        $this->assertTrue($this->object->path('path/'));
        $this->assertTrue($this->object->path('path/', true));
        $this->assertFalse($this->object->path('path;/'));
    }

    /**
     * Test that the directory path works
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testDocPath()
    {
        $this->assertTrue($this->object->docpath('path/'));
        $this->assertFalse($this->object->docpath('path'));
        $this->assertTrue($this->object->docpath('path/path/'));
        $this->assertFalse($this->object->docpath('/path/path'));
    }

    /**
     * Test valid domain name format
     * Test that the correct help pages are returned using the help map.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testDomain()
    {
        $this->assertTrue($this->object->domain('example.com'));
        $this->assertTrue($this->object->domain('example.com.uk'));
        $this->assertFalse($this->object->domain('example.com\uk'));
    }

    /**
     * Test that groupname is between 5 and 25 characters long with only upper and
     * lower case characters
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testGroupname()
    {
        $this->assertTrue($this->object->groupname('examp'));
        $this->assertTrue($this->object->groupname('examplegroupnamejktntrfve'));
        $this->assertTrue($this->object->groupname('EXAMP'));
        $this->assertFalse($this->object->groupname('exam'));
        $this->assertFalse($this->object->groupname('examplegroupnamejktntrfved'));
    }

    /**
     * Group Description between 5 and 255 characters including spaces and full stops
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testGroupdescription()
    {
        $this->assertTrue($this->object->groupdescription('examp'));
        $this->assertTrue(
            $this->object->groupdescription('This is a group description.')
        );
        $this->assertFalse($this->object->groupdescription('This'));
        $this->assertFalse(
            $this->object->groupdescription('This is a group destription;')
        );
    }

    /**
     * Test GeneralText can contain spaces and some punctuation.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testGeneraltext()
    {
        $this->assertTrue($this->object->generaltext('examp'));
        $this->assertTrue(
            $this->object->generaltext('This is a group description.')
        );
        $this->assertFalse(
            $this->object->generaltext('This is a group destription;')
        );
    }

    /**
     * Test Token contains 10 characters of lower case letters and numbers.
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testToken()
    {
        $this->assertTrue($this->object->token('abcd1234uh'));
        $this->assertFalse($this->object->token('abcd1234'));
        $this->assertFalse($this->object->token('abcd1234uhd'));
        $this->assertFalse($this->object->token('abcd1234Uh'));
    }

    /**
     * Test the HTML File Name validation function
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testhtmlFile()
    {
        $this->assertTrue($this->object->htmlFile('sandy.html'));
        $this->assertTrue($this->object->htmlFile('release-notes.html'));
        $this->assertFalse($this->object->htmlFile('sandy.htm'));
        $this->assertFalse($this->object->htmlFile('../../sandy.html'));
        $this->assertFalse($this->object->htmlFile('sandy'));
    }
}
