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

namespace g7mzr\webtemplate\general;

 /**
 * GeneralCode Class is a static class containing common application functions.
 *
 **/
class General
{
    /**
     * This method users the HTTP_ACCEPT_LANGUAGE string from the
     * browser to select the correct language to display
     * If the language file is not available it defaults to English
     *
     * @param array $configDirectory SMARTY Config directory.
     *
     * @return string config file name
     *
     * @access public
     */
    public static function getconfigfile(array $configDirectory)
    {
        // Get the browsers language
        if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        } else {
            $lang = 'en';
        }

        // Create the config files name
        $configFile = $lang . '.conf';
        // error_log ("Server Language: " .$_SERVER['HTTP_ACCEPT_LANGUAGE']);
        // error_log("config Dir: " . $configDirectory);
        // error_log("config file: " . $configFile);

        // If it exists returen the config file name or default to english
        if (file_exists($configDirectory[0] . '/' . $configFile)) {
            return $configFile;
        } else {
            return "en.conf";
        }
    }

    /**
     * This method is used to automatically generate new passwords
     *
     * @return string password
     *
     * @access public
     */
    public static function generatePassword()
    {

        //we need these vars to create a password string
        $upper     = 4;
        $lower     = 4;
        $numeric   = 2;
        //$other     = 0;
        $passOrder = array();
        $passWord  = '';

        //generate the contents of the password
        for ($i = 0; $i < $upper; $i++) {
            $passOrder[] = chr(rand(65, 90));
        }
        for ($i = 0; $i < $lower; $i++) {
            $passOrder[] = chr(rand(97, 122));
        }
        for ($i = 0; $i < $numeric; $i++) {
            $passOrder[] = chr(rand(48, 57));
        }
        //for ($i = 0; $i < $other; $i++) {
        //    $passOrder[] = chr(rand(33, 47));
        //}

        //randomize the order of characters
        shuffle($passOrder);

        //concatenate into a string
        foreach ($passOrder as $char) {
            $passWord .= $char;
        }

        //we're done
        return $passWord;
    }

    /**
     * This method returns a string that defines what a password
     * should contain based on the 'users.passwdstrength'
     * parameter.
     *
     * @param string $strength Password strength.
     *
     * @return string containing the password format description
     *
     * @access public
     */
    public static function passwdFormat(string $strength)
    {
        $passwdstr = gettext("Password must be between 8 and 20 characters. ");
        $str1 = gettext("No other checks");
        $str2 = gettext("Lower Case letters only");
        $str3 = gettext("Must contain at least one lower and one upper case letter");
        $str4 = gettext("and one number");
        $str5 = gettext("and one special character");
        switch ($strength) {
            case '1':
                $passwdstr .= $str1;
                break;
            case '2':
                $passwdstr .= $str2;
                break;
            case '3':
                $passwdstr .=  $str3;
                break;
            case '4':
                $passwdstr .= $str3 . " " . $str4;
                break;
            default:
                $passwdstr .= $str3 . " " . $str4 . " " . $str5;
        }
        return $passwdstr;
    }

    /**
     * This method returns the encrypted users password
     *
     * @param string $passwd The users plain text password.
     *
     * @return string containing the encrypted password
     *
     * @access public
     */
    public static function encryptPasswd(string $passwd)
    {
        if (isset($GLOBALS['passwdfail'])) {
            return false;
        } else {
            $options = ['cost' => 10];
            return password_hash($passwd, PASSWORD_DEFAULT, $options);
        }
    }


    /**
     * This method checks if a user password and hash are the same
     *
     * @param string $passwd The users plain text password.
     * @param string $hash   The password hash from the database.
     *
     * @return string containing the encrypted password
     *
     * @access public
     */
    public static function verifyPasswd(string $passwd, string $hash)
    {
        return password_verify($passwd, $hash);
    }



    /**
     * This method is used to check that key parameters used by the
     * application have been set by the maintainer.  If they have not
     * an error message is returned.
     *
     * @param string $urlbase      Fully qualified domain name for the application.
     * @param string $emailaddress The applications email address for sending mail.
     * @param string $maintainer   The maintainers email address.
     *
     * @return string Error Message
     *
     * @access public
     */
    public static function checkkeyParameters(
        string $urlbase,
        string $emailaddress,
        string $maintainer
    ) {
        // Set the flag to its default value
        $paramnotset = false;

        // Set the start of the error message
        $msg = gettext("The following Parameters need to be set.  Please go to");
        $msg .= "<a href='editconfig.php'>";
        $msg .= gettext("Edit Configuration") . "</a>\n\n";

        // Check if the URL Base is set.
        if ($urlbase == '') {
            // If not set update the error message and flag to true
            $msg .= gettext("URL Base") . "\n";
            $paramnotset = true;
        }

        // Check the application's e-mail address is set
        if ($emailaddress == '') {
            // If not set update the error message and flag to true
            $msg .= gettext("Application's E-mail Address") . "\n";
            $paramnotset = true;
        }

        // Check if the maintainer's e-mail address is set
        if ($maintainer == '') {
            // If not set update the error message and flag to true
            $msg .= gettext("Maintainer's E-mail Address") . "\n";
            $paramnotset = true;
        }

        // Finalise the message
        $msg .= "\n\n";
        $msg .= gettext("You are seeing this message as you are in the ADMIN group");
        $msg .= "\n";

        // If a parameter is not set
        if ($paramnotset == true) {
            // return the message
            return $msg;
        } else {
            // else return a empty string
            return '';
        }
    }

    /**
     * This method is used to check if documentation is available
     *
     * @param string $docBase  The base directory the documents are in.
     * @param string $language The users chosen language.
     *
     * @return boolean True if application documents are available
     *
     * @access public
     */
    public static function checkdocs(string $docBase, string $language)
    {
        // Set the flag to its default value
        $docsfound = false;
        $docroot = $_SERVER["DOCUMENT_ROOT"];

        // Check if the  docbase parameter is set
        if ($docBase != '') {
            // Set docBase to the users chosen Language
            $testDocBase = str_replace("%lang%", $language, $docBase);
            if (is_dir($testDocBase)) {
                $docBase = str_replace("%lang%", $language, $docBase);
            } else {
                $docBase = str_replace("%lang%", "en", $docBase);
            }

            // If docbase is set check if the index file is there
            if (file_exists($docroot . "/" . $docBase . "index.html")) {
                // Documentation is available
                $docsfound = true;
            }
        }
        return $docsfound;
    }


    /**
     * Disk Space Method
     *
     * This function returns the size of the $dir
     *
     * @param string $path Directory name.
     *
     * @return array containing total bytes, files and directories
     *
     * @access public
     */
    public static function getDirectorySize(string $path)
    {
        $totalsize = 0;
        $totalcount = 0;
        $dircount = 0;
        $handle = opendir($path);
        if ($handle !== false) {
            while (false !== ($file = readdir($handle))) {
                $nextpath = $path . '/' . $file;
                if ($file != '.' && $file != '..' && !is_link($nextpath)) {
                    if (is_dir($nextpath)) {
                        $dircount++;
                        $result = self::getDirectorySize($nextpath);
                        $totalsize += $result['size'];
                        $totalcount += $result['count'];
                        $dircount += $result['dircount'];
                    } elseif (is_file($nextpath)) {
                        $totalsize += filesize($nextpath);
                        $totalcount++;
                    }
                }
            }
        }
        closedir($handle);
        $total['size'] = $totalsize;
        $total['count'] = $totalcount;
        $total['dircount'] = $dircount;
        return $total;
    }

    /**
     * Format Sizes
     *
     * This function returns the size of the $dir
     *
     * @param integer $size The number to be converted.
     *
     * @return string The Size as bytes, KB, MB or GB
     *
     * @access public
     */
    public static function sizeFormat(int $size)
    {
        if ($size < 1024) {
            return $size . " bytes";
        } elseif ($size < (1024 * 1024)) {
            $size = round($size / 1024, 1);
            return $size . " KB";
        } elseif ($size < (1024 * 1024 * 1024)) {
            $size = round($size / (1024 * 1024), 1);
            return $size . " MB";
        } else {
            $size = round($size / (1024 * 1024 * 1024), 1);
            return $size . " GB";
        }
    }

    /**
     * This method is used to check if the supplied variable is an error type
     *
     * @param mixed $data The value to test.
     *
     * @return boolean True if $data is an error class
     *
     * @access public
     */
    public static function isError($data)
    {
        return is_a($data, '\g7mzr\webtemplate\application\Error', false);
    }

    /**
     * This method is raise an error
     *
     * @param string  $message A text message or error class.
     * @param integer $code    The error code.
     *
     * @return class an Error class
     *
     * @access public
     */
    public static function raiseError(string $message = "", int $code = 0)
    {
        return new \g7mzr\webtemplate\application\Error($message, $code);
    }

    /**
     * This function is used to check if an updated version of the application is
     * available
     *
     * @param string $appname        The name of the application t be used to retrieve
     *                               the update file from the server.
     * @param string $updateServer   The address of the server holding the file
     *                               containing  the latest version information.
     * @param string $currentVersion The version string of this version of the
     *                               application.
     *
     * @return string update message
     *
     * @access public
     */
    public static function checkUpdate(
        string $appname = "",
        string $updateServer = "",
        string $currentVersion = ""
    ) {
        $updateMessage = "";


        // Check if both $updateServer and $currentVersion have been set
        if (($appname == "") or ($updateServer == "") or ($currentVersion == "")) {
            return $updateMessage;
        }

        $updateFile = rtrim($updateServer, "/") . "/" . strtolower($appname) . ".json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $updateFile);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // Skip SSL Verification
        $updatedata = curl_exec($ch);

        if (curl_errno($ch)) {
            return $updateMessage;
        }

        $updatedataarray = json_decode($updatedata, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            return $updateMessage;
        }

        if ($updatedataarray == null) {
            return $updateMessage;
        }

        foreach ($updatedataarray as $key => $data) {
            if (version_compare($currentVersion, $data["version"]) < 0) {
                if ($updateMessage != "") {
                    $updateMessage .= "\n";
                }
                $updateMessage .= "Version " . $data["version"] . " (" . $key . ") released on " . $data["date"];
            }
        }
        return $updateMessage;
    }
}
