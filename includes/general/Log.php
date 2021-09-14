<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage General
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\general;

/**
 * Webtemplate Application Logging Class
 *
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
     * @param integer $errorLevel  Type of message to be logged.
     * @param integer $rotateLevel When to rotate logfiles.
     *
     * @access public
     */
    public function __construct(int $errorLevel = 0, int $rotateLevel = 2)
    {
        settype($errorLevel, "integer");
        settype($rotateLevel, "integer");
        $this->errorLevel = $errorLevel;
        $this->rotateLevel = $rotateLevel;
        $this->logDir = dirname(dirname(dirname(__FILE__))) . "/logs/";
        //error_log($this->errorLevel);
    }



    /**
     * Open Logging File
     *
     * @param string $fileName The name of the file to be logged to.
     *
     * @return pointer  File Handle
     * @access protected
     */
    final protected function openLogFile(string $fileName)
    {
        if ($this->rotateLevel == 1) {
            $rotateName = "daily-" . date("Ymd_", time());
        } elseif ($this->rotateLevel == 2) {
            $rotateName = "weekly-" . date("YW_", time());
        } else {
            $rotateName = "monthly-" . date("Ym_", time());
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
     * @param string $data The message to get logged.
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function error(string $data)
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
     * @param string $data The message to get logged.
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function warn(string $data)
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
     * @param string $data The message to get logged.
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function info(string $data)
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
     * @param string $data The message to get logged.
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function debug(string $data)
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
     * @param string $data The message to get logged.
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function trace(string $data)
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
     * @param string $data The message to get logged.
     *
     * @return boolean currently always returns true.
     * @access public
     */
    final public function security(string $data)
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
