<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Install
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\install;

/**
 * FileManger Class is a static class used to setup and modify the required
 * configuration files and directory permissions
 **/
class FileManager
{
    /**
     * This array contains the initial contents of the Parameters array.  It is also
     * used to update the parameters file.
     *
     * @var    array
     * @access private
     */
    private $newParameters = array(
        array("urlbase" , "", ''),
        array("maintainer", "", ''),
        array("docbase", "", ''),
        array("cookiedomain", "", ''),
        array("cookiepath", "", '/'),

        array("admin", "logging", '1'),
        array("admin", "logrotate", '2'),
        array("admin" , "newwindow", true),
        array("admin" , "maxrecords", '2'),


        array("users", "newaccount", false),
        array("users", "newpassword", false),
        array("users", "regexp", '/^[a-zA-Z0-9]{5,12}$/'),
        array(
            "users",
            "regexpdesc",
            'Must between 5 and 12 characters long and contain upper '
            . 'and lower case letters and numbers.'
        ),
        array("users", "passwdstrength", '4'),
        array("users", "passwdage", '1'),
        array("users", "autocomplete", false),
        array("users", "autologout", '1'),

        array("email", "smtpdeliverymethod", 'none'),
        array("email", "emailaddress", ''),
        array("email", "smtpserver", ''),
        array("email", "smtpusername", ''),
        array("email", "smtppassword", ''),
        array("email", "smtpdebug", false)
    );


    /**
     * This array contains the initial contents of the preferences array.  It is also
     * used to update the preferences file.
     *
     * @var    array
     * @access private
     */
    private $newsitePreferences = array(
        array("theme", "value", "Dusk"),
        array("theme", "enabled", true),
        array("zoomtext", "value", true),
        array("zoomtext", "enabled", true),
        array("displayrows", "value", "2"),
        array("displayrows" , "enabled", true)
    );

    /**
     * This array contains the file permissions needed to run the applications
     * 0640; o=rw g=r    File
     * 0750; o=wrx g=rx  Directory
     * 0660; o=rw g=rw   File
     * 0770; o=wrx g=rwx Directory
     * 0600; o=wr g=     File
     * 0700; o=wrx g=    Directory
     *
     * @var    array
     * @access private
     */
    private $filePermissions = array (
        array('dir', 'templates_c', 0770, 0660),
        array('dir', 'configs', 0770, 0660),
        array('dir', 'cache', 0770, 0660),
        array('dir', 'logs', 0770, 0660),
        array('dir', 'base', 0750, 0640),
        array('dir', 'templates', 0750, 0640),
        array('dir', 'includes', 0750, 0640),
        array('dir', 'tests', 0750, 0640),
        array('dir', 'base/docs/en/xml', 0700, 0600),
        array('dir', 'base/docs/developer/develop_xml', 0700, 0600),
        array('dir', 'base/docs/developer/restapi_xml', 0700, 0600),
        array('file', 'install.php', 0700, 0600),
        array('file', 'base/docs/makedocs.php', 0700, 0600),
        array('file', 'config.php', 0700, 0600),

    );

    /**
     * The string holds the base directory for the application
     *
     * @var    string
     * @access private
     */
    private $homeDir;

    /**
     * Constructor
     *
     * @param string $homeDir The base directory for webtemplate.
     */
    public function __construct(string $homeDir)
    {
        $this->homeDir = $homeDir;
    }

