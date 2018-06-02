<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\admin;

/**
 * Parameters Interface Class
 *
 * @category Webtemplate
 * @package  Admin
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class Parameters
{
    /**
     * Configuration class object
     *
     * @var \webtemplate\config\Configuration
     */
    protected $config;

    /**
     * Parameters Section Class object
     *
     * @var \webtemplate\admin\parameters\Required
     */
    protected $section;

    /**
     * Constructor
     *
     * @param \webtemplate\config\Configure $config Configuration class
     *
     * @access public
     */
    public function __construct($config)
    {
        $this->config = $config;
    } // end constructor


    /**
     * This function links to the section being tested
     *
     * @param string $section The parameters section being used
     */
    public function setSection($section)
    {

        $classFile = __DIR__ . "/parameters/" . $section . ".class.php";
        if (file_exists($classFile)) {
            require_once $classFile;

            $classname = '\\webtemplate\\admin\\parameters\\'. ucfirst($section);
            if (class_exists($classname)) {
                 $this->section = new $classname($this->config);
                 return true;
            }
        }
        throw new \webtemplate\application\exceptions\AppException(
            "Invalid Parameter Section: " . $section
        );
    }

    /**
     * This function gets the page list
     *
     * @return array Parameter page list
     *
     * @access public
     */
    public function getPageList()
    {
        return $this->config->readMenu('parampagelist');
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
        $classname = "\\webtemplate\\admin\\parameters\\ParametersAbstract";
        if (is_a($this->section, $classname)) {
            return $this->section->getLastMsg();
        } else {
            throw new \webtemplate\application\exceptions\AppException(
                "Invalid Class."
            );
        }
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
        $classname = "\\webtemplate\\admin\\parameters\\ParametersAbstract";
        if (is_a($this->section, $classname)) {
            return $this->section->getCurrentParameters();
        } else {
            throw new \webtemplate\application\exceptions\AppException(
                "Invalid Class."
            );
        }
    }



    /**
    * Save the current parameters to a file called parameters.php.  The
    * file is located in the $config directory,
    *
    * @param string $configDir Location of the parameter file
    *
    * @return boolean true if parameterfile saved false otherwise
    *
    * @access public
    */
    final public function saveParamFile($configDir)
    {
        $classname = "\\webtemplate\\admin\\parameters\\ParametersAbstract";
        if (is_a($this->section, $classname)) {
            return $this->section->saveParamFile($configDir);
        } else {
            throw new \webtemplate\application\exceptions\AppException(
                "Invalid Class."
            );
        }
    }


    /**
    * Validate the parameters input by the user.  Last Msg will
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
        $classname = "\\webtemplate\\admin\\parameters\\ParametersAbstract";
        if (is_a($this->section, $classname)) {
            return $this->section->validateParameters($inputData);
        } else {
            throw new \webtemplate\application\exceptions\AppException(
                "Invalid Class."
            );
        }
    }

    /**
     * Check if any of the Parameters have changed.
     *
     * Check if any of the Parameters have changed.  The localy stored parameters
     * created as part of the validation process are compared to the ones in the
     * $parameters variable.  LastMsg will contain a list of parameters which have
     * changed.
     *
     * @param array $parameters Array of application Parameters
     *
     * @return boolean true if data changed
     *
     * @access public
     */
    final public function checkParametersChanged($parameters)
    {
        $classname = "\\webtemplate\\admin\\parameters\\ParametersAbstract";
        if (is_a($this->section, $classname)) {
            return $this->section->checkParametersChanged($parameters);
        } else {
            throw new \webtemplate\application\exceptions\AppException(
                "Invalid Class."
            );
        }
    }
}
