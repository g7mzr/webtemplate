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
 * Webtemplate User Login Class
 *
 **/
class Login
{
    /**
     * String containing the current status of the users password
     *
     * @var    string
     * @access protected
     */
    protected $passwdagemsg = '';

    /**
     * The date the user last logged in.  It is only valid when the
     * user logs in otherwise it remains an empty string.
     *
     * @var    string
     * @access protected
     */
    protected $last_seen_date = '';

    /**
     * Database connection object
     *
     * @var    \g7mzr\db\interfaces\InterfaceDatabaseDriver
     * @access protected
     */
    protected $db  = null;

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
     * @param \g7mzr\db\interfaces\InterfaceDatabaseDriver $db Database Connection Object.
     *
     * @access public
     */
    public function __construct(\g7mzr\db\interfaces\InterfaceDatabaseDriver $db)
    {
        $this->db       = $db ;
    }

    /**
     * Login the current user.  Once a user is logged in the
     * register function needs to be called.
     *
     * @param string  $userName  Name of user to be logged in.
     * @param string  $passwd    Password for user.
     * @param integer $passwdage Control Parameter for password aging.
     * @param array   $userData  Array that will contain the users information.
     *
     * @return mixed True if user logged in.  Returns WEBTEMPLATE::Error if unable
     *               to complete login
     *               error code 1 = system error, 2 = account locked,
     *               3 = invalid username/password, 4 = unable to register user
     *
     * @access public
     */
    final public function login(
        string $userName,
        string $passwd,
        int $passwdage,
        array &$userData
    ) {
        //date_default_timezone_set('Europe/London');
        // Set Passwd Age Msg to default value
        $this->passwdagemsg = '';

        // Set up the default variables
        // User not logged in
        //$loggedin = false;

        // Transfer user name to local variable
        $tempuser = $userName;

        // Default WEBTEMPLATE Error Code
        $errorCode = 1;

        $fields = array(
            'user_name',
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
        if (\g7mzr\db\common\Common::isError($userArray)) {
            if ($userArray->getCode() == DB_ERROR_NOT_FOUND) {
                // Invalid Username and password
                $errorMsg = gettext("Invalid Username and password");
                $errorCode = 3;
            } else {
                // Error Running SQL Query
                $errorMsg = gettext("Error Running SQL Query");
                $errorCode = 1;
            }
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, $errorCode);
        }

        // Test if the correct password has been entered
        $userpwd = chop($userArray['user_passwd']);
        if (\g7mzr\webtemplate\general\General::verifyPasswd($passwd, $userpwd) == false) {
            // Set the Last invalid Password field
            $insertData = array('last_failed_login' => 'now()');
            $searchData = array('user_name' => $userArray['user_name']);
            $result = $this->db->dbupdate('users', $insertData, $searchData);

            // Return Error Message
            $errorMsg = gettext("Invalid Username and password");
            $errorCode = 1;
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, $errorCode);
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
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, $errorCode);
        }

        // Check if the account has been locked.
        $userEnabled = chop($userArray['user_enabled']);
        if ($userEnabled == 'N') {
            // Account Locked.
            $errorMsg = gettext("Invalid Username and password");
            $errorCode = 2;
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, $errorCode);
        }

        // User is logged in
        //$loggedin = true;
        $this->last_seen_date = chop($userArray['last_seen_date']);
        $changedate = strtotime(chop($userArray['passwd_changed']));

        $result = $this->passwordAge($passwdage, $changedate);

        if (\g7mzr\webtemplate\general\General::isError($result)) {
            return $result;
        }

        $userData['last_seen_date'] = $this->last_seen_date;
        $userData['passwdAgeMsg'] = $this->passwdagemsg;

        return true;
    }

        /**
     * Function to test if a users password has expired
     *
     * This function checks if a users password has expired
     *
     * @param integer $passwdage  Control Parameter for password aging.
     * @param integer $changedate The date the users password was last changed.
     *
     * @return mixed true if password does not need immediate changing
     *                    or WEBTEMPLATE Error object if the password must be changed
     *
     * @access private
     */
    private function passwordAge(int $passwdage, int $changedate)
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
            return\g7mzr\webtemplate\general\General::raiseError(
                $errorMsg,
                $errorCode
            );
        }
        return true;
    }
}
