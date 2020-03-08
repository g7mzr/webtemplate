<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Admin Parameters
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace webtemplate\admin\parameters;

/**
 * Parameters Interface Class
 *
 **/
class Auth extends ParametersAbstract
{

    /**
     * Users can create new accounts
     *
     * @var    boolean
     * @access protected
     */
    protected $newaccount = false;

    /**
     * Users can request password updates
     *
     * @var    boolean
     * @access protected
     */
    protected $newpassword = false;


    /**
     * Regular expression for user names
     *
     * @var       string
     * @protected
     */
    protected $username_regexp = '';

    /**
     * Description of user names regex
     *
     * @var       string
     * @protected
     */
    protected $uname_regx_des = '';


    /**
     * Password Strength
     *
     * @var       string
     * @protected
     */
    protected $passwdStrength = '';

    /**
     * Password Age
     *
     * @var       string
     * @protected
     */
    protected $passwdAge = '';

    /**
     * Autocomplete
     *
     * @var       boolean
     * @protected
     */
    protected $autocomplete = false;

    /**
     * Autologout
     *
     * @var       string
     * @protected
     */
    protected $autologout = '';

    /**
     * Constructor
     *
     * @param \webtemplate\config\Configure $config Configuration class.
     *
     * @access public
     */
    public function __construct(\webtemplate\config\Configure $config)
    {

        parent::__construct($config);

        // Preload the local variables with the current parameters
        // Users section
        $this->newaccount = $this->config->read('param.users.newaccount');
        $this->newpassword = $this->config->read('param.users.newpassword');
        $this->username_regexp = $this->config->read('param.users.regexp');
        $this->uname_regx_des = $this->config->read('param.users.regexpdesc');
        $this->passwdStrength = $this->config->read('param.users.passwdstrength');
        $this->passwdAge = $this->config->read('param.users.passwdage');
        $this->autocomplete = $this->config->read('param.users.autocomplete');
        $this->autologout = $this->config->read('param.users.autologout');
    } // end constructor

    /**
     * Validate the Auth set of parameters input by the user.  Last Msg will
     * contain a list of any parameters which failed validation.
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @return boolean true if data Validated
     *
     * @access public
     */
    final public function validateParameters(array &$inputData)
    {

        // Check the data which has a default value if the user input is invalid

        // Allow users to create there own accounts
        $this->validateNewAccount($inputData);

        // Allow users to request new passwords
        $this->validateNewPassword($inputData);

        // Validate the Password Strength field is 1,2,3,4,5.
        $this->validatePasswdStrength($inputData);

        // Validate the Password Age field is 1,2,3,4.
        $this->validatePasswdAge($inputData);

        // Allow users to save password in browser
        $this->validateAutoComplete($inputData);

        // Validate the Autologout Value
        $this->validateAutoLogout($inputData);

        // Now we check the data that can either be valid or invalid.
        $dataOk = true;
        $this->lastMsg = '';

        // Username Regular expression
        if (!$this->validateUserNameRegEx($inputData)) {
            $dataOk = false;
        }

        // Username Regular expression description
        if (!$this->validateUserNameRegExDesc($inputData)) {
            $dataOk = false;
        }
        return $dataOk;
    }

    /**
     * Check if any of the Auth set of Parameters have changed.  The
     * locally stored parameters created as part of the validation process
     * are compared to the ones in the $parameters variable.  Last Msg will
     * contain a list of parameters which have changed.
     *
     * @param array $parameters Array of application Parameters.
     *
     * @return boolean true if data changed
     *
     * @access public
     */
    final public function checkParametersChanged(array $parameters)
    {

        // Set the data changed flags to false
        // These flags will be set true if their associated parameter
        // has changed
        $dataChanged = false;
        $msg = '';

        // Check if the User Create account Parameter has changed
        if ($this->newaccount != $parameters['users']['newaccount']) {
            $dataChanged = true;
            $msg .= gettext("Create Accounts Parameter Changed") . "\n";
        }

        // Check if the user can request new password parameter has changed
        if ($this->newpassword != $parameters['users']['newpassword']) {
            $dataChanged = true;
            $msg .= gettext("New Password Parameter Changed") . "\n";
        }

        // Check is the user regular expression has changed
        if ($this->username_regexp != $parameters['users']['regexp']) {
            $dataChanged = true;
            $msg .= gettext("User Name Regexp Parameter Changed") . "\n";
        }

        //check if the user regular expression description has changed
        if ($this->uname_regx_des != $parameters['users']['regexpdesc']) {
            $dataChanged = true;
            $msg .= gettext("User Name Regexp Description Parameter Changed") . "\n";
        }

        // Check if the password Strength has changed
        if ($this->passwdStrength != $parameters['users']['passwdstrength']) {
            $dataChanged = true;
            $msg .= gettext("Password Strength has changed") . "\n";
        }

        // Check if the password Age has changed
        if ($this->passwdAge != $parameters['users']['passwdage']) {
            $dataChanged = true;
            $msg .= gettext("Password Ageing has changed") . "\n";
            $passwordAgeChanged = true;
        }

        // Check if the autocomplete has changed
        if ($this->autocomplete != $parameters['users']['autocomplete']) {
            $dataChanged = true;
            $msg .= gettext("Autocomplete has changed") . "\n";
        }

        // Check if the autologout has changed
        if ($this->autologout != $parameters['users']['autologout']) {
            $dataChanged = true;
            $msg .= gettext("Auto Logout has changed") . "\n";
            $autologoutchanged = true;
        }

        $this->lastMsg = $msg;
        return $dataChanged;
    }


