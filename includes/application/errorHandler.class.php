<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\application;

/**
* Webtemplate Class error Handler
*
* @category Webtemplate
* @package  ErrorHandler
* @author   Sandy McNeil <g7mzrdev@gmail.com>
* @license  View the license file distributed with this source code
**/
class ErrorHandler
{
    /**
     * Error Handler for Webtemplate so that a blank page is not displayed.
     *
     * @param int    $errno   The error number
     * @param string $errstr  The error message
     * @param string $errfile The file the error occured on
     * @param string $errline The line the error occured
     *
     * @return boolan True if error has been handles
     *
     * @access public
     */
    public function handleError($errno, $errstr, $errfile, $errline)
    {

        // Set up the Smarty Template Engine
        $tpl = new \webtemplate\application\SmartyTemplate;
        $template = 'global/phperror.tpl';

        //Set up the correct language and associated templates
        $languageconfig = \webtemplate\general\General::getconfigfile(
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
     * @param Exception $exception Base class for all Exceptions in PHP 5,
     *
     * @return boolan True if error has been handles
     *
     * @access public
     */
    public function exceptionHandler($exception)
    {

        // Set up the Smarty Template Engine
        $tpl = new \webtemplate\application\SmartyTemplate;
        $template = 'global/phperror.tpl';

        //Set up the correct language and associated templates
        $languageconfig = \webtemplate\general\General::getconfigfile(
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
        error_log($appName .": Exception");
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
