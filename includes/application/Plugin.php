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
 * @copyright (c) 2020, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\application;

use \g7mzr\webtemplate\application\plugins\InitiatePlugins;
use \g7mzr\webtemplate\application\plugins\PluginConst;

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
     * @param string                                     $plugindir The Directory containing the Webtemplate plugins.
     * @param \g7mzr\webtemplate\application\Application $app       Pointer to application class.
     *
     * @access public
     */
    public function __construct(
        string $plugindir,
        \g7mzr\webtemplate\application\Application $app
    ) {
        $this->plugindir = $plugindir;
        $this->pluginPointers = InitiatePlugins::initatePlugins($plugindir, $app);
    }

    /**
     * Get the names and version numbers of all active plugins
     *
     * @return array Of Stings containing plugin names and versions
     *
     * @access public
     */
    public function getPluginVersionInformation()
    {
        $resultArray = array();
        foreach ($this->pluginPointers as $key => $pointer) {
            $resultArray[$key] = $pointer->getVersionInformation();
        }
        return $resultArray;
    }


    /**
     * Get the names and version numbers of all active plugins
     *
     * @return array Of Stings containing plugin names and versions
     *
     * @access public
     */
    public function getPluginVersionStr()
    {
        $resultArray = array();
        foreach ($this->pluginPointers as $key => $pointer) {
            $resultArray[$key] = $pointer->getVersionInformation(PluginConst::GET_PLUGIN_VERSION_STRING);
        }
        return $resultArray;
    }

    /**
     * processHook
     *
     * processHook scans through all the available plugins and if a function with
     * the hooks name is present it runs the function.
     *
     * @param string $hookname  The name of the hook being called.
     * @param array  $hookparam An array holding the parameters used by the hook.
     *
     * @return void
     *
     * @access public
     */
    public function processHook(string $hookname, array &$hookparam)
    {
        foreach ($this->pluginPointers as $pointer) {
            if (method_exists($pointer, $hookname)) {
                $result = $pointer->$hookname($hookparam);
            }
        }
    }
}
