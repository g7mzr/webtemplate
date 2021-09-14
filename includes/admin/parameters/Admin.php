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

namespace g7mzr\webtemplate\admin\parameters;

use g7mzr\webtemplate\config\Configure;

/**
 * Parameters ADMIN Interface Class
 *
 **/
class Admin extends ParametersAbstract
{

    /**
     * Level of logging
     *
     * @var    string
     * @access protected
     */
    protected $logging = '';

    /**
     * Duration between log rotation
     *
     * @var    string
     * @access protected
     */
    protected $logrotate = '';

    /**
     * Links open in new windows
     *
     * @var    boolean
     * @access protected
     */
    protected $newwindow = false;

    /**
     * Maximum number of records to display
     *
     * @var    string
     * @access protected
     */
    protected $maxrecords = '';

    /**
     * Constructor
     *
     * @param Configure $config Configuration class.
     *
     * @access public
     */
    public function __construct(Configure $config)
    {

        parent::__construct($config);

        // Preload the local variables with the current parameters
        // Admin Section
        $this->logging = $this->config->read('param.admin.logging');
        $this->logrotate = $this->config->read('param.admin.logrotate');
        $this->newwindow = $this->config->read('param.admin.newwindow');
        $this->maxrecords = $this->config->read('param.admin.maxrecords');
    }

    /**
     * Validate the Admin set of parameters input by the user.  Last Msg will
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
        // Logging level
        $this->validateLoggingLevel($inputData);

        // Log Rotate Interval
        $this->validateLogRotate($inputData);

        // Open links in new windows.
        $this->validateNewWindows($inputData);

        // number of records to display at one time.  Multipy value by 100.
        $this->validateMaxRecords($inputData);

        return true;
    }

    /**
     * Check if any of the Admin set of Parameters have changed.  The
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

        // Check if logging level has changed
        if ($this->logging != $parameters['admin']['logging']) {
            $dataChanged = true;
            $msg .= gettext("Logging Level Parameter Changed") . "\n";
        }

        // Check if logging rotate has changed
        if ($this->logrotate != $parameters['admin']['logrotate']) {
            $dataChanged = true;
            $msg .= gettext("Log Rotate Parameter Changed") . "\n";
        }

        // Check if new windo has changed
        if ($this->newwindow != $parameters['admin']['newwindow']) {
            $dataChanged = true;
            $msg .= gettext("New Window Parameter Changed") . "\n";
        }

        // Check if max_records has changed
        if ($this->maxrecords != $parameters['admin']['maxrecords']) {
            $dataChanged = true;
            $msg .= gettext("Max Records Parameter Changed") . "\n";
        }

        $this->lastMsg = $msg;
        return $dataChanged;
    }


    /**
    * Validate the level of logging the application is to do
    *
    * @param array $inputData Pointer to an array of User Input Data.
    *
    * @return boolean true if data Validated
    *
    * @access private
    */
    private function validateLoggingLevel(array &$inputData)
    {
        $dataok = true;

        //This is a non binary input. Valid data is either 0, 1, 2, 3, 4 or 5.
        // If input data is invalid default to old value
        $this->logging  = $this->config->read('param.admin.logging');
        if (isset($inputData['logging'])) {
            $loggingLevelRegex = "/^(0|1|2|3|4|5)$/";
            if (preg_match($loggingLevelRegex, $inputData['logging'], $regs) == 1) {
                $this->logging = $inputData['logging'];
            }
        }

        return $dataok;
    }


    /**
    * Validate when the logs are to be rotated
    *
    * @param array $inputData Pointer to an array of User Input Data.
    *
    * @return boolean true if data Validated
    *
    * @access private
    */
    private function validateLogRotate(array &$inputData)
    {
        $dataok = true;
        //This is a non binary input. Valid data is either 1, 2, 3.
        // If input data is invalid default to old value
        $this->logrotate  = $this->config->read('param.admin.logrotate');
        if (isset($inputData['logrotate'])) {
            $logRotateRegex = "/(1|2|3)/";
            if (preg_match($logRotateRegex, $inputData['logrotate'], $regs)) {
                $this->logrotate = $inputData['logrotate'];
            }
        }

        return $dataok;
    }

    /**
    * Validate if links are to open new windows
    *
    * @param array $inputData Pointer to an array of User Input Data.
    *
    * @return boolean true if data Validated
    *
    * @access private
    */
    private function validateNewWindows(array &$inputData)
    {
        $dataok = true;

        // This is a non binary input. Valid data is either YES or NO.
        // If input data is invalid default to old value
        $this->newwindow = $this->config->read('param.admin.newwindow');
        if (isset($inputData['new_window'])) {
            if (preg_match("/(yes)|(no)/", $inputData['new_window'], $regs)) {
                if ($inputData['new_window'] == 'yes') {
                    $this->newwindow = true;
                } else {
                    $this->newwindow = false;
                }
            }
        }

        return $dataok;
    }

    /**
     * Validate the max number of records a database search is to return
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @return boolean true if data Validated
     *
     * @access private
     */
    private function validateMaxRecords(array &$inputData)
    {
        $dataok = true;

        //This is a non binary input. Valid data is either 1, 2, 3, 4 or 5.
        // If input data is invalid default to old value
        $this->maxrecords  = $this->config->read('param.admin.maxrecords');
        if (isset($inputData['max_records'])) {
            $maxRecsRegex = "/(1|2|3|4|5)/";
            if (preg_match($maxRecsRegex, $inputData['max_records'], $regs)) {
                $this->maxrecords = $inputData['max_records'];
            }
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
        $this->config->write('param.admin.logging', $this->logging);
        $this->config->write('param.admin.logrotate', $this->logrotate);
        $this->config->write('param.admin.newwindow', $this->newwindow);
        $this->config->write('param.admin.maxrecords', $this->maxrecords);
        return true;
    }
}
