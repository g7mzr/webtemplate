<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\application\exceptions;

/**
* Webtemplate Exception Class
*
* @category Webtemplate
* @package  Exception
* @author   Sandy McNeil <g7mzrdev@gmail.com>
* @license  View the license file distributed with this source code
**/
class AppException extends \Exception
{
    /**
     * Constructor for AppException.
     *
     * AppException makes the message manditory unlike the PHP version
     *
     * @param string    $message  The Exception message to throw.
     * @param integer   $code     The Exception code.
     * @param exception $previous The previous exception used for chaining.
     *
     * @access public
     */
    public function __construct($message, $code = 0, Throwable $previous = null)
    {


        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}
