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

namespace g7mzr\webtemplate\users;

/**
 * Webtemplate User Register Class
 *
 **/
class Register
{
    /**
     * The current user's user name
     *
     * @var    string
     * @access protected
     */
    protected $userName = "";

    /**
     * The current user's real name
     *
     * @var    string
     * @access protected
     */
    protected $realName = "";

    /**
     * The current user's email address
     *
     * @var    string
     * @access protected
     */
    protected $userEmail = "";

    /**
     * The userid of the current user
     *
     * @var    integer
     * @access protected
     */
    protected $userId = 0;

    /**
      * The current user's encrypted password
     *
      * @var    string
      * @access protected
      */
    protected $encryptedPasswd = '';

    /**
     * Database connection object
     *
     * @var    \g7mzr\db\interfaces\InterfaceDatabaseDriver
     * @access protected
     */
    protected $db  = null;

    /**
     * An array holding the current user's permissions
     *
     * @var    array
     * @access protected
     */
    protected $permissions = array();

    /**
     * The name of the theme used by the current user
     *
     * @var    string
     * @access protected
     */
    protected $theme = '';

    /**
     * Flag to show if current user wants enlarged textboxes
     *
     * @var    boolean
     * @access protected
     */
    protected $zoomText = false;

    /**
     * The number of search result rows to show.
     *
     * @var    string
     * @access protected
     */
    protected $displayrows = '1';

    /**
     * The Default Style Directory
     *
     * @var    string
     * @access protected
     */
    protected $styleDir = '';

    /**
     * User Class Constructor
     *
     * @param \g7mzr\db\interfaces\InterfaceDatabaseDriver $db Database Connection Object.
     *
     * @access public
     */
    public function __construct(\g7mzr\db\interfaces\InterfaceDatabaseDriver $db)
    {
        $this->db       = $db ;
        $this->userName = "None";
        $this->styleDir = __DIR__ . "/style/";
    }


    /**
     * Register a currently logged in user.  Store their realname, userid,
     * encrypted password, permissions and preferences in the USER Class object.
     *
     * @param string $userName    Name of user logged in.
     * @param array  $preferences Array containing the site preferences.
     * @param array  $userData    Array to contain the user data.
     *
     * @return mixed True if user registered.  Returns WEBTEMPLATE::Error on error.
     *
     * @access public
     */
    final public function register(string $userName, array $preferences, array &$userData)
    {

        // Flush all data
        $this->userName = "";
        $this->realName = "None";
        $this->userEmail = "";
        $this->userId = 0;
        $this->encryptedPasswd = '';
        $this->permissions = array();
        $this->theme = '';
        $this->zoomText = false;
        $this->displayrows = '1';

        // Save usernaem in the Class vairable
        $this->userName = $userName;

        // Get the users details from the database
        $fieldNames = array(
            'user_id',
            'user_name',
            'user_passwd',
            'user_realname',
            'user_email',
            'user_enabled',
            'user_disable_mail',
            'date(last_seen_date)'
        );
        $searchData = array('user_name' => $this->userName);
        $userArray = $this->db->dbselectsingle('users', $fieldNames, $searchData);
        if (\g7mzr\db\common\Common::isError($userArray)) {
            // Error encountered when selecting user from database
            // Registration process has failed
            $msg = gettext("Unable to Register User");
            return\g7mzr\webtemplate\general\General::raiseError($msg, 1);
        }

        // Populate the class variables with the collected data
        $this->realName = $userArray['user_realname'];
        $this->userEmail = $userArray['user_email'];
        $this->userId = $userArray['user_id'];

        $this->encryptedPasswd = $userArray['user_passwd'];

        // Get the Users Preferences if they have any.
        $this->getPreferences($preferences);

        // Failure to set last seen date fails registration
        $insertData = array('last_seen_date' => 'now()');
        $searchData = array('user_id' => (string) $this->userId);
        $result = $this->db->dbupdate('users', $insertData, $searchData);
        if (\g7mzr\db\common\Common::isError($result)) {
            // Error encountered when selecting user from database
            // Registration process has failed
            $msg = gettext("Unable to Register User");
            return\g7mzr\webtemplate\general\General::raiseError($msg, 1);
        }

        $userData['userName'] = $this->userName;
        $userData['userId'] = $this->userId;
        $userData['realName'] = $this->realName;
        $userData['userEmail'] = $this->userEmail;
        $userData['displayRows'] = $this->displayrows;

        $userData['theme'] = $this->theme;
        $userData['zoomText'] = $this->zoomText;
        $userData['permissions'] = $this->permissions;
        $userData['encryptedPasswd'] = $this->encryptedPasswd;

        if (!array_key_exists('passwdAgeMsg', $userData)) {
            $userData['passwdAgeMsg'] = '';
        }
        if (!array_key_exists('last_seen_date', $userData)) {
            $userData['last_seen_date'] = '';
        }
        return true;
    }

