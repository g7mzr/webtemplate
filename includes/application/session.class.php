<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\application;

/**
 * Session Code Class is a class used to set up and access PHP Session Variables
 *
 * @category Webtemplate
 * @package  General
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/

class Session
{

    /**
     * Username of logged in user
     *
     * @var    string
     * @access private
     */
    private $userName = '';

    /**
     * ID of logged in user
     *
     * @var    string
     * @access private
     */
    private $userId = '';

    /**
     * User has to create a new password before procedding if true
     *
     * @var    boolean
     * @access private
     */
    private $newPasswd = false;

    /**
     * The time the user last accessed the application
     *
     * @var    int
     * @access private
     */
    private $lastused;

    /**
     * The value of the cookie
     *
     * @var    string
     * @access private
     */
    private $cookievalue = '';

    /**
     * The session name
     *
     * @var    string
     * @access private
     */
    private $sessname = '';

    /**
     * The Garbage Collector Probability
     *
     * @var    integer
     * @access private
     */
    private $gc_probability = 1;

    /**
     * The Garbage Collector Divisor
     *
     * @var    integer
     * @access private
     */
    private $gc_divisor = 100;

    /**
     * Garbage Collection Run flag
     *
     * @var    boolean
     * @access private
     */
    private $gc_run = false;

    /**
     * The session lifetime in hours
     *
     * @var    integer
     * @access private
     */
    private $sessLifeTime = 168;

    /**
     * DB access object
     *
     * @var    object
     * @access private
     */
    private $db = null;

    /**
     * Cookie Path
     *
     * @var    string
     * @access private
     */
    private $cookiepath = '';

    /**
     * Cookie Domain
     *
     * @var    string
     * @access private
     */
    private $cookiedomain = '';

    /**
     * This function is the Session Constructor.  It is used to initalise the
     * php session for webtemplate.
     *
     * @param string  $cookiepath   The path on the server the cookie is valid for
     * @param string  $cookiedomain The sub domain that the cookie is valid in
     * @param string  $autologout   The auto logout flag.
     * @param pointer $tpl          Smarty Template variable
     * @param object  $db           Database Object
     *
     * @return boolean Function run okay
     *
     * @since Method available since Release 1.0.0
     *
     * @access public
     */
    public function __construct($cookiepath, $cookiedomain, $autologout, &$tpl, $db)
    {

        $this->userName = '';
        $this->userId = '';
        $this->newPasswd = false;
        $this->cookiepath = $cookiepath;
        $this->cookiedomain = $cookiedomain;


        $this->db = $db;

        // Set the session name from the SMARTY Config File
        $this->sessname = $tpl->getConfigVars("sessionname");
        $this->gc_divisor = $tpl->getConfigVars("gc_divisor");
        $this->gc_probability = $tpl->getConfigVars("gc_probability");

        // If Autologout is set to Never clean up stale sessions every 30 days
        // otherwise cleanup as defined in lifetime.  Normally 7 days.
        // Set the Cookie time out to 10 years; other wise set to timeout at the
        // end of the session
        if ($autologout == '5') {
            $this->sessLifeTime = 720;
            $cookieTimeOut = time()+60*60*24*365*10;
        } else {
            $this->sessLifeTime = $tpl->getConfigVars("lifetime");
            $cookieTimeOut = 0;
        }

        // Check if Stale sessions are to be deleted
        if (lcg_value() < ($this->gc_probability/$this->gc_divisor)) {
            $this->gc_run = $this->deleteStaleSessions($this->sessLifeTime);
        }

        if (isset($_COOKIE[$this->sessname])) {
            $this->cookievalue = filter_var($_COOKIE[$this->sessname]);
            if ($this->getSession() == true) {
                if ($this->checkAutoLogout($autologout)  == false) {
                    $this->updateSession();
                } else {
                    $this->destroy();
                }
            }
        } else {
            $cookiestring = '';
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $cookiestring .= filter_var(
                    $_SERVER['REMOTE_ADDR'],
                    FILTER_VALIDATE_IP
                );
            }
            $cookiestring .= time();
            $this->cookievalue = md5($cookiestring);
        }

        $this->setcookie(
            $this->sessname,
            $this->cookievalue,
            $cookieTimeOut,
            $this->cookiepath,
            $this->cookiedomain,
            false,
            true
        );
    }

    /**
     * This function updates the local Variables with the session data.
     *
     * @return boolean True if update successful
     *
     * @access private
     */
    private function getSession()
    {
        $gotData = false;
        $fields = array(
            'user_id',
            'user_name',
            'newpasswd',
            'lastused'
        );
        $search = array(
            "cookie" => $this->cookievalue
        );
        $result = $this->db->dbselectsingle('logindata', $fields, $search);
        if (!\webtemplate\general\General::isError($result)) {
            $this->lastused = strtotime($result['lastused']);
            $this->userName = chop($result['user_name']);
            $this->userId = chop($result['user_id']);
            if ($result['newpasswd'] == 'Y') {
                $this->newPasswd = true;
            } else {
                $this->newPasswd = false;
            }

            $gotData = true;
        }
        return $gotData;
    }

    /**
     * This function updates the local Variables with the session data.
     *
     * @return boolean True if update successful
     *
     * @access private
     */
    private function sessionExists()
    {
        $gotData = false;
        $fields = array(
            'user_id',
            'user_name',
            'newpasswd',
            'lastused'
        );
        $search = array(
            "cookie" => $this->cookievalue
        );
        $result = $this->db->dbselectsingle('logindata', $fields, $search);
        if (!\webtemplate\general\General::isError($result)) {
            $gotData = true;
        }
        return $gotData;
    }

    /**
     * This function updates the Session Variables with the new data entered
     *
     * @return boolean True if update successful
     *
     * @access private
     */
    private function saveSession()
    {
        $sessionsaved = false;
        if ($this->sessionExists() == false) {
            // Insert a New Session to the DB
            if ($this->newPasswd == true) {
                $newPasswd = 'Y';
            } else {
                $newPasswd = 'N';
            }
            $data = array(
                'cookie' => $this->cookievalue,
                'user_id' => $this->userId,
                'user_name' => $this->userName,
                'newpasswd' => $newPasswd,
                'lastused'  => 'now()'
            );
            $result = $this->db->dbinsert('logindata', $data);
            if (!\webtemplate\general\General::isError($result)) {
                $sessionsaved = true;
            }
        } else {
            // Update an existing session
            if ($this->newPasswd == true) {
                $newPasswd = 'Y';
            } else {
                $newPasswd = 'N';
            }

            $data = array(
                'user_id' => $this->userId,
                'user_name' => $this->userName,
                'newpasswd' => $newPasswd,
                'lastused'  => 'now()'
            );
            $search = array('cookie' => $this->cookievalue);
            $result = $this->db->dbupdate('logindata', $data, $search);
            if (!\webtemplate\general\General::isError($result)) {
                $sessionsaved = true;
            }
        }
        return $sessionsaved;
    }

    /**
     * This function updates the Session Variables with the new data entered
     *
     * @return boolean True if update successful
     *
     * @access private
     */
    private function updateSession()
    {
        $data = array(
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'lastused'  => 'now()'
        );
        $search = array('cookie' => $this->cookievalue);
        $this->db->dbupdate('logindata', $data, $search);
        return true;
    }

    /**
     * This function destroys the current session
     *
     * @return boolean Function run okay
     *
     * @since Method available since Release 1.0.0
     */
    public function destroy()
    {
        // Unset and destroy the current session
        $search = array('cookie' => $this->cookievalue);
        $result = $this->db->dbdelete('logindata', $search);
        $this->setcookie(
            $this->sessname,
            $this->cookievalue,
            time() - 3600,
            $this->cookiepath,
            $this->cookiedomain,
            false,
            true
        );
        return $result;
    }

    /**
     * This function saves the user name in the SESSION Variable.
     *
     * @param string $name The user name to be stored in the session variable
     *
     * @return boolean Function run okay
     *
     * @since Method available since Release 1.0.0
     */
    public function setUserName($name)
    {
        $this->userName = $name;
        return $this->saveSession();
    }

    /**
     * This function returns the user name from the SESSION Variable.
     *
     * @return string The user name stored in the SESSION Variable
     *
     * @since Method available since Release 1.0.0
     */
    public function getUserName()
    {
        return $this->userName;
    }


    /**
     * This function saves the user id in the SESSION Variable.
     *
     * @param string $id The user id to be stored in the session variable
     *
     * @return boolean Function run okay
     *
     * @since Method available since Release 1.0.0
     */
    public function setUserId($id)
    {
        $this->userId = $id;
        return $this->saveSession();
    }

    /**
     * This function returns the user name from the SESSION Variable.
     *
     * @return string The user name stored in the SESSION Variable
     *
     * @since Method available since Release 1.0.0
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * This function saves if the user has to change their password in the
     * SESSION Variable.
     *
     * @param boolean $setpasswd The passwd change flag to be stored in the session
     *                           variable
     *
     * @return boolean Function run okay
     *
     * @since Method available since Release 1.0.0
     */
    public function setPasswdChange($setpasswd)
    {
        $this->newPasswd = $setpasswd;
        return $this->saveSession();
    }

    /**
     * This function returns newpasswd flag from the SESSION Variable.
     *
     * @return boolean True if the user has to change their password
     *
     * @since Method available since Release 1.0.0
     */
    public function getPasswdChange()
    {
        return $this->newPasswd;
    }


    /**
     * This function returns the GC run flag.
     * It is mainly used for testing
     *
     * @return boolean gr_run flag
     *
     * @since Method available since Release 1.0.0
     */
    public function getGCRun()
    {
        return $this->gc_run;
    }

    /**
     * This function is used to create the session entry in the database when the
     * user logs in
     *
     * @param string  $username  The user name of the logged in user
     * @param string  $userid    The user id of the logged in user
     * @param boolean $newpasswd True if the user is requred to reset there password
     *
     * @return boolean true is session created
     */
    public function createSession($username, $userid, $newpasswd)
    {
        $this->newPasswd = $newpasswd;
        $this->userId = $userid;
        $this->userName = $username;
        $result = $this->saveSession();
        return $result;
    }

    /**
     * This function is used to delete stale sessions in the database.  These older
     * than lifeTime.
     *
     * @param integer $lifeTime Session entries older than this time in hours
     *                          are deleted
     *
     * @return boolen True if the delete command run.  General::Error otherwise
     *
     * @access private
     */
    private function deleteStaleSessions($lifeTime)
    {
        $lifeTimeSeconds = $lifeTime * 3600;
        $deleteTime = date('Y-m-d G:i:s', time() - $lifeTimeSeconds);
        $searchData = array(
            'lastused' => array('type' => '<', 'data' => $deleteTime)
        );
        $result = $this->db->dbdeletemultiple('logindata', $searchData);
        return $result;
    }


    /**
     * This function checks if the user has timed out and if they have deletes
     * their username and id from the class
     *
     * @param string $autologouttime The value of the autologout parameter
     *
     * @return boolean True if the user remains logged in false otherwise
     *
     * @access private
     */
    private function checkAutoLogout($autologouttime)
    {
        $loggedout = false;
        switch ($autologouttime) {
            case '2':
                $checktimeout = true;
                $timeout = strtotime('-10 minutes');
                break;
            case '3':
                $checktimeout = true;
                $timeout = strtotime('-20 minutes');
                break;
            case '4':
                $checktimeout = true;
                $timeout = strtotime('-30 minutes');
                break;
            default:
                $checktimeout = false;
        }
        if (($checktimeout == true) and ($this->lastused < $timeout)) {
            $this->userName = '';
            $this->userId = '';
            $loggedout = true;
        }
        return $loggedout;
    }

    /**
     * The function is a wrapper to call setcookie
     *
     * @param type $name     The Cookie name.  This is the application name
     * @param type $value    The value of the cookie
     * @param type $expire   The date the cookie expiers.  0 for a session cookie
     * @param type $path     The Web server path the cookie is valid for
     * @param type $domain   The domain the cookie is valid for
     * @param type $secure   True if the cookie can only be sent across SSL
     * @param type $httponly True if cookie can only be used for http
     *
     * @return boolean
     */
    private function setcookie(
        $name,
        $value,
        $expire,
        $path,
        $domain,
        $secure,
        $httponly
    ) {
        global $sessiontest;

        if (!isset($sessiontest)) {
            return setcookie(
                $name,
                $value,
                $expire,
                $path,
                $domain,
                $secure,
                $httponly
            );
        } else {
            $sessiontest['name'] = $name;
            $sessiontest['value'] = $value;
            $sessiontest['expire'] = $expire;
            $sessiontest['path'] = $path;
            $sessiontest['domain'] = $domain;
            $sessiontest['secure'] = $secure;
            $sessiontest['httponly'] = $httponly;
            return true;
        }
    }
}
