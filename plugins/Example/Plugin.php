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

namespace g7mzr\webtemplate\plugins\Example;

use \g7mzr\webtemplate\application\plugins\PluginBaseClass;

/**
 * Description of Example
 *
 * @author sandy
 */
class Plugin extends PluginBaseClass
{
    /**
     * Constant: Default Plugin name
     */
    const PLUGIN_NAME = "Example Plugin";

    /**
     * Constant: Default Plugin Version
     */
    const PLUGIN_VERSION = "0.1.0";

    /**
     * Constant: The home directory of the current plugin
     */
    const PLUGIN_DIR = __DIR__;

    /**
     * hook_about_display Hook
     *
     * @param array $hookparam An array holding the parameters that can be used by
     *                         the hook. In some cases it may be an empty array. The
     *                         array is passed by pointer to allow updated values to
     *                         be returned to the calling module/class.
     *
     * @return void
     *
     * @access public
     *
     */
    public function hookAboutDisplay(array &$hookparam)
    {
        $fieldNames = array(
            'user_id',
            'user_name',
            'user_realname',
            'user_email',
            'user_enabled',
            'user_disable_mail',
            'date(last_seen_date)',
            'date(passwd_changed) as passwd_changed'
        );
        $result = $this->app->db()->dbselectmultiple(
            'users',
            $fieldNames,
            array(),
            'user_id'
        );
        if (!\g7mzr\db\common\Common::isError($result)) {
            $numberOfUsers = $this->app->db()->rowCount();
            $this->app->tpl()->assign("REGISTEREDUSERS", $numberOfUsers);
            $templatefilename = __DIR__ . "/templates/about.tpl";
            $this->app->tpl()->addPluginTemplate("abouthook", $templatefilename);
        }
    }
}
