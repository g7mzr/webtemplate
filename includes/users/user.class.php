<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\users;

/**
 * Webtemplate Config Class
 *
 * @category Webtemplate
 * @package  Users
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

class User
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
     * The date the user last logged in.  It is only valid when the
     * user logs in otherwise it remains an empty string.
     *
     * @var    string
     * @access protected
     */
    protected $last_seen_date = '';

    /**
      * The current user's encrypted password
     *
      * @var    string
      * @access protected
      */
    protected $encryptedPasswd = '';

    /**
     * Database MDB2 Database connection object
     *
     * @var    object
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
     * String containing the current status of the users password
     *
     * @var    string
     * @access protected
     */
    protected $passwdagemsg = '';

    /**
     * The Default Style Directory
     *
     * @var    string
     * @access protected
     */
    protected $styleDir = '';

    /**
     * Integer containing the failed login timeout
     *
     * @var    integer
     * @access protected
     */
    protected $failedLoginTimeout = 15;

    /**
    * User Class Constructor
    *
    * @param array $db MDB2 Database Connection Object
    *
    * @access public
    */
    public function __construct($db)
    {
        $this->db      = $db ;
        $this->userName = "None";
        $this->styleDir = __DIR__ . "/style/";
    } // end constructor


    /**
     * Login and register the current user.  Once a user is logged in only the
     * register function needs to be called.
     *
     * @param string  $userName  Name of user to be logged in.
     * @param string  $passwd    Password for user
     * @param integer $passwdage Control Parameter for password aging.
     * @param array   $config    Array containing the site preferences
     *
     * @return mixed True if user logged in.  Returns WEBTEMPLATE::Error if unable
     *               to complete login
     *               error code 1 = system error, 2 = account locked,
     *               3 = invalid username/password, 4 = unable to register user
     *
     * @access public
     */
    final public function login($userName, $passwd, $passwdage, $config)
    {

         //date_default_timezone_set('Europe/London');
        // Set Passwd Age Msg to default value
        $this->passwdagemsg = '';

        // Set up the default variables
        // User not logged in
        $loggedin = false;

        // Transfer user name to local variable
        $tempuser = $userName;

        // Default WEBTEMPLATE Error Code
        $errorCode = 1;

        $fields = array('
            user_name',
            'last_seen_date',
            'user_enabled',
            'user_passwd',
            'date(passwd_changed) as passwd_changed',
            'last_failed_login'
        );
        $searchData = array (
            'user_name' => $tempuser,
        );

        $userArray = $this->db->dbselectsingle('users', $fields, $searchData);

        // Test if data has been returned
        if (\webtemplate\general\General::isError($userArray)) {
            if ($userArray->getCode() == DB_ERROR_NOT_FOUND) {
                // Invalid Username and password
                $errorMsg = gettext("Invalid Username and password");
                $errorCode = 3;
            } else {
                // Error Running SQL Query
                $errorMsg = gettext("Error Running SQL Query");
                $errorCode = 1;
            }
            return \webtemplate\general\General::raiseError($errorMsg, $errorCode);
        }

        // Test if the correct password has been entered
        $userpwd = chop($userArray['user_passwd']);
        if (\webtemplate\general\General::verifyPasswd($passwd, $userpwd) == false) {
            // Set the Last invalid Password field
            $insertData = array('last_failed_login' => 'now()');
            $searchData = array('user_name' => $userArray['user_name']);
            $result = $this->db->dbupdate('users', $insertData, $searchData);

            // Return Error Message
            $errorMsg = gettext("Invalid Username and password");
            $errorCode = 1;
            return \webtemplate\general\General::raiseError($errorMsg, $errorCode);
        }

        // Check is is more than x seconds since the last failed login
        $last_failed_login = strtotime(chop($userArray['last_failed_login']));

        if (($last_failed_login + $this->failedLoginTimeout) > time()) {
            // Set the Last invalid Password field
            $insertData = array('last_failed_login' => 'now()');
            $searchData = array('user_name' => $userArray['user_name']);
            $result = $this->db->dbupdate('users', $insertData, $searchData);

            // Return Error Message
            $errorMsg = gettext("Invalid Username and password");
            $errorCode = 1;
            return \webtemplate\general\General::raiseError($errorMsg, $errorCode);
        }

        // Check if the account has been locked.
        $userEnabled = chop($userArray['user_enabled']);
        if ($userEnabled == 'N') {
            // Account Locked.
            $errorMsg = gettext("Invalid Username and password");
            $errorCode = 2;
            return \webtemplate\general\General::raiseError($errorMsg, $errorCode);
        }

        // User is logged in
        $loggedin = true;
        $this->last_seen_date = chop($userArray['last_seen_date']);
        $changedate = strtotime(chop($userArray['passwd_changed']));

        $result = $this->passwordAge($passwdage, $changedate);

        if (\webtemplate\general\General::isError($result)) {
            return $result;
        }
        // Logged in. Register the user
        $registeredOk = $this->register($userName, $config);
        if (!\webtemplate\general\General::isError($registeredOk)) {
            return true;
        } else {
            // Failed to register the user okay
            $errorMsg = gettext("Unable to Complete Login Process");
            return \webtemplate\general\General::raiseError($errorMsg, 4);
        }
    }



    /**
     * Register a currently logged in user.  Stire their realname, userid,
     * encrypted password, permissions and preferences in the USER Class object.
     *
     * @param string $userName    Name of user logged in.
     * @param array  $preferences Array containing the site preferences
     *
     * @return mixed True if user registered.  Returns WEBTEMPLATE::Error on error.
     *
     * @access public
     */
    final public function register($userName, $preferences)
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

        // Set local variables
        $registeredOk = true;

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
        if (\webtemplate\general\General::isError($userArray)) {
            // Error encountered when selecting user from database
            // Registration process has failed
            $msg = gettext("Unable to Register User");
            return \webtemplate\general\General::raiseError($msg, 1);
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
        $searchData = array('user_id' => $this->userId);
        $result = $this->db->dbupdate('users', $insertData, $searchData);
        if (\webtemplate\general\General::isError($result)) {
            // Error encountered when selecting user from database
            // Registration process has failed
            $msg = gettext("Unable to Register User");
            return \webtemplate\general\General::raiseError($msg, 1);
        }
        return true;
    }


    /**
    * Return the current users user name
    *
    * @return string User Name
    *
    * @access public
    */
    final public function getUserName()
    {
        return $this->userName;
    }

    /**
    * Return the current users realname
    *
    * @param string $realName value to set a new real name
    *
    * @return string Real Name
    *
    * @access public
    */
    final public function getRealName($realName = null)
    {
        if (isset($realName)) {
            $this->realName = $realName;
        }
        return $this->realName;
    }

    /**
    * Return the current users user id
    *
    * @return integer Userid
    *
    * @access public
    */
    final public function getUserId()
    {
        return $this->userId;
    }


    /**
    * Return the current users e-mail address
    *
    * @param string $email value to set a new email address
    *
    * @return string email
    *
    * @access public
    */
    final public function getUserEmail($email = null)
    {
        if (isset($email)) {
            $this->userEmail = $email;
        }
        return $this->userEmail;
    }

    /**
    * Check if the supplied password is the same as the current
    * users encrypted password
    *
    * @param string $passwd The password that needs to be verified
    *
    * @return boolean true if $passwd_to_check = $encryptedPasswd otherwise false
    *
    * @access public
    */
    final public function checkPasswd($passwd)
    {
        return \webtemplate\general\General::verifyPasswd(
            $passwd,
            $this->encryptedPasswd
        );
    }

    /**
    * Either set the current users theme is $themeName is set or return
    * the users current theme.
    *
    * @param string $themeName = NULL The users new theme.
    *
    * @return string theme
    *
    * @access public
    */
    final public function getUserTheme($themeName = null)
    {
        if (isset($themeName)) {
            $this->theme = $themeName;
        }
        return $this->theme; //$sitePreferences['theme']['value'];
    }

    /**
    * Set the number of rows to display is $setVariable is true
    * otherwise return the number of rows to display
    *
    * @param boolean $zoomText    = null Zoom in text boxes if true
    * @param boolean $setVariable = null Set the xoom Variable if true
    *
    * @return boolean
    *
    * @access public
    */
    final public function getUserZoom($zoomText = null, $setVariable = null)
    {
        if (isset($setVariable)) {
            $this->zoomText = $zoomText;
        }
        return $this->zoomText;
    }


    /**
    * If $displayRows = null return the number of results to display on
    * a single page. Other wise set the number.
    *
    * @param string $displayRows = NULL The number of results to display.
    *
    * @return string
    *
    * @access public
    */
    final public function getDisplayRows($displayRows = null)
    {
        if (isset($displayRows)) {
            $this->displayrows = $displayRows;
        }
        return $this->displayrows;
    }

    /**
     * Get the date the user was last seen for login
     *
     * @return string
     *
     * @access public
     */
    final public function getLastSeenDate()
    {
        return $this->last_seen_date;
    }

    /**
     * This function returns a message to the user if there password has less than
     * 15 days until it expires.
     *
     * @return string
     *
     * @access public
     */
    final public function getPasswdAgeMsg()
    {
        return $this->passwdagemsg;
    }

    /**
     * Function to get user's preferences from the database
     *
     * This function gets the users preferences from the database.  If no preferences
     * are found or there is a database error the default preferences are used.
     *
     * @param array $preferences Array containing the site preferences
     *
     * @return boolen True
     *
     * @access private
     */
    private function getPreferences($preferences)
    {

        // Set the users preferences
        // Set the default Site preferences for user.
        $this->theme = $preferences['theme']['value'];
        $this->zoomText = $preferences['zoomtext']['value'];
        $this->displayrows = $preferences['displayrows']['value'];

        // Search database for User Preferences
        $fieldNames = array('settingname', 'settingvalue');
        $searchData = array('user_id' => $this->userId);
        $userArray = $this->db->dbselectmultiple(
            'userprefs',
            $fieldNames,
            $searchData
        );

        // Missing Preferences do not stop registration
        if (!\webtemplate\general\General::isError($userArray)) {
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
     * Function to get the user's prefered theme.
     * It is called by getPreferences
     *
     * @param array   $value   The array holding the users preferences
     * @param boolean $enabled True if user selectable theme is enabled
     *
     * @return boolean True in all cases
     */
    private function getTheme($value, $enabled)
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
     * @param array   $value   The array holding the users preferences
     * @param boolean $enabled True if user selectable theme is enabled
     *
     * @return boolean True in all cases
     */
    private function getZoomArea($value, $enabled)
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
     * Function to get the user's prefered theme.
     * It is called by getPreferences
     *
     * @param array   $value   The array holding the users preferences
     * @param boolean $enabled True if user selectable theme is enabled
     *
     * @return boolean True in all cases
     */
    private function getRows($value, $enabled)
    {
        if ((chop($value['settingname']) == 'displayrows') and ($enabled == true)) {
            // The user has set the number of sata rows to display
            $this->displayrows = chop($value['settingvalue']);
        }

        return true;
    }


    /**
     * Function to test if a users password has expired
     *
     * This function checks if a users password has expired
     *
     * @param integer $passwdage  Control Parameter for password aging.
     * @param string  $changedate The date the users password was last changed.
     *
     * @return mixed true if password does not need imediate changing
     *                    or WEBTEMPLATE Error object if the password must be changed
     *
     * @access private
     */
    private function passwordAge($passwdage, $changedate)
    {
        switch ($passwdage) {
            case 2:
                $passwddays = 60;
                break;
            case 3:
                $passwddays = 90;
                break;
            case 4:
                $passwddays = 180;
                break;
            default:
                $passwddays = 0;
                break;
        }

        if ($passwddays == 0) {
            return true;
        }

        $todaysdate = time();
        $secs = $todaysdate - $changedate;
        $days = (int) ($secs / 86400);
        $daysleft = $passwddays - $days;
        if (($daysleft > 0) and ($daysleft < 15)) {
            $str1 = gettext("Your password is due to expire in ");
            $str2 = gettext(" days. To change your password click ");
            $str3 = '<a href="/userprefs.php?tab=account">here.</a>';
            $this->passwdagemsg = $str1 . $daysleft . $str2 . $str3;
        }
        if (($daysleft < 1) or ($changedate == null)) {
            $errorMsg = gettext("Your password has expired and must be changed");
            $errorCode = 5;
            return \webtemplate\general\General::raiseError(
                $errorMsg,
                $errorCode
            );
        }
        return true;
    }

    /**
     * Function to override the default Style Directory
     *
     * This function will override the default style directory which is set by
     * the constructor.  Parameter 1 must contain a valid directory which contains
     * directories containg the css files.  The Path must finish with a "/".
     *
     * @param string $styleDir The new Style Directory.
     *
     * @return boolean true
     *
     * @access public
     */
    public function setDefaultStyleDir($styleDir)
    {
        $this->styleDir = $styleDir;
    }
}
