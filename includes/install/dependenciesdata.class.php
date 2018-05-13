<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\install;

/**
 * DependenciesData Class is the class that contains the dependancies for webtemplate
 * It is managed using the Dependencies class.
 *
 * @category Webtemplate
 * @package  General
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
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
