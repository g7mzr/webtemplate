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
 * @copyright (c) 2012, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace webtemplate\application;

/**
 * Plugin class identifies, activates and manages access to Webtemplate Plugins
 */
class Plugin
{
    /**
     * property: $plugindir
     * Location of plugins
     *
     * @var string
     * @access private
     */
    private $plugindir = "";

    /**
     * property: $active_plugins
     * Number of active plugins
     *
     * @var integer
     * @access private
     */
    private $active_plugins = 0;

    /**
     * property: pluginDirectories
     * List of active plugins directories
     *
     * @var array
     * @access private
     */
    private $pluginDirectories = array();

    /**
     * Property: pluginNames
     * List of active plugin names
     *
     * @var array
     * @access private
     */
    private $pluginNames = array();

    /**
     * Property: pluginPointers
     * Array of pointer to active plugin classes
     *
     * @var array
     * @access private
     */
    private $pluginPointers = array();

    /**
     * __construct()
     *
     * @param string $plugindir The Directory containing the Webtemplate plugins
     *
     * @access public
     */
    public function __construct(string $plugindir)
    {
        $this->active_plugins = 0;
        $this->plugindir = $plugindir;
        $this->scanPluginDir();
        //error_log($this->active_plugins);
        error_log(json_encode($this->pluginDirectories));
        error_log(json_encode($this->pluginNames));
    }

    /*
     * Scan for Active Plugins
     *
     * @return boolean True if scan completed
     *
     * @access private
     */
    private function scanPluginDir()
    {

        $installedPluginsdir = array();
        $dirHandle = @opendir($this->plugindir);
        if ($dirHandle !== false) {
            while (($file = readdir($dirHandle)) !== false) {
                $currentPlugin = $this->plugindir . '/' . $file;
                if (is_dir($currentPlugin)) {
                    if ($file != "." && $file != "..") {
                        $pluginDisableFile = $currentPlugin . "/disable";
                        if (!file_exists($pluginDisableFile)) {
                            $pluginname = basename($currentPlugin);
                            $installedPluginsdir[$pluginname] = $currentPlugin;
                        }
                    }
                }
            }
        }
        $this->pluginDirectories = $installedPluginsdir;
    }

    /**
     * Get active plugins
     *
     * This function returns the number of active plugins
     *
     * @return integer Number pf active plugins
     *
     * @access public
     */
    public function getActivePlugins()
    {
        return $this->active_plugins;
    }

    /**
     * Get list of active plugins
     *
     * This function returns the names of all the active plugins
     *
     * @return array Names of active plugins
     *
     * @access public
     */
    public function getActivePluginsNames()
    {
        return $this->pluginNames;
    }

}
