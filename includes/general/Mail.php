<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage General
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\general;

/**
 * Webtemplate Application Mail Class
 **/
class Mail
{

    /**
     * Log Directory
     *
     * @var    string
     * @access protected
     */

    protected $logDir = '';

    /**
     * Smtpdeliverymethod
     *
     * @var    string
     * @access protected
     */

    protected $smtpdeliverymethod = '';

    /**
     * Emailaddress
     *
     * @var    string
     * @access protected
     */

    protected $emailaddress = '';

    /**
     * Smtpserver
     *
     * @var    string
     * @access protected
     */

    protected $smtpserver = '';

    /**
     * Smtpusername
     *
     * @var    string
     * @access protected
     */

    protected $smtpusername = '';

    /**
     * Smtppassword
     *
     * @var    string
     * @access protected
     */

    protected $smtppassword = '';

    /**
     * Smtpdebug
     *
     * @var    boolean
     * @access protected
     */

    protected $smtpdebug = false;


    /**
     * Mailsystem activated
     *
     * @var    boolean
     * @access protected
     */

    protected $mailActive = false;


    /**
     * ErrorMsg
     *
     * @var    string
     * @access protected
     */

    protected $errorMsg = '';

     /**
     * Receiver(s) of the mail. Comma separated
      *
     * @var    string
     * @access protected
     */

    protected $toAddr = '';

    /**
     * Copy addressee(s) of the mail. Comma separated
     *
     * @var    string
     * @access protected
     */

    protected $ccAddr = '';


     /**
     * Blind Copy addressee(s) of the mail. Comma separated
      *
     * @var    string
     * @access protected
     */

    protected $bccAddr = '';


    /**
     * Subject of the email to be sent.
     *
     * @var    string
     * @access protected
     */

    protected $subject = '';


    /**
     * Message to be sent.
     *
     * @var    string
     * @access protected
     */

    protected $message = '';


    /**
     * Constructor
     *
     * @param array $mailParam Mail Section of the applications Parameters.
     *
     * @access public
     */
    public function __construct(array $mailParam)
    {
        $this->smtpdeliverymethod = $mailParam['smtpdeliverymethod'];
        $this->emailaddress       = $mailParam['emailaddress'];
        $this->smtpserver         = $mailParam['smtpserver'];
        $this->smtpusername       = $mailParam['smtpusername'];
        $this->smtppassword       = $mailParam['smtppassword'];
        $this->smtpdebug          = $mailParam['smtpdebug'];

        if ($this->smtpdeliverymethod == 'none') {
            $this->mailActive = false;
        } else {
            $this->mailActive = true;
        }

        $this->logDir = dirname(dirname(dirname(__FILE__))) . "/logs/";
    }


    /**
     * This function return the current status of the mail system
     *
     * @return boolean True if e-mails can be sent false otherwise.
     *
     * @access public
     */
    final public function status()
    {
        return $this->mailActive;
    }


    /**
     * This function return the current mail system error message
     *
     * @return string Empty if there is no message.
     *
     * @access public
     */
    final public function errorMsg()
    {
        return $this->errorMsg;
    }


    /**
     * This is the function used to send the email.  Depending on the
     * delivery method chosen in the application configuraion it will
     * choose one of its helper functions.
     *
     * @param string $toAddr  Receiver(s) of the mail. Comma separated.
     * @param string $ccAddr  Copy addressee(s) of the mail. Comma separated.
     * @param string $bccAddr Blind copy addressee(s) of the mail. Comma separated.
     * @param string $subject Subject of the email to be sent.
     * @param string $message Message to be sent. Each line should be separated
     *                        with a LF (\n). Lines should not be larger than
     *                        70 characters.
     *
     * @return boolean True if e-mail sent, false otherwise
     *
     * @access public
     */
    final public function sendEmail(
        string $toAddr,
        string $ccAddr,
        string $bccAddr,
        string $subject,
        string $message
    ) {
        // Set up local flags.
        $mailsent      = false;

        // Transfer mail information to Clas Variables.
        $this->toAddr      = $toAddr;
        $this->ccAddr      = $ccAddr;
        $this->bccAddr     = $bccAddr;
        $this->subject = $subject;
        $this->message = wordwrap($message, 70);  // Restrict each line to 70 chars

        switch ($this->smtpdeliverymethod) {
            case "none":
                $mailsent = $this->sendNone();
                break;
            case "test":
                $mailsent = $this->sendTest();
                break;
            case "smtp":
                $mailsent = $this->sendSMTP();
                break;
            case "sendmail":
                $mailsent = $this->sendSendMail();
                break;
            default:
                $this->errorMsg = gettext("Mail Error: Unknown delivery method: ");
                $this->errorMsg .= $this->smtpdeliverymethod;
                $mailsent = false;
        }

        return $mailsent;
    }


