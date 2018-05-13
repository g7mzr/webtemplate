<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\general;

/**
 * Webtemplate Application Logging Class
 *
 * @category Webtemplate
 * @package  General
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

class Log
{
     /**
     * Error Level
     * 0 = All Looging Disabled
     * 1 = Error
     * 2 = Error and Warn
     * 3 = Error, Warn and Info
     * 4 = Error, Warn, Info and Debug
     * 5 = Error, Warn, Info, Debug and trace
     *
     * @var    integer
     * @access protected
     */

    protected $errorLevel = 0;

     /**
     * Rotate Files
     * 1 = Daily
     * 2 = Weekly
     * 3 = Monthly
      *
     * @var    integer
     * @access protected
     */

    protected $rotateLevel = 2;


    /**
     * Log Directory
     *
     * @var    string
     * @access protected
     */

    protected $logDir = '';


    /**
     * Constructor
     *
     * @param integer $errorLevel  Type of message to be logged
     * @param integer $rotateLevel When to rotate logfiles
     *
     * @access public
     */
    public function __construct($errorLevel = 0, $rotateLevel = 2)
    {
        settype($errorLevel, "integer");
        settype($rotateLevel, "integer");
        $this->errorLevel = $errorLevel;
        $this->rotateLevel = $rotateLevel;
        $this->logDir = dirname(dirname(dirname(__FILE__))) . "/logs/";
        //error_log($this->errorLevel);
    } // end constructor



    /**
     * Open Logging File
     *
     * @param string $fileName The name of the file to be logged to
     *
     * @return pointer  File Handle
     * @access protected
     */
    final protected function openLogFile($fileName)
    {
        if ($this->rotateLevel == 1) {
            $rotateName = "daily-" . date("Ymd_", time());
        } elseif ($this->rotateLevel == 2) {
            $rotateName = "weekly-" .date("YW_", time());
        } else {
            $rotateName = "monthly-".date("Ym_", time());
        }
        $localFileName = $this->logDir . $rotateName . $fileName;
        $handle = fopen($localFileName, "a");
        if ($handle == false) {
            error_log("Unable to Open $fileName");
        }
        return $handle;
    }

    /**
     * This function logs Errors messages
     *
     * @param string $data The message to get logged
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function error($data)
    {
        $result = true;
        if ($this->errorLevel > 0) {
            // Open the main log file
            if ($handle = $this->openLogFile("main.log")) {
                $currentDate = date("d/m/Y H:i:s", time());
                $clientAddr = $_SERVER["REMOTE_ADDR"];
                fwrite(
                    $handle,
                    $currentDate . " [error] [client $clientAddr] " . $data
                );
                if (strpos($data, "\n") === false) {
                    fwrite($handle, "\n");
                }
                fclose($handle);
            }
        }
        return $result;
    }


    /**
     * This function logs warning messages
     *
     * @param string $data The message to get logged
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function warn($data)
    {
        $result = true;
        if ($this->errorLevel > 1) {
            // Open the main log file
            if ($handle = $this->openLogFile("main.log")) {
                $currentDate = date("d/m/Y H:i:s", time());
                $clientAddr = $_SERVER["REMOTE_ADDR"];
                fwrite(
                    $handle,
                    $currentDate . " [warning] [client $clientAddr] " . $data
                );
                if (strpos($data, "\n") === false) {
                    fwrite($handle, "\n");
                }
                fclose($handle);
            }
        }
        return $result;
    }

    /**
     * This function logs information messages
     *
     * @param string $data The message to get logged
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function info($data)
    {
        $result = true;
        if ($this->errorLevel > 2) {
            // Open the main log file
            if ($handle = $this->openLogFile("main.log")) {
                $currentDate = date("d/m/Y H:i:s", time());
                $clientAddr = $_SERVER["REMOTE_ADDR"];
                fwrite(
                    $handle,
                    $currentDate . " [info] [client $clientAddr] " . $data
                );
                if (strpos($data, "\n") === false) {
                    fwrite($handle, "\n");
                }
                fclose($handle);
            }
        }
        return $result;
    }

    /**
     * This function logs debug messages
     *
     * @param string $data The message to get logged
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function debug($data)
    {
        $result = true;
        if ($this->errorLevel > 3) {
            // Open the debug file
            if ($handle = $this->openLogFile("debug.log")) {
                $currentDate = date("d/m/Y H:i:s", time());
                $clientAddr = $_SERVER["REMOTE_ADDR"];
                fwrite($handle, $currentDate . " [client $clientAddr] " . $data);
                if (strpos($data, "\n") === false) {
                    fwrite($handle, "\n");
                }
                fclose($handle);
            }
        }
        return $result;
    }


    /**
     * This function logs trace messages
     *
     * @param string $data The message to get logged
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function trace($data)
    {
        $result = true;
        if ($this->errorLevel > 4) {
            // Open the trace file
            if ($handle = $this->openLogFile("trace.log")) {
                $currentDate = date("d/m/Y H:i:s", time());
                $clientAddr = $_SERVER["REMOTE_ADDR"];
                fwrite($handle, $currentDate . " [client $clientAddr] " . $data);
                if (strpos($data, "\n") === false) {
                    fwrite($handle, "\n");
                }
                fclose($handle);
            }
        }
        return $result;
    }

    /**
     * This function logs security messages
     *
     * @param string $data The message to get logged
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function security($data)
    {
        $result = true;

        // Security Messages are always logged if logging is on
        if ($this->errorLevel > 0) {
            // Open the security file
            if ($handle = $this->openLogFile("security.log")) {
                $currentDate = date("d/m/Y H:i:s", time());
                $clientAddr = $_SERVER["REMOTE_ADDR"];
                fwrite($handle, $currentDate . " [client $clientAddr] " . $data);
                if (strpos($data, "\n") === false) {
                    fwrite($handle, "\n");
                }
                fclose($handle);
            }
        }
        return $result;
    }
}
