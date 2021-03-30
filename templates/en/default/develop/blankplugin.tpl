{***********************************************************************************

 This file is part of Webtemplate.

 copyright (c) 2020 Sandy McNeil <g7mzrdev@gmail.com>

 For the full copyright and license information, please view the LICENSE
 file that was distributed with this source code.

***********************************************************************************}
<?php
/**
 * This file has been created using Webtamplate setup.php.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Plugin
 * Sandy McNeil <g7mzrdev@gmail.com>
 * @author   Your Name <email Address>
 * @copyright (c) 2020, Sandy McNeil
 * @license Licence Conditions
 *
 */

namespace g7mzr\webtemplate\plugins\{$PLUGINNAME};

use \g7mzr\webtemplate\application\plugins\PluginBaseClass;

/**
 * Description of {$PLUGINNAME}
 *
 * @author Your Name <email Address>
 */
class Plugin extends PluginBaseClass
{
    /**
     * Constant: Default Plugin name
     */
    const PLUGIN_NAME = "{$PLUGINNAME}";

    /**
     * Constant: Default Plugin Version
     */
    const PLUGIN_VERSION = "0.1.0";

    /**
     * Constant: The home directory of the current plugin
     */
    const PLUGIN_DIR = __DIR__;

    /**
     * HOOK Functions.
     *
     * See documentation details on how to use the hooks.
     * The list below has all currently active hooks.  Remove the ones that are not
     * required for your plugin
     */

    /**
     * hook_about_display Hook
     *
     * @param array $hookparam An array holding the parameters used by the hook.
     *
     * @return void
     *
     * @access public
     *
     */
    public function hookAboutDisplay(array &$hookparam)
    {

    }
}
