<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Application
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\application;

/**
* Webtemplate Class error Handler
**/
class ErrorHandler
{
    /**
     * Error Handler for Webtemplate so that a blank page is not displayed.
     *
     * @param integer $errno   The error number.
     * @param string  $errstr  The error message.
     * @param string  $errfile The file the error occur on.
     * @param integer $errline The line the error occur.
     *
     * @return boolan True if error has been handles
     *
     * @access public
     */
    public function handleError(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline
    ) {

        // Set up the Smarty Template Engine
        $tpl = new \g7mzr\webtemplate\application\SmartyTemplate();
        $template = 'global/phperror.tpl';

        //Set up the correct language and associated templates
        $languageconfig = \g7mzr\webtemplate\general\General::getconfigfile(
            $tpl->getConfigDir()
        );
        $tpl->assign('CONFIGFILE', $languageconfig);
        $tpl->configLoad($languageconfig);
        $language = $tpl->getConfigVars('Language');
        $templateArray = $tpl->getTemplateDir();
        $tpl->setTemplateDir($templateArray[0] . '/' . $language);
        $tpl->setCompileId($language);
        $dateArray = getdate();

        // Get the Application Name
        $appName = $tpl->getConfigVars('application_name');


        $result = false;
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
            case E_STRICT:
                //error_log("FIRST");
                break;

            case E_WARNING:
            case E_USER_WARNING:
                error_log($appName . " Warning: " . $errstr);
                $result = true;
                break;

            case E_ERROR:
            case E_USER_ERROR:
                error_log($appName . " Error: " . $errstr);
                $result = true;
                break;

            default:
                error_log($appName . ": " . $errstr);
                $result = true;
        }


        return $result;
    }

    /**
     * Error Handler for Webtemplate so that a blank page is not displayed.
     *
     * @param mixed $exception Base class for all Exceptions in PHP 7.
     *
     * @return boolan True if error has been handles
     *
     * @access public
     */
    public function exceptionHandler($exception)
    {

        // Set up the Smarty Template Engine
        $tpl = new \g7mzr\webtemplate\application\SmartyTemplate();
        $template = 'global/phperror.tpl';

        //Set up the correct language and associated templates
        $languageconfig = \g7mzr\webtemplate\general\General::getconfigfile(
            $tpl->getConfigDir()
        );
        $tpl->assign('CONFIGFILE', $languageconfig);
        $tpl->configLoad($languageconfig);
        $language = $tpl->getConfigVars('Language');
        $templateArray = $tpl->getTemplateDir();
        $tpl->setTemplateDir($templateArray[0] . '/' . $language);
        $tpl->setCompileId($language);
        $dateArray = getdate();

        // Get the Application Name
        $appName = $tpl->getConfigVars('application_name');

        //Create new config class
        $configdir = $tpl->getConfigDir(0);
        $config = new \g7mzr\webtemplate\config\Configure($configdir);

        // Load the menu and assign it to a SMARTY Variable
        $mainmenu = $config->readMenu('mainmenu');
        $tpl->assign('MAINMENU', $mainmenu);



        // Format the Stack Trace for the Web page
        $trace = $exception->getTrace();
        $tracedata = array();
        foreach ($trace as $traceitem) {
            $tracestr = $traceitem['file'] . ':' . $traceitem['line'];
            if ($traceitem['class'] <> '') {
                $tracestr .= '  ' . $traceitem['class'];
                $tracestr .= $traceitem['type'];
            }
            $tracestr .= $traceitem['function'] . '(';
            if (count($traceitem['args'] > 0)) {
                $comma = '';
                foreach ($traceitem['args'] as $arg) {
                    if (is_array($arg)) {
                    } else {
                        $tracestr .= $comma . $arg;
                        $comma = ', ';
                    }
                }
            }
            $tracestr .= ')';
            $tracedata[] = $tracestr;
        }

        // Log the Error to the Apache log file
        error_log($appName . ": Exception");
        error_log($exception->getMessage());
        error_log($exception->getTraceAsString());

        // Populate and Display the Web Page
        $tpl->assign("YEAR", "$dateArray[year]");
        $tpl->assign('TRACE', $tracedata);
        $tpl->assign("FILE", $exception->getFile());
        $tpl->assign("LINE", $exception->getLine());
        $tpl->assign("MESSAGE", $exception->getMessage());
        $tpl->assign("ADMIN", $_SERVER['SERVER_ADMIN']);
        $tpl->assign('LOGIN', 'true');
        $tpl->display($template);

        // Default is it has handled the error
        return true;
    }
}
