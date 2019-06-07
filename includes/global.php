<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Global
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

// Include and register the Autoloader Class
require_once __DIR__ . '/autoloader.class.php';
\spl_autoload_register("\webtemplate\AutoLoader::loader");

// Include the composer Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Include and register the Webtemplate error handler for webbased Applications
if (php_sapi_name() !== 'cli') {
    // include the Default Error handler
    require_once __DIR__ . '/application/errorHandler.class.php';

    // SET the Error Handler
    \set_error_handler("\webtemplate\application\ErrorHandler::handleError");

    // Set the Exception Handler
    \set_exception_handler(
        "\webtemplate\application\ErrorHandler::exceptionHandler"
    );
}

/**
 * PDO Data Source Name
 *
 * @var array
 */

$dsn = array(
    'phptype'  => '',
    'hostspec' => '',
    'database' => '',
    'username' => '',
    'password' => '',
    'disable_iso_date' => 'disable'
);
