<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Application/Plugin
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2020, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\application\plugins;

use g7mzr\webtemplate\application\exceptions\AppException;
use g7mzr\webtemplate\application\plugins\PluginConst;

/**
 * The Base Class that all Webtemplate plugins are built on
 *
 * @author sandy
 */
class PluginBaseClass
{
    /**
     * Constant: Default Plugin name
     */
    public const PLUGIN_NAME = "PLUGIN BASE CLASS";

    /**
     * Constant: Default Plugin Version
     */
    public const PLUGIN_VERSION = "1.0.0";

    /**
     * Constant: The default home directory of the current plugin
     */
    public const PLUGIN_DIR = __DIR__;

    /**
     * Property: app
     *
     * @var \g7mzr\webtemplate\application\Application
     * @access protected
     */
    protected $app = null;

    /**
     * __construct()
     *
     * @param \g7mzr\webtemplate\application\Application $app Pointer to application class.
     *
     * @access public
     */
    public function __construct(\g7mzr\webtemplate\application\Application $app)
    {
        $this->app = $app;
    }

    /**
     * Function to return version info
     *
     * @param integer $versionData Defines what information is to be returned.
     *
     * @return mixed Array containing Version Information or a string containing the
     *               requested element or full string
     *
     * @throws AppException If invalid version data format is requested.
     *
     * @access public
     */
    public function getVersionInformation(int $versionData = PluginConst::GET_PLUGIN_INFO)
    {
        $versionArray = array(
            "name" => $this::PLUGIN_NAME,
            "version" => $this::PLUGIN_VERSION
        );

        $versionString = sprintf(
            "Name: %s Version: %s",
            $this::PLUGIN_NAME,
            $this::PLUGIN_VERSION
        );

        switch ($versionData) {
            case PluginConst::GET_PLUGIN_INFO:
                $result = $versionArray;
                break;
            case PluginConst::GET_PLUGIN_NAME:
                $result = $this::PLUGIN_NAME;
                break;
            case PluginConst::GET_PLUGIN_VERSION:
                $result = $this::PLUGIN_VERSION;
                break;
            case PluginConst::GET_PLUGIN_VERSION_STRING:
                $result = $versionString;
                break;
            default:
                throw new AppException("Plugin Version Information: Invalid Option");
        }
        return $result;
    }

    /**
     * getDBSchema
     *
     * This function returns the fully qualified path for the file containing the
     * database scheme applicable to the plugin.
     *
     * @return string Containing the fully qualified schema filename or empty if file
     *                does not exist.
     *
     * @access public
     */
    public function getDBSchema()
    {
        $filename = $this::PLUGIN_DIR . "/schema.json";
        if (!file_exists($filename)) {
            return "";
        }
        return $filename;
    }

    /**
     * getDBSchema
     *
     * This function returns the fully qualified path for the file containing the
     * the default database data applicable to the plugin.
     *
     * @return string Containing the fully qualified filename or empty string if file
     *                does not exist.
     *
     * @access public
     */
    public function getDBData()
    {
        $filename = $this::PLUGIN_DIR . "/data.json";
        if (!file_exists($filename)) {
            return "";
        }
        return $filename;
    }
}
