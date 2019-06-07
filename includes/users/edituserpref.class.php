<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Users
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace webtemplate\users;

/**
 * UserPreferences Class
 *
 **/
class EditUserPref
{
    /**
     * The userid of the user being updated
     *
     * @var    integer
     * @access protected
     */

    protected $userId = 0;

    /**
     * Database MDB2 Database Connection Object
     *
     * @var    \webtemplate\db\driver\InterfaceDatabaseDriver
     * @access protected
     */

    protected $db = null;


    /**
     * An associative array of installed Themes.
     *
     * @var    array
     * @access protected
     */

    protected $installedThemes = array();

    /**
     * An associative array of Zoom text Options
     *
     * @var    array
     * @access protected
     */

    protected $installedZoomText = array();

    /**
     * An associative array of Display Row Options
     *
     * @var    array
     * @access protected
     */

    protected $installedDisplayRows = array();

    /**
     * Last Message
     *
     * @var    string
     * @access protected
     */
    protected $lastMsg = '';

    /**
     * New Theme Value
     *
     * @var    string
     * @access protected
     */
    protected $newThemeValue = '';

    /**
     * New Zoom Text Area Value
     *
     * @var    string
     * @access protected
     */
    protected $newZoomTextAreaValue = '';

    /**
     * New Display Rows Value
     *
     * @var    string
     * @access protected
     */
    protected $newDisplayRowsValue = '';

    /**
     * Preferences Changed
     *
     * @var    boolean
     * @access protected
     */
    protected $preferencesChanged = false;


    /**
    * User Class Constructor
    *
    * @param \webtemplate\db\driver\InterfaceDatabaseDriver $db     Database Object.
    * @param integer                                        $userId Id of current user.
    *
    * @access public
    */
    public function __construct(
        \webtemplate\db\driver\InterfaceDatabaseDriver $db,
        int $userId = 0
    ) {
        $this->db     = $db ;
        $this->userId = $userId;
    } // end constructor


    /**
     * Function to set the $userid which is separate from the class constructor
     *
     * @param string $userId Id of user whose preferences are being updated.
     *
     * @return void
     *
     * @access public
     */
    public function setUserId(string $userId)
    {
        $this->userId = $userId;
    }

