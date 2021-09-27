<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Admin
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\admin;

/**
 * Preferences Interface Class
 *
 **/
class Preferences
{

    /**
     * Theme Value
     *
     * @var    string
     * @access protected
     */
    protected $themeValue = '';

    /**
     * Theme Enabled
     *
     * @var    boolean
     * @access protected
     */
    protected $themeEnabled = false;

    /**
     * Zoom Text Value
     *
     * @var    boolean
     * @access protected
     */
    protected $zoomtextValue = false;

    /**
     * Zoom Text Enabled
     *
     * @var    boolean
     * @access protected
     */
    protected $zoomtextEnabled = false;


    /**
     * Display Rows Value
     *
     * @var    string
     * @access protected
     */
    protected $displayRowsValue = '';

    /**
     * Display Rows Enabled
     *
     * @var    boolean
     * @access protected
     */
    protected $displayRowsEnabled = false;

    /**
     * Installed Themes
     *
     * @var    array
     * @access protected
     */
    protected $installedThemes = array();


    /**
     * Last Message
     *
     * @var    string
     * @access protected
     */
    protected $lastMsg = '';

    /**
     * Configuration class object
     *
     * @var\g7mzr\webtemplate\config\Configuration
     */
    protected $config;

    /**
     * Constructor
     *
     * @param \g7mzr\webtemplate\config\Configure $config Configuration class.
     *
     * @access public
     */
    public function __construct(\g7mzr\webtemplate\config\Configure $config)
    {

        $this->config = $config;
        $this->themeValue = $this->config->read('pref.theme.value');
        $this->themeEnabled = $this->config->read('pref.theme.enabled');
        $this->zoomtextValue = $this->config->read('pref.zoomtext.value');
        $this->zoomtextEnabled = $this->config->read('pref.zoomtext.enabled');
        $this->displayRowsValue = $this->config->read('pref.displayrows.value');
        $this->displayRowsEnabled = $this->config->read('pref.displayrows.enabled');
    }

    /**
     * Load the list of Themes installed on this system by scanning the styles
     * directory which is located in the $rootDir.
     *
     * @param string $rootDir Web Server Root Directory.
     *
     * @return mixed Containing all installed themes or WEBTEMPLATE error
     *
     * @access public
     */
    final public function loadThemes(string $rootDir)
    {
        $styleDir = $rootDir . "/style";
        $installedThemes = array();
        $gotThemes = true;

        if ($dirHandle = @opendir($styleDir)) {
            while (($file = readdir($dirHandle)) !== false) {
                if (is_dir($styleDir . '/' . $file)) {
                    if ($file != "." && $file != ".." && $file[0] != ".") {
                        $installedThemes[] = array(
                            "name" => $file,
                            "selected" => false
                        );
                    }
                }
            }
            closedir($dirHandle);
            if (count($installedThemes) == 0) {
                $gotThemes = false;
                $this->lastMsg = gettext("No Theme have been installed");
            }
        } else {
            // Unable to read the contents of the style directory
            $gotThemes = false;
            $this->lastMsg = gettext("Unable to Open Style Directory");
        }
        $this->installedThemes = $installedThemes;
        return $gotThemes;
    }

    /**
    * Set the Selected element of the applications default theme. Using
    * the themeValue Variable.
    *
    * @return boolean True if default theme set
    *
    * @access public
    */
    final public function setDefaultThemes()
    {
        $defaultThemeFound = false;
        foreach ($this->installedThemes as &$value) {
            if ($value['name'] == $this->themeValue) {
                $value['selected'] = true;
                $defaultThemeFound = true;
            } else {
                $value['selected'] = false;
            }
        }
        return $defaultThemeFound;
    }

    /**
    * Return the array of installed themes
    *
    * @return array loaded themes
    *
    * @access public
    */
    final public function getThemes()
    {
        return $this->installedThemes;
    }

    /**
    * Get the last message created by the class
    *
    * @return string Last Message created by CLASS
    *
    * @access public
    */
    final public function getLastMsg()
    {
        return $this->lastMsg;
    }

    /**
    * Returns the current values of the preferences is an array.
    *
    * @return array of preferences
    *
    * @access public
    */
    final public function getCurrentPreferences()
    {
        $preferences = array();
        $preferences['theme']['value'] = $this->themeValue;
        $preferences['theme']['enabled'] = $this->themeEnabled;
        $preferences['zoomtext']['value'] = $this->zoomtextValue;
        $preferences['zoomtext']['enabled'] = $this->zoomtextEnabled;
        $preferences['displayrows']['value'] = $this->displayRowsValue;
        $preferences['displayrows']['enabled'] = $this->displayRowsEnabled ;
        return $preferences;
    }

    /**
     * Save the current Preferences to a file called preferences.php
     * located in the $configDir.
     *
     * @return boolean true if Preferences Saved
     *
     * @access public
     */
    final public function savePrefFile()
    {
        // Write the data to the config class
        $this->savetoConfigurationClass();

        // Save the resulst to the file
        $result = $this->config->savePrefs();
        return $result;
    }