    /**
     * Validate if users can create their own accounts
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateNewAccount(array &$inputData)
    {
        $dataok = true;

        // This is a non binary input. Valid data is either YES or NO.
        // If input data is invalid default to no
        $this->newaccount = false;
        if (isset($inputData['create_account'])) {
            if (preg_match("/(yes)|(no)/", $inputData['create_account'], $regs)) {
                if ($inputData['create_account'] == 'yes') {
                    $this->newaccount = true;
                }
            }
        }

        return $dataok;
    }

    /**
     * Validate if users can reset their own passwords
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateNewPassword(array &$inputData)
    {
        $dataok = true;

         // This is a binary input. Valid data is either YES or NO.
        // If input data is invalid default to no
        $this->newpassword = false;
        if (isset($inputData['new_password'])) {
            if (preg_match("/(yes)|(no)/", $inputData['new_password'], $regs)) {
                if ($inputData['new_password'] == 'yes') {
                    $this->newpassword = true;
                }
            }
        }

        return $dataok;
    }


    /**
     * Validate the password strength
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validatePasswdStrength(array &$inputData)
    {
        $dataok = true;

        // Save as 1 if invalid
        $this->passwdStrength = '5';
        if (isset($inputData['passwdstrength'])) {
            $preg_matchResult = preg_match(
                "/(1)|(2)|(3)|(4)|(5)/",
                $inputData['passwdstrength'],
                $regs
            );
            if ($preg_matchResult) {
                $this->passwdStrength = $inputData['passwdstrength'];
            }
        }

        return $dataok;
    }

    /**
     * Validate the maximum password age
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validatePasswdAge(array &$inputData)
    {
        $dataok = true;

        // Save as 1 if invalid
        $this->passwdAge = '1';
        if (isset($inputData['passwdage'])) {
            if (preg_match("/(1)|(2)|(3)|(4)/", $inputData['passwdage'], $regs)) {
                $this->passwdAge = $inputData['passwdage'];
            }
        }

        return $dataok;
    }

    /**
     * Validate auto complete
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateAutoComplete(array &$inputData)
    {
        $dataok = true;

        // This is a binary input. Valid data is either YES or NO.
        // If input data is invalid default to no
        $this->autocomplete = false;
        if (isset($inputData['autocomplete'])) {
            if (preg_match("/(yes)|(no)/", $inputData['autocomplete'], $regs)) {
                if ($inputData['autocomplete'] == 'yes') {
                    $this->autocomplete = true;
                }
            }
        }

        return $dataok;
    }

    /**
     * Validate auto logout
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateAutoLogout(array &$inputData)
    {
        $dataok = true;

        // Set to Session if invalid
        $this->autologout = '1';
        if (isset($inputData['autologout'])) {
            $preg_matchResult = preg_match(
                "/(1)|(2)|(3)|(4)|(5)/",
                $inputData['autologout'],
                $regs
            );
            if ($preg_matchResult) {
                $this->autologout = $inputData['autologout'];
            }
        }

        return $dataok;
    }

    /**
     * Validate the user name regular expression
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateUserNameRegEx(array &$inputData)
    {
        $dataok = true;

        //Get the usename regular expression
        if (isset($inputData['regexp'])) {
            $this->username_regexp = substr($inputData['regexp'], 0, 60);
        } else {
            $this->username_regexp = '';
        }

        // Check the username regexp contains valid characters
        if ((!\webtemplate\general\LocalValidate::regexp($this->username_regexp))
            or (strlen($this->username_regexp) == 0)
        ) {
            $dataok = false;
            $this->lastMsg .= gettext("User name Regular Expression ");
            $this->lastMsg .= gettext("contains invalid characters");
            $this->lastMsg .= "\n";
        }

        return $dataok;
    }

    /**
     * Validate the user name regular expression description
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateUserNameRegExDesc(array &$inputData)
    {
        $dataok = true;

        //Get the usename regular expression description
        if (isset($inputData['regexpdesc'])) {
            $this->uname_regx_des = substr($inputData['regexpdesc'], 0, 200);
        } else {
            $this->uname_regx_des = '';
        }

        // Check the username regexp description contains valid characters
        if ((!\webtemplate\general\LocalValidate::generaltext($this->uname_regx_des))
            or (strlen($this->uname_regx_des) == 0)
        ) {
            $dataok = false;
            $this->lastMsg .= gettext("User name Regular Expression Description ");
            $this->lastMsg .= gettext("contains invalid characters");
            $this->lastMsg .= "\n";
        }

        return $dataok;
    }

    /**
     * This function transfers the parameters stored in this class to the
     * Configuration Class.
     *
     * @return boolean True if write is successful
     *
     * @access private
     */
    protected function savetoConfigurationClass()
    {
        $this->config->write('param.users.newaccount', $this->newaccount);
        $this->config->write('param.users.newpassword', $this->newpassword);
        $this->config->write('param.users.regexp', $this->username_regexp);
        $this->config->write('param.users.regexpdesc', $this->uname_regx_des);
        $this->config->write('param.users.passwdstrength', $this->passwdStrength);
        $this->config->write('param.users.passwdage', $this->passwdAge);
        $this->config->write('param.users.autocomplete', $this->autocomplete);
        $this->config->write('param.users.autologout', $this->autologout);
        return true;
    }
}
