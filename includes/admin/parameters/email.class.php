<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\admin\parameters;

/**
 * Parameters Interface Class
 *
 * @category Webtemplate
 * @package  Admin
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class Email extends ParametersAbstract
{

    /**
     * Mail Delivery Method
     *
     * @var    string
     * @access protected
     */
    protected $mailDeliveryMethod = '';

    /**
     * SMTP Server
     *
     * @var    string
     * @access protected
     */
    protected $smtpServer = '';

    /**
     * SMTP User Name
     *
     * @var    string
     * @access protected
     */
    protected $smtpUserName = '';

    /**
     * SMTP Password
     *
     * @var    string
     * @access protected
     */
    protected $smtpPassword = '';

    /**
     * SMTP Debug
     *
     * @var    boolean
     * @access protected
     */
    protected $smtpDebug = false;

    /**
     * Constructor
     *
     * @param \webtemplate\config\Configure $config Configuration class
     *
     * @access public
     */
    public function __construct($config)
    {

        parent::__construct($config);

        // Preload the local variables with the current parameters
        // Email Section
        $this->mailDeliveryMethod = $this->config->read(
            'param.email.smtpdeliverymethod'
        );
        $this->fromaddress = $this->config->read('param.email.emailaddress');
        $this->smtpServer = $this->config->read('param.email.smtpserver');
        $this->smtpUserName = $this->config->read('param.email.smtpusername');
        $this->smtpPassword = $this->config->read('param.email.smtppassword');
        $this->smtpDebug = $this->config->read('param.email.smtpdebug');
    } // end constructor


    /**
     * Validate the E-Mail set of parameters input by the user.  Last Msg will
     * contain a list of any parameters which failed validation.
     *
     * @param array $inputData Pointer to an array of User Input Data
     *
     * @return boolean true if data Validated
     *
     * @access public
     */
    final public function validateParameters(&$inputData)
    {
        // Set up the validation variables.
        $dataok = true;

        // Set up Error Message string
        $this->lastMsg = '';

        // VALIDATE AND SET PARAMETERS WITH DEFAULT VALUES

        // Validate and Set the mail delivary method
        $this->validateMailDeliveryMethod($inputData);

        // Validate and Set the SMTP Debug flag
        $this->validateSMTPDebug($inputData);


        // Validate the email parameters.
        // if a test fails set the flag it and the data to false

        // Validate the database's e-mail address
        if (!$this->validateFromAddress($inputData)) {
            $dataok = false;
        }

        // Validate the SMTP Server address
        if (!$this->validateSMTPServer($inputData)) {
            $dataok = false;
        }

        // Validate the SMTP username
        if (!$this->validateSMTPUserName($inputData)) {
            $dataok = false;
        }

        // Validate the SMTP password
        if (!$this->validateSMTPPasswd($inputData)) {
            $dataok = false;
        }

        // SMTP Server give if Delivery method is smtp
        if (($this->mailDeliveryMethod == 'smtp') and ($this->smtpServer == '')) {
            $this->lastMsg .= gettext("You must enter the name of your SMTP server");
            $this->mailDeliveryMethod = "None";
            $dataok = false;
        }

        // Ensure that both a SMTP username and Password are entered
        if (($this->smtpUserName != '') and $this->smtpPassword == '') {
            $this->lastMsg .= gettext(
                "You must enter both the SMTP Username and Password"
            );
            $dataok = false;
        }

        return $dataok;
    }

    /**
     * Check if any of the e-mail set of Parameters have changed.  The
     * localy stored parameters created as part of the validation process
     * are compared to the ones in the $parameters variable.  Last Msg will
     * contain a list of parameters whioch have changed.
     *
     * @param array $parameters Array of application Parameters
     *
     * @return boolean true if data changed
     *
     * @access public
     */
    final public function checkParametersChanged($parameters)
    {

        // Set the data changed flags to false
        // These flags will be set true if their associated parameter
        // has changed
        $dataChanged = false;
        $msg = "";

        // Check if the mail deliver method has changed
        $testStr = $parameters['email']['smtpdeliverymethod'];
        if ($this->mailDeliveryMethod != $testStr) {
            $dataChanged = true;
            $msg .= gettext("Mail Delivery Method Changed") . "\n";
        }

        // Check if the applications e-mail address has changed
        if ($this->fromaddress != $parameters['email']['emailaddress']) {
            $dataChanged = true;
            $msg .= gettext("E-Mail Address Changed") . "\n";
        }

        // Check if the SMTP Server address has changed
        if ($this->smtpServer != $parameters['email']['smtpserver']) {
            $dataChanged = true;
            $msg .= gettext("SMTP Server Changed") . "\n";
            $smtpServerChanged = true;
        }

        //check if the SMTP User name has changed
        if ($this->smtpUserName != $parameters['email']['smtpusername']) {
            $dataChanged = true;
            $msg .= gettext("SMTP User Name Changed") . "\n";
        }

        //Check if the SMTP Password hase changed
        if ($this->smtpPassword != $parameters['email']['smtppassword']) {
            $dataChanged = true;
            $msg .= gettext("SMTP Password Changed") . "\n";
        }

        //Check if the SMTP Debug status has changed
        if ($this->smtpDebug != $parameters['email']['smtpdebug']) {
            $dataChanged = true;
            $msg .= gettext("SMTP Debug Changed") . "\n";
        }

        $this->lastMsg = $msg;
        return $dataChanged;
    }


    /**
     * Validate the mail delivery method
     *
     * @param array $inputData Pointer to an array of User Input Data
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateMailDeliveryMethod(&$inputData)
    {
        $dataok = true;

        // Get the  mail deliver method from the webserver
        // default to none
        $this->mailDeliveryMethod = 'none';
        if (isset($inputData['mail_delivery_method'])) {
            $testStr = "/(none)|(smtp)|(sendmail)|(test)/";
            if (preg_match($testStr, $inputData['mail_delivery_method'], $regs)) {
                $this->mailDeliveryMethod = $inputData['mail_delivery_method'];
            }
        }

        return $dataok;
    }

    /**
     * Validate the Fromm address for sent e-mails
     *
     * @param array $inputData Pointer to an array of User Input Data
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateFromAddress(&$inputData)
    {
        $dataok = true;

        // Get the applications e-mail address
        $this->fromaddress = '';
        if (isset($inputData['email_address_id'])) {
            $this->fromaddress = substr($inputData['email_address_id'], 0, 60);
        }

        if (!\webtemplate\general\LocalValidate::email($this->fromaddress)) {
            $this->lastMsg .= gettext("Invalid E-mail Address") ."\n";
            $dataok = false;
        }

        return $dataok;
    }

    /**
     * Validate the SMTP address
     *
     * @param array $inputData Pointer to an array of User Input Data
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateSMTPServer(&$inputData)
    {
        $dataok = true;

        // Get the SMTP Server address
        $this->smtpServer = '';
        if (isset($inputData['smtp_server_id'])) {
            $this->smtpServer = substr($inputData['smtp_server_id'], 0, 60);
        }

        if ($this->smtpServer != '') {
            if (!\webtemplate\general\LocalValidate::domain($this->smtpServer)) {
                $this->lastMsg .= gettext("Invalid SMTP Server Name") . "\n";
                $dataok = false;
            }
        }

        return $dataok;
    }

    /**
     * Validate the SMTP Server User name
     *
     * @param array $inputData Pointer to an array of User Input Data
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateSMTPUserName(&$inputData)
    {
        $dataok = true;

        // Get the SMTP User Name
        $this->smtpUserName  = '';
        if (isset($inputData['smtp_user_name_id'])) {
            $this->smtpUserName = substr($inputData['smtp_user_name_id'], 0, 60);
        }

        if (!\webtemplate\general\LocalValidate::generaltext($this->smtpUserName)) {
            $this->lastMsg .= gettext("Invalid Username") . "\n";
            $dataok = false;
        }

        return $dataok;
    }

    /**
     * Validate the SMTP Server Password
     *
     * @param array $inputData Pointer to an array of User Input Data
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateSMTPPasswd(&$inputData)
    {
        $dataok = true;

        // Get the SMTP Password
        $this->smtpPassword = '';
        if (isset($inputData['smtp_passwd_id'])) {
            $this->smtpPassword = substr($inputData['smtp_passwd_id'], 0, 60);
        }

        if (!\webtemplate\general\LocalValidate::generaltext($this->smtpPassword)) {
            $this->lastMsg .= gettext("Invalid Password") . "\n";
            $dataok = false;
        }

        return $dataok;
    }

    /**
     * Validate the SMTP Debug status
     *
     * @param array $inputData Pointer to an array of User Input Data
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateSMTPDebug(&$inputData)
    {
        $dataok = true;

        // Get the smtp debug status from the webserver
        // Defaults to false
        $this->smtpDebug = false;
        if (isset($inputData['smtp_debug'])) {
            if (preg_match("/(yes)|(no)/", $inputData['smtp_debug'], $regs)) {
                if ($inputData['smtp_debug'] == 'yes') {
                    $this->smtpDebug = true;
                }
            }
        }

        return $dataok;
    }


    /**
     * This function transfers the paramaters stored in this class to the
     * Configuration Class.
     *
     * @return boolean True if write is successful
     *
     * @access private
     */
    protected function savetoConfigurationClass()
    {
        $this->config->write(
            'param.email.smtpdeliverymethod',
            $this->mailDeliveryMethod
        );
        $this->config->write('param.email.emailaddress', $this->fromaddress);
        $this->config->write('param.email.smtpserver', $this->smtpServer);
        $this->config->write('param.email.smtpusername', $this->smtpUserName);
        $this->config->write('param.email.smtppassword', $this->smtpPassword);
        $this->config->write('param.email.smtpdebug', $this->smtpDebug);
        return true;
    }
}