    /**
     * Validate the Preferences input by the users. lastMsg contains a list of
     * preferences which failed validation
     *
     * @param array $inputArray Pointer to an array containing users input.
     *
     * @return boolean true if all input Preferences Validated
     *
     * @access public
     */
    final public function validatePreferences(array &$inputArray)
    {

        // Set Default validation variables
        $dataValid = true;
        $this->lastMsg = '';

        //  SET UP THE DEFAULT THEME AND WITHER THE USER CAN CHANGE IT
        //Validate the theme.
        if (!$this->validateTheme($inputArray)) {
            $dataValid = false;
        }

        // This is a binary input. If it is set it is true.  If not set it is false
        $this->themeEnabled = isset($inputArray['theme-enabled']);

        // SET UP THE DEFAULT TEXT AREA ZOOM AND WITHER THE UERS CAN CHANGE IT
        //This is a non binary input. Valid data is either ON or OFF.
        // If input data is invalid default to off
        $this->validateTextAreaZoom($inputArray);

        // This is a binary input. If it is set it is true.  If not set it is false
        $this->zoomtextEnabled = isset($inputArray['zoom_textareas_enabled']);

        // SET UP THE DEFAULT NUMBER OF DATA ROWS TO DISPLAY AND WITHER THE USER CAN
        // CHANGE IT
        // Set the number of rows of information to be displayed.
        // The number here is multiplied by 100 to give the number of rows.
        $this->validateDisplayRows($inputArray);

        // Allow users to select the number of rows to be displayed.
        $this->displayRowsEnabled = isset($inputArray['display_rows_enabled']);

        return $dataValid;
    }

    /**
     * Validate the chosen theme
     *
     * @param array $inputArray Pointer to an array containing users input.
     *
     * @return boolean true if all theme is Validated
     *
     * @access private
     */
    private function validateTheme(array $inputArray)
    {
        $themeOK = false;

        // Themes must be checked first as it sets dataValid to true if it exists
        if (preg_match("/^([a-zA-Z]{4,20})$/", $inputArray['theme'])) {
            $tempTheme = $inputArray['theme'];
            foreach ($this->installedThemes as &$value) {
                if ($value['name'] == $tempTheme) {
                    $themeOK = true;
                    $this->themeValue = $tempTheme;
                }
            }
        }

        if ($themeOK == false) {
            $this->lastMsg .= gettext("Invalid Theme") . "\n";
        }

        return $themeOK;
    }

    /**
     * Validate the text area zoom flag
     *
     * @param array $inputArray Pointer to an array containing users input.
     *
     * @return boolean true
     *
     * @access private
     */
    private function validateTextAreaZoom(array &$inputArray)
    {
        $this->zoomtextValue = false;
        if (isset($inputArray['zoom_textareas'])) {
            if (preg_match("/^(on)|(off)$/", $inputArray['zoom_textareas'])) {
                if ($inputArray['zoom_textareas'] == 'on') {
                    $this->zoomtextValue = true;
                }
            }
        }
        return true;
    }

    /**
     * Validate the the number of records to display
     *
     * @param array $inputArray Pointer to an array containing users input.
     *
     * @return boolean true
     *
     * @access private
     */
    private function validateDisplayRows(array &$inputArray)
    {
        $this->displayRowsValue = '2';
        if (isset($inputArray['display_rows'])) {
            if (preg_match("/(1|2|3|4|5)/", $inputArray['display_rows'])) {
                $this->displayRowsValue = $inputArray['display_rows'];
            }
        }
        return true;
    }

    /**
     * Check if any of the prefernces have changed by comparing the locally
     * stored preferences with the ones located in $preferences.  lastMsg
     * contains a list of the preferences which have changed.
     *
     * @return boolean true if data changed
     *
     * @access public
     */
    final public function checkPreferencesChanged()
    {

        $dataUpdated = false;
        //Create Update String
        $msg = '';
        if ($this->themeValue != $this->config->read('pref.theme.value')) {
            $msg .= gettext("Theme Updated") . "\n";
            $dataUpdated = true;
        }
        if ($this->themeEnabled != $this->config->read('pref.theme.enabled')) {
            $msg .= gettext("Theme Enabled Updated") . "\n";
            $dataUpdated = true;
        }
        if ($this->zoomtextValue != $this->config->read('pref.zoomtext.value')) {
            $msg .= gettext("Zoom Text Areas Updated") . "\n";
            $dataUpdated = true;
        }
        if ($this->zoomtextEnabled != $this->config->read('pref.zoomtext.enabled')) {
            $msg .= gettext("Zoom Text Areas Enabled Updated") . "\n";
            $dataUpdated = true;
        }
        if (
            $this->displayRowsValue != $this->config->read(
                'pref.displayrows.value'
            )
        ) {
            $msg .= gettext("Display Rows Updated") . "\n";
            $dataUpdated = true;
        }
        if (
            $this->displayRowsEnabled != $this->config->read(
                'pref.displayrows.enabled'
            )
        ) {
            $msg .= gettext("Display Rows Enabled Updated") . "\n";
            $dataUpdated = true;
        }

        $this->lastMsg = $msg;
        return $dataUpdated;
    }

    /**
     * This function transfers the prefernces stored in this class to the
     * Configuration Class.
     *
     * @return void
     *
     * @access private
     */
    private function savetoConfigurationClass()
    {
        $this->config->write('pref.theme.value', $this->themeValue);
        $this->config->write('pref.theme.enabled', $this->themeEnabled);
        $this->config->write('pref.zoomtext.value', $this->zoomtextValue);
        $this->config->write('pref.zoomtext.enabled', $this->zoomtextEnabled);
        $this->config->write('pref.displayrows.value', $this->displayRowsValue);
        $this->config->write('pref.displayrows.enabled', $this->displayRowsEnabled);
    }
}
