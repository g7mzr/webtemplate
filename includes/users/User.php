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
 * Webtemplate User Class
 *
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
    protected $passwdAgeMsg = '';

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
     * @access public
     */
    public function __construct()
    {
    }


    /**
     * User Class Load User Data
     *
     * @param array $userData The information on the current user.
     *
     * @return void
     * @access public
     */
    public function loadUserData(array $userData)
    {
        $this->userName = $userData['userName'];
        $this->userId = $userData['userId'];
        $this->realName = $userData['realName'];
        $this->userEmail = $userData['userEmail'];
        $this->displayrows = $userData['displayRows'];
        $this->last_seen_date = $userData['last_seen_date'];
        $this->passwdAgeMsg = $userData['passwdAgeMsg'];
        $this->theme = $userData['theme'];
        $this->zoomText = $userData['zoomText'];
        $this->encryptedPasswd = $userData['encryptedPasswd'];
        $this->permissions = $userData['permissions'];
        $this->styleDir = __DIR__ . "/style/";
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
    * @param string $realName Value to set a new real name.
    *
    * @return string Real Name
    *
    * @access public
    */
    final public function getRealName(string $realName = '')
    {
        if ($realName != '') {
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
    * @param string $email Value to set a new email address.
    *
    * @return string email
    *
    * @access public
    */
    final public function getUserEmail(string $email = '')
    {
        if ($email != '') {
            $this->userEmail = $email;
        }
        return $this->userEmail;
    }

    /**
    * Check if the supplied password is the same as the current
    * users encrypted password
    *
    * @param string $passwd The password that needs to be verified.
    *
    * @return boolean true if $passwd_to_check = $encryptedPasswd otherwise false
    *
    * @access public
    */
    final public function checkPasswd(string $passwd)
    {
        return\g7mzr\webtemplate\general\General::verifyPasswd(
            $passwd,
            $this->encryptedPasswd
        );
    }

    /**
    * Either set the current users theme is $themeName is set or return
    * the users current theme.
    *
    * @param string $themeName The users new theme.
    *
    * @return string theme
    *
    * @access public
    */
    final public function getUserTheme(string $themeName = '')
    {
        if ($themeName != '') {
            $this->theme = $themeName;
        }
        return $this->theme; //$sitePreferences['theme']['value'];
    }

    /**
    * Set the number of rows to display is $setVariable is true
    * otherwise return the number of rows to display
    *
    * @param boolean $zoomText    Zoom in text boxes if true.
    * @param boolean $setVariable Set the zoom Variable if true.
    *
    * @return boolean
    *
    * @access public
    */
    final public function getUserZoom(
        bool $zoomText = false,
        bool $setVariable = false
    ) {
        if ($setVariable === true) {
            $this->zoomText = $zoomText;
        }
        return $this->zoomText;
    }


    /**
    * If $displayRows = null return the number of results to display on
    * a single page. Other wise set the number.
    *
    * @param string $displayRows The number of results to display.
    *
    * @return string
    *
    * @access public
    */
    final public function getDisplayRows(string $displayRows = '')
    {
        if ($displayRows != '') {
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
        return $this->passwdAgeMsg;
    }

    /**
     * Function to override the default Style Directory
     *
     * This function will override the default style directory which is set by
     * the constructor.  Parameter 1 must contain a valid directory which contains
     * directories containing the css files.  The Path must finish with a "/".
     *
     * @param string $styleDir The new Style Directory.
     *
     * @return void
     *
     * @access public
     */
    public function setDefaultStyleDir(string $styleDir)
    {
        $this->styleDir = $styleDir;
    }
}
