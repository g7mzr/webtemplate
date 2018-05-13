<?php
/**
 * This module is used to create the webtemplate error class
 *
 * PHP version  5
 *
 * LICENSE: This source file is subject to version 2.1 of the GPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html.
 *
 * @category  Webtemplate
 * @package   General
 * @author    Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright 2012 Sandy McNeil
 * @license   View the license file distributed with this source code
 * @version   SVN: $Id$
 * @link      http://www.g7mzr.demon.co.uk/
 */
namespace webtemplate\application;

/**
 * This module is used to create the webtemplate error class
 *
 * @category Webtemplate
 * @package  General
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code

**/

class Error
{

    /*
     * @var string
     * @acess protected
     */
    protected $errorMsg = '';

    /*
     * @var integer
     * @access protected
     */
    protected $errorCode = 0;


    /**
     * Constructor
     *
     * @param string  $errorMsg  The error message the exception has thrown
     * @param integer $errorCode The code of the error
     *
     * @access public
     */
    public function __construct($errorMsg = null, $errorCode = null)
    {
        $this->errorMsg = $errorMsg;
        $this->errorCode = $errorCode;
    } // end constructor


    /**
    * This function returns the error message
    *
    * @return string Return the error message
    *
    * @since Method available since Release 1.0.0
    */
    public function getMessage()
    {

        return $this->errorMsg;
    }


    /**
    * This function returns the error code
    *
    * @return integer The error code
    *
    * @since Method available since Release 1.0.0
    */
    public function getCode()
    {

        return $this->errorCode;
    }
}
