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

namespace g7mzr\webtemplate\install;

/**
 * Dependencies Data Class
 *
 * DependenciesData Class is the class that contains the dependencies for webtemplate
 * It is managed using the Dependencies class.
 **/
class DependenciesData
{

    /**
     * Minimum PHP Version
     *
     * @var string $phpVersion
     */
    public static $phpVersion = "7.0.7";

    /**
     * Array of php modules required by Webtemplate
     *
     * @var array $phpModules
     */
    public static $phpModules = array(
        'POSIX' => array(
            'name' => 'posix',
            'install' => "Install posix as instructed by your version of PHP"
        ),
        'SESSION' => array(
            'name' => 'session',
            'install' => "Install session as instructed by your version of PHP"
        ),
        'PDO' => array(
            'name' => 'pdo',
            'install' => "Install session as instructed by your version of PHP"
        ),
        'JSON' => array(
            "name" => "json",
            'install' => "Install session as instructed by your version of PHP"
        )
    );

    /**
     * Array of other modules required by Webtemplate
     *
     * @var array $otherModules
     */
    public static $composerModules = array(
        'SMARTY' => array(
            'classname'      => '\Smarty',
            'version'       => '3.1.29',
            'versionvar'    => "\Smarty::SMARTY_VERSION",
            'versionsubstr' => 0,
            'install'       => 'Smarty: Install via composer'
        )
    );

    /**
     * Array of database drivers and database versions required by Webtemplate
     *
     * @var array $database
     */
    public static $databases = array(
        'pgsql' => array(
            'phpdriver'     => 'pgsql',
            'phpinstall'    => "Install pgsql as instructed by your version of PHP",
            'dbversion'     => '9.3.0',
            'dbinstall'     => 'Download postgreSQL from http://www.postgresql.org/',
            'dbupgrade'     => 'Download postgreSQL from http://www.postgresql.org/',
            'templatedb'    => 'template1'
        )
    );
}
