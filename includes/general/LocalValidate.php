<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage General
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace webtemplate\general;

/**
 * Local Validation Class is a static class used to validate user input
 *
 **/
class LocalValidate
{

    /**
    * Validates that a Database ID is between 1 and 99999
    *
    * @param string $dbid Database ID.
    *
    * @return boolan True if ID validated false otherwise
    *
    * @access public
    */
    final public function dbid(string $dbid)
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
    * @param string $userName Users Name.
    * @param string $regexp   The username regular expression.
    *
    * @return boolan True if username validated false otherwise
    *
    * @access public
    */
    final public function username(string $userName, string $regexp)
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
     * @param string $check_regex The regular expression to be checked.
     *
     * @return boolean True if check_regex is validated false otherwise
     *
     * @access public
     */
    final public function regexp(string $check_regex)
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
     * @param string $realName Users Real Name.
     *
     * @return boolan True if realname validated false otherwise
     *
     * @access public
     */
    final public function realname(string $realName)
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
    * @param string $passwd   Users Password.
    * @param string $strength Number which defines password format.
    *
    * @return boolan True if password validated false otherwise
    *
    * @access public
    */
    final public function password(string $passwd, string $strength)
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
    * @param string $email Users email address.
    *
    * @return boolan True if email validated false otherwise
    *
    * @access public
    */
    final public function email(string $email)
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
    * @param string $url URL to be checked.
    *
    * @return boolan True if url validated false otherwise
    *
    * @access public
    */
    final public function url(string $url)
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
    * @param string  $path          Path to be checked.
    * @param boolean $trailingslash Path finishes with a slash.
    *
    * @return boolan True if path validated false otherwise.
    *
    * @access public
    */
    final public function path(string $path, bool $trailingslash = false)
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
    * @param string $path Path to be checked.
    *
    * @return boolan True if path validated false otherwise
    *
    * @access public
    */
    final public function docPath(string $path)
    {
        $regex = "(([a-z0-9%+\$_-]\/?)+)*\/"; // Path
        if (preg_match("/^$regex$/", $path)) {
            return true;
        } else {
            return false;
        }
    }


    /**
    * * Validate that a domain name or if address is in a valid format.
    *
    * @param string $domain Domain name to be checked.
    *
    * @return boolan True if domain validated false otherwise
    *
    * @access public
    */
    final public function domain(string $domain)
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
    * @param string $groupname Group Name to be checked.
    *
    * @return boolan True if groupname validated false otherwise
    *
    * @access public
    */
    final public function groupname(string $groupname)
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
    * @param string $groupdescription Group description to be checked.
    *
    * @return boolan True if groupdescription validated false otherwise
    *
    * @access public
    */
    final public function groupdescription(string $groupdescription)
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
    final public function generaltext(string $gentext)
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
    * @param string $token The token to be validated.
    *
    * @return boolan True if token validated false otherwise
    *
    * @access public
    */
    final public function token(string $token)
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
    * @param string $filename The filename to be validated.
    *
    * @return boolan True if filename validated false otherwise
    *
    * @access public
    */
    final public function htmlFile(string $filename)
    {
        if (preg_match("/^[\w,\s-]+\.(html)$/", $filename)) {
            return true;
        } else {
            return false;
        }
    }
}
