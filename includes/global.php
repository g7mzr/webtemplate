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

// Include the Autoloader Class
require_once __DIR__ . '/PSR4AutoLoader.php';

// Include the composer Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Initite WebApp Autoloader
$loader = new \g7mzr\webtemplate\PSR4AutoLoader();

// Register the autoloader
$loader->register();

// Register WebApp Namespaces
$appdir = dirname(__DIR__);
$loader->addNamespace("\g7mzr\webtemplate", $appdir . "/includes");
$loader->addNamespace("\g7mzr\webtemplate\plugins", $appdir . "/plugins");

// Include and register the Webtemplate error handler for webbased Applications
if (php_sapi_name() !== 'cli') {
    // include the Default Error handler
    require_once __DIR__ . '/application/ErrorHandler.php';

    // SET the Error Handler
    \set_error_handler("\g7mzr\webtemplate\application\ErrorHandler::handleError");

    // Set the Exception Handler
    \set_exception_handler(
        "\g7mzr\webtemplate\application\ErrorHandler::exceptionHandler"
    );
}

/**
 * PDO Data Source Name
 *
 * @var array
 */

$dsn = array(
    'dbtype'  => '',
    'hostspec' => '',
    'database' => '',
    'username' => '',
    'password' => '',
    'disable_iso_date' => 'disable'
);
