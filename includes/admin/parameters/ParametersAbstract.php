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

/**
 * Parameters Abstract Class
 *
 **/
abstract class ParametersAbstract
{
    /**
     * Last Message
     *
     * @var    string
     * @access protected
     */
    protected $lastMsg = '';

    /**
     * Configuration class object
     *
     * @var\g7mzr\webtemplate\config\Configuration
     */
    protected $config;


    /**
     * Constructor
     *
     * @param \g7mzr\webtemplate\config\Configure $config Configuration class.
     *
     * @access public
     */
    public function __construct(\g7mzr\webtemplate\config\Configure $config)
    {
        $this->config = $config;
    }


    /**
    * This function returns the last message created by the class.
    * This could be either a error or status update.
    *
    * @return string Last Message created by CLASS
    *
    * @access public
    */
    final public function getLastMsg()
    {
        return $this->lastMsg;
    }

    /**
    * This function returns the current parameter settings.  This may
    * have been updated as a result of validating user input.
    *
    * @return array of parameters;
    *
    * @access public
    */
    final public function getCurrentParameters()
    {
        $this->savetoConfigurationClass();
        return $this->config->read('param');
    }



    /**
    * Save the current parameters to a file called parameters.php.  The
    * file is located in the $config directory,
    *
    * @return boolean true if parameterfile saved false otherwise
    *
    * @access public
    */
    final public function saveParamFile()
    {
        // Load the values in to configuration object
        $this->savetoConfigurationClass();

        // Save the values
        $result = $this->config->saveParams();
        return $result;
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
    abstract public function validateParameters(array &$inputData);

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
    abstract public function checkParametersChanged(array $parameters);

    /**
     * This function transfers the parameters stored in this class to the
     * Configuration Class.
     *
     * @return boolean True if write is successful
     *
     * @access private
     */
    abstract protected function savetoConfigurationClass();
}
