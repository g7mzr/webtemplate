<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Plugins
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2020, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\application\plugins;

/**
 * Initiate Plugins is a static class used to register all active plugins for
 * webtemplate
 *
 * @author sandy
 */
class InitiatePlugins
{
    /**
     * initiatePlugins is the main function used by Plugin.php to initialise the
     * Plugin infrastructure for webtemplate
     *
     * @param string                                     $parentdir The Directory containing the Webtemplate plugins.
     * @param \g7mzr\webtemplate\application\Application $app       Pointer to application class.
     *
     * @return array Containing the name and pointer of each plugin.
     *
     * @access public
     */
    public static function initatePlugins(
        string $parentdir,
        \g7mzr\webtemplate\application\Application $app
    ) {
        // Initiate the local variables needed to create plugin list
        $activePluginNames = array();
        $activePlugins = array();

        // Scan the parent plugin directory for active plugins
        $scanned_directory = array_diff(scandir($parentdir), array('..', '.'));
        foreach ($scanned_directory as $filename) {
            $plugindir = $parentdir . '/' . $filename;
            if (!\is_dir($plugindir)) {
                continue;
            }
            $testDisabled =  $plugindir . "/disable";
            if (\file_exists($testDisabled)) {
                continue;
            }
            $testClassFileExists = $plugindir . "/Plugin.php";
            if (!\file_exists($testClassFileExists)) {
                continue;
            }
            $activePluginNames[] = $filename;
        }

        // Initalise all the active plugins.
        $pluginnamespace = '\\g7mzr\\webtemplate\\plugins\\';
        foreach ($activePluginNames as $plugin) {
            $classname = $pluginnamespace . $plugin . '\\Plugin';
            try {
                $activeplugin = new $classname($app);
                $activePlugins[$plugin] = $activeplugin;
            } catch (\Throwable $ex) {
                error_log("plugin: " . $classname . " Not Found");
                error_log($ex->getMessage());
            }
        }

        // Return the pointers to the active plugin instances.
        return $activePlugins;
    }
}