    /**
    * Get the last message created by class
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
     * Load Preferences
     *
     * This function creates the arrays to be used by the dropdown menu
     * items used in the UsersPreferenceForm where the user can select
     * to use the site default values or his own.
     *
     * @param string $rootDir         Web Server Root Directory.
     * @param array  $sitePreferences Pointer to the application Default Preferences.
     *
     * @return boolean True if Preferences Loaded okay
     *
     * @access public
     */
    final public function loadPreferences(string $rootDir, array $sitePreferences)
    {
        // Set Global Variables
        $gotThemes = true;
        $msg = '';

        // Get the Style Directory
        $styleDir = $rootDir . "/style";
        $defaultTheme = $sitePreferences['theme']['value'] . " (Site Default)";

        $tempSelected = false;
        $installedThemes = array();
        $installedThemes[] = array("name" => "default",
                                  "selected" => $tempSelected,
                                   "title" => $defaultTheme);


        if ($dirHandle = @opendir($styleDir)) {
            while (($file = readdir($dirHandle)) !== false) {
                if (is_dir($styleDir . '/' . $file)) {
                    if ($file != "." && $file != "..") {
                        $installedThemes[] = array("name" => $file,
                                        "selected" => $tempSelected,
                                        "title" => $file);
                    }
                }
            }
            closedir($dirHandle);
            if (count($installedThemes) == 1) {
                $gotThemes = false;
                $msg = gettext("No Theme have been installed\n");
            }
        } else {
            // Unable to read the contents of the style directory
            $gotThemes = false;
            $msg = gettext("Unable to Open Style Directory\n");
        }

        // Load the ZOOM Text ARRAY
        $installedZoomText = array();
        if ($sitePreferences['zoomtext']['value'] == true) {
            $defaultZoomText = 'On (Site Default)';
        } else {
            $defaultZoomText = 'Off (Site Default)';
        }
        $installedZoomText[] = array("name" => 'default',
                                     "selected" => $tempSelected,
                                     "title" => $defaultZoomText);

        $installedZoomText[] = array("name" => 'on',
                                     "selected" => $tempSelected,
                                     "title" => 'On');

        $installedZoomText[] = array("name" => 'off',
                                     "selected" => $tempSelected,
                                     "title" => 'Off');

        // Load the Display Rows Array
        $installedDisplayRows = array();
        $defDisplayRowText =  $sitePreferences['displayrows']['value'];
        $defDisplayRowText .= '0 (Site Default)';
        $displayRowName = 'default';
        $installedDisplayRows[] = array("name" => $displayRowName,
                                        "selected" => $tempSelected,
                                        "title" => $defDisplayRowText);

        $CurrentDisplayRow = '1';
        $defDisplayRowText =  $CurrentDisplayRow . '0';
        $displayRowName = $CurrentDisplayRow ;
        $installedDisplayRows[] = array("name" => $displayRowName,
                                        "selected" => $tempSelected,
                                        "title" => $defDisplayRowText);

        $CurrentDisplayRow = '2';
        $defDisplayRowText =  $CurrentDisplayRow . '0';
        $displayRowName = $CurrentDisplayRow ;
        $installedDisplayRows[] = array("name" => $displayRowName,
                                        "selected" => $tempSelected,
                                        "title" => $defDisplayRowText);

        $CurrentDisplayRow = '3';
        $defDisplayRowText =  $CurrentDisplayRow . '0';
        $displayRowName = $CurrentDisplayRow ;
        $installedDisplayRows[] = array("name" => $displayRowName,
                                        "selected" => $tempSelected,
                                        "title" => $defDisplayRowText);

        $CurrentDisplayRow = '4';
        $defDisplayRowText =  $CurrentDisplayRow . '0';
        $displayRowName = $CurrentDisplayRow ;
        $installedDisplayRows[] = array("name" => $displayRowName,
                                        "selected" => $tempSelected,
                                        "title" => $defDisplayRowText);

        $CurrentDisplayRow = '5';
        $defDisplayRowText =  $CurrentDisplayRow . '0';
        $displayRowName = $CurrentDisplayRow ;
        $installedDisplayRows[] = array("name" => $displayRowName,
                                        "selected" => $tempSelected,
                                        "title" => $defDisplayRowText);

        $this->installedThemes = $installedThemes;
        $this->installedZoomText = $installedZoomText;
        $this->installedDisplayRows = $installedDisplayRows ;

        $gotCurrentPrefs = $this->updatePreferences();
        $this->lastMsg = $msg;

        return $gotCurrentPrefs and $gotThemes;
    }  // End of getThemes


    /**
    * Get the installed themes array
    *
    * @return array Installed Themes
    *
    * @access public
    */
    final public function getInstalledThemes()
    {
        return $this->installedThemes;
    }

    /**
    * Get the Zoom Text Option array
    *
    * @return array Zoom Text Options
    *
    * @access public
    */
    final public function getZoomTextOptions()
    {
        return $this->installedZoomText;
    }

    /**
    * Get the Display Rows Options array
    *
    * @return array Display Row Options
    *
    * @access public
    */
    final public function getDisplayRowsOptions()
    {
        return $this->installedDisplayRows;
    }


    /**
     * Validate UserPreferences
     *
     * @param array $inputArray Pointer to an array containing users input.
     *
     * @return boolean true if Preferences Validated
     *
     * @access public
     */
    final public function validateUserPreferences(array &$inputArray)
    {
        // Reset all new preferences to blank
        $this->newThemeValue = '';
        $this->newZoomTextAreaValue = '';
        $this->newDisplayRowsValue = '';

        // Set local flags
        $dataValid = true;
        $this->lastMsg = '';

        //Validate the theme.
        $result = $this->validateTheme($inputArray);
        if ($result == false) {
            $dataValid = false;
        }


        //Validate the ZoomText area
        $result = $this->validateZoomTextAreas($inputArray);
        if ($result == false) {
            $dataValid = false;
        }

         // Validate the Display Rows input
        $result = $this->validateDisplayRows($inputArray);
        if ($result == false) {
            $dataValid = false;
        }

        return $dataValid;
    }



