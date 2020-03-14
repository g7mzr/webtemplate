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

namespace g7mzr\webtemplate\application\exceptions;

/**
* Webtemplate Exception Class
**/
class AppException extends \Exception
{
    /**
     * Constructor for AppException.
     *
     * AppException makes the message mandatory unlike the PHP version
     *
     * @param string    $message  The Exception message to throw.
     * @param integer   $code     The Exception code.
     * @param exception $previous The previous exception used for chaining.
     *
     * @access public
     */
    public function __construct(
        string $message,
        int $code = 0,
        exception $previous = null
    ) {


        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}