    /**
     * Function to get user's preferences from the database
     *
     * This function gets the users preferences from the database.  If no preferences
     * are found or there is a database error the default preferences are used.
     *
     * @param array $preferences Array containing the site preferences.
     *
     * @return boolen True
     *
     * @access private
     */
    private function getPreferences(array $preferences)
    {

        // Set the users preferences
        // Set the default Site preferences for user.
        $this->theme = $preferences['theme']['value'];
        $this->zoomText = $preferences['zoomtext']['value'];
        $this->displayrows = $preferences['displayrows']['value'];

        // Search database for User Preferences
        $fieldNames = array('settingname', 'settingvalue');
        $searchData = array('user_id' => (string) $this->userId);
        $userArray = $this->db->dbselectmultiple(
            'userprefs',
            $fieldNames,
            $searchData
        );

        // Missing Preferences do not stop registration
        if (!\g7mzr\webtemplate\general\General::isError($userArray)) {
            foreach ($userArray as $value) {
                // Walk through the results
                // Get theme
                $this->getTheme($value, $preferences['theme']['enabled']);

                // Check zoom text
                $this->getZoomArea($value, $preferences['zoomtext']['enabled']);

                // Check Display rows
                $this->getRows(
                    $value,
                    $preferences['displayrows']['enabled']
                );
            }
        }
        return true;
    }

    /**
     * Function to get the user's preferred theme.
     * It is called by getPreferences
     *
     * @param array   $value   The array holding the users preferences.
     * @param boolean $enabled True if user selectable theme is enabled.
     *
     * @return boolean True in all cases
     */
    private function getTheme(array $value, bool $enabled)
    {
        // Check the theme preference
        if ((chop($value['settingname']) == 'theme') and ($enabled == true)) {
            // The user has set their own theme
            // Get the user chosen theme
            $localTheme = chop($value['settingvalue']);

            // The file name for the themes main file
            $testfile = $this->styleDir . $localTheme . '/main.css';

            if (file_exists($testfile)) {
                // The theme exists.
                // Set it as the users chosen theme
                $this->theme = $localTheme;
            }
        }

        return true;
    }

    /**
     * Function to get the user's text area zoom flag
     * It is called by getPreferences
     *
     * @param array   $value   The array holding the users preferences.
     * @param boolean $enabled True if user selectable theme is enabled.
     *
     * @return boolean True in all cases
     */
    private function getZoomArea(array $value, bool $enabled)
    {
        if ((chop($value['settingname']) == 'zoomtext') and ($enabled == true)) {
            // The user has set their own zoon text.
            if (chop($value['settingvalue']) == 'true') {
                $this->zoomText = true;
            } else {
                $this->zoomText = false;
            }
        }

        return true;
    }
    /**
     * Function to get the user's preferred theme.
     * It is called by getPreferences
     *
     * @param array   $value   The array holding the users preferences.
     * @param boolean $enabled True if user selectable theme is enabled.
     *
     * @return boolean True in all cases
     */
    private function getRows(array $value, bool $enabled)
    {
        if ((chop($value['settingname']) == 'displayrows') and ($enabled == true)) {
            // The user has set the number of sata rows to display
            $this->displayrows = chop($value['settingvalue']);
        }

        return true;
    }
}