    /**
     * Check if the User's Preferences have changed
     *
     * @return boolean true if Preferences changed
     *
     * @access public
     */
    final public function checkUserPreferencesChanged()
    {
        $preferencesChanged = false;
        $this->lastMsg = '';

        // Check if Theme has Changed
        $result = $this->themeChanged();
        if ($result == true) {
            $preferencesChanged = true;
        }

        // Check if Zoom Text has Changed
        $result = $this->zoomTextAreaChanged();
        if ($result == true) {
            $preferencesChanged = true;
        }

        // Check if Display Rows has Changed
        $result = $this->displayRowsChanged();
        if ($result == true) {
            $preferencesChanged = true;
        }

        if ($preferencesChanged == false) {
            $this->lastMsg .= gettext("No changes have been made\n");
        }

        $this->preferencesChanged = $preferencesChanged;
        return $preferencesChanged;
    }




    /**
     * Save the UserPreferences
     *
     * @return boolean true if Preferences Saved
     *
     * @access public
     */
    final public function saveUserPreferences()
    {
        // Local Flags
        $userPrefsDeletedok = false;
        $userPrefsSavedOk = true;

        //initialise local variables
        $msg = '';
        $updateok = true;
        $datadeleted = false;

        // Stop if Preferences not Changed
        if ($this->preferencesChanged == false) {
            $this->lastMsg = gettext("No Changes Made.\n");
            return false;
        }

        // Set up the database transaction
        $result = $this->db->startTransaction();
        if ($result == false) {
            $this->lastMsg = gettext("Unable to create database transaction");
            return false;
        }

        $userId = $this->userId;
        $data = array('user_id' => $userId);
        $result = $this->db->dbdelete('userprefs', $data);
        if (!\webtemplate\general\General::isError($result)) {
            // Save Theme
            $result = $this->saveTheme();
            if ($result == false) {
                $updateok = false;
            }

            // Save Zoom Text
            $result = $this->saveZoomTextArea();
            if ($result == false) {
                $updateok = false;
            }


            // Save Display Rows
            $result = $this->saveDisplayRows();
            if ($result == false) {
                $updateok = false;
            }
        } else {
            $updateok = false;
        }

        $result = $this->db->endTransaction($updateok);
        if ($result == false) {
            $this->lastMsg = gettext("Unable to save user prefrences");
            $userPrefsSavedOk = false;
        }

        return $userPrefsSavedOk;
    }


    /**
     * Update Preferences
     *
     * @return boolean True All the Time
     *
     * @access public
     */
    final public function updatePreferences()
    {
        $msg = '';
        // Set Current Preferences
        $localTheme = 'default';
        $localZoomText = 'default';
        $localDisplayRows = 'default';
        $gotCurrentPrefs  = true;

        $this->getUsersPrefs($localTheme, $localZoomText, $localDisplayRows);

        // Set Selected Theme
        foreach ($this->installedThemes as &$value) {
            if ($value['name'] == $localTheme) {
                $value['selected'] = true;
            } else {
                $value['selected'] = false;
            }
        }

        // Set Selected Zoom text
        if ($localZoomText == 'true') {
            $localZoomText = 'on';
        } elseif ($localZoomText == 'false') {
            $localZoomText = 'off';
        }

        foreach ($this->installedZoomText as &$value) {
            if ($value['name'] == $localZoomText) {
                $value['selected'] = true;
            } else {
                $value['selected'] = false;
            }
        }
        // Set Selected Display rows
        foreach ($this->installedDisplayRows as &$value) {
            if ($value['name'] == $localDisplayRows) {
                $value['selected'] = true;
            } else {
                $value['selected'] = false;
            }
        }
        $this->lastMsg = $msg;
        return $gotCurrentPrefs;
    }

