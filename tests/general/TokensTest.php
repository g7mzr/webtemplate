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

// Include the Test Database Connection details
require_once dirname(__FILE__) . '/../_data/database.php';

/**
 * Tokens Class Unit Tests
 *
 **/
class TokensTest extends TestCase
{
    /**
     * Tokens Class
     *
     * @var \webtemplate\general\Tokens
     */
    protected $object;

    /**
     * Database Class
     *
     * @var \g7mzr\db\DBManager
     */
    protected $object2;


    /**
     * Database Connection
     *
     * @var Valid Database connection
     */
    protected $databaseconnection;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        global $testdsn;

        // Set up a Smarty Template object to get config variables
        $tpl = new \webtemplate\application\SmartyTemplate();

        //Set up the correct language and associated templates
        $languageconfig = "en.conf";
        $tpl->configLoad($languageconfig);

        // Check that we can connect to the database
        try {
            $this->object2 = new \g7mzr\db\DBManager(
                $testdsn,
                $testdsn['username'],
                $testdsn['password']
            );
            $setresult = $this->object2->setMode("datadriver");
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

        // Create a new User Object
        $this->object = new \webtemplate\general\Tokens($tpl, $this->object2->getDataDriver());
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        if ($this->databaseconnection == true) {
            $this->object2->getDataDriver()->disconnect();
        }
    }


