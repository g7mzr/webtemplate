<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Configuration
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace webtemplate\config;

/**
 * Configuration Class
 *
 **/
class Configure
{
    /**
     * Location of Configuration Directory
     *
     * @var    array
     * @access protected
     */
    protected $configDir = null;

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
     * Property Application Parameters Menu
     *
     * @var array
     * @access protected
     */
    protected $parametersmenu;

    /**
     * Property Application User Preferences Menu
     *
     * @var array
     * @access protected
     */
    protected $userprefmenu;

     /**
     * Property Application Admin  Menu
     *
     * @var array
     * @access protected
     */
    protected $adminmenu;

    /**
     * Constructor for the edit user class.
     *
     * @param string $configDir Location of the parameter file.
     *
     * @access public
     */
    public function __construct(string $configDir)
    {

        $this->configDir = $configDir;

        // Load global parameters and preferences
        $parametersfile = $configDir . "/parameters.json";
        $parameterstr = file_get_contents($parametersfile);
        $this->parameters = json_decode($parameterstr, true);

        $preferencesfile = $configDir . "/preferences.json";
        $preferencesstr = file_get_contents($preferencesfile);
        $this->preferences = json_decode($preferencesstr, true);

        // Load the Main menu
        $mainmenufilename = $configDir . '/menus/mainmenu.json';
        $mainmenustr = file_get_contents($mainmenufilename);
        $this->mainmenu = json_decode($mainmenustr, true);

        // Load the Parameter Settings Menu
        $parammenufilename = $configDir . '/menus/parammenu.json';
        $parammenustr = file_get_contents($parammenufilename);
        $this->parametersmenu = json_decode($parammenustr, true);

        // Load the Users Preferences Menu
        $userprefmenufilename = $configDir . '/menus/userprefmenu.json';
        $userprefmenustr = file_get_contents($userprefmenufilename);
        $this->userprefmenu = json_decode($userprefmenustr, true);

        // Load the Main Admin Menu
        $adminmenufilename = $configDir . '/menus/adminmenu.json';
        $adminmenustr = file_get_contents($adminmenufilename);
        $this->adminmenu = json_decode($adminmenustr, true);
    } // end constructor


    /**
     * This function returns the data in the specified key or null if the key does
     * not exist.  Dot notation can be used.  The first name in the path should be
     * param for $parameters of pref for $preferences.
     *
     * @param string $config The Parameter key being requested.
     *
     * @throws \InvalidArgumentException If the Parameter key is not a dot
     *                                   notation string.
     * @throws \InvalidArgumentException If 1st key item is not param or pref .
     * @throws \InvalidArgumentException If more than 4 items in the key.
     *
     * @return mixed The vale of the Parameter requested or null if it does not exist
     *
     * @access public
     */
    final public function read(string $config = '')
    {

        // Set up an array for working with.
        $configarray = array();
        $configarray['parameters'] = $this->parameters;
        $configarray['preferences'] = $this->preferences;

        // If the Key is null return the whole array
        if ($config === '') {
            return $configarray;
        }

        // Return the individual result
        if (!is_numeric($config)) {
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
     * @param string $config The Parameter key being updated.
     * @param mixed  $data   The value of the parameter being updated.
     *
     * @throws \InvalidArgumentException If the Parameter key is not a dot
     *                                   notation string.
     * @throws \InvalidArgumentException If param list is more than 4 items.
     * @throws \InvalidArgumentException If pref list is more than 4 items.
     * @throws \InvalidArgumentException If 1st key item is not param or pref .
     *
     * @return boolean Always returns true
     * @access public
     */
    final public function write(string $config, $data)
    {
        if (!is_numeric($config)) {
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
        return true;
    }

    /**
     * This function checks if a configuration value exists and is not null
     * Dot notation can be used.
     *
     * @param string $config The Parameter key being requested.
     *
     * @return boolean True if the parameter exists False otherwise.
     * @access public
     */
    final public function check(string $config)
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
     * @param string $config The Parameter key to be deleted.
     *
     * @return boolean True if the parameter exists and is deleted.
     * @access public
     */
    final public function delete(string $config)
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
     * @param array $parameters Parameters to reload into the class.
     *
     * @return boolean Returns true
     */
    final public function reloadParams(array $parameters)
    {
        $this->parameters = $parameters;
        return true;
    }


    /**
     * This function is used to reload the preferences into the class
     *
     * @param array $preferences Preferences to reload into the class.
     *
     * @return boolean Returns true
     */
    final public function reloadpref(array $preferences)
    {
        $this->preferences = $preferences;
        return true;
    }


    /**
    * Save the current parameters to a file called parameters.json  The
    * file is located in the $config directory,
    *
    * @param string $configDir Location of the parameter file.
    *
    * @return boolean true if parameterfile saved false otherwise
    *
    * @access public
    */
    final public function saveParams(string $configDir)
    {

        $filename = $configDir . "/parameters.json";
        $jsonstr = json_encode($this->parameters, JSON_PRETTY_PRINT);
        if (!is_writable($filename)) {
            return false;
        }
        $result = file_put_contents($filename, $jsonstr);
        if ($result === false) {
            return false;
        }
        if (extension_loaded('Zend OPcache')) {
            if (\opcache_get_status() !== false) {
                \opcache_invalidate(\realpath($filename));
            }
        }
        return true;
    }

    /**
    * Save the current preferences to a file called preferences.json.  The
    * file is located in the $config directory,
    *
    * @param string $configDir Location of the parameter file.
    *
    * @return boolean true if preferences file saved false otherwise
    *
    * @access public
    */
    final public function savePrefs(string $configDir)
    {
        $filename = $configDir . "/preferences.json";
        $jsonstr = json_encode($this->preferences, JSON_PRETTY_PRINT);
        if (!is_writable($filename)) {
            return false;
        }
        $result = \file_put_contents($filename, $jsonstr);
        if ($result === false) {
            return false;
        }
        if (extension_loaded('Zend OPcache')) {
            if (\opcache_get_status() !== false) {
                \opcache_invalidate(\realpath($filename));
            }
        }
        return true;
    }

    /**
     * This function returns the menu specified in menu
     *
     * @param string $menu The menu being requested.
     *
     * @return mixed The menu being requested or an empty array if it does not exist
     * @access public
     */
    final public function readMenu(string $menu)
    {
        // Set up the super array containing all menus
        $menulist = array();
        $menulist['mainmenu'] = $this->mainmenu;
        $menulist['parampagelist'] = $this->parametersmenu;
        $menulist['userprefpagelist'] = $this->userprefmenu;
        $menulist['adminpagelist'] = $this->adminmenu;

        // Check if the requested menu exists and return it.
        if (array_key_exists($menu, $menulist) == true) {
            return $menulist[$menu];
        } else {
            return array();
        }
    }
}