    /**
     * This function modifies the permissions of the selected directory and its files
     *
     * @param string $dirName  Directory name.
     * @param string $owner    Directory Owner.
     * @param string $group    Directory Group.
     * @param string $filemode File permissions.
     * @param string $dirmode  Directory Permissions.
     *
     * @return void
     *
     * @access private
     */
    private function fixDirs(
        string $dirName,
        string $owner,
        string $group,
        string $filemode,
        string $dirmode
    ) {
        //global $installConfig;

        chown($dirName, $owner);
        chgrp($dirName, $group);
        chmod($dirName, $dirmode);

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirName),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            if (is_dir($item)) {
                if ((basename($item) != '..') and (basename($item) != '.')) {
                    chown($item, $owner);
                    chgrp($item, $group);
                    chmod($item, $dirmode);
                    //echo $item . ": DIR\n";
                }
            } else {
                chown($item, $owner);
                chgrp($item, $group);
                chmod($item, $filemode);
                //echo $item . ": FILE\n";
            }
        }
    }


    /**
    * This function modifies the permissions of the selected directory and its files
    *
    * @param string $fileName Directory name.
    * @param string $owner    Directory Owner.
    * @param string $group    Directory Group.
    * @param string $filemode File permissions.
    *
    * @return void
    *
    * @access private
    */
    private function fixFile(
        string $fileName,
        string $owner,
        string $group,
        string $filemode
    ) {

        chown($fileName, $owner);
        chgrp($fileName, $group);
        chmod($fileName, $filemode);
    }


    /**
     * This function creates the config.php file
     *
     * @param array $installConfig Configuration array for setting up application.
     *
     * @return mixed boolean true if complete.  Webtemplate error otherwise;
     *
     * @access public
     */
    public function checkInstallConfig(array $installConfig)
    {
        //global $installConfig;

        if (!file_exists($this->homeDir . '/config.php')) {
            $msg = "Please copy config.php.dist to config.php and update";
            $msg .= " to match your installation.\n";
            $msg .=  "Without this information installation cannot take place\n\n";
            return\g7mzr\webtemplate\general\General::raiseError($msg);
        }
        $result = $this->CheckConfigFileConfigured($installConfig);
        if (\g7mzr\webtemplate\general\General::isError($result)) {
            $msg = "Please update config.php to match your installation.\n";
            $msg .= "Without this information installation cannot take place\n\n";
            $msg .= $result->getMessage();
            return\g7mzr\webtemplate\general\General::raiseError($msg);
        }
        return true;
    }


    /**
     * This function creates/modifies the local.conf file
     *
     * @param array $installConfig Configuration array for setting up application.
     *
     * @return nil
     *
     * @access public
     */
    public function createLocalConf(array &$installConfig)
    {

        $errorcreatingfile = false;
        if (file_exists($this->homeDir . "/configs/local.conf")) {
            $filecreated = false;
        } else {
            $handle = @fopen($this->homeDir . "/configs/local.conf", "w");
            if ($handle !== false) {
                fwrite($handle, "[database]\n");
                fwrite($handle, "dbtype  = " . $installConfig['database_type']);
                fwrite($handle, "\n");
                fwrite($handle, "hostspec = " . $installConfig['database_host']);
                fwrite($handle, "\n");
                fwrite($handle, "database = " . $installConfig['database_name']);
                fwrite($handle, "\n");
                fwrite($handle, "username = " . $installConfig['database_user']);
                fwrite($handle, "\n");
                fwrite($handle, "password = ");
                fwrite($handle, $installConfig['database_user_passwd']);
                fwrite($handle, "\n");

                fclose($handle);
                $filecreated = true;
            } else {
                $errorcreatingfile = true;
            }
        }
        if ($errorcreatingfile == true) {
            $msg =  "Error creating local.conf\n\n";
            return\g7mzr\webtemplate\general\General::raiseError($msg);
        } else {
            return $filecreated;
        }
    }



    /**
    * This function creates the Unittest Databse configuration file
    *
    * @param array $installConfig Configuration array for setting up application.
    *
    * @return nil
    *
    * @access public
    */
    public function createTestConf(array &$installConfig)
    {
        $errorcreatingfile = false;
        $handle = @fopen($this->homeDir . "/tests/_data/database.php", "w");
        if ($handle !== false) {
            fwrite($handle, "<?php\n");

            fwrite($handle, '$testdsn["dbtype"] = "');
            fwrite($handle, $installConfig['database_type'] . '";');
            fwrite($handle, "\n");

            fwrite($handle, '$testdsn["hostspec"]  = "');
            fwrite($handle, $installConfig['database_host'] . '";');
            fwrite($handle, "\n");

            fwrite($handle, '$testdsn["databasename"] = "');
            fwrite($handle, $installConfig['database_name'] . '";');
            fwrite($handle, "\n");

            fwrite($handle, '$testdsn["username"] = "');
            fwrite($handle, $installConfig['database_user'] . '";');
            fwrite($handle, "\n");

            fwrite($handle, '$testdsn["password"] = "');
            fwrite($handle, $installConfig['database_user_passwd'] . '";');
            fwrite($handle, "\n");

            fwrite($handle, "\n?>\n");
            fclose($handle);
        } else {
            $errorcreatingfile = true;
        }
        if ($errorcreatingfile == true) {
            $msg =  "Error creating tests/_data/database.php\n\n";
            return\g7mzr\webtemplate\general\General::raiseError($msg);
        } else {
            return true;
        }
    }


    /**
    * This function creates/modifies the preferences.json file
    *
    * @param array $parameters Applications Configuration Parameters.
    *
    * @return boolean Result of creating or updating parameter file
    *
    * @access public
    */
    public function createParameters(array &$parameters)
    {
        //global $installConfig, $parameters;

        if (!file_exists($this->homeDir . "/configs/parameters.json")) {
            return $this->createNewParameters();
        } else {
            return $this->updateParameters($parameters);
        }
    }


    /**
    * This function creates/modifies the preferences.php file
    *
    * @param array $sitePreferences Applications customisation preferences.
    *
    * @return boolean Result of creating or updating Preferences file
    *
    * @access public
    */
    public function createPreferences(array &$sitePreferences)
    {
        //global $installConfig, $sitePreferences;

        if (!file_exists($this->homeDir . "/configs/preferences.json")) {
            return $this->createNewPreferences();
        } else {
            return $this->updatePreferences($sitePreferences);
        }
    }


    /**
    * This function updates the installations file permissions
    *
    * @param array $installConfig Configuration array for setting up application.
    *
    * @return boolean True
    *
    * @access public
    */
    public function setPermissions(array &$installConfig)
    {
        $webservergroupdetails = posix_getgrnam($installConfig['webservergroup']) ;
        if ($webservergroupdetails === false) {
            $msg = "Invalid Web Server Group.  Unable to set file permissions\n\n";
            return\g7mzr\webtemplate\general\General::raiseError($msg);
        }

        $owner = "root";
        $group = $installConfig['webservergroup'];

        foreach ($this->filePermissions as $value) {
            if ($value[0] == 'dir') {
                // Fix the directory permissions
                Filemanager::fixdirs(
                    $this->homeDir . "/" . $value[1],
                    $owner,
                    $group,
                    $value[3],
                    $value[2]
                );
            } else {
                // Fix the file permissions
                Filemanager::fixfile(
                    $this->homeDir . "/" . $value[0],
                    $owner,
                    $group,
                    $value[2]
                );
            }
        }
        return true;
    }

    /**
    * This function deletes all existing compiled templates
    *
    * @return void
    *
    * @access public
    */
    public function deleteCompiledTemplates()
    {

        $dirName = $this->homeDir . "/templates_c/";
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirName),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            if (!is_dir($item)) {
                $file = basename($item);
                if ($file[0] != '.') {
                    unlink($item);
                }
            }
        }
    }

    /**
     * This function creates the Parameters file
     *
     * @return boolean True if parameter file created; False otherwise.
     *
     * @access private
     */
    private function createNewParameters()
    {

        // Create the ne parameters array
        $tempParameters = array();

        // Walk through the newParameters array
        foreach ($this->newParameters as $value) {
            // Create $parameters variable name

            $tempindex1 = $value[0];
            $tempindex2 = $value[1];
            $tempvalue  = $value[2];
            if ($tempindex2 == '') {
                $tempParameters[$tempindex1] = $tempvalue;
            } else {
                $tempParameters[$tempindex1][$tempindex2] = $tempvalue;
            }
        }
        $filename = $this->homeDir . "/configs/parameters.json";
        $jsonstr = json_encode($tempParameters, JSON_PRETTY_PRINT);
        $handle = @fopen($filename, "w");
        if ($handle === false) {
            return false;
        }
        fwrite($handle, $jsonstr);
        fclose($handle);
        if (extension_loaded('Zend OPcache')) {
            if (\opcache_get_status() !== false) {
                \opcache_invalidate(\realpath($filename));
            }
        }
        return true;
    }

    /**
     * This function updates the Parameters file
     *
     * @param array $parameters The existing Parameter array.
     *
     * @return boolean True if file updated
     *
     * @access private
     */
    private function updateParameters(array &$parameters)
    {
        // Create the ne parameters array
        $tempParameters = array();

        // Walk through the newParameters array

        foreach ($this->newParameters as $value) {
            // Add the value of the parameter.
            // Set the default in case it is new
            $tempindex1 = $value[0];
            $tempindex2 = $value[1];
            $tempvalue = $value[2];

            // Check if the 4parameter exists.
            // If it does get the save the current value.
            if ($value[1] == '') {
                if (isset($parameters[$tempindex1])) {
                    $tempvalue = $parameters[$tempindex1];
                }
            } else {
                if (isset($parameters[$tempindex1][$tempindex2])) {
                    $tempvalue = $parameters[$tempindex1][$tempindex2];
                }
            }

            // Create the updated parameters
            if ($tempindex2 == '') {
                $tempParameters[$tempindex1] = $tempvalue;
            } else {
                $tempParameters[$tempindex1][$tempindex2] = $tempvalue;
            }
        }
        $filename = $this->homeDir . "/configs/parameters.json";
        $jsonstr = json_encode($tempParameters, JSON_PRETTY_PRINT);
        $handle = @fopen($filename, "w");
        if ($handle === false) {
            return false;
        }
        fwrite($handle, $jsonstr);
        fclose($handle);
        if (extension_loaded('Zend OPcache')) {
            if (\opcache_get_status() !== false) {
                \opcache_invalidate(\realpath($filename));
            }
        }
        return true;
    }


    /**
     * This function creates the Preference file
     *
     * @return boolean True if preference file created; false otherwise.
     *
     * @access private
     */
    private function createNewPreferences()
    {
        // set up the temp preferences array
        $tempPreferences = array();
        // Walk through the newPreferences array
        foreach ($this->newsitePreferences as $value) {
            $tempindex1 = $value[0];
            $tempindex2 = $value[1];
            $tempvalue  = $value[2];
            $tempPreferences[$tempindex1][$tempindex2] = $tempvalue;
        }
        $filename = $this->homeDir . "/configs/preferences.json";
        $jsonstr = json_encode($tempPreferences, JSON_PRETTY_PRINT);
        $handle = @fopen($filename, "w");
        if ($handle === false) {
            return false;
        }
        fwrite($handle, $jsonstr);
        fclose($handle);
        if (extension_loaded('Zend OPcache')) {
            if (\opcache_get_status() !== false) {
                \opcache_invalidate(\realpath($filename));
            }
        }
        return true;
    }

    /**
     * This function updates the Preferences file
     *
     * @param array $sitePreferences Array containing the preferences a user can
     *                               change.
     *
     * @return boolean True if Preference File updated; False otherwise
     *
     * @access private
     */
    private function updatePreferences(array &$sitePreferences)
    {
       // set up the temp preferences array
        $tempPreferences = array();
        // Walk through the newPreferences array
        foreach ($this->newsitePreferences as $value) {
            $tempindex1 = $value[0];
            $tempindex2 = $value[1];
            $tempvalue  = $value[2];

            if (isset($sitePreferences[$tempindex1][$tempindex2])) {
                $tempvalue = $sitePreferences[$tempindex1][$tempindex2];
            }
            $tempPreferences[$tempindex1][$tempindex2] = $tempvalue;
        }
        $filename = $this->homeDir . "/configs/preferences.json";
        $jsonstr = json_encode($tempPreferences, JSON_PRETTY_PRINT);
        $handle = @fopen($filename, "w");
        if ($handle === false) {
            return false;
        }
        fwrite($handle, $jsonstr);
        fclose($handle);
        if (extension_loaded('Zend OPcache')) {
            if (\opcache_get_status() !== false) {
                \opcache_invalidate(\realpath($filename));
            }
        }
        return true;
    }


    /**
     * This function checks that config file is configured
     *
     * @param array $installConfig Configuration array for setting up application.
     *
     * @return mixed Boolean true if all set.  Webtemplate error object otherwise
     *
     * @access private
     */
    private function checkConfigFileConfigured(array $installConfig)
    {

        // Set Variables
        $configOk = true;
        $msg = "";

        // Step through the $installConfig array to check if all elements are set
        foreach ($installConfig as $key => $value) {
            if ($value == '') {
                $configOk = false;
                $msg .= "\$installConfig[$key] is not set\n";
            }
        }

        if ($configOk == true) {
            return $configOk;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($msg);
        }
    }

    /**
     * This function creates the secret key used by the application to encrypt and
     * decrypt tokens
     *
     * @return mixed Secret key on success. Webtemplate error object otherwise
     */
    private function createSecretkey()
    {
        $keyok = true;
        $secret = "";
        $msg = "";

        $chars = '0123456789abcdef';
        $randstring = '';
        for ($i = 0; $i < 64; $i++) {
            $randstring .= $chars[rand(0, strlen($chars) - 1)];
        }
        $secret = $randstring;

        if ($keyok == true) {
            return $secret;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($msg);
        }
    }
}