    /**
     * This function tests the the craete token
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testCreateToken()
    {
        if ($this->databaseconnection == true) {
            // Create a 10 character text token based on a specific character set
            $result = $this->object->createToken(
                '1',
                'UNITTEST',
                1,
                'FIRST',
                false,
                false
            );
            if (\webtemplate\general\General::isError($result)) {
                $this->fail("Unable to create user Token");
            } else {
                $this->assertRegExp("/^([a-z0-9]{10})$/", $result);
            }

            // Create a MD5 MicroTime Token
            $result = $this->object->createToken('1', 'UNITTEST', 1, 'FIRST', true);
            if (\webtemplate\general\General::isError($result)) {
                $this->fail("Unable to create user Token");
            } else {
                $this->assertRegExp("/^([a-z0-9]{46})(\.)([a-z0-9]{8})$/", $result);
            }

            // Create a Token for an invalid user
            $result = $this->object->createToken('100', 'UNITTEST');
            if (\webtemplate\general\General::isError($result)) {
                $this->assertEquals(
                    "Error creating User Token",
                    $result->getMessage()
                );
            } else {
                $this->fail("Created token for invalid user");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * This function tests getTokenUserId function
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testgetTokenuserId()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createToken('1', 'UNITTEST', 1, 'FIRST');
            if (\webtemplate\general\General::isError($result)) {
                $this->fail("Unable to create user Token");
            } else {
                $userid = $this->object->getTokenUserid($result, 'UNITTEST');
                $this->assertEquals(1, $userid);

                // Invalid Token
                $userid = $this->object->getTokenUserid('hgytghf', 'UNITTEST');
                $this->assertEquals(0, $userid);

                //Invalid Token Type
                $userid = $this->object->getTokenUserid($result, 'UNIT');
                $this->assertEquals(0, $userid);

                // Valid Tokeb again
                $userid = $this->object->getTokenUserid($result, 'UNITTEST');
                $this->assertEquals(1, $userid);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * This function tests getTokenUserId function
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testverifyTokenuser()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createToken('1', 'UNITTEST', 1, 'FIRST');
            if (\webtemplate\general\General::isError($result)) {
                $this->fail("Unable to create user Token");
            } else {
                $verifyResult = $this->object->verifyToken($result, 'UNITTEST', 1);
                $this->assertTrue($verifyResult);

                // Invalid Token
                $verifyResult = $this->object->verifyToken('hgytghf', 'UNITTEST', 1);
                $this->assertFalse($verifyResult);

                //Invalid Token Type
                $verifyResult = $this->object->verifyToken($result, 'PASSWD', 1);
                $this->assertFalse($verifyResult);

                //Invalid user id
                $verifyResult = $this->object->verifyToken($result, 'UNITTEST', 2);
                $this->assertFalse($verifyResult);

                // Valid Token again
                $verifyResult = $this->object->verifyToken($result, 'UNITTEST', 1);
                $this->assertTrue($verifyResult);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * This function tests getTokenUserId function
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testgetEventData()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createToken('1', 'UNITTEST', 1, 'FIRST');
            if (\webtemplate\general\General::isError($result)) {
                $this->fail("Unable to create user Token");
            } else {
                $verifyResult = $this->object->verifyToken($result, 'UNITTEST', 1);
                $this->assertTrue($verifyResult);
                $this->assertEquals('FIRST', $this->object->getEventData());

                // Invalid Token
                $verifyResult = $this->object->verifyToken('hgytghf', 'UNITTEST', 1);
                $this->assertFalse($verifyResult);
                $this->assertEquals('', $this->object->getEventData());

                // Valid Token again
                $verifyResult = $this->object->verifyToken($result, 'UNITTEST', 1);
                $this->assertTrue($verifyResult);
                $this->assertEquals('FIRST', $this->object->getEventData());
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests delete Token  function
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testdeleteToken()
    {
        if ($this->databaseconnection == true) {
            $result = $this->object->createToken('1', 'UNITTEST', 1, 'FIRST');
            if (\webtemplate\general\General::isError($result)) {
                $this->fail("Unable to create user Token");
            } else {
                // Check a token exists
                $verifyResult = $this->object->verifyToken($result, 'UNITTEST', 1);
                $this->assertTrue($verifyResult);

                // Delete the token
                $verifyResult = $this->object->deleteToken($result);
                $this->assertTrue($verifyResult);

                // Check the Token no longer exists
                $verifyResult = $this->object->verifyToken($result, 'UNITTEST', 1);
                $this->assertFalse($verifyResult);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * This function tests if that the auto delete of tokens when new ones are
     * created can be disabled.  Userid, token type and data must match for auto
     * delete to work
     *
     * @group   unittest
     * @group   general
     * @depends testCreateToken
     * @depends testverifyTokenuser
     *
     * @return void
     */
    public function testDisableAutoDeleteToken()
    {
        if ($this->databaseconnection == true) {
            $token1 = $this->object->createToken('1', 'UNITTEST', 1, 'AutoDelete1');
            if (\webtemplate\general\General::isError($token1)) {
                $this->fail("Unable to create user Token");
            } else {
                // Check a token exists
                $verifyResult = $this->object->verifyToken($token1, 'UNITTEST', 1);
                $this->assertTrue($verifyResult);

                // Create a second token.  The only difference being the "data" field
                $token2 = $this->object->createToken(
                    '1',
                    'UNITTEST',
                    1,
                    'AutoDelete2'
                );

                // Check that both tokens exist in the database
                $verifyResult = $this->object->verifyToken($token1, 'UNITTEST', 1);
                $this->assertTrue($verifyResult);
                $verifyResult = $this->object->verifyToken($token2, 'UNITTEST', 1);
                $this->assertTrue($verifyResult);

                // Re run create token using the same data as the initial run.
                $token3 = $this->object->createToken(
                    '1',
                    'UNITTEST',
                    1,
                    'AutoDelete1'
                );

                // Confirm token1 is deleted tokens 2 and 3 exist
                $verifyResult = $this->object->verifyToken($token1, 'UNITTEST', 1);
                $this->assertFalse($verifyResult);
                $verifyResult = $this->object->verifyToken($token2, 'UNITTEST', 1);
                $this->assertTrue($verifyResult);
                $verifyResult = $this->object->verifyToken($token3, 'UNITTEST', 1);
                $this->assertTrue($verifyResult);

                // Delete the two remaining tokens
                $verifyResult = $this->object->deleteToken($token2);
                $this->assertTrue($verifyResult);
                $verifyResult = $this->object->deleteToken($token3);
                $this->assertTrue($verifyResult);

                // Check the Tokens no longer exists
                $verifyResult = $this->object->verifyToken($token2, 'UNITTEST', 1);
                $this->assertFalse($verifyResult);
                $verifyResult = $this->object->verifyToken($token3, 'UNITTEST', 1);
                $this->assertFalse($verifyResult);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
}
