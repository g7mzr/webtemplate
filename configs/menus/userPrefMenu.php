<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// General Preferences TAB
$userprefpagelist["settings"]["description"] = "General Preferences";
$userprefpagelist["settings"]["template"] = "users/preferences/settings.tpl";
$userprefpagelist["settings"]["url"] = "userprefs.php?tab=setting";
$userprefpagelist["settings"]["selected"] = false;

// Account TAB
$userprefpagelist["account"]["description"] = "Name and Password";
$userprefpagelist["account"]["template"] = "users/preferences/account.tpl";
$userprefpagelist["account"]["url"] = "userprefs.php?tab=account";
$userprefpagelist["account"]["selected"] = false;

// Permissions TAB
$userprefpagelist["permissions"]["description"] = "Permissions";
$userprefpagelist["permissions"]["template"] = "users/preferences/permissions.tpl";
$userprefpagelist["permissions"]["url"] = "userprefs.php?tab=permissions";
$userprefpagelist["permissions"]["selected"] = false;
