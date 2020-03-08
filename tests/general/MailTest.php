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
 * MailClass Unit Tests
 *
 **/
class MailTest extends TestCase
{
    /**
     * Mail Class Object
     *
     * @var \webtemplate\general\Mail
     */
    protected $object;

    /**
     * Mail Class Object
     *
     * @var \webtemplate\general\Mail
     */
    protected $object2;

     /**
     * Mail Class Object
     *
     * @var \webtemplate\general\Mail
     */
    protected $object3;

     /**
     * Mail Class Object
     *
     * @var \webtemplate\general\Mail
     */
    protected $object4;

     /**
     * Mail Class Object
     *
     * @var \webtemplate\general\Mail
     */
    protected $object5;

    /**
     * This function is called prior to any tests being run.
     * Its purpose is to set up any variables that are needed to tun the tests.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $mailParam['smtpdeliverymethod'] = 'none';
        $mailParam['emailaddress'] = "test@example.com";
        $mailParam['smtpserver']  =  "smtp.example.com";
        $mailParam['smtpusername'] = "smtpuser";
        $mailParam['smtppassword'] = "smtppasswd";
        $mailParam['smtpdebug'] = false;

        $this->object = new \webtemplate\general\Mail($mailParam);

        // Set up a second instance to check the test procedure
        $mailParam['smtpdeliverymethod'] = 'test';
        $this->object2 = new \webtemplate\general\Mail($mailParam);

        // Set up an unkno Delivery Method
        $mailParam['smtpdeliverymethod'] = 'down';
        $this->object3 = new \webtemplate\general\Mail($mailParam);

        // Set up an SMTP Delivery Method
        $mailParam['smtpdeliverymethod'] = 'smtp';
        $this->object4 = new \webtemplate\general\Mail($mailParam);

        // Set up an sendmail Delivery Method
        $mailParam['smtpdeliverymethod'] = 'sendmail';
        $this->object5 = new \webtemplate\general\Mail($mailParam);
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
     * Test if the Mail Substesm is Active or not
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testStatus()
    {
        $this->assertFalse($this->object->status());
    }

    /**
     * Test if an email can be sent
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testsendEmail()
    {
        // Send Message with mail system off
        $result = $this->object->sendEmail(
            'test@example.com',
            '',
            '',
            'Subject',
            "Message Body\nLine 2\nLine 3\n"
        );
        $this->assertTrue($result);

        // Send Message with mail system at test
        $result = $this->object->sendEmail(
            'test@example.com',
            '',
            '',
            'Subject',
            "Message Body\nLine 2\nLine 3\n"
        );
        $this->assertTrue($result);

        $testDir = dirname(__FILE__) . "/../_data";
        $filename = dirname(dirname(dirname(__FILE__))) . '/logs/mailer.testfile';
        $expectedfilename = $testDir . '/email_test_file.txt';

        $result = $this->object2->sendEmail(
            'test@example.com',
            'cc1@example.com,cc2@example.com',
            'bcc1@example.com',
            'Subject',
            "Message Body\nLine 2\nLine 3\n"
        );
        $this->assertTrue($result);


        $this->assertFileExists($filename);
        $this->assertFileEquals($expectedfilename, $filename);
    }


    /**
     * Test if an email can be sent via smtp
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testsendSMTP()
    {
        if (array_key_exists('unittest', $GLOBALS)) {
            $result = $this->object4->sendEmail(
                'test@example.com',
                'test2@example.com',
                'test3@example.com',
                'Subject',
                "Message Body\nLine 2\nLine 3\n"
            );
            $this->assertFalse($result);
            $msg = $this->object4->errorMsg();
            $this->assertEquals("Unable to test PHPMailer", $msg);
        } else {
            $this->markTestSkipped('Mail System Live');
        }
    }

    /**
     * Test if an email can be sent via smtp
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testsendsendmail()
    {
        if (array_key_exists('unittest', $GLOBALS)) {
            $result = $this->object5->sendEmail(
                'test@example.com',
                'test2@example.com',
                'test3@example.com',
                'Subject',
                "Message Body\nLine 2\nLine 3\n"
            );
            $this->assertTrue($result);
        } else {
            $this->markTestSkipped('Mail System Live');
        }
    }


    /**
     * Test that Error Message are returned if anything goes wrong
     *
     * @group unittest
     * @group general
     *
     * @return void
     */
    public function testErrorMsg()
    {
        // Blank Error Message
        $msg = $this->object->errorMsg();
        $this->assertEquals("", $msg);

        // Send Message with mail system off
        $result = $this->object->sendEmail(
            'test@example.com',
            '',
            '',
            'Subject',
            "Message Body\nLine 2\nLine 3\n"
        );
        $this->assertTrue($result);
        $msg = $this->object->errorMsg();
        $this->assertEquals("Mail Subsystem Deactivated", $msg);

        // Blank Error Message
        $msg = $this->object3->errorMsg();
        $this->assertEquals("", $msg);

        // Send Message with unknown Mail System,
        $result = $this->object3->sendEmail(
            'test@example.com',
            '',
            '',
            'Subject',
            "Message Body\nLine 2\nLine 3\n"
        );
        $this->assertFalse($result);
        $msg = $this->object3->errorMsg();
        $this->assertEquals("Mail Error: Unknown delivery method: down", $msg);
    }
}
