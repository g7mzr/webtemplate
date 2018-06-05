<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// Home Menu Item
$mainMenu['home']['name'] = 'Home';
$mainMenu['home']['url'] = './';
$mainMenu['home']['loggedin'] = false;
$mainMenu['home']['admin'] = false;

// User Preferences Menu Item
$mainMenu['pref']['name'] = 'Preferences';
$mainMenu['pref']['url'] = 'userprefs.php';
$mainMenu['pref']['loggedin'] = true;
$mainMenu['pref']['admin'] = false;

// Administration Menu Item
$mainMenu['admin']['name'] = 'Administration';
$mainMenu['admin']['url'] = 'admin.php';
$mainMenu['admin']['loggedin'] = true;
$mainMenu['admin']['admin'] = true;

// Logou Menu Item
$mainMenu['logout']['name'] = 'Logout';
$mainMenu['logout']['url'] = 'logout.php';
$mainMenu['logout']['loggedin'] = true;
$mainMenu['logout']['admin'] = false;
