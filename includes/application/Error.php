<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Application
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\application;

/**
 * This module is used to create the webtemplate error class
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
     * @param string  $errorMsg  The error message the exception has thrown.
     * @param integer $errorCode The code of the error.
     *
     * @access public
     */
    public function __construct(string $errorMsg = '', int $errorCode = 0)
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
