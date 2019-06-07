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
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace webtemplate\application;

/**
 * WebtemplateCommon Class is a static class used to initialise the database
 * access system
 *
**/
class WebTemplateCommon
{

    /**
     * This function populates the DSN using the database configuration details
     * from a Smarty Config file
     *
     * @param SmartyTemplate $tpl Smarty Object.
     * @param array          $dsn Data Source Name.
     *
     * @return boolean
     * @access public
     * @since  Method available since Release 1.0.0
     */
    public static function loadDSN(SmartyTemplate &$tpl, array &$dsn)
    {
        //global $dsn;
        $tpl->configLoad("local.conf", "database");
        $dsn["phptype"]  = $tpl->getConfigVars("phptype");
        $dsn["hostspec"] = $tpl->getConfigVars("hostspec");
        $dsn["database"] = $tpl->getConfigVars("database");
        $dsn["username"] = $tpl->getConfigVars("username");
        $dsn["password"] = $tpl->getConfigVars("password");
        return true;
    }

    /**
     * This function returns the database name from a Smarty Configuration file
     *
     * @param SmartyTemplate $tpl Smarty Object.
     *
     * @return string database name
     *
     * @access public
     * @since  Method available since Release 1.0.0
     */
    public static function getDatabaseName(SmartyTemplate &$tpl)
    {
        $tpl->configLoad("local.conf", "database");
        return $tpl->getConfigVars("database");
    }
}
