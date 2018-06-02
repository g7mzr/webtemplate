<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\config;

/**
 * Configuration Class
 *
 * @category Webtemplate
 * @package  Configuration
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class Configure
{
    /**
     * Database MDB2 Database Connection Object
     *
     * @var    array
     * @access protected
     */
    protected $db = null;

    /**
     * Application configuration parameters
     *
     * @var    array
     * @access protected
     */
    protected $parameters;

    /**
     * Application preference settings
     *
     * @var    array
     * @access protected
     */
    protected $preferences;

    /**
     * Property Application Main Menu
     *
     * @var array
     * @access protected
     */
    protected $mainmenu;

    /**
     * Constructor for the edit user class.
     *
     * @param array $db MDB2 Database Connection Object
     *
     * @access public
     */
    public function __construct($db)
    {

        // Load global parameters and preferences
        include __DIR__ . '/../../configs/parameters.php';
        $this->parameters = $parameters;

        include  __DIR__ . '/../../configs/preferences.php';
        $this->preferences = $sitePreferences;

        include __DIR__ . '/../../configs/menus/mainMenu.php';
        $this->mainmenu = $mainMenu;

        // Set up the database access.  Not used at present.
        $this->db       = $db;
    } // end constructor


    /**
     * This function returns the data in the specified key or null if the key does
     * not exist.  Dot notation can be used.  The first name in the path should be
     * param for $parameters of pref for $preferences.
     *
     * @param string $config The Parameter key being requested
     *
     * @return mixed The vale of the Parameter requested or null if it does not exist
     * @access public
     */
    final public function read($config = null)
    {

        // Set up an array for working with.
        $configarray = array();
        $configarray['parameters'] = $this->parameters;
        $configarray['preferences'] = $this->preferences;

        // If the Key is null return the whole array
        if ($config === null) {
            return $configarray;
        }

        // Return the individual result
        if (is_string($config)) {
            $key = explode(".", $config);
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid Argument %s, should be a dot seperated path",
                    $config
                )
            );
        }

        if ($key[0] == "param") {
            $key[0] = "parameters";
        } elseif ($key[0] == "pref") {
            $key[0] = "preferences";
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid Argument %s, should be param or pref",
                    $key[0]
                )
            );
        }


        switch (count($key)) {
            case 1:
                return isset($configarray[$key[0]])
                ? $configarray[$key[0]]
                : null;
            case 2:
                return isset($configarray[$key[0]][$key[1]])
                ? $configarray[$key[0]][$key[1]]
                : null;
            case 3:
                return isset($configarray[$key[0]][$key[1]][$key[2]])
                ? $configarray[$key[0]][$key[1]][$key[2]]
                : null;
            case 4:
                return isset($configarray[$key[0]][$key[1]][$key[2]][$key[3]])
                ? $configarray[$key[0]][$key[1]][$key[2]][$key[3]]
                : null;
            default:
                throw new \InvalidArgumentException(
                    sprintf(
                        "Invalid Argument %s, path must contain less than 5 items",
                        $config
                    )
                );
        }
    }


    /**
     * This function returns the details of a single XXX
     *
     * @param string $config The Parameter key being updated
     * @param mixed  $data   The value of the parameter being updated
     *
     * @return boolean Always returns true
     * @access public
     */
    final public function write($config, $data)
    {
        if (is_string($config)) {
            $key = explode(".", $config);
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid Argument %s, should be a dot seperated path",
                    $config
                )
            );
        }

        if ($key[0] == "param") {
            switch (count($key)) {
                case 2:
                    $this->parameters[$key[1]] = $data;
                    break;
                case 3:
                    $this->parameters[$key[1]][$key[2]] = $data;
                    break;
                case 4:
                    $this->parameters[$key[1]][$key[2]][$key[3]] = $data;
                    break;
                default:
                    throw new \InvalidArgumentException(
                        sprintf(
                            "Invalid Argument %s, path must contain less than 5 items",
                            $config
                        )
                    );
            }
        } elseif ($key[0] == "pref") {
            switch (count($key)) {
                case 2:
                    $this->preferences[$key[1]] = $data;
                    break;
                case 3:
                    $this->preferences[$key[1]][$key[2]] = $data;
                    break;
                case 4:
                    $this->preferences[$key[1]][$key[2]][$key[3]] = $data;
                    break;
                default:
                    throw new \InvalidArgumentException(
                        sprintf(
                            "Invalid Argument %s, path must contain less than 5 items",
                            $config
                        )
                    );
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid Argument %s, should be param or pref",
                    $key[0]
                )
            );
        }
    }

    /**
     * This function checks if a configuration value exists and is not null
     * Dot notation can be used.
     *
     * @param string $config The Parameter key being requested
     *
     * @return boolean True if the parameter exists False otherwise.
     * @access public
     */
    final public function check($config)
    {
        $result = false;
        if (empty($config)) {
            return false;
        }
        if ($this->read($config) !== null) {
            $result = true;
        }
        return $result;
    }


    /**
     * This function deletes a configuration value
     * Dot notation can be used.
     *
     * @param string $config The Parameter key to be deleted
     *
     * @return boolean True if the parameter exists and is deleted.
     * @access public
     */
    final public function delete($config)
    {
        // Check if the entered key exists.
        if ($this->check($config) == false) {
            return false;
        }

        $key = explode(".", $config);

        if ($key[0] == "param") {
            switch (count($key)) {
                case 2:
                    unset($this->parameters[$key[1]]);
                    break;
                case 3:
                    unset($this->parameters[$key[1]][$key[2]]);
                    break;
                case 4:
                    unset($this->parameters[$key[1]][$key[2]][$key[3]]);
                    break;
            }
        } elseif ($key[0] == "pref") {
            switch (count($key)) {
                case 2:
                    unset($this->preferences[$key[1]]);
                    break;
                case 3:
                    unset($this->preferences[$key[1]][$key[2]]);
                    break;
            }
        }
        return true;
    }

    /**
     * This function is used to reload the parameters into the class
     *
     * @param array $parameters Parameters to reload into the classs
     *
     * @return boolean Returns true
     */
    final public function reloadParams($parameters)
    {
        $this->parameters = $parameters;
        return true;
    }


    /**
     * This function is used to reload the preferences into the class
     *
     * @param array $preferences preferences to reload into the classs
     *
     * @return boolean Returns true
     */
    final public function reloadpref($preferences)
    {
        $this->preferences = $preferences;
        return true;
    }


    /**
    * Save the current parameters to a file called parameters.php.  The
    * file is located in the $config directory,
    *
    * @param string $configDir Location of the parameter file
    *
    * @return boolean true if parameterfile saved false otherwise
    *
    * @access public
    */
    final public function saveParams($configDir)
    {
        // Open the parameters.php file.. If there is a problem fail.
        if ($handle = @fopen($configDir . '/parameters.php', "w")) {
            // write the opening tag
            fwrite($handle, "<?php\n");

            /* Write the REQUIRED section of the parameters array */
            $tempstr = $this->parameters['urlbase'];
            fwrite($handle, "\$parameters['urlbase'] = '$tempstr';\n");

            $tempstr = $this->parameters['maintainer'];
            fwrite($handle, "\$parameters['maintainer'] = '$tempstr';\n");

            $tempstr = $this->parameters['docbase'];
            fwrite($handle, "\$parameters['docbase'] = '$tempstr';\n");

            $tempstr = $this->parameters['cookiedomain'];
            fwrite($handle, "\$parameters['cookiedomain'] = '$tempstr';\n");

            $tempstr = $this->parameters['cookiepath'];
            fwrite($handle, "\$parameters['cookiepath'] = '$tempstr';\n");

            /* Write the ADMIN section of the parameters array */
            $tempstr = $this->parameters['admin']['logging'];
            fwrite(
                $handle,
                "\$parameters['admin']['logging'] = '$tempstr';\n"
            );

            $tempstr = $this->parameters['admin']['logrotate'];
            fwrite(
                $handle,
                "\$parameters['admin']['logrotate'] = '$tempstr';\n"
            );

            if ($this->parameters['admin']['newwindow'] == true) {
                fwrite(
                    $handle,
                    "\$parameters['admin']['newwindow'] = true;\n"
                );
            } else {
                fwrite(
                    $handle,
                    "\$parameters['admin']['newwindow'] = false;\n"
                );
            }

            $tempstr = $this->parameters['admin']['maxrecords'];
            fwrite(
                $handle,
                "\$parameters['admin']['maxrecords'] = '$tempstr';\n"
            );

            /* Write the USERS section of the parameters array */
            if ($this->parameters['users']['newaccount']) {
                fwrite(
                    $handle,
                    "\$parameters['users']['newaccount'] = true;\n"
                );
            } else {
                fwrite(
                    $handle,
                    "\$parameters['users']['newaccount'] = false;\n"
                );
            }

            if ($this->parameters['users']['newpassword'] == true) {
                fwrite(
                    $handle,
                    "\$parameters['users']['newpassword'] = true;\n"
                );
            } else {
                fwrite(
                    $handle,
                    "\$parameters['users']['newpassword'] = false;\n"
                );
            }

            $tempstr = $this->parameters['users']['regexp'];
            fwrite(
                $handle,
                "\$parameters['users']['regexp'] = '$tempstr';\n"
            );

            $tempstr = $this->parameters['users']['regexpdesc'];
            fwrite(
                $handle,
                "\$parameters['users']['regexpdesc'] = '$tempstr';\n"
            );

            $tempstr = $this->parameters['users']['passwdstrength'];
            fwrite(
                $handle,
                "\$parameters['users']['passwdstrength'] = '$tempstr';\n"
            );

            $tempstr = $this->parameters['users']['passwdage'];
            fwrite(
                $handle,
                "\$parameters['users']['passwdage'] = '$tempstr';\n"
            );


            if ($this->parameters['users']['autocomplete'] == true) {
                fwrite(
                    $handle,
                    "\$parameters['users']['autocomplete'] = true;\n"
                );
            } else {
                fwrite(
                    $handle,
                    "\$parameters['users']['autocomplete'] = false;\n"
                );
            }

            $tempstr = $this->parameters['users']['autologout'];
            fwrite(
                $handle,
                "\$parameters['users']['autologout'] = '$tempstr';\n"
            );

            // Write the e-mail section of the Parameters Array
            $tempstr = $this->parameters['email']['smtpdeliverymethod'];
            fwrite(
                $handle,
                "\$parameters['email']['smtpdeliverymethod'] = '$tempstr';\n"
            );

            $tempstr = $this->parameters['email']['emailaddress'];
            fwrite(
                $handle,
                "\$parameters['email']['emailaddress'] = '$tempstr';\n"
            );

            $tempstr = $this->parameters['email']['smtpserver'];
            fwrite(
                $handle,
                "\$parameters['email']['smtpserver'] = '$tempstr';\n"
            );

            $tempstr = $this->parameters['email']['smtpusername'];
            fwrite(
                $handle,
                "\$parameters['email']['smtpusername'] = '$tempstr';\n"
            );

            $tempstr = $this->parameters['email']['smtppassword'];
            fwrite(
                $handle,
                "\$parameters['email']['smtppassword'] = '$tempstr';\n"
            );

            if ($this->parameters['email']['smtpdebug'] == true) {
                fwrite($handle, "\$parameters['email']['smtpdebug'] = true;\n");
            } else {
                fwrite($handle, "\$parameters['email']['smtpdebug'] = false;\n");
            }

            // Write the closing tag
            fwrite($handle, "?>\n");
            fflush($handle);

            // Close the file and return true
            fclose($handle);

            if (extension_loaded('Zend OPcache')) {
                if (\opcache_get_status() !== false) {
                    \opcache_invalidate(\realpath($configDir . '/parameters.php'));
                }
            }
            return true;
        } else {
            // return false as the file could not be opened
            return false;
        }
    }

    /**
    * Save the current preferences to a file called preferences.php.  The
    * file is located in the $config directory,
    *
    * @param string $configDir Location of the parameter file
    *
    * @return boolean true if parameterfile saved false otherwise
    *
    * @access public
    */
    final public function savePrefs($configDir)
    {
        if ($handle = @fopen($configDir . '/preferences.php', "w")) {
            // Write the file
            fwrite($handle, "<?php\n");
            $tmpstr = $this->preferences['theme']['value'];
            fwrite(
                $handle,
                "\$sitePreferences['theme']['value'] = '$tmpstr';\n"
            );

            // Save binary data by converting value into string
            if ($this->preferences['theme']['enabled'] == true) {
                $tempstr = "true";
            } else {
                $tempstr = "false";
            }
            fwrite(
                $handle,
                "\$sitePreferences['theme']['enabled'] = $tempstr;\n"
            );

            // Save binary data by converting value into string
            if ($this->preferences['zoomtext']['value'] == true) {
                $tempstr = "true";
            } else {
                $tempstr = "false";
            }
            fwrite(
                $handle,
                "\$sitePreferences['zoomtext']['value'] = $tempstr;\n"
            );

            // Save binary data by converting value into string
            if ($this->preferences['zoomtext']['enabled'] == true) {
                $tempstr = "true";
            } else {
                $tempstr = "false";
            }
            fwrite(
                $handle,
                "\$sitePreferences['zoomtext']['enabled'] = $tempstr;\n"
            );

            $tmpstr = $this->preferences['displayrows']['value'];
            fwrite(
                $handle,
                "\$sitePreferences['displayrows']['value'] = '$tmpstr';\n"
            );

            // Save binary data by converting value into string
            if ($this->preferences['displayrows']['enabled'] == true) {
                $tempstr = "true";
            } else {
                $tempstr = "false";
            }
            fwrite(
                $handle,
                "\$sitePreferences['displayrows']['enabled'] = $tempstr;\n"
            );

            // Finish and close the file
            fwrite($handle, "?>\n");
            fflush($handle);
            fclose($handle);

            if (extension_loaded('Zend OPcache')) {
                if (\opcache_get_status() !== false) {
                    \opcache_invalidate(\realpath($configDir . '/preferences.php'));
                }
            }
            return true;
        } else {
            // An error was encountered creating the file
            return false;
        }
    }

    /**
     * This function returns the menu specified in menu
     *
     * @param string $menu The menu being requested
     *
     * @return mixed The menu being requested or an empty array if it does not exist
     * @access public
     */
    final public function readMenu($menu)
    {
        // Sit up the supper array containing all menus
        $menulist = array();
        $menulist['mainmenu'] = $this->mainmenu;

        // Check if the requested menu exists and return it.
        if (array_key_exists($menu, $menulist) == true) {
            return $menulist[$menu];
        } else {
            return array();
        }
    }

}