    /**
     * This function get the users preferences from the database
     *
     * @param string $localTheme       Flag containing value of css theme to be used.
     * @param string $localZoomText    Flag showing in textareas are to be enlarged.
     * @param string $localDisplayRows Flag showing number of rows to be displayed.
     *
     * @return boolean true in all cases
     */
    private function getUsersPrefs(
        string &$localTheme,
        string &$localZoomText,
        string &$localDisplayRows
    ) {
        if ($this->userId != 0) {
            $fieldNames = array('settingname', 'settingvalue');
            $searchData = array('user_id' => $this->userId);
            $preferenceArray = $this->db->dbselectmultiple(
                'userprefs',
                $fieldNames,
                $searchData
            );

            if (!\webtemplate\general\General::isError($preferenceArray)) {
                foreach ($preferenceArray as $value) {
                    if (chop($value['settingname']) == 'theme') {
                        $localTheme = chop($value['settingvalue']);
                    }
                    if (chop($value['settingname']) == 'zoomtext') {
                        $localZoomText = chop($value['settingvalue']);
                    }
                    if (chop($value['settingname']) == 'displayrows') {
                        $localDisplayRows = chop($value['settingvalue']);
                    }
                }
            }
        }
        return true;
    }
    /**
     * Get new Theme
     *
     * @param string $defaultTheme The default theme used by the application.
     *
     * @return string New Theme
     *
     * @access public
     */
    final public function getNewTheme(string $defaultTheme)
    {
        if (($this->newThemeValue == 'default') or ($this->newThemeValue == '')) {
            return $defaultTheme;
        } else {
            return $this->newThemeValue;
        }
    }



    /**
     *  Validate the users chosen theme
     *
     * @param array $inputArray Pointer to an array containing users input.
     *
     * @return boolen Return true if theme validated false otherwise
     * @access private
     */
    private function validateTheme(array &$inputArray)
    {
        // Local Flags
        $themeValid = false;
        $dataValid = true;

        if (isset($inputArray['theme'])) {
            if (preg_match("/^([a-zA-Z]{4,20})$/", $inputArray['theme'])) {
                $tempTheme = $inputArray['theme'];
                foreach ($this->installedThemes as &$value) {
                    if ($value['name'] == $tempTheme) {
                        $themeValid = true;
                        $this->newThemeValue = $tempTheme;
                    }
                }
            }
        } else {
            $themeValid = true;
        }
        if ($themeValid == false) {
            $dataValid = false;
            $this->lastMsg .= gettext(
                "The chosen theme is not installed on this system\n"
            );
        }
        return $dataValid;
    }

    /**
     *  Validate the the number of rows of data to display
     *
     * @param array $inputArray Pointer to an array containing users input.
     *
     * @return boolen Return true if number of rows validate,  false otherwise
     * @access private
     */
    private function validateDisplayRows(array &$inputArray)
    {
        $displayRowsValid = false;
        $dataValid = true;

        if (isset($inputArray['display_rows'])) {
            $filter = "/(default)|(1|2|3|4|5)/";
            if (preg_match($filter, $inputArray['display_rows']) == true) {
                $this->newDisplayRowsValue =  $inputArray['display_rows'];
                $displayRowsValid = true;
            }
        } else {
            $displayRowsValid = true;
        }

        if ($displayRowsValid == false) {
            $dataValid = false;
            $this->lastMsg .= gettext(
                "The number of display rows chosen is invalid\n"
            );
        }

        return $dataValid;
    }


    /**
     *  Validate the zoom textarea input
     *
     * @param array $inputArray Pointer to an array containing users input.
     *
     * @return boolen Return true if zoom text validated false otherwise
     * @access private
     */
    private function validateZoomTextAreas(array &$inputArray)
    {
        $zoomTextAreaValid = false;
        $dataValid = true;

        if (isset($inputArray['zoom_textareas'])) {
            if (preg_match(
                "/^(default)|(on)|(off)$/",
                $inputArray['zoom_textareas']
            ) == true
            ) {
                $zoomTextAreaValid = true;
                $this->newZoomTextAreaValue = $inputArray['zoom_textareas'];
            }
        } else {
            $zoomTextAreaValid = true;
        }
        if ($zoomTextAreaValid == false) {
            $this->lastMsg .= gettext("The ZoomTextArea option is invalid.\n");
            $dataValid = false;
        }
        return $dataValid;
    }

