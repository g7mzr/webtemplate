<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Admin
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\admin;

use g7mzr\webtemplate\application\exceptions\AppException;

/**
 * Parameters Interface Class
 *
 **/
class Parameters
{
    /**
     * Configuration class object
     *
     * @var \g7mzr\webtemplate\config\Configuration
     */
    protected $config;

    /**
     * Menu class object
     *
     * @var \g7mzr\webtemplate\config\Menus
     */
    protected $menus;

    /**
     * Parameters Section Class object
     *
     * @var \g7mzr\webtemplate\admin\parameters\Required
     */
    protected $section;

    /**
     * Constructor
     *
     * @param \g7mzr\webtemplate\config\Configure $config Configuration class.
     * @param \g7mzr\webtemplate\config\Menus     $menus  Menu Class class.
     *
     * @access public
     */
    public function __construct(
        \g7mzr\webtemplate\config\Configure $config,
        \g7mzr\webtemplate\config\Menus $menus
    ) {
        $this->config = $config;
        $this->menus = $menus;
    }


    /**
     * This function links to the section being tested
     *
     * @param string $section The parameters section being used.
     *
     * @throws AppException If an invalid section is chosen.
     *
     * @return boolean True if a valid section is chosen and selected
     */
    public function setSection(string $section)
    {

        $classFile = __DIR__ . "/parameters/" . ucfirst($section) . ".php";
        if (file_exists($classFile)) {
            require_once $classFile;

            $classname = '\\g7mzr\\webtemplate\\admin\\parameters\\' . ucfirst($section);
            if (class_exists($classname)) {
                 $this->section = new $classname($this->config);
                 return true;
            }
        }
        throw new AppException("Invalid Parameter Section: " . $section);
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
        return $this->menus->readMenu('parampagelist');
    }

    /**
     * This function returns the last message created by the class.
     * This could be either a error or status update.
     *
     * @throws AppException If an invalid class is selected.
     *
     * @return string Last Message created by CLASS
     *
     * @access public
     */
    final public function getLastMsg()
    {
        $classname = "\\g7mzr\\webtemplate\\admin\\parameters\\ParametersAbstract";
        if (is_a($this->section, $classname)) {
            return $this->section->getLastMsg();
        } else {
            throw new AppException("Invalid Class.");
        }
    }

    /**
     * This function returns the current parameter settings.  This may
     * have been updated as a result of validating user input.
     *
     * @throws AppException If an invalid class is selected.
     *
     * @return array of parameters;
     *
     * @access public
     */
    final public function getCurrentParameters()
    {
        $classname = "\\g7mzr\\webtemplate\\admin\\parameters\\ParametersAbstract";
        if (is_a($this->section, $classname)) {
            return $this->section->getCurrentParameters();
        } else {
            throw new AppException("Invalid Class.");
        }
    }



    /**
     * Save the current parameters to a file called parameters.php.  The
     * file is located in the $config directory,
     *
     *
     * @throws AppException If an invalid class is selected.
     *
     * @return boolean true if parameterfile saved false otherwise
     *
     * @access public
     */
    final public function saveParamFile()
    {
        $classname = "\\g7mzr\\webtemplate\\admin\\parameters\\ParametersAbstract";
        if (is_a($this->section, $classname)) {
            return $this->section->saveParamFile();
        } else {
            throw new AppException("Invalid Class.");
        }
    }


    /**
     * Validate the parameters input by the user.  Last Msg will
     * contain a list of any parameters which failed validation.
     *
     * @param array $inputData Pointer to an array of User Input Data.
     *
     * @throws AppException If an invalid class is selected.
     *
     * @return boolean true if data Validated
     *
     * @access public
     */
    final public function validateParameters(array &$inputData)
    {
        $classname = "\\g7mzr\\webtemplate\\admin\\parameters\\ParametersAbstract";
        if (is_a($this->section, $classname)) {
            return $this->section->validateParameters($inputData);
        } else {
            throw new AppException("Invalid Class.");
        }
    }

    /**
     * Check if any of the Parameters have changed.
     *
     * Check if any of the Parameters have changed.  The locally stored parameters
     * created as part of the validation process are compared to the ones in the
     * $parameters variable.  LastMsg will contain a list of parameters which have
     * changed.
     *
     * @param array $parameters Array of application Parameters.
     *
     * @throws AppException If an invalid class is selected.
     *
     * @return boolean true if data changed
     *
     * @access public
     */
    final public function checkParametersChanged(array $parameters)
    {
        $classname = "\\g7mzr\\webtemplate\\admin\\parameters\\ParametersAbstract";
        if (is_a($this->section, $classname)) {
            return $this->section->checkParametersChanged($parameters);
        } else {
            throw new AppException("Invalid Class.");
        }
    }
}
