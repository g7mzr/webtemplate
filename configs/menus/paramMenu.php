<?php

/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Parameters Menu Required Section
$parampagelist["required"]["description"] = "Required Settings";
$parampagelist["required"]["template"] = "admin/config/required.tpl";
$parampagelist["required"]["url"] = "editconfig.php?section=required";
$parampagelist["required"]["selected"] = false;

// Parameters Menue Admin Section
$parampagelist["admin"]["description"] = "Administrative Policys";
$parampagelist["admin"]["template"] = "admin/config/admin.tpl";
$parampagelist["admin"]["url"] = "editconfig.php?section=admin";
$parampagelist["admin"]["selected"] = false;

// Parameters Menus Auth Section
$parampagelist["auth"]["description"] = "User Authentication";
$parampagelist["auth"]["template"] = "admin/config/auth.tpl";
$parampagelist["auth"]["url"] = "editconfig.php?section=auth";
$parampagelist["auth"]["selected"] = false;

// Parameters Menu Email Section
$parampagelist['email']["description"] = "e-mail";
$parampagelist['email']["template"] = "admin/config/email.tpl";
$parampagelist['email']["url"] = "editconfig.php?section=email";
$parampagelist['email']["selected"] = false;
