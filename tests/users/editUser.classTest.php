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

// Include the Test Database Connection details
require_once dirname(__FILE__) .'/../_data/database.php';

// Include Parameters File
//require_once dirname(__FILE__) ."/../../configs/parameters.php";

/**
 * EditUser Class Unit Tests
 *
 * @category Webtemplate
 * @package  Tests
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class EditUserClassTest extends TestCase
{
    /**
     * Edit User Class
     *
     * @var Edituser Class
     */
    protected $object;

    /**
     * MDB2 Database Connection Object
     *
     * @var MDB2 Database Connection Object
     */
    protected $object2;

     /**
     * User Class Object for MOCK database Class
     *
     * @var UserClass
     */
    protected $object3;

    /**
     * User Class
     *
     * @var User_class
     */
    protected $userclass;

    /**
     * Database Connection
     *
     * @var Valid Database connection
     */
    protected $databaseconnection;

    /**
     * MOCK Database connection
     *
     * @var MOCK Database connection
     */
    protected $mockdatabaseconnection;

    /**
     * Flag to identify that a new user was created
     *
     * @var newuserflag
     */
    protected $newuserflag;

    /**
     * Configuration Class Object
     *
     * @var /webtemplate/config/Configuration
     */
    protected $config;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return null No return data
     */
    protected function setUp()
    {
        global $testdsn;
         // Check that we can connect to the database
        $this->object2 = \webtemplate\db\DB::load($testdsn);
        if (!\webtemplate\general\General::isError($this->object2)) {
            $this->databaseconnection = true;
        } else {
            $this->databaseconnection = false;
        }

        // Create a new User Object
        $this->object = new \webtemplate\users\EditUser($this->object2);

        // Set newuserflag
        $this->newuserflag = false;

        //User Class
        $this->user_class = new \webtemplate\users\User($this->object2);

        // Set up MOCK Connection
        $tempDBDriver = $testdsn['phptype'];
        $testdsn['phptype'] = 'mock';
        $this->mockdatabaseconnection = \webtemplate\db\DB::load($testdsn);
        $this->object3 = new \webtemplate\users\EditUser(
            $this->mockdatabaseconnection
        );
        $testdsn['phptype'] = $tempDBDriver;

        // Set up the configuration object
        $configDir = __DIR__ . "/../../configs";
        $this->config = new \webtemplate\config\Configure($configDir);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return null No return data
     */
    protected function tearDown()
    {
        global $testdsn, $options;

        if ($this->newuserflag == true) {
            $conStr = sprintf(
                "pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s",
                $testdsn["hostspec"],
                '5432',
                $testdsn["database"],
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
            $sql =  "delete from users where user_name = 'phpunit2'";

            $localconn->query($sql);
            $localconn = null;
        }

        if ($this->databaseconnection === true) {
            $this->object2->disconnect();
        }
    }

    /**
     * Test that database can be searched for users by username, real name and
     * email.
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testSearch()
    {
        if ($this->databaseconnection == true) {
            //Test Username Search
            $resultarray = $this->object->search('username', 'phpunit');
            if (\webtemplate\general\General::isError($resultarray)) {
                $this->fail(
                    "Edit User search username : " . $resultarray->getMessage()
                );
            } else {
                $this->assertEquals('phpunit', $resultarray[0]['username']);
            }

            //Test Realname Search
             $resultarray = $this->object->search('realname', 'Phpunit');
            if (\webtemplate\general\General::isError($resultarray)) {
                $this->fail(
                    "Edit User search realname : " . $resultarray->getMessage()
                );
            } else {
                $this->assertEquals('phpunit', $resultarray[0]['username']);
            }

            //Test email Search
            $resultarray = $this->object->search('email', 'example');
            if (\webtemplate\general\General::isError($resultarray)) {
                $this->fail(
                    "Edit User Search e-mail : " . $resultarray->getMessage()
                );
            } else {
                $this->assertEquals('phpunit', $resultarray[0]['username']);
            }

            // Test the Last Seen column
            $date = date("Y-m-d");
            $user = $this->user_class->register(
                'phpunit',
                $this->config->read('pref')
            );
            $resultarray = $this->object->search('username', 'phpunit');
            if (\webtemplate\general\General::isError($resultarray)) {
                $this->fail("Last Seen Date : " . $resultarray->getMessage());
            } else {
                $this->assertEquals($date, $resultarray[0]['lastseendate']);
            }

            //Test invalid search column - Default to username
            $resultarray = $this->object->search('nocolumn', 'phpunit');
            if (\webtemplate\general\General::isError($resultarray)) {
                $this->fail(
                    "Edit User search username : " . $resultarray->getMessage()
                );
            } else {
                $this->assertEquals('phpunit', $resultarray[0]['username']);
            }

            //Test Username Search - No user Found
            $resultarray = $this->object->search('username', 'nouser');
            if (\webtemplate\general\General::isError($resultarray)) {
                $this->assertEquals('Not Found', $resultarray->getMessage());
            } else {
                $this->fail("Edit User search username : No User Found test failed");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that a single users details can be read from the database
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testGetuser()
    {
        if ($this->databaseconnection == true) {
            //Test GET USER
            $resultarray = $this->object->search('username', 'phpunit');
            if (\webtemplate\general\General::isError($resultarray)) {
                $msg = "Get User - Search Failed to find phpunit user : ";
                $msg .= $resultarray->getMessage();
                $this->fail($msg);
            } else {
                // Set the lastseendate by registering the phpunit user
                $date = date("Y-m-d");
                $user = $this->user_class->register(
                    'phpunit',
                    $this->config->read('pref')
                );

                // Get the users details
                $user = $this->object->getuser($resultarray[0]['userid']);
                if (\webtemplate\general\General::isError($user)) {
                    $msg = "Get User - Search Failed to find phpunit user : ";
                    $msg .= $user->getMessage();
                    $this->fail($msg);
                } else {
                    // Test we got the right username
                    $this->assertEquals('phpunit', $user[0]['username']);

                    // Test the lastseendate is valid
                    $this->assertEquals($date, $resultarray[0]['lastseendate']);
                }
            }

            // Tst for invalid User ID
            $user = $this->object->getuser(900);
            if (\webtemplate\general\General::isError($user)) {
                $this->assertEquals('Not Found', $user->getMessage());
            } else {
                $this->fail("Get User : No User Found test failed");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that a users details can ve saved or updated in the database
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testSaveuser()
    {
        if ($this->databaseconnection == true) {
            $userId= 0;

            // Create User
            $usersaved = $this->object->saveuser(
                $userId,
                'phpunit2',
                'Phpunit User2',
                'phpunit2@example.com',
                'phpunit1',
                'N',
                'Y',
                'N'
            );
            if (\webtemplate\general\General::isERROR($usersaved)) {
                $this->fail("Save User : " . $usersaved->getMessage());
            } else {
                $this->assertTrue($usersaved);
                $this->assertNotEquals(0, $usersaved);
            }
            $this->newuserflag = true;
            if ($userId <> 0) {
                // Update User with new password
                $usersaved = $this->object->saveuser(
                    $userId,
                    'phpunit2',
                    'Phpunit User2',
                    'phpunit2@example2.com',
                    'phpunit1',
                    'N',
                    'Y',
                    'N'
                );
                if (\webtemplate\general\General::isERROR($usersaved)) {
                    $msg = "Update User (Password) : " . $usersaved->getMessage();
                    $this->fail($msg);
                } else {
                    $this->assertTrue($usersaved);
                }

                // Uspadate User no Password
                $usersaved = $this->object->saveuser(
                    $userId,
                    'phpunit2',
                    'Phpunit User2',
                    'phpunit2@example.com',
                    '',
                    'Y',
                    'Y',
                    'N'
                );
                if (\webtemplate\general\General::isERROR($usersaved)) {
                    $msg = "Update User (No Password) : " . $usersaved->getMessage();
                    $this->fail($msg);
                } else {
                    $this->assertTrue($usersaved);
                }


                //  Save user Password fail
                $GLOBALS['passwdfail'] = true;
                $usersaved = $this->object->saveuser(
                    $userId,
                    'phpunit2',
                    'Phpunit User2',
                    'phpunit2@example2.com',
                    'phpunit1',
                    'N',
                    'Y',
                    'N'
                );
                if (!\webtemplate\general\General::isERROR($usersaved)) {
                    $msg = "Update User (Password) : Saved testing password fail" ;
                    $this->fail($msg);
                } else {
                    $this->assertContains(
                        'Unable to create Encrypted Password',
                        $usersaved->getMessage()
                    );
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
    /**
     * Test that the save user can deal with errors using the mock database
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testMockSaveuser()
    {
        if ($this->databaseconnection == true) {
            $userId= 0;

            // Fail Saving User
            // Create User
            $usersaved = $this->object3->saveuser(
                $userId,
                'phpunit2',
                'Phpunit User2',
                'phpunit2@example.com',
                'phpunit1',
                'N',
                'Y',
                'Y'
            );
            if (\webtemplate\general\General::isERROR($usersaved)) {
                $this->assertEquals(
                    "Errors Saving user phpunit2",
                    $usersaved->getMessage()
                );
            } else {
                $this->fail("Saved User using MOCK Database");
            }

            // Fail getting USER ID
            $functions = array(
                'insertUser' => array(
                    "pass" => true,
                    "notfound" => false
                )
            );

            $passwd = '$2y$10$II8N.OUTfQygovSVvuH5t.P20IrBj7Upb50WBztwGIbIUAVZCYAsK';
            $testdata = array(
                'register' => array(
                    'user_id' => 100,
                    'user_name' => 'mock',
                    'user_passwd' => $passwd,
                    'user_realname' => 'Mock User',
                    'user_email' => 'mock@example.com',
                    'user_enabled' => 'Y',
                    'user_disable_mail' => 'N',
                    'last_seen_date' => '2015/08/10',
                    'passwd_changed' => '2015/08/10'
                )
            );

             // Set up Valid Data so the save passes but the getid fails
            $this->mockdatabaseconnection->control($functions, $testdata);

            $usersaved = $this->object3->saveuser(
                $userId,
                'phpunit2',
                'Phpunit User2',
                'phpunit2@example.com',
                'phpunit1',
                'N',
                'Y',
                'Y'
            );
            if (\webtemplate\general\General::isERROR($usersaved)) {
                $this->assertEquals(
                    "Error getting id for user phpunit2",
                    $usersaved->getMessage()
                );
            } else {
                $this->fail("Saved User using MOCK Database");
            }

            // Fail update test
            $functions['saveuser']['pass'] = false;
            $this->mockdatabaseconnection->control($functions, $testdata);
            $userId = 1;
            $usersaved = $this->object3->saveuser(
                $userId,
                'phpunit2',
                'Phpunit User2',
                'phpunit2@example.com',
                'phpunit1',
                'N',
                'Y',
                'Y'
            );
            if (\webtemplate\general\General::isERROR($usersaved)) {
                $this->assertEquals(
                    "Error updating user: phpunit2",
                    $usersaved->getMessage()
                );
            } else {
                $this->fail("Saved User using MOCK Database");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that changes to a users details can be recognised
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testDatachanged()
    {
        if ($this->databaseconnection == true) {
            //Test Datachanged
            $resultarray = $this->object->search('username', 'phpunit');
            if (\webtemplate\general\General::isError($resultarray)) {
                $msg = "Datachanged - Search Failed to find phpunit user : ";
                $msg .= $resultarray->getMessage();
                $this->fail($msg);
            } else {
                $userId = $resultarray[0]['userid'];

                // No change
                $datachanged = $this->object->datachanged(
                    $userId,
                    'phpunit',
                    'Phpunit User',
                    'phpunit@example.com',
                    '',
                    'N',
                    'Y'
                );
                $this->assertFalse($datachanged);


                //  User Name Changed
                // The user Name cannot be changed
                $datachanged = $this->object->datachanged(
                    $userId,
                    'phpunit2',
                    'Phpunit User',
                    'phpunit@example.com',
                    '',
                    'N',
                    'Y'
                );
                $this->assertFalse($datachanged);

                //  Real Name Changed
                $datachanged = $this->object->datachanged(
                    $userId,
                    'phpunit',
                    'Phpunit User2',
                    'phpunit@example.com',
                    '',
                    'N',
                    'Y'
                );
                $this->assertTrue($datachanged);

                //  Email Changed
                $datachanged = $this->object->datachanged(
                    $userId,
                    'phpunit',
                    'Phpunit User',
                    'phpunit2@example.com',
                    '',
                    'N',
                    'Y'
                );
                $this->assertTrue($datachanged);

                //  Password Changed
                $datachanged = $this->object->datachanged(
                    $userId,
                    'phpunit',
                    'Phpunit User',
                    'phpunit@example.com',
                    'Password',
                    'N',
                    'Y'
                );
                $this->assertTrue($datachanged);

                //  No Email
                $datachanged = $this->object->datachanged(
                    $userId,
                    'phpunit',
                    'Phpunit User',
                    'phpunit@example.com',
                    '',
                    'Y',
                    'Y'
                );
                $this->assertTrue($datachanged);

                //  Locktext Changed
                $datachanged = $this->object->datachanged(
                    $userId,
                    'phpunit',
                    'Phpunit User',
                    'phpunit@example.com',
                    '',
                    'N',
                    'N'
                );
                $this->assertTrue($datachanged);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that the Change String can be retrieved from the Class and it is empty
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testGetChangeString()
    {
        $changestring = $this->object->getChangeString();
        $this->assertEquals('', $changestring);
    }

    /**
     * Test that usesr who exist in the database can be identified
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testCheckUserExists()
    {
        if ($this->databaseconnection == true) {
              //Test user exists
              $userexists = $this->object->checkUserExists('phpunit');
            if (\webtemplate\general\General::isError($userexists)) {
                $this->fail("Check User Exists  : " . $userexists->getMessage());
            } else {
                $this->assertTrue($userexists);
            }

            //Test user does not exists
            $userexists = $this->object->checkUserExists('nouser');
            if (\webtemplate\general\General::isError($userexists)) {
                $this->fail("Check User Exists  : " . $userexists->getMessage());
            } else {
                $this->assertFalse($userexists);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that user exists using the MOCK DB Connection
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testMockCheckUserExists()
    {
        if ($this->databaseconnection == true) {
              //Test user exists
              $userexists = $this->object3->checkUserExists('phpunit');
            if (!\webtemplate\general\General::isError($userexists)) {
                $this->fail("Check User Exists using Mock database failed");
            } else {
                $this->assertEquals("SQL Query Error", $userexists->getMessage());
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that the function to check if a user is enabled works
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testCheckUserEnabled()
    {
        if ($this->databaseconnection == true) {
            //Test user enabled
            $userenabled = $this->object->checkUserEnabled('phpunit');
            if (\webtemplate\general\General::isError($userenabled)) {
                $this->fail("Check User Enabled  : " . $userenabled->getMessage());
            } else {
                $this->assertTrue($userenabled);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test that the function using the MOCK Database to see if it copes
     * with database errors
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testMockCheckUserEnabled()
    {
        if ($this->databaseconnection == true) {
            //Test user enabled
            $userenabled = $this->object3->checkUserEnabled('phpunit');
            if (!\webtemplate\general\General::isError($userenabled)) {
                 $this->fail("Check User enabled using Mock database failed");
            } else {
                $this->assertEquals("SQL Query Error", $userenabled->getMessage());
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that an email address whhich exists in the database can be identified
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testCheckemailExists()
    {
        if ($this->databaseconnection == true) {
            //Test user exists
            $emailexists = $this->object->checkEmailExists("phpunit@example.com");
            if (\webtemplate\general\General::isError($emailexists)) {
                $this->fail("Check Email Exists  : " . $emailexists->getMessage());
            } else {
                $this->assertTrue($emailexists);
            }

            //Test user does not exists
            $emailexists = $this->object->checkEmailExists('nouser@example.com');
            if (\webtemplate\general\General::isError($emailexists)) {
                $this->fail("Check Email Exists  : " . $emailexists->getMessage());
            } else {
                $this->assertFalse($emailexists);
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that the function using the MOCK Database to see if it copes
     * with database errors
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testMockCheckEmailExists()
    {
        if ($this->databaseconnection == true) {
            //Test user enabled
            $emailexists = $this->object3->checkEmailExists('phpunit@example.com');
            if (!\webtemplate\general\General::isError($emailexists)) {
                 $this->fail("Check email exists using Mock database failed");
            } else {
                $this->assertEquals("SQL Query Error", $emailexists->getMessage());
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }

    /**
     * Test that the user can update certain elements of their record in the
     * database
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testUpdateUserDetails()
    {
        if ($this->databaseconnection == true) {
            //Test Update USER no Password
            $resultarray = $this->object->search('username', 'phpunit');
            if (\webtemplate\general\General::isError($resultarray)) {
                $msg = "Update User Details - Search Failed to find phpunit user : ";
                $msg .= $resultarray->getMessage();
                $this->fail($msg);
            } else {
                $updateuser = $this->object->updateUserDetails(
                    'Phpunit User2',
                    '',
                    'phpunit@example.com',
                    $resultarray[0]['userid']
                );
                if (\webtemplate\general\General::isError($updateuser)) {
                    $msg = "Update User - Failed to update phpunit user : ";
                    $msg .= $updateuser->getMessage();
                    $this->fail($msg);
                } else {
                    $this->assertTrue($updateuser);
                }
            }

            //Test Update USER with Password
            $resultarray = $this->object->search('username', 'phpunit');
            if (\webtemplate\general\General::isError($resultarray)) {
                $msg = "Update User Details - Search Failed to find phpunit user : ";
                $msg .= $resultarray->getMessage();
                $this->fail($msg);
            } else {
                $updateuser = $this->object->updateUserDetails(
                    'Phpunit User2',
                    'phpunit2',
                    'phpunit@example.com',
                    $resultarray[0]['userid']
                );
                if (\webtemplate\general\General::isError($updateuser)) {
                    $msg = "Update User - Failed to update phpunit user : ";
                    $msg .= $updateuser->getMessage();
                    $this->fail($msg);
                } else {
                    $this->assertTrue($updateuser);
                }
            }

            //Test Update USER no Password
            $resultarray = $this->object->search('username', 'phpunit');
            if (\webtemplate\general\General::isError($resultarray)) {
                $msg = "Update User Details - Search Failed to find phpunit user : ";
                $msg .= $resultarray->getMessage();
                $this->fail($msg);
            } else {
                $updateuser = $this->object->updateUserDetails(
                    'Phpunit User',
                    'phpunit1',
                    'phpunit@example.com',
                    $resultarray[0]['userid']
                );
                if (\webtemplate\general\General::isError($updateuser)) {
                    $msg = "Update User - Failed to update phpunit user : ";
                    $msg .= $updateuser->getMessage();
                    $this->fail($msg);
                } else {
                    $this->assertTrue($updateuser);
                }
            }

            //Update an invalid Userid
            $updateuser = $this->object->updateUserDetails(
                'Phpunit User',
                '',
                'phpunit@example.com',
                1000
            );
            if (\webtemplate\general\General::isError($updateuser)) {
                $this->assertEquals("Record not found", $updateuser->getMessage());
            } else {
                $this->fail("Updated userdetails on invalid user. ");
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }


    /**
     * Test to see that the correct error is received if a new password cannot
     * be created when updating user's detail
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testUpdateUsersDetailsPassedFail()
    {

        if ($this->databaseconnection == true) {
            // Test that the password creation fails
            $GLOBALS['passwdfail'] = true;
            $resultarray = $this->object->search('username', 'phpunit');
            if (\webtemplate\general\General::isError($resultarray)) {
                $msg = "Update User Details - Search Failed to find phpunit user : ";
                $msg .= $resultarray->getMessage();
                $this->fail($msg);
            } else {
                $updateuser = $this->object->updateUserDetails(
                    'Phpunit User2',
                    'phpunit2',
                    'phpunit@example.com',
                    $resultarray[0]['userid']
                );
                if (!\webtemplate\general\General::isError($updateuser)) {
                    $msg = "Update User: Saved testing Password fail";
                    $this->fail($msg);
                } else {
                    $this->assertContains(
                        'Unable to create Encrypted Password',
                        $updateuser->getMessage()
                    );
                }
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
    /**
     * Test that the users password can be updated
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testUpdatePasswd()
    {
        if ($this->databaseconnection == true) {
            //Update Password
            $passwordupdated = $this->object->updatePasswd('phpunit', 'phpUnit2');
            if (\webtemplate\general\General::isError($passwordupdated)) {
                $this->fail("Update Password : " . $passwordupdated->getMessage());
            } else {
                $this->assertTrue($passwordupdated);
            }
            $passwordupdated = $this->object->updatePasswd('phpunit', 'phpUnit1');
            if (\webtemplate\general\General::isError($passwordupdated)) {
                $this->fail("Update Password : " . $passwordupdated->getMessage());
            } else {
                $this->assertTrue($passwordupdated);
            }

            //Update Password
            $passwordupdated = $this->object->updatePasswd(
                'phpunituser',
                'phpUnit1'
            );
            if (\webtemplate\general\General::isError($passwordupdated)) {
                $this->assertEquals(
                    "Record not found",
                    $passwordupdated->getMessage()
                );
            } else {
                $this->fail("Updated Password on invalid user");
            }

            //  Fail to create a new password
            $GLOBALS['passwdfail'] = true;
            $passwordupdated = $this->object->updatePasswd('phpunit', 'phpUnit2');
            if (!\webtemplate\general\General::isError($passwordupdated)) {
                $this->fail("Update Password :  Saved testing password fail");
            } else {
                $this->assertContains(
                    'Unable to create Encrypted Password',
                    $passwordupdated->getMessage()
                );
            }
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }



    /**
     * Test that the data being input is valid
     *
     * @group unittest
     * @group users
     *
     * @return null
     */
    public function testValidateUserData()
    {
        if ($this->databaseconnection == true) {
            //Test Validate User Database

            // Set the Parameter Data
            $parameters['users']['newaccount'] = false;
            $parameters['users']['newpassword'] = true;
            $parameters['users']['regexp'] = '/^[a-zA-Z0-9]{5,12}$/';
            $regExStr = 'Must between 5 and 12 characters long and contain upper ';
            $regExStr .= 'and lower case letters and numbers.';
            $parameters['users']['regexpdesc'] = $regExStr;
            $parameters['users']['passwdstrength'] = '4';


            //All Data Ok
            $inputArray = array(
                "userid" =>'1',
                "username" =>'phpuser',
                "realname" =>'PHP Unit User',
                "useremail" =>'phpunit@example.com',
                "userenabled" =>'Y',
                "userdisablemail" =>'N',
                "passwd" => 'Password123',
                "passwdchange" => 'Y'
            );
            $resultArray = $this->object->validateUserData(
                $inputArray,
                $parameters['users']
            );
            $this->assertEquals("", $resultArray[0]['msg']);
            $this->assertContains("Y", $resultArray[0]['userdisablemail']);

            //Invalid User ID
            $inputArray = array(
                "userid" =>'1111111111111',
                "username" =>'phpuser',
                "realname" =>'PHP Unit User',
                "useremail" =>'phpunit@example.com',
                "enabled" =>'N',
                "userdisablemail" =>'N',
                "passwd" => 'Password123'
            );
            $resultArray = $this->object->validateUserData(
                $inputArray,
                $parameters['users']
            );
            $this->assertContains("Invalid User ID", $resultArray[0]['msg']);

            //Invalid User name
            $inputArray = array(
                "userid" =>'11',
                "username" =>'php user',
                "realname" =>'PHP Unit User',
                "useremail" =>'phpunit@example.com',
                "enabled" =>'N',
                "userdisablemail" =>'N',
                "passwd" => 'Password123'
            );
            $resultArray = $this->object->validateUserData(
                $inputArray,
                $parameters['users']
            );
            $this->assertContains("Invalid User Name", $resultArray[0]['msg']);

            //Invalid User real name
            $inputArray = array(
                "userid" =>'11',
                "username" =>'phpuser',
                "realname" =>'PHP Unit User1',
                "useremail" =>'phpunit@example.com',
                "enabled" =>'N',
                "userdisablemail" =>'N',
                "passwd" => 'Password123'
            );
            $resultArray = $this->object->validateUserData(
                $inputArray,
                $parameters['users']
            );
            $this->assertContains("Invalid Real Name", $resultArray[0]['msg']);

            //Invalid email address
            $inputArray = array(
                "userid" =>'11',
                "username" =>'phpuser',
                "realname" =>'PHP Unit User',
                "useremail" =>'phpunitexample.com',
                "enabled" =>'N',
                "userdisablemail" =>'N',
                "passwd" => 'Password123'
            );
            $resultArray = $this->object->validateUserData(
                $inputArray,
                $parameters['users']
            );
            $this->assertContains("Invalid Email Address", $resultArray[0]['msg']);

            //Invalid Password
            $inputArray = array(
                "userid" =>'11',
                "username" =>'phpuser',
                "realname" =>'PHP Unit User',
                "useremail" =>'phpunit@example.com',
                "enabled" =>'N',
                "userdisablemail" =>'N',
                "passwd" => 'Password'
            );
            $resultArray = $this->object->validateUserData(
                $inputArray,
                $parameters['users']
            );
            $this->assertContains("Invalid Password", $resultArray[0]['msg']);

            // Check User Diable e-mail not set
            $inputArray = array(
                "userid" =>'11',
                "username" =>'phpuser',
                "realname" =>'PHP Unit User',
                "useremail" =>'phpunit@example.com',
                "enabled" =>'N',
                "passwd" => 'Password123'
            );
            $resultArray = $this->object->validateUserData(
                $inputArray,
                $parameters['users']
            );
            $this->assertContains("N", $resultArray[0]['userdisablemail']);

            // Check empty array
            $inputArray = array("userid2" =>'11',
                                );
            $resultArray = $this->object->validateUserData(
                $inputArray,
                $parameters['users']
            );
            $this->assertContains("Invalid", $resultArray[0]['msg']);

            //Check for 2 equal passwords
            $inputArray = array(
                "realname" =>'PHP Unit User',
                "useremail" =>'phpunit@example.com',
                "passwd" => 'Password123',
                "passwd2" => 'Password123'
            );
            $resultArray = $this->object->validateUserData(
                $inputArray,
                $parameters['users'],
                true
            );
            $this->assertEquals("", $resultArray[0]['msg']);

            //Check for 2 unequal passwords
            $inputArray = array(
                "useremail" =>'phpunit@example.com',
                "realname" =>'PHP Unit User',
                "passwd" => 'Password123',
                "passwd2" => 'Password12'
            );
            $resultArray = $this->object->validateUserData(
                $inputArray,
                $parameters['users'],
                true
            );
            $this->assertContains(
                "Passwords are not the same",
                $resultArray[0]['msg']
            );

            //Password2 Fails
            $inputArray = array(
                "useremail" =>'phpunit@example.com',
                "realname" =>'PHP Unit User',
                "passwd" => 'Password123',
                "passwd2" => 'Password'
            );
            $resultArray = $this->object->validateUserData(
                $inputArray,
                $parameters['users'],
                true
            );
            $this->assertContains(
                "Invaild Confirmation Password",
                $resultArray[0]['msg']
            );
        } else {
            $this->markTestSkipped('No Database Connection Available');
        }
    }
}
