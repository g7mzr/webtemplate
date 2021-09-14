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
 * Edit User Class
 **/
class EditUser
{
    /**
     *  Database Object
     *
     * @var    \g7mzr\db\interfaces\InterfaceDatabaseDriver
     * @access protected
     */

    protected $db = null;


    /**
     * String containing the list of field which have been changed
     *
     * @var    string
     * @access protected
     */

    protected $dataChanged = '';

    /**
     * Constructor for the edit user class.
     *
     * @param \g7mzr\db\interfaces\InterfaceDatabaseDriver $db Database object.
     *
     * @access public
     */
    public function __construct(\g7mzr\db\interfaces\InterfaceDatabaseDriver $db)
    {
        $this->db       = $db;
        $this->userName = "None";
    }


    /**
     * This function returns a list of users who meet the search criteria
     *
     * @param string $searchtype The field to search.
     * @param string $searchstr  The text to search for.
     *
     * @return mixed Array with the search results or WEBTEMPLATE error type
     * @access public
     */
    final public function search(string $searchtype, string $searchstr)
    {

        // Initalise Local Variables
        $gotdata = true;
        $fieldNames = array(
            'user_id',
            'user_name',
            'user_realname',
            'user_email',
            'user_enabled',
            'user_disable_mail',
            'date(last_seen_date)',
            'date(passwd_changed) as passwd_changed'
        );

        // Select the column to search
        switch ($searchtype) {
            case 'username':
                $searchdata = array(
                "user_name" => '%' . $searchstr . '%'
                    );
                break;
            case 'realname':
                $searchdata = array(
                "user_realname" => '%' . $searchstr . '%'
                    );
                break;
            case 'email':
                $searchdata = array(
                "user_email" => '%' . $searchstr . '%'
                    );
                break;
            default:
                $searchdata = array(
                   "user_name" => '%' . $searchstr . '%'
                    );
                break;
        }


        $result = $this->db->dbselectmultiple(
            'users',
            $fieldNames,
            $searchdata,
            'user_id'
        );

        if (\g7mzr\db\common\Common::isError($result)) {
            $gotdata = false;
            $errorMsg =  $result->getMessage();
            ;
        } else {
            $resultArray = array();
            foreach ($result as $uao) {
                $resultArray[] = array("userid" => $uao['user_id'],
                                    "username" => $uao['user_name'],
                                    "realname" => $uao['user_realname'],
                                    "useremail" => $uao['user_email'],
                                    "userenabled" => chop($uao['user_enabled']),
                                    "userdisablemail" => $uao['user_disable_mail'],
                                    "passwd" => '',
                                    "lastseendate" => $uao['date'],
                                    "passwdchanged" => $uao['passwd_changed']);
            }
        }
        if ($gotdata == true) {
            return $resultArray;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }


    /**
     * This function returns the details of a single user
     *
     * @param integer $userId The Id of the selected user.
     *
     * @return mixed Array with the search results or WEBTEMPLATE error type
     * @access public
     */
    final public function getuser(int $userId)
    {
        $gotdata = true;
        $fieldNames = array(
            'user_id',
            'user_name',
            'user_passwd',
            'user_realname',
            'user_email',
            'user_enabled',
            'user_disable_mail',
            'date(last_seen_date)',
            'date(passwd_changed) as passwd_changed'
        );
        $searchData = array('user_id' => $userId);
        $uao = $this->db->dbselectsingle('users', $fieldNames, $searchData);

        if (!\g7mzr\db\common\Common::isError($uao)) {
            $resultarray = array();
            $resultarray[] = array("userid" => $uao['user_id'],
                                   "username" => $uao['user_name'],
                                   "realname" => $uao['user_realname'],
                                   "useremail" => $uao['user_email'],
                                   "userenabled" => $uao['user_enabled'],
                                   "userdisablemail" => $uao['user_disable_mail'],
                                   "passwd" => '',
                                   "lastseendate" => $uao['date'],
                                   "passwdchanged" => $uao['passwd_changed']);
        } else {
            $gotdata = false;
            $errorMsg = $uao->getMessage();
        }

        if ($gotdata == true) {
            return $resultarray;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }

    /**
     * This function is the interface which updates/creates the user in the database
     *
     * @param integer $userId            Pointer to the Id of the selected user.
     * @param string  $username          The users system name.
     * @param string  $realname          The users realname.
     * @param string  $usermail          The users e-mail address.
     * @param string  $passwd            The users password.
     * @param string  $userdisablemail   Set to Y if user does not want e-mail alerts.
     * @param string  $enabled           Set to Y to enable user.
     * @param string  $forcePasswdChange Set to Y to force the user to change their
     *                                   password at the next login.
     *
     * @return mixed true if data saved okay or WEBTEMPLATE error type
     * @access public
     */
    final public function saveuser(
        int &$userId,
        string $username,
        string $realname,
        string $usermail,
        string $passwd,
        string $userdisablemail,
        string $enabled,
        string $forcePasswdChange
    ) {

        // Encrypt the users Password
        if ($passwd == '') {
            $encryptPasswd = '';
        } else {
            $encryptPasswd = \g7mzr\webtemplate\general\General::encryptPasswd($passwd);

            // If unable to create password abort save
            if ($encryptPasswd === false) {
                $errorMsg = gettext("Unable to create Encrypted Password");
                return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
            }
        }

        // Set the text to force a passwd change
        if ($forcePasswdChange == 'Y') {
            $passwdChangeText = '';
        } else {
            $passwdChangeText = 'Now()';
        }

        if ($userId <> 0) {
            // Update and existing user
            $saveok = $this->updateUser(
                $userId,
                $username,
                $realname,
                $usermail,
                $encryptPasswd,
                $userdisablemail,
                $enabled,
                $passwdChangeText
            );
        } else {
            // Add a new user
            $saveok = $this->insertUser(
                $userId,
                $username,
                $realname,
                $usermail,
                $encryptPasswd,
                $userdisablemail,
                $enabled,
                $passwdChangeText
            );
        }

        if (!\g7mzr\webtemplate\general\General::isError($saveok)) {
            return true;
            ;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError(
                $saveok->getMessage(),
                1
            );
        }
    }


     /**
     * This function creates a changestring for the users data
     *
     * @param integer $userId          The Id of the selected user.
     * @param string  $username        The users system name.
     * @param string  $realname        The users realname.
     * @param string  $usermail        The users e-mail address.
     * @param string  $passwd          The users password.
     * @param string  $userdisablemail Set to Y if user does not want e-mail alerts.
     * @param string  $enabled         Set to Y to enable user.
     *
     * @return boolean true if change string created
     * @access public
     */
    final public function datachanged(
        int $userId,
        string $username,
        string $realname,
        string $usermail,
        string $passwd,
        string $userdisablemail,
        string $enabled
    ) {
        $gotOldData = false;
        $localDataChanged = false;
        // Get the existing data for the user
        if ($userId <> '0') {
            $resultArray = $this->getuser($userId);
            if (!\g7mzr\webtemplate\general\General::isError($resultArray)) {
                // Create a blank change string
                $msg = '';

                // Check if the user name has changed
                //if ($resultArray[0]['username'] <> $username) {
                //    $msg = $msg . gettext("User Name Changed") . "\n";
                //    $localDataChanged = true;
                //}

                // Check if the real name has changed
                if ($resultArray[0]['realname'] <> $realname) {
                    $msg = $msg . gettext("Real Name Changed") . "\n";
                    $localDataChanged = true;
                }

                // Check if the e-mail address has changed
                if ($resultArray[0]['useremail'] <> $usermail) {
                    $msg = $msg . gettext("E-Mail Address Changed") . "\n";
                    $localDataChanged = true;
                }

                // Check if the lock text has changed
                if ($resultArray[0]['userenabled'] <> $enabled) {
                    $msg = $msg . gettext("User Enabled Status Changed") . "\n";
                    $localDataChanged = true;
                }

                // Check if the disable e-mail flag has changed
                if ($resultArray[0]['userdisablemail'] <> $userdisablemail) {
                    $msg = $msg . gettext("Disable E-Mail Changed") . "\n";
                    $localDataChanged = true;
                }

                // Check if the password has changed
                if ($passwd <> '') {
                    $msg = $msg . gettext("Password Changed") . "\n";
                    $localDataChanged = true;
                }
            }
        }

        if ($localDataChanged === true) {
            // Users data has changed. Save change string and return true
            $this->dataChanged = $msg;
            return true;
        } else {
            return false;
        }
    }

     /**
     * This function returns the changestring for the user's data
     *
     * @return string Change String for the user's data
     * @access public
     */
    final public function getChangeString()
    {
        return $this->dataChanged;
    }

     /**
     * This function checks if a user exists on the database
     *
     * @param string $username The users system name.
     *
     * @return mixed true if user exist. WEBTEMPLATE:Error if error encountered
     * @access public
     */
    final public function checkUserExists(string $username)
    {

        // Set default values for local flags
        $searchok = true;
        $userExists = false;
        $fieldNames = array(
            'user_id',
            'user_name',
            'user_passwd',
            'user_realname',
            'user_email',
            'user_enabled',
            'user_disable_mail',
            'date(last_seen_date)',
            'date(passwd_changed) as passwd_changed'
        );
        $searchData = array('user_name' => $username);
        $uao = $this->db->dbselectsingle('users', $fieldNames, $searchData);

        if (!\g7mzr\db\common\Common::isError($uao)) {
            $userExists = true;
        } else {
            if ($uao->getCode() != DB_ERROR_NOT_FOUND) {
                $searchok = false;
                $errorMsg = $uao->getMessage();
            }
        }

        if ($searchok) {
            return $userExists;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }

     /**
     * This function checks if a user exists on the database
     *
     * @param string $email The email address to be checked.
     *
     * @return mixed true if user exist. WEBTEMPLATE:Error if error encountered
     * @access public
     */
    final public function checkEmailExists(string $email)
    {

        // Set default values for local flags
        $searchok = true;
        $emailExists = false;
        $fieldNames = array(
            'user_id',
            'user_name',
            'user_passwd',
            'user_realname',
            'user_email',
            'user_enabled',
            'user_disable_mail',
            'date(last_seen_date)',
            'date(passwd_changed) as passwd_changed'
        );
        $searchData = array('user_email' => $email);
        $uao = $this->db->dbselectsingle('users', $fieldNames, $searchData);

        if (!\g7mzr\db\common\Common::isError($uao)) {
            $emailExists = true;
        } else {
            if ($uao->getCode() != DB_ERROR_NOT_FOUND) {
                $searchok = false;
                $errorMsg = $uao->getMessage();
            }
        }

        if ($searchok) {
            return $emailExists;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }


    /**
     * This function checks if a user is enabled or disabled
     *
     * @param string $username The users system name.
     *
     * @return mixed true if user exist. WEBTEMPLATE:Error if error encountered
     * @access public
     */
    final public function checkUserEnabled(string $username)
    {

        // Set default values for local flags
        $searchok = true;
        $userEnabled = false;

        $fieldNames = array(
            'user_id',
            'user_name',
            'user_passwd',
            'user_realname',
            'user_email',
            'user_enabled',
            'user_disable_mail',
            'date(last_seen_date)',
            'date(passwd_changed) as passwd_changed'
        );
        $searchData = array('user_name' => $username);
        $uao = $this->db->dbselectsingle('users', $fieldNames, $searchData);
        //$uao = $this->db->getUser($username);
        if (!\g7mzr\db\common\Common::isError($uao)) {
            if ($uao['user_enabled'] == 'Y') {
                // User is enabled
                $userEnabled = true;
            }
        } else {
            $searchok = false;
            $errorMsg = $uao->getMessage();
        }

        if ($searchok) {
            return $userEnabled;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }


     /**
     * This function Updates the users Real Name, E-mail and Password
     *
     * @param string  $realname The users realname.
     * @param string  $passwd   The users password.
     * @param string  $usermail The users e-mail address.
     * @param integer $userId   The Id of the selected user.
     *
     * @return mixed true id data saved okay. WEBTEMPLATE:Error if error encountered
     * @access public
     */
    final public function updateUserDetails(
        string $realname,
        string $passwd,
        string $usermail,
        int $userId
    ) {

        // Set local Flags
        $saveok = true;
        if ($passwd == '') {
            $encryptPasswd = '';
        } else {
            // User has changed password.. Encrypt it
            $encryptPasswd = \g7mzr\webtemplate\general\General::encryptPasswd($passwd);

            // If unable to create password abort save
            if ($encryptPasswd === false) {
                $errorMsg = gettext("Unable to create Encrypted Password");
                return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
            }
        }

        if ($encryptPasswd == '') {
            // Updated userdetails excluding their password
            $insertData = array(
                'user_realname' => $realname,
                'user_email' => $usermail
            );
            $searchData = array('user_id' => $userId);
            $result = $this->db->dbupdate('users', $insertData, $searchData);
        } else {
            // Update user details including their password;
            $insertData = array(
                'user_realname' => $realname,
                'user_email' => $usermail,
                'user_passwd' => $encryptPasswd,
                'passwd_changed' => 'Now()'
            );
            $searchData = array('user_id' => $userId);
            $result = $this->db->dbupdate('users', $insertData, $searchData);
        }
        if (\g7mzr\db\common\Common::isError($result)) {
            $saveok = false;
            $errorMsg = $result->getMessage();
        } else {
            $saveok = $result;
        }

        // If query run okay return true.
        // Else return a\g7mzr\webtemplate\general\General::Error
        if ($saveok) {
            return true;
            ;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }

     /**
     * This function updated the users password
     *
     * @param string $username The users system name.
     * @param string $passwd   The users password.
     *
     * @return mixed true id data saved okay. WEBTEMPLATE:Error if error encountered
     * @access public
     */
    final public function updatePasswd(string $username, string $passwd)
    {

        // Set local Flags
        $saveok = true;

        // Encrypt the password
        $encryptPasswd = \g7mzr\webtemplate\general\General::encryptPasswd($passwd);

        // If unable to create password abort save
        if ($encryptPasswd === false) {
            $errorMsg = gettext("Unable to create Encrypted Password");
            return \g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }

        $insertData = array(
            'user_passwd' => $encryptPasswd,
            'passwd_changed' => 'Now()'
        );
        $searchdata = array('user_name' => $username);
        $result = $this->db->dbupdate('users', $insertData, $searchdata);
        if (\g7mzr\db\common\Common::isError($result)) {
            $saveok = false;
            $errorMsg = $result->getMessage();
        } else {
            $saveok = $result;
        }

        // If query run okay return true.
        // Else return a\g7mzr\webtemplate\general\General::Error
        if ($saveok) {
            return true;
            ;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }



     /**
     * This function validated the user data.
     *
     * @param array   $inputArray Pointer to an Array containing the user
     *                            data to be validated.
     * @param array   $params     The user field of parameters array.
     * @param boolean $prefsOnly  True when checking user preferences only.
     *
     * @return array Validated user data. msg element contains any error message
     * @access public
     */
    final public function validateUserData(
        array &$inputArray,
        array $params,
        bool $prefsOnly = false
    ) {

        $data = array();
        $data['msg'] = '';

        if (isset($inputArray['userdisablemail'])) {
            $data['disableMail'] = 'Y';
        } else {
            $data['disableMail'] = 'N';
        }

        if (isset($inputArray['userenabled'])) {
            $data['enabled'] = 'Y';
        } else {
            $data['enabled'] = 'N';
        }

        if (isset($inputArray['passwdchange'])) {
            $data['passwdchange'] = 'Y';
        } else {
            $data['passwdchange'] = 'N';
        }

        $this->validateUserId($data, $inputArray, $prefsOnly);

        $this->validateUserName($data, $inputArray, $params, $prefsOnly);

        $this->validateUserRealName($data, $inputArray, $prefsOnly);

        $this->validateUseremail($data, $inputArray, $prefsOnly);

        $this->validateUserPasswd($data, $inputArray, $params, $prefsOnly);

        $resultArray[] = array("userid" => $data['userId'],
                               "username" => $data['userName'],
                               "realname" => $data['realName'],
                               "useremail" => $data['userMail'],
                               "userenabled" => $data['enabled'],
                               "userdisablemail" => $data['disableMail'],
                               "passwd" => $data['passwd'],
                               "passwdchange" => $data['passwdchange'],
                               "lastseendate" => '',
                               "msg" => $data['msg']
        );

        return $resultArray;
    }


    /**
     * This function validated the user id
     *
     * @param array   $data       An array containing the validated input data.
     * @param array   $inputArray The array containing the unvalidated user data.
     * @param boolean $prefsOnly  True when checking user preferences only.
     *
     * @return boolean True if the data validated false otherwise.
     *
     * @access private
     */
    private function validateUserId(
        array &$data,
        array &$inputArray,
        bool $prefsOnly
    ) {
        $userDataOk = true;

        if (isset($inputArray['userid'])) {
            $data['userId'] = substr($inputArray['userid'], 0, 6);
        } else {
            $data['userId'] = '';
        }

        if (
            (!\g7mzr\webtemplate\general\LocalValidate::dbid($data['userId']))
            and ($prefsOnly == false)
        ) {
            $data['msg'] .= gettext("Invalid User ID") . "\n";
            $userDataOk = false;
        }

        return $userDataOk;
    }

    /**
     * This function validated the username
     *
     * @param array   $data       An array containing the validated input data.
     * @param array   $inputArray The array containing the unvalidated user data.
     * @param array   $params     The User element of the $parameters array.
     * @param boolean $prefsOnly  True when checking user preferences only.
     *
     * @return boolean True if the data validated false otherwise.
     *
     * @access private
     */
    private function validateUserName(
        array &$data,
        array &$inputArray,
        array $params,
        bool $prefsOnly
    ) {
        $userDataOk = true;

        if (isset($inputArray['username'])) {
            $data['userName'] = substr($inputArray['username'], 0, 20);
        } else {
            $data['userName'] = '';
        }

        $validUsername = \g7mzr\webtemplate\general\LocalValidate::username(
            $data['userName'],
            $params['regexp']
        );
        if (($validUsername == false) and ($prefsOnly == false)) {
            $data['msg'] .= gettext("Invalid User Name: ");
            $data ['msg'] .= $params['regexpdesc'] . "\n";
            $userDataOk = false;
        }

        return $userDataOk;
    }

    /**
     * This function validated the users realname
     *
     * @param array   $data       An array containing the validated input data.
     * @param array   $inputArray The array containing the unvalidated user data.
     * @param boolean $prefsOnly  True when checking user preferences only.
     *
     * @return boolean True if the data validated false otherwise.
     *
     * @access private
     */
    private function validateUserRealName(
        array &$data,
        array &$inputArray,
        bool $prefsOnly
    ) {

        $userDataOk = true;

        if (isset($inputArray['realname'])) {
            $data['realName'] = substr($inputArray['realname'], 0, 60);
        } else {
            $data['realName'] = '';
        }

        if (!\g7mzr\webtemplate\general\LocalValidate::realname($data['realName'])) {
            $data['msg'] .= gettext("Invalid Real Name") . "\n";
            $userDataOk = false;
        }

        return $userDataOk;
    }


    /**
     * This function validated the users email address
     *
     * @param array   $data       An array containing the validated input data.
     * @param array   $inputArray The array containing the unvalidated user data.
     * @param boolean $prefsOnly  True when checking user preferences only.
     *
     * @return boolean True if the data validated false otherwise.
     *
     * @access private
     */
    private function validateUseremail(
        array &$data,
        array &$inputArray,
        bool $prefsOnly
    ) {
        $userDataOk = true;

        if (isset($inputArray['useremail'])) {
            $data['userMail'] = substr($inputArray['useremail'], 0, 60);
        } else {
            $data['userMail'] = '';
        }

        if (!\g7mzr\webtemplate\general\LocalValidate::email($data['userMail'])) {
            $data['msg'] .= gettext("Invalid Email Address") . "\n";
            $userDataOk = false;
        }

        return $userDataOk;
    }

    /**
     * This function validated the users password
     *
     * @param array   $data       An array containing the validated input data.
     * @param array   $inputArray The array containing the unvalidated user data.
     * @param array   $params     The User element of the $parameters array.
     * @param boolean $prefsOnly  True when checking user preferences only.
     *
     * @return boolean True if the data validated false otherwise.
     *
     * @access private
     */
    private function validateUserPasswd(
        array &$data,
        array &$inputArray,
        array $params,
        bool $prefsOnly
    ) {
        $userDataOk = true;

        if (isset($inputArray['passwd'])) {
            $data['passwd'] = substr($inputArray['passwd'], 0, 20);
        } else {
            $data['passwd'] = '';
        }

        if (isset($inputArray['passwd2'])) {
            $data['passwd2'] = substr($inputArray['passwd2'], 0, 20);
        } else {
            $data['passwd2'] = '';
        }

        if (($data['passwd'] <> '') or ($data['userId'] == '0')) {
            if (
                !\g7mzr\webtemplate\general\LocalValidate::password(
                    $data['passwd'],
                    $params['passwdstrength']
                )
            ) {
                $data['msg'] .= gettext("Invalid Password: ");
                $tempno = $params['passwdstrength'];
                $data['msg'] .= \g7mzr\webtemplate\general\General::passwdFormat($tempno);
                $data['msg'] .= "\n";
                $userDataOk = false;
            }
        }

        if ($data['passwd2'] <> '') {
            if (
                !\g7mzr\webtemplate\general\LocalValidate::password(
                    $data['passwd2'],
                    $params['passwdstrength']
                )
            ) {
                // Password 2 is not valid
                $data['msg'] .= gettext("Invaild Confirmation Password") . "\n";
                $userDataOk = false;
            } else {
                // Password 2 is valid.  Check if both passwords are the same.
                if ($data['passwd'] <> $data['passwd2']) {
                    $data['msg'] .= gettext("Passwords are not the same") . "\n";
                    $userDataOk = false;
                }
            }
        }

        return $userDataOk;
    }


    /**
     * This function is the interface which updates/creates the user in the database
     *
     * @param integer $userId           Pointer to the Id of the selected user.
     * @param string  $username         The users system name.
     * @param string  $realname         The users realname.
     * @param string  $usermail         The users e-mail address.
     * @param string  $encryptPasswd    The users encrypted password.
     * @param string  $userdisablemail  Set to Y if user does not want e-mail alerts.
     * @param string  $enabled          Set to Y to enable user.
     * @param string  $passwdChangeText Date of last password change Null means
     *                                   a password change has been forced.
     *
     * @return mixed true if data saved okay or WEBTEMPLATE error type
     * @access private
     */
    final private function insertUser(
        int &$userId,
        string $username,
        string $realname,
        string $usermail,
        string $encryptPasswd,
        string $userdisablemail,
        string $enabled,
        string $passwdChangeText
    ) {
        $saveok = true;

        // Add a new user
        $insertData = array(
            'user_name' => $username,
            'user_realname' => $realname,
            'user_email' => $usermail,
            'user_enabled' => $enabled,
            'user_disable_mail' => $userdisablemail,
            'user_passwd' => $encryptPasswd,
            'passwd_changed' => $passwdChangeText
        );

        $result = $this->db->dbinsert('users', $insertData);
        if (!\g7mzr\db\common\Common::isError($result)) {
            $userId = $this->db->dbinsertid(
                "users",
                "user_id",
                "user_name",
                $username
            );
            if (\g7mzr\db\common\Common::isError($userId)) {
                $saveok = false;
                $errorMsg = gettext("Error getting id for user ");
                $errorMsg .= $username;
            }
        } else {
            $saveok = false;
            $errorMsg = gettext("Errors Saving user ") . $username;
        }

        if ($saveok) {
            return true;
            ;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }
    /**
     * This function is the interface which updates/creates the user in the database
     *
     * @param integer $userId           Pointer to the Id of the selected user.
     * @param string  $username         The users system name.
     * @param string  $realname         The users realname.
     * @param string  $usermail         The users e-mail address.
     * @param string  $encryptPasswd    The users encrypted password.
     * @param string  $userdisablemail  Set to Y if user does not want e-mail alerts.
     * @param string  $enabled          Set to Y to enable user.
     * @param string  $passwdChangeText Date of last password change Null means
     *                                  a password change has been forced.
     *
     * @return mixed true if data saved okay or WEBTEMPLATE error type
     * @access private
     */
    final private function updateUser(
        int &$userId,
        string $username,
        string $realname,
        string $usermail,
        string $encryptPasswd,
        string $userdisablemail,
        string $enabled,
        string $passwdChangeText
    ) {
        $saveok = true;

        // Update and existing user
        $insertData = array(
            'user_realname' => $realname,
            'user_email' => $usermail,
            'user_enabled' => $enabled,
            'user_disable_mail' => $userdisablemail
        );
        if ($encryptPasswd != '') {
            $insertData['user_passwd']    = $encryptPasswd;
            $insertData['passwd_changed'] = $passwdChangeText;
        }
        $searchData = array('user_id' => $userId);
        $result = $this->db->dbupdate('users', $insertData, $searchData);
        if (\g7mzr\db\common\Common::isError($result)) {
            $saveok = false;
            $errorMsg = gettext("Error updating user: ") . $username;
        }

        if ($saveok) {
            return true;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }
}
