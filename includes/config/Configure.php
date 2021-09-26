<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Configuration
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\config;

/**
 * Configuration Class
 *
 **/
class Configure
{
    /**
     * Application configuration parameters
     *
     * @var    array
     * @access protected
     */
    protected $parameters;

    /**
     * Application preference settings
     *
     * @var    array
     * @access protected
     */
    protected $preferences;

    /**
     * Database connection object
     *
     * @var    \g7mzr\db\interfaces\InterfaceDatabaseDriver
     * @access protected
     */
    protected $db  = null;

    /**
     * Constructor for the edit user class.
     *
     * @param \g7mzr\db\interfaces\InterfaceDatabaseDriver $db Database Object.
     *
     * @throws \Exception If Parameters are not loaded from Database.
     * @throws \Exception If Preferences are not loaded from Database.
     *
     * @access public
     */
    public function __construct(\g7mzr\db\interfaces\InterfaceDatabaseDriver $db)
    {
        $this->db = $db;

        // Load global parameters and preferences
        $parameterResult = $this->loadParameters();
        if (\g7mzr\webtemplate\general\General::isError($parameterResult)) {
            throw new \Exception($parameterResult->getMessage());
        }
        //$parametersfile = $configDir . "/parameters.json";
        //$parameterstr = file_get_contents($parametersfile);
        //$this->parameters = json_decode($parameterstr, true);

        $preferenceResult = $this->loadPreferences();
        if (\g7mzr\webtemplate\general\General::isError($preferenceResult)) {
            throw new \Exception($preferenceResult->getMessage());
        }
        //$preferencesfile = $configDir . "/preferences.json";
        //$preferencesstr = file_get_contents($preferencesfile);
        //$this->preferences = json_decode($preferencesstr, true);
    }


