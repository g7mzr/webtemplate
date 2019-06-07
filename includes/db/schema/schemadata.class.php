<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\db\schema;

/**
 * SchemaData Class is the class that contains the database schema for webtemplate
 * It is managed using the SchemaFunction Class
 *
 * @category Webtemplate
 * @package  General
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class SchemaData
{
    public static $schema_version = 1.00;

    public static $schema = array(
        'schema' => array(
            'COLUMNS' => array(
                'version' => array('TYPE' => 'numeric(3,2)', 'NOTNULL' => '1'),
                'schema' => array('TYPE' => 'text', 'NOTNULL' => '1'),
                'groups' => array('TYPE' => 'text', 'NOTNULL' => '1')
            )
        ),
        'users' => array(
            'COLUMNS' => array(
                'user_id' => array('TYPE' => 'serial', 'PRIMARY' => '1'),
                'user_name' => array('TYPE' => 'varchar(64)', 'NOTNULL' => '1', 'UNIQUE' => 1),
                'user_passwd' => array('TYPE' => 'varchar(255)', 'NOTNULL' => '1'),
                'user_realname' => array('TYPE' => 'varchar(64)'),
                'user_email' => array('TYPE' => 'varchar(64)'),
                'user_enabled' => array('TYPE' => 'char(1)', 'DEFAULT' => 'Y', 'NOTNULL' => '1'),
                'user_disable_mail' => array('TYPE' => 'char(1)', 'DEFAULT' => 'N', 'NOTNULL' => '1'),
                'last_seen_date' => array('TYPE' => 'DATETIME'),
                'passwd_changed' => array('TYPE' => 'DATETIME'),
                'last_failed_login' => array('TYPE' => 'DATETIME')
            )
        ),

        'groups' => array(
            'COLUMNS' => array(
                'group_id' => array('TYPE' => 'serial', 'PRIMARY' => '1'),
                'group_name' => array('TYPE' => 'varchar(32)', 'NOTNULL' => '1'),
                'group_description' => array('TYPE' => 'varchar(255)'),
                'group_useforproduct' => array('TYPE' => 'char(1)', 'DEFAULT' => 'N', 'NOTNULL' => '1'),
                'group_autogroup' => array('TYPE' => 'char(1)', 'DEFAULT' => 'N', 'NOTNULL' => '1'),
                'group_admingroup' => array('TYPE' => 'char(1)', 'DEFAULT' => 'N', 'NOTNULL' => '1'),
                'group_editable' => array('TYPE' => 'char(1)', 'DEFAULT' => 'Y', 'NOTNULL' => '1')
            )
        ),

        'user_group_map' => array(
            'COLUMNS' => array(
                'user_group_map_id' => array('TYPE' => 'serial', 'PRIMARY' => 1),
                'user_id' => array('TYPE' => 'bigint', 'CONSTRAINTS' => array('table' => 'users', 'column' => 'user_id')),
                'group_id' => array('TYPE' => 'bigint', 'CONSTRAINTS' => array('table' => 'groups', 'column' => 'group_id'))
            )
        ),

        'userprefs' => array(
            'COLUMNS' => array(
                'userprefs_id' => array('TYPE' => 'serial', 'PRIMARY' => '1'),
                'user_id' => array('TYPE' => 'bigint', 'CONSTRAINTS' => array('table' => 'users', 'column' => 'user_id')),
                'settingname' => array('TYPE' => 'varchar(32)'),
                'settingvalue' => array('TYPE' => 'varchar(32)')
            )
        ),

        'tokens' => array(
            'COLUMNS' => array(
                'user_id' => array('TYPE' => 'int', 'NOTNULL' => '1', 'CONSTRAINTS' => array('table' => 'users', 'column' => 'user_id')),
                'issuedate' => array('TYPE' => 'DATETIME', 'NOTNULL' => '1'),
                'expiredate' => array('TYPE' => 'DATETIME', 'NOTNULL' => 1),
                'life' => array('TYPE' => 'int', 'NOTNULL' => '1'),
                'token' => array('TYPE' => 'varchar(110)', 'NOTNULL' => '1', 'PRIMARY' => '1'),
                'tokentype' => array('TYPE' => 'varchar(32)', 'NOTNULL' => '1'),
                'eventdata' => array('TYPE' => 'varchar(255)')
            ),
            'INDEXES' => array(
                'tokens_user_id_idx' => array('COLUMN' => 'user_id')
            )
        ),
        'logindata' => array(
            'COLUMNS' => array(
                'user_id' => array('TYPE' => 'int', 'NOTNULL' => '1', 'CONSTRAINTS' => array('table' => 'users', 'column' => 'user_id')),
                'cookie' => array('TYPE' => 'varchar(32)', 'NOTNULL' => '1', 'PRIMARY' => '1'),
                'lastused' => array('TYPE' => 'DATETIME', 'NOTNULL' => '1'),
                'ipaddr' => array('TYPE' => 'varchar(40)'),
                'user_name' => array('TYPE' => 'varchar(64)', 'NOTNULL' => '1', 'CONSTRAINTS' => array('table' => 'users', 'column' => 'user_name')),
                'newpasswd' => array('TYPE' => 'char(1)', 'DEFAULT' => 'N', 'NOTNULL' => '1')
            ),
            'INDEXES' => array(
                'logindata_user_id_idx' => array('COLUMN' => 'user_id'),
                'logindata_user_name_idx' => array('COLUMN' => 'user_name')
            )
        )


    );

    // The Main Application Groups.
    public static $defaultgroups = array(
        'admin' => array(
            'description' => 'Administrators',
            'useforproduct' => 'N',
            'autogroup' => 'N',
            'editable' => 'N',
            'admingroup' => 'Y'
        ),
        'editusers' => array(
            'description' => 'Members of this group can create and edit users',
            'useforproduct' => 'N',
            'autogroup' => 'N',
            'editable' => 'N',
            'admingroup' => 'Y'
        ),
        'editgroups' => array(
            'description' => 'Members of this group can create and edit groups',
            'useforproduct' => 'N',
            'autogroup' => 'N',
            'editable' => 'N',
            'admingroup' => 'Y'
        )
    );

    // Test Groups for use with PHPUNIT and SELENIUM
    public static $testgroups = array(
        'testgroup' => array(
            'description' => 'Test Group One',
            'useforproduct' => 'N',
            'autogroup' => 'Y',
            'editable' => 'N',
            'admingroup' => 'N'
        ),
        'testgrouptwo' => array(
            'description' => 'Test Group Two',
            'useforproduct' => 'N',
            'autogroup' => 'N',
            'editable' => 'N',
            'admingroup' => 'N',
            'groups' => array('editusers')
        )
    );

    // The Default Database Users
    public static $defaultUsers = array(
        'admin' => array(
            'realname' => 'Administrator',
            'passwd'   => 'Admin1admin',
            'email'    => '',
            'enabled'  => 'Y',
            'passwdchanged' => 'now()',
            'groups'   => array('admin')
        )
    );

    // Test Users for use with PHPUNIT and SELENIUM
    public static $testUsers = array(
        'phpunit' => array(
            'realname' => 'Phpunit User',
            'passwd'   => 'phpUnit1',
            'email'    => 'phpunit@example.com',
            'enabled'  => 'Y',
            'passwdchanged' => 'now()',
            'groups'   => array('admin')
        ),
        'settingsuser' => array(
            'realname' => 'Settings User',
            'passwd'   => 'settingsuser',
            'email'    => 'settingsuser@example.com',
            'enabled'  => 'Y',
            'passwdchanged' => 'now()',
            'prefs'    => array(
                'theme'       => 'text',
                'zoomtext'    => 'false',
                'displayrows' => '2'
            )
        ),
        'lockeduser' => array(
            'realname' => 'Locked User',
            'passwd'   => 'lockedUser1',
            'email'    => 'lockeduser@example.com',
            'enabled'  => 'N',
            'passwdchanged' => 'now()'
        ),
        'passwduser' => array(
            'realname' => 'Password Change User',
            'passwd'   => 'passwduser',
            'email'    => 'passwduser@example.com',
            'enabled'  => 'Y',
            'passwdchanged' => null
        ),
        'passwduser2' => array(
            'realname' => 'Password Change User Two',
            'passwd'   => 'passwduser2',
            'email'    => 'passwduser@example.com',
            'enabled'  => 'Y',
            'passwdchanged' => null

        ),
        'secuser' => array(
            'realname' => 'Security User',
            'passwd'   => 'secUser1',
            'email'    => 'secuser@example.com',
            'enabled'  => 'Y',
            'passwdchanged' => 'now()',
            'groups'   => array('editusers')
        ),
        'secgroups' => array(
            'realname' => 'Security Groups',
            'passwd'   => 'secGroups1',
            'email'    => 'secgroups@example.com',
            'enabled'  => 'Y',
            'passwdchanged' => 'now()',
            'groups'   => array('editgroups')
        ),
        'secboth' => array(
            'realname' => 'Security Both',
            'passwd'   => 'secBoth1',
            'email'    => 'secboth@example.com',
            'enabled'  => 'Y',
            'passwdchanged' => 'now()',
            'groups'   => array('editusers','editgroups')
        ),
        'secnone' => array(
            'realname' => 'Security None',
            'passwd'   => 'secNone1',
            'email'    => 'secnone@example.com',
            'enabled'  => 'Y',
            'passwdchanged' => 'now()'
        )
    );
}
