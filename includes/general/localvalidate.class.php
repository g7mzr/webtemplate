<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\general;

/**
 * Include files for Class
 */
//require_once 'Validate.php';

/**
 * Local Validation Class is a static class used to validate user input
 *
 * @category Webtemplate
 * @package  General
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

class LocalValidate
{

    /**
    * Validates that a Database ID is between 1 and 99999
    *
    * @param string $dbid Database ID
    *
    * @return boolan True if ID validated false otherwise
    *
    * @access public
    */
    final public function dbid($dbid)
    {
        if (preg_match("/^([0-9]{1,5})$/", $dbid)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Validate that a username complies with the userbame regular expression
    * parameter.
    *
    * @param string $userName Users Name
    * @param string $regexp   The username regular expression
    *
    * @return boolan True if username validated false otherwise
    *
    * @access public
    */
    final public function username($userName, $regexp)
    {

        // Test to see if the username is valid.
        if (preg_match($regexp, $userName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Validate that a regular expression only contains a specific set
     * of characters [/^[]{}$a-zA-Z0-9].  It does not check to see if the
     * regular expression itself is valid.
     *
     * @param string $check_regex The regular expression to be checked
     *
     * @return boolean True if check_regex is validated false otherwise
     *
     * @access public
     */
    final public function regexp($check_regex)
    {
        $result = array();
        $regex = "/^([\/\^\-\[\]\{\}\$a-zA-Z0-9\,\(\)\.]+)$/";
        if (preg_match($regex, $check_regex, $result) === 1) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Validate that a real name is between 3 and 60 characters long and
     * can contain Upper and Lower Case letters and spaces
     *
     * @param string $realName Users Real Name
     *
     * @return boolan True if realname validated false otherwise
     *
     * @access public
     */
    final public function realname($realName)
    {
        if (preg_match("/^([a-zA-Z\s\']{3,60})$/", $realName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Validate that a password is between 8 and 20 characters long and will
    * be of the following formats depending on the $strength variable
    * 1. No constrainst
    * 2. Lower Case Letters only
    * 3. Lower and Upper Case letters. One of each
    * 4. Lower and Upper Case letters and numbers. One of each
    * 5. Lower and Upper Case letters, numbers and specoal chars. One of each
    *
    * @param string $passwd   Users Password
    * @param string $strength number which defines password format
    *
    * @return boolan True if password validated false otherwise
    *
    * @access public
    */
    final public function password($passwd, $strength)
    {
        switch ($strength) {
            case '1':
                $passExp = "/^([a-zA-Z\W\d]{8,20})$/";
                break;
            case '2':
                $passExp = "/^([a-z]{8,20})$/";
                break;
            case '3':
                $passExp = "/^.*(?=.{8,20})(?!.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\W+).*$/";
                break;
            case '4':
                $passExp = "/^.*(?=.{8,20})(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?!.*\W+).*$/";
                break;
            default:
                $passExp = "/^.*(?=.{8,20})(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*\W+).*$/";
        }

        if (preg_match($passExp, $passwd)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Validate that a e-mail address is in avalid format
    *
    * @param string $email Users email address
    *
    * @return boolan True if email validated false otherwise
    *
    * @access public
    */
    final public function email($email)
    {
        $emailExp  = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)";
        $emailExp .= "*@[a-z0-9-]+(\.[a-z0-9-]+)";
        $emailExp .= "*(\.[a-z]{2,3})$/";
        if (preg_match($emailExp, $email)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Validate that a URL address is in a valid format.
    * It checks the following parts:
    * Scheme, User and Password, Host or IP address, port, path,
    * Get Query and Anchor
    *
    * @param string $url URL
    *
    * @return boolan True if url validated false otherwise
    *
    * @access public
    */
    final public function url($url)
    {

        // SCHEME
        $regex = "((https?|ftp)\:\/\/)?";

         // User and Pass
        $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";

         // Host or IP
        $regex .= "([a-z0-9-.]*)\.([a-z]{2,3})";

        // Port
        $regex .= "(\:[0-9]{2,5})?";

        // Path
        $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";

         // GET Query
        $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?";

        // Anchor
        $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?";

        // heck if the URL is valid
        if (preg_match("/^$regex$/", $url)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Validate that a path with or without a trailing slash is in a valid format
    *
    * @param string  $path          path
    * @param boolean $trailingslash path finishes with a slash
    *
    * @return boolan True if path validated false otherwise
    *
    * @access public
    */
    final public function path($path, $trailingslash = false)
    {
        if ($trailingslash == true) {
            $regex = "(\/?([a-z0-9+\$_-]\.?)+)*\/?"; // Path
        } else {
            $regex = "(\/?([a-z0-9+\$_-]\.?)+)*\/?"; // Path
        }
        if (preg_match("/^$regex$/", $path)) {
            return true;
        } else {
            return false;
        }
    }


    /**
    * Validate that a document path with a trailing slash is in a valid format
    *
    * @param string $path path
    *
    * @return boolan True if path validated false otherwise
    *
    * @access public
    */
    final public function docPath($path)
    {
        $regex = "(([a-z0-9%+\$_-]\/?)+)*\/"; // Path
        if (preg_match("/^$regex$/", $path)) {
            return true;
        } else {
            return false;
        }
    }


    /**
    * * Validate that a domain name or ip address is in a valid format.
    *
    * @param string $domain domain name
    *
    * @return boolan True if domain validated false otherwise
    *
    * @access public
    */
    final public function domain($domain)
    {
        $regex = "([a-z0-9-.]*)\.([a-z]{2,3})"; // Host or IP
        if (preg_match("/^$regex$/", $domain)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Validate that a group name is between 5 and 25 characters long and
    * can contain Upper and Lower Case letters
    *
    * @param string $groupname Group Name
    *
    * @return boolan True if groupname validated false otherwise
    *
    * @access public
    */
    final public function groupname($groupname)
    {
        if (preg_match("/^([a-zA-Z]{5,25})$/", $groupname)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Validate that a group destription is between 5 and 255 characters long and
    * can contain Upper and Lower Case letters and spaces
    *
    * @param string $groupdescription Group description
    *
    * @return boolan True if groupdescription validated false otherwise
    *
    * @access public
    */
    final public function groupdescription($groupdescription)
    {
        if (preg_match("/^([a-zA-Z\s\.]{5,225})$/", $groupdescription)) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Validate that general text is between 1 and 2048 characters long and
    * can contain Upper and Lower Case letters and spaces
    *
    * @param string $gentext Text to be validated.
    *
    * @return boolan True if gentext validated false otherwise
    *
    * @access public
    */
    final public function generaltext($gentext)
    {
        if (preg_match("/^([a-zA-Z0-9\s\.]{0,2048})$/", $gentext)) {
            return true;
        } else {
            return false;
        }
    }


    /**
    * Validate that a a token of 10 characters and
    * can contain Lower Case letters and numbers
    *
    * @param string $token The token to be validated
    *
    * @return boolan True if token validated false otherwise
    *
    * @access public
    */
    final public function token($token)
    {
        if (preg_match("/^([a-z0-9]{10})$/", $token)) {
            return true;
        } else {
            return false;
        }
    }


    /**
    * Validate that a filename is in the format *.html
    *
    * @param string $filename The filename to be validated
    *
    * @return boolan True if filename validated false otherwise
    *
    * @access public
    */
    final public function htmlFile($filename)
    {
        if (preg_match("/^[\w,\s-]+\.(html)$/", $filename)) {
            return true;
        } else {
            return false;
        }
    }
}
