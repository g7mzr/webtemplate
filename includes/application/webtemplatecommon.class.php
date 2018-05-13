<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\application;

/**
 * WebtemplateCommon Class is a static class used to initialise the database
 * access system
 *
 * @category Webtemplate
 * @package  General
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
**/
class WebTemplateCommon
{

    /**
    * This function populates the DSN using the database configuration details
    * from a Smarty Config file
    *
    * @param Pointer $tpl Smarty Object
    * @param Pointer $dsn Data Source Name
    *
    * @return boolean
    * @access public
    * @since  Method available since Release 1.0.0
    */
    public static function loadDSN(&$tpl, &$dsn)
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
    * This function returns the database name
    * from a Smarty Config file
    *
    * @param Pointer $tpl Smarty Object
    *
    * @return string database name
    *
    * @access public
    * @since  Method available since Release 1.0.0
    */
    public static function getDatabaseName(&$tpl)
    {
        $tpl->configLoad("local.conf", "database");
        return $tpl->getConfigVars("database");
    }
}
