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

use g7mzr\webtemplate\application\plugins\PluginBaseClass;
use g7mzr\webtemplate\plugins\Example\lib\Functions;
use g7mzr\webtemplate\plugins\Example\lib\Version;

/**
 * Description of Example
 *
 * @author sandy
 */
class Plugin extends PluginBaseClass
{
    /**
     * Property: functions
     *
     * @var \g7mzr\webtemplate\plugins\Example\lib\Functions
     * @access protected
     */
    protected $functions = null;

    /**
     * __construct()
     *
     * @param \g7mzr\webtemplate\application\Application $app Pointer to application class.
     *
     * @access public
     */
    public function __construct(\g7mzr\webtemplate\application\Application $app)
    {
        parent::__construct($app);
        $this->PLUGIN_DIR = __DIR__;
        $this->PLUGIN_NAME = Version::$PLUGIN_NAME;
        $this->PLUGIN_VERSION = Version::$PLUGIN_VERSION;
        $this->functions = new Functions($app);
    }

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
        $numberOfUsers = $this->functions->noOfUsers();
        if (!\g7mzr\db\common\Common::isError($numberOfUsers)) {
            $this->app->tpl()->assign("REGISTEREDUSERS", $numberOfUsers);
            $templatefilename = __DIR__ . "/templates/about.tpl";
            $this->app->tpl()->addPluginTemplate("abouthook", $templatefilename);
        }
    }
}