    /**
     * This function returns the data in the specified key or null if the key does
     * not exist.  Dot notation can be used.  The first name in the path should be
     * param for $parameters of pref for $preferences.
     *
     * @param string $config The Parameter key being requested.
     *
     * @throws \InvalidArgumentException If the Parameter key is not a dot
     *                                   notation string.
     * @throws \InvalidArgumentException If 1st key item is not param or pref .
     * @throws \InvalidArgumentException If more than 4 items in the key.
     *
     * @return mixed The vale of the Parameter requested or null if it does not exist
     *
     * @access public
     */
    final public function read(string $config = '')
    {

        // Set up an array for working with.
        $configarray = array();
        $configarray['parameters'] = $this->parameters;
        $configarray['preferences'] = $this->preferences;

        // If the Key is null return the whole array
        if ($config === '') {
            return $configarray;
        }

        // Return the individual result
        if (!is_numeric($config)) {
            $key = explode(".", $config);
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid Argument %s, should be a dot seperated path",
                    $config
                )
            );
        }

        if ($key[0] == "param") {
            $key[0] = "parameters";
        } elseif ($key[0] == "pref") {
            $key[0] = "preferences";
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid Argument %s, should be param or pref",
                    $key[0]
                )
            );
        }


        switch (count($key)) {
            case 1:
                return isset($configarray[$key[0]])
                ? $configarray[$key[0]]
                : null;
            case 2:
                return isset($configarray[$key[0]][$key[1]])
                ? $configarray[$key[0]][$key[1]]
                : null;
            case 3:
                return isset($configarray[$key[0]][$key[1]][$key[2]])
                ? $configarray[$key[0]][$key[1]][$key[2]]
                : null;
            case 4:
                return isset($configarray[$key[0]][$key[1]][$key[2]][$key[3]])
                ? $configarray[$key[0]][$key[1]][$key[2]][$key[3]]
                : null;
            default:
                throw new \InvalidArgumentException(
                    sprintf(
                        "Invalid Argument %s, path must contain less than 5 items",
                        $config
                    )
                );
        }
    }


    /**
     * This function returns the details of a single XXX
     *
     * @param string $config The Parameter key being updated.
     * @param mixed  $data   The value of the parameter being updated.
     *
     * @throws \InvalidArgumentException If the Parameter key is not a dot
     *                                   notation string.
     * @throws \InvalidArgumentException If param list is more than 4 items.
     * @throws \InvalidArgumentException If pref list is more than 4 items.
     * @throws \InvalidArgumentException If 1st key item is not param or pref .
     *
     * @return boolean Always returns true
     * @access public
     */
    final public function write(string $config, $data)
    {
        if (!is_numeric($config)) {
            $key = explode(".", $config);
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid Argument %s, should be a dot seperated path",
                    $config
                )
            );
        }

        if ($key[0] == "param") {
            switch (count($key)) {
                case 2:
                    $this->parameters[$key[1]] = $data;
                    break;
                case 3:
                    $this->parameters[$key[1]][$key[2]] = $data;
                    break;
                case 4:
                    $this->parameters[$key[1]][$key[2]][$key[3]] = $data;
                    break;
                default:
                    throw new \InvalidArgumentException(
                        sprintf(
                            "Invalid Argument %s, path must contain less than 5 items",
                            $config
                        )
                    );
            }
        } elseif ($key[0] == "pref") {
            switch (count($key)) {
                case 2:
                    $this->preferences[$key[1]] = $data;
                    break;
                case 3:
                    $this->preferences[$key[1]][$key[2]] = $data;
                    break;
                case 4:
                    $this->preferences[$key[1]][$key[2]][$key[3]] = $data;
                    break;
                default:
                    throw new \InvalidArgumentException(
                        sprintf(
                            "Invalid Argument %s, path must contain less than 5 items",
                            $config
                        )
                    );
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid Argument %s, should be param or pref",
                    $key[0]
                )
            );
        }
        return true;
    }

    /**
     * This function checks if a configuration value exists and is not null
     * Dot notation can be used.
     *
     * @param string $config The Parameter key being requested.
     *
     * @return boolean True if the parameter exists False otherwise.
     * @access public
     */
    final public function check(string $config)
    {
        $result = false;
        if (empty($config)) {
            return false;
        }
        if ($this->read($config) !== null) {
            $result = true;
        }
        return $result;
    }


    /**
     * This function deletes a configuration value
     * Dot notation can be used.
     *
     * @param string $config The Parameter key to be deleted.
     *
     * @return boolean True if the parameter exists and is deleted.
     * @access public
     */
    final public function delete(string $config)
    {
        // Check if the entered key exists.
        if ($this->check($config) == false) {
            return false;
        }

        $key = explode(".", $config);

        if ($key[0] == "param") {
            switch (count($key)) {
                case 2:
                    unset($this->parameters[$key[1]]);
                    break;
                case 3:
                    unset($this->parameters[$key[1]][$key[2]]);
                    break;
                case 4:
                    unset($this->parameters[$key[1]][$key[2]][$key[3]]);
                    break;
            }
        } elseif ($key[0] == "pref") {
            switch (count($key)) {
                case 2:
                    unset($this->preferences[$key[1]]);
                    break;
                case 3:
                    unset($this->preferences[$key[1]][$key[2]]);
                    break;
            }
        }
        return true;
    }

    /**
     * This function is used to reload the parameters into the class
     *
     * @param array $parameters Parameters to reload into the class.
     *
     * @return boolean Returns true
     */
    final public function reloadParams(array $parameters)
    {
        $this->parameters = $parameters;
        return true;
    }


    /**
     * This function is used to reload the preferences into the class
     *
     * @param array $preferences Preferences to reload into the class.
     *
     * @return boolean Returns true
     */
    final public function reloadpref(array $preferences)
    {
        $this->preferences = $preferences;
        return true;
    }


    /**
    * Save the current parameters to the database
    *
    * @return boolean true if parameters are saved to the database false otherwise
    *
    * @access public
    */
    final public function saveParams()
    {
        // Set the result variable
        $saveResult = true;

        // Process the multi dimensional Parameters Array to a Single dimensional array
        $arrayToSave = $this->processArray($this->parameters);

        // Start the database transaction
        $this->db->startTransaction();

        // Process the array
        foreach ($arrayToSave as $key => $value) {
            //  Create the array of data values to be updated
            $updateData = array('config_value' => $value);

            // Create the array of the data to be used to select the correct field to update
            $searchData = array('config_key'  => $key);

            // Update the preferences
            $result = $this->db->dbupdate('config', $updateData, $searchData);

            // Check if there was an error
            if (\g7mzr\db\common\Common::isError($result)) {
                   $saveResult = false;
            }
        }
        $this->db->endTransaction($saveResult);
        return $saveResult;
    }

    /**
    * Save the current preferences to a the database
    *
    * @return boolean true if preferences saved to database false otherwise
    *
    * @access public
    */
    final public function savePrefs()
    {
        // Set the result variable
        $saveResult = true;

        // Process the multi dimensional Preferences  Array to a Single dimensional array
        $arrayToSave = $this->processArray($this->preferences);

        // Start the database transaction
        $this->db->startTransaction();

        // Process the array
        foreach ($arrayToSave as $key => $value) {
            //  Create the array of data values to be updated
            $updateData = array('config_value' => $value);

            // Create the array of the data to be used to select the correct field to update
            $searchData = array('config_key'  => $key);

            // Update the preferences
            $result = $this->db->dbupdate('config', $updateData, $searchData);

            // Check if there was an error
            if (\g7mzr\db\common\Common::isError($result)) {
                   $saveResult = false;
            }
        }
        $this->db->endTransaction($saveResult);
        return $saveResult;
    }

    /**
     * Load Parameter Array
     *
     * This function loads the Parameters Array from the database
     *
     * @throws \InvalidArgumentException If path has more than 4 elements.
     *
     * @return mixed True if the parameter array is loaded or an Error if it fails
     *
     * @access private
     */
    private function loadParameters()
    {
        $gotdata = true;
        $fields = array(
            "config_key",
            "config_value",
            "config_type"
        );
        $searchdata = array(
            "config_array" => "parameters"
        );
        $result = $this->db->dbselectmultiple("config", $fields, $searchdata);
        if (\g7mzr\db\common\Common::isError($result)) {
            $gotdata = false;
            $errorMsg =  $result->getMessage();
            ;
        } else {
            foreach ($result as $uao) {
                $key = explode(".", $uao['config_key']);
                switch (count($key)) {
                    case 1:
                        if ($uao['config_type'] == "bool") {
                            $this->parameters[$key[0]]
                                = $this->setBool($uao['config_value']);
                        } else {
                            $this->parameters[$key[0]]
                                = (string) $uao['config_value'];
                        }
                        break;
                    case 2:
                        if ($uao['config_type'] == "bool") {
                            $this->parameters[$key[0]][$key[1]]
                                = $this->setBool($uao['config_value']);
                        } else {
                            $this->parameters[$key[0]][$key[1]]
                                = (string) $uao['config_value'];
                        }
                        break;
                    case 3:
                        if ($uao['config_type'] == "bool") {
                            $this->parameters[$key[0]][$key[1]][$key[2]]
                                = $this->setBool($uao['config_value']);
                        } else {
                            $this->parameters[$key[0]][$key[1]][$key[2]]
                                = (string) $uao['config_value'];
                        }
                        break;
                    case 4:
                        if ($uao['config_type'] == "bool") {
                            $this->parameters[$key[0]][$key[1]][$key[2]][$key[3]]
                                = $this->setBool($uao['config_value']);
                        } elseif ($uao['config_type'] == "int") {
                            $this->parameters[$key[0]][$key[1]][$key[2]][$key[3]]
                                = (int) $uao['config_value'];
                        } else {
                            $this->parameters[$key[0]][$key[1]][$key[2]][$key[3]]
                                = (string) $uao['config_value'];
                        }
                        break;
                    default:
                        throw new \InvalidArgumentException(
                            sprintf(
                                "Invalid Argument %s, path must contain less than 5 items",
                                $key
                            )
                        );
                }
            }
        }
        if ($gotdata == true) {
            return true;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }


    /**
     * Load Preferences Array
     *
     * This function loads Preferences Array from the database
     *
     * @throws \InvalidArgumentException If path has more than 4 elements.
     *
     * @return mixed True if the preferences array is loaded or an Error if it fails
     *
     * @access private
     */
    private function loadPreferences()
    {
        $gotdata = true;
        $fields = array(
            "config_key",
            "config_value",
            "config_type"
        );
        $searchdata = array(
            "config_array" => "preferences"
        );
        $result = $this->db->dbselectmultiple("config", $fields, $searchdata);
        if (\g7mzr\db\common\Common::isError($result)) {
            $gotdata = false;
            $errorMsg =  $result->getMessage();
            ;
        } else {
            foreach ($result as $uao) {
                $key = explode(".", $uao['config_key']);
                switch (count($key)) {
                    case 1:
                        if ($uao['config_type'] == "bool") {
                            $this->preferences[$key[0]]
                                = $this->setBool($uao['config_value']);
                        } else {
                            $this->preferences[$key[0]]
                                = (string) $uao['config_value'];
                        }
                        break;
                    case 2:
                        if ($uao['config_type'] == "bool") {
                            $this->preferences[$key[0]][$key[1]]
                                = $this->setBool($uao['config_value']);
                        } elseif ($uao['config_type'] == "int") {
                            $this->parameters[$key[0]][$key[1]][$key[2]][$key[3]]
                                = (int) $uao['config_value'];
                        } else {
                            $this->preferences[$key[0]][$key[1]]
                                = (string) $uao['config_value'];
                        }
                        break;
                    case 3:
                        if ($uao['config_type'] == "bool") {
                            $this->preferences[$key[0]][$key[1]][$key[2]]
                                = $this->setBool($uao['config_value']);
                        } else {
                            $this->preferences[$key[0]][$key[1]][$key[2]]
                                = (string) $uao['config_value'];
                        }
                        break;
                    case 4:
                        if ($uao['config_type'] == "bool") {
                            $this->preferences[$key[0]][$key[1]][$key[2]][$key[3]]
                                = $this->setBool($uao['config_value']);
                        } else {
                            $this->preferences[$key[0]][$key[1]][$key[2]][$key[3]]
                                = (string) $uao['config_value'];
                        }
                        break;
                    default:
                        throw new \InvalidArgumentException(
                            sprintf(
                                "Invalid Argument %s, path must contain less than 5 items",
                                $key
                            )
                        );
                }
            }
        }
        if ($gotdata == true) {
            return true;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }

    /**
     * Set Bool
     *
     * This function taked to string representation of a boolean value and converts
     * it to boolean true if $inputstr is "t" otherwise false.
     *
     * @param string $inputstr The string value to be converted to boolean.
     *
     * @return boolean True if $inputstr is "t" otherwise false.
     *
     * @acess private
     */
    private function setBool(string $inputstr)
    {
        if (chop($inputstr) == "t") {
            return true;
        }
        return false;
    }


    /**
     * Process Array
     *
     * This function converts a multi dimensional array to a one dimensional array
     * containing a key and value.  The key is a dot separated path made up of the
     * original keys.
     *
     * @param array  $input     The array to be processed to a sot separated path.
     * @param string $arrayroot The current path.
     *
     * @return array A one dimensional array containing the dpt separated path and value.
     *
     * @access private
     */
    private function processArray(array $input, string $arrayroot = "")
    {
        $result = array();

        foreach ($input as $key => $value) {
            if ($arrayroot == "") {
                $path = $key;
            } else {
                $path = $arrayroot . "." . $key;
            }
            if (is_array($value)) {
                $localresult = $this->processArray($value, $path);
                $result = \array_merge($result, $localresult);
            } else {
                $result[$path] = $value;
            }
        }
        return $result;
    }
}
