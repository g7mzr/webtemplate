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

namespace webtemplate\application;

/**
 * Header Class is a static class used to send HTTP Headers to the client.
 *
**/
class Header
{
    /**
     * This function send the HTTP Headers required too ensure the applications
     * is secure.
     *
     * @return boolean Function run okay
     *
     * @since Method available since Release 1.0.0
     *
     * @access public
     */
    public static function sendHeaders()
    {
        // Set Local Variables
        $sendHeaders = false;

        // Check if headers sent.  If not send our headers
        if (!headers_sent()) {
            // Set local Flag to true
            $sendHeaders = true;

            // Adds X-Frame-Options to HTTP header,
            // so that page can only be shown in an iframe of the same site.
            // FF 3.6.9+ Chrome 4.1+ IE 8+ Safari 4+ Opera 10.5+
            header('X-Frame-Options: SAMEORIGIN');

            // Adds the Content-Security-Policy to the HTTP header.
            // JavaScript will be restricted to the same domain as the page itself.
            // FF 23+ Chrome 25+ Safari 7+ Opera 19+
            $headerstr = "Content-Security-Policy: default-src 'self';";
            $headerstr .= " script-src 'self' 'unsafe-inline'; style-src 'self';";
            header($headerstr);
            // IE 10+
            $headerstr = "X-Content-Security-Policy: default-src 'self';";
            $headerstr .= " script-src 'self' 'unsafe-inline'; style-src 'self';";
            header($headerstr);

            // Add the X-Content-Type-Option header
            header("X-Content-Type-Options: nosniff");

            // Enable Browser XXS Protection
            header("X-XSS-Protection: 1; mode=block");
        }


        return $sendHeaders;
    }




    /**
    * This function send the redirect HTTP Header
    * is secure.
    *
    * @param string $url  The URL to be redirected to.
    * @param string $data Any Data to be sent as part of the redirect.
    *
    * @return boolean Function run okay
    *
    * @since Method available since Release 1.0.0
    */
    public static function sendRedirect(string $url, string $data = '')
    {
        // Set Local Variables
        $sendRedirect = false;

        // Check if headers sent.  If not send our headers
        if (!headers_sent()) {
            // Set local Flag to true
            $sendRedirect = true;

            // Build the relocate url
            $relocateurl = $url;

            if ($data != '') {
                // Add data if it exists
                $relocateurl .= "?" . $data;
            }

            // Send the REdirect Header
            header('Location: ' . $relocateurl);
        }


        return $sendRedirect;
    }
}