    /**
     * This is the function used if the mail delivery method is set to none
     *
     * @return boolean True
     *
     * @access protected
     */
    final protected function sendNone()
    {
        $mailsent = true;
        $this->errorMsg = gettext("Mail Subsystem Deactivated");
        return $mailsent;
    }

    /**
     * This is the function used if the mail delivery method is set to Test.
     * The message is to a test file located in the log directory
     *
     * @return boolean True if file created, false otherwise
     *
     * @access protected
     */
    final protected function sendTest()
    {
        $mailsent = true;
        $filename = $this->logDir . "mailer.testfile";

        if ($handle = fopen($filename, "w")) {
            // Print out to: addresses
            $toAddr_addresses = explode(',', $this->toAddr);
            foreach ($toAddr_addresses as $toAddr) {
                fwrite($handle, "To: " . $toAddr . "\n");
            }
            fwrite($handle, "\n");

            // Print out cc addresses
            if ($this->ccAddr != '') {
                $ccAddr_addresses = explode(',', $this->ccAddr);
                foreach ($ccAddr_addresses as $ccAddr) {
                    fwrite($handle, "Cc: " . $ccAddr . "\n");
                }
                fwrite($handle, "\n");
            }


            // Print out bcc addresses
            if ($this->bccAddr != '') {
                $bccAddr_addresses = explode(',', $this->bccAddr);
                foreach ($bccAddr_addresses as $bccAddr) {
                    fwrite($handle, "Bcc: " . $bccAddr . "\n");
                }
                fwrite($handle, "\n");
            }


            fwrite($handle, $this->subject . "\n\n");
            fwrite($handle, $this->message . "\n");
            fclose($handle);
        } else {
            $mailsent = false;
            $this->errorMsg(gettext("Mailer: Error creating mailer.testfile"));
        }
        return $mailsent;
    }

    /**
     * This is the function used if the mail delivery method is set to SMTP.
     *
     * @return boolean True
     *
     * @access protected
     */
    final protected function sendSMTP()
    {
        if (array_key_exists('unittest', $GLOBALS)) {
            $this->errorMsg = 'Unable to test PHPMailer';
            return false;
        }

        $mail = new \PHPmailer();
        $mail->isSMTP();
        $mail->isHTML(false);

        if ($this->smtpdebug) {
            $mail->debug = 0;
        } else {
            $mail->debug = 3;
        }

        $mail->host = $this->smtpserver;

        if ($this->smtpusername != '') {
            $mail->SMTPauth = true;
            $mail->Username = $this->smtpusername;
            $mail->Password = $this->smtppassword;
        }

        $mail->setFrom($this->emailaddress);
        $mail->addReplyTo($this->emailaddress);

        $toAddr_addresses = explode(',', $this->toAddr);
        foreach ($toAddr_addresses as $toAddr) {
            $mail->addAddress($toAddr);
        }

        if ($this->ccAddr != '') {
            $ccAddr_addresses = explode(',', $this->ccAddr);
            foreach ($ccAddr_addresses as $ccAddr) {
                $mail->addCC($ccAddr);
            }
        }

        if ($this->bccAddr != '') {
            $bccAddr_addresses = explode(',', $this->bccAddr);
            foreach ($bccAddr_addresses as $bccAddr) {
                $mail->addBCC($bccAddr);
            }
        }

        $mail->Subject = $this->subject;
        $mail->Body = $this->message;

        if ($mail->send()) {
            $mailsent = true;
        } else {
            $mailsent = false;
            $this->errorMsg = $mail->ErrorInfo;
        }
        return $mailsent;
    }

    /**
     * This is the function used if the mail delivery method is set to Sendmail.
     * The sendmail option is currently disabled in the template sdmin/config.tpl
     *
     * @return boolean True
     *
     * @acess protected
     */
    final protected function sendSendMail()
    {
        $mailsent = true;
        return $mailsent;
    }
}