    /**
     * Function to check if the user has chosen a new theme
     *
     * @return boolean Return true if the user has chosen a new theme
     *
     * @access private
     */
    private function themeChanged()
    {
        $themchanged = false;

        foreach ($this->installedThemes as $value) {
            if (($value['selected'])
                and ($this->newThemeValue != '')
                and ($value['name'] != $this->newThemeValue)
            ) {
                $themchanged = true;
                $this->lastMsg .= gettext("Theme Updated\n");
            }
        }
        return $themchanged;
    }


    /**
     * Function to check if the user has chosen a different setting for enlarging
     * text area edit boxes
     *
     * @return boolean Returns true if the user has chosen a new value.
     *
     * @access private
     */
    private function zoomTextAreaChanged()
    {
        $zoomTextAreaChanged = false;

        foreach ($this->installedZoomText as $value) {
            if (($value['selected'])
                and ($this->newZoomTextAreaValue != '')
                and ($value['name'] != $this->newZoomTextAreaValue)
            ) {
                $zoomTextAreaChanged = true;
                $this->lastMsg .= gettext("Text Area Zoom Updated\n");
            }
        }
        return $zoomTextAreaChanged;
    }

    /**
     * Function to check if the user has chosen a different setting for number of
     * rows of data to show on the screen
     *
     * @return boolean Returns true if the user has chosen a new value.
     *
     * @access private
     */
    private function displayRowsChanged()
    {
        $displayRowsChanged = false;

        foreach ($this->installedDisplayRows as $value) {
            if (($value['selected'])
                and ($this->newDisplayRowsValue != '')
                and ($value['name'] != $this->newDisplayRowsValue)
            ) {
                $displayRowsChanged = true;
                $this->lastMsg .= gettext(
                    "Number of Data Rows to display updated\n"
                );
            }
        }
        return $displayRowsChanged;
    }

    /**
     * This function saves the users prefered theme if they have selected one
     *
     * @return boolean True if the data is saved okay
     *
     * @access private
     */
    private function saveTheme()
    {
        $updateok = true;

        if (($this->newThemeValue != 'default')
            and ($this->newThemeValue != '')
        ) {
            $data = array(
                'settingname' => 'theme',
                'settingvalue' => $this->newThemeValue,
                'user_id' => $this->userId
            );
            $result = $this->db->dbinsert('userprefs', $data);
            if (\webtemplate\general\General::isError($result)) {
                $updateok = false;
            }
        }
        return $updateok;
    }

    /**
     * This function saves the users prefered option for zooming text boxes
     *
     * @return boolean True if data is saved okay
     *
     * @access private
     */
    private function saveZoomTextArea()
    {
        $updateok = true;

        if (($this->newZoomTextAreaValue != 'default')
            and ($this->newZoomTextAreaValue != '')
        ) {
            if ($this->newZoomTextAreaValue == 'on') {
                $data = array(
                    'settingname' => 'zoomtext',
                    'settingvalue' => 'true',
                    'user_id' => $this->userId
                );
                $result = $this->db->dbinsert('userprefs', $data);
                if (\webtemplate\general\General::isError($result)) {
                    $updateok = false;
                }
            } else {
                $data = array(
                   'settingname' => 'zoomtext',
                   'settingvalue' => 'false',
                    'user_id' => $this->userId
                );
                $result = $this->db->dbinsert('userprefs', $data);
                if (\webtemplate\general\General::isError($result)) {
                    $updateok = false;
                }
            }
        }
        return $updateok;
    }

    /**
     * This function saves the number of rows of data a user wandt to display
     *
     * @return boolean Tue if the data is saved okay
     *
     * @access private
     */
    private function saveDisplayRows()
    {
        $updateok = true;

        if (($this->newDisplayRowsValue  != 'default')
            and ($this->newDisplayRowsValue  != '')
        ) {
            $data = array(
                'settingname' => 'displayrows',
                'settingvalue' => $this->newDisplayRowsValue,
                'user_id' => $this->userId
            );
            $result = $this->db->dbinsert('userprefs', $data);
            if (\webtemplate\general\General::isError($result)) {
                $updateok = false;
            }
        }
        return $updateok;
    }
}
