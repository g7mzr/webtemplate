<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// About Menu Option
$adminpagelist['about']['name'] = 'About';
$adminpagelist['about']['description'] = "Information about the application and "
    . "server it is running on.";
$adminpagelist['about']['url'] = "/about.php";
$adminpagelist['about']['icon'] = 'about.png';
$adminpagelist['about']['alttext'] = 'About';
$adminpagelist['about']['group'] = 'admin';

// Edit Settings Menu Option
$adminpagelist['settings']['name'] = 'Setup';
$adminpagelist['settings']['description'] = "Edit the application configuration, "
    . "including mandatory settings.";
$adminpagelist['settings']['url'] = "/editconfig.php";
$adminpagelist['settings']['icon'] = 'setup.png';
$adminpagelist['settings']['alttext'] = 'Edit Settings';
$adminpagelist['settings']['group'] = 'admin';

// Edit Preferences Menu Option
$adminpagelist['preferences']['name'] = 'Site Preferences';
$adminpagelist['preferences']['description'] = 'Set the default user preferences. '
    . 'These are the values which will be used by default for all users. Users will '
    . 'be able to edit their own preferences.';
$adminpagelist['preferences']['url'] = "/editsettings.php";
$adminpagelist['preferences']['icon'] = 'preferences.png';
$adminpagelist['preferences']['alttext'] = 'Edit User Preferences';
$adminpagelist['preferences']['group'] = 'admin';

// Edit Groups Menu Options
$adminpagelist['editgroup']['name'] = 'Groups';
$adminpagelist['editgroup']['description'] = "Define groups which will be used in "
    . "the installation. They can be used to define user privileges";
$adminpagelist['editgroup']['url'] = "/editgroups.php";
$adminpagelist['editgroup']['icon'] = 'groups.png';
$adminpagelist['editgroup']['alttext'] = 'Edit Groups';
$adminpagelist['editgroup']['group'] = 'editgroups';

// Edit Users Menu
$adminpagelist['editusers']['name'] = 'Users';
$adminpagelist['editusers']['description'] = "Create new user accounts or edit "
    . "existing ones. You can also add and remove users from groups.";
$adminpagelist['editusers']['url'] = "/editusers.php";
$adminpagelist['editusers']['icon'] = 'users.png';
$adminpagelist['editusers']['alttext'] = 'Edit Users';
$adminpagelist['editusers']['group'] = 'editusers';
