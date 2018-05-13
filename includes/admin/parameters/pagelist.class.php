<?php

/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace webtemplate\admin\parameters;

/**
 * List of Parameter Pages
 *
 * This static class contains the list of pages that can be accessed to update
 * the configuration of Webtemplate
 *
 * @author sandy
 */
class Pagelist
{
    public static $pagelist = array(
        "required" => array(
            "description" => "Required Settings",
            "template" => "admin/config/required.tpl",
            "url" => "editconfig.php?section=required",
            "selected" => false
        ),
        "admin" => array(
            "description" => "Administrative Policys",
            "template" => "admin/config/admin.tpl",
            "url" => "editconfig.php?section=admin",
            "selected" => false
        ),
        "auth" => array(
            "description" => "User Authentication",
            "template" => "admin/config/auth.tpl",
            "url" => "editconfig.php?section=auth",
            "selected" => false
        ),
        "email" => array(
            "description" => "e-mail",
            "template" => "admin/config/email.tpl",
            "url" => "editconfig.php?section=email",
            "selected" => false
        )
    );
}
