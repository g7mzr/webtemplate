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
 * Tokens Class
 **/
class Tokens
{
    /**
     * Database Driver
     *
     * @var    \g7mzr\db\interfaces\InterfaceDatabaseDriver
     * @access protected
     */
    protected $db = null;

    /**
     * Additional data stored in the token.
     *
     * @var    string
     * @access protected
     */
    protected $eventdata = '';

    /**
     * Date the last created token will expire
     *
     * @var    string
     * @access protected
     */
    protected $expiretime = 0;

    /**
     * Constructor for the edit user class.
     *
     * @param \g7mzr\webtemplate\application\SmartyTemplate $tpl Smarty Template variable.
     * @param \g7mzr\db\interfaces\InterfaceDatabaseDriver  $db  Database Connection Object.
     *
     * @access public
     */
    public function __construct(
        \g7mzr\webtemplate\application\SmartyTemplate &$tpl,
        \g7mzr\db\interfaces\InterfaceDatabaseDriver $db
    ) {
        $this->db = $db;

        // Get Garbage collectior Information
        $this->_gc_divisor = intval($tpl->getConfigVars("gc_divisor"));
        $this->_gc_probability = intval($tpl->getConfigVars("gc_probibility"));

        // Check if Stale Tokens are to be deleted
        if (lcg_value() < ($this->_gc_probability / $this->_gc_divisor)) {
            $this->deleteStaleTokens();
        }
    } // end constructor


    /**
     * This function creates the token for user changes
     *
     * @param string  $userid    The Id of the user making the change.
     * @param string  $tokentype The type of token being created.
     * @param integer $life      The life of the token in hours.
     * @param string  $data      Additional data stored in the token.
     * @param boolean $type      If false create token for e-mail authentication
     *                           If True create token for webpage authentication.
     *
     * @return mixed token string if created okay. WEBTEMPLATE:Error if error
     *               encountered
     * @access public
     */
    public function createToken(
        string $userid,
        string $tokentype,
        int $life = 24,
        string $data = '',
        bool $type = true
    ) {
        // Set local Flags
        $saveok = true;
        $errorMsg = '';
        $token = '';

        if ($type == true) {
            $token = uniqid(md5(microtime()), true);
        } else {
            $length = 10;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyz';

            for ($p = 0; $p < $length; $p++) {
                $token .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
        }

        if ($this->db->startTransaction()) {
            // Delete similar tokens for this user
            $searchData = array(
                'user_id' => $userid,
                'tokentype' => $tokentype
            );

            if ($data <> '') {
                $searchData['eventdata'] = $data;
            }

            $result = $this->db->dbdelete('tokens', $searchData);
            if ($result == false) {
                $saveok = false;
                $errorMsg = gettext("Unable to Delete Token");
            }

            $time = time();
            $expiretime = $time + (3600 * $life);
            $timestamp = date('Y-m-d G:i:s', $time);
            $expiretimestamp = date('Y-m-d G:i:s', $expiretime);
            $insertData = array(
                'user_id' => $userid,
                'issuedate' => $timestamp,
                'expiredate' => $expiretimestamp,
                'life' => $life,
                'token' => $token,
                'tokentype' => $tokentype,
                'eventdata' => $data
            );
            $result = $this->db->dbinsert('tokens', $insertData);
            if (\g7mzr\db\common\Common::isError($result)) {
                $saveok = false;
                $errorMsg = gettext("Error creating User Token");
            }
            $this->db->endTransaction($saveok);
            $this->eventdata = $data;
            $this->expiretime = $expiretime;
        } else {
            $saveok = false;
            $errorMsg = gettext("Failed to create user token");
        }

        // If token saved return value else return an Error
        if ($saveok) {
            return $token;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }



     /**
     * This function validates the token for user changes
     *
     * @param string $userToken The users token for validation.
     * @param string $tokentype The type of token being validated.
     *
     * @return integer userid if token valid. 0 otherwise.
     * @access public
     */
    public function getTokenUserid(string $userToken, string $tokentype)
    {
        $tokenok = false;
        $tokenchecked = true;
        $userid = 0;
        $this->eventdata = '';
        $currenttime = time();
        $currenttimestamp = date('Y-m-d G:i:s', $currenttime);

        $fieldNames = array(
            'user_id',
            'issuedate',
            'life',
            'eventdata',
            'expiredate'
        );
        $searchData = array('token' => $userToken, 'tokentype' => $tokentype);

        $result = $this->db->dbselectsingle('tokens', $fieldNames, $searchData);
        if (\g7mzr\db\common\Common::isError($result)) {
            // Database query failed.
            return $userid;
        }
        $expiretime = \strtotime($result['expiredate']);
        if ($expiretime > time()) {
            // Token is OKAY
            $tokenok = true;
            $userid = $result['user_id'];
            $this->eventdata = chop($result['eventdata']);
            $this->expiretime = $expiretime;
        }

        return $userid;
    }


    /**
     * This function validates the token exists for the required user
     *
     * @param string $userToken The users token for validation.
     * @param string $tokentype The type of token being validated.
     * @param string $uid       The Id of the user whose token is being validated.
     *
     * @return boolean true if token valid. False if token invalid or if error e
     *                 ncountered.
     *
     * @access public
     */
    public function verifyToken(string $userToken, string $tokentype, string $uid)
    {
        $tokenok = false;
        $tokenchecked = true;
        $this->eventdata = '';

        $fieldNames = array(
            'user_id',
            'issuedate',
            'life',
            'eventdata',
            'expiredate'
        );

        $searchData = array(
            'token' => $userToken,
            'tokentype' => $tokentype,
            'user_id' => $uid
        );

        $result = $this->db->dbselectsingle('tokens', $fieldNames, $searchData);

        if (\g7mzr\db\common\Common::isError($result)) {
            // Database Search Failed
            return $tokenok;
        }

        $expiretime = \strtotime($result['expiredate']);
        if ($expiretime > time()) {
            // Token is OKAY
            $tokenok = true;
            $this->eventdata = chop($result['eventdata']);
        }
        $this->expiretime = $expiretime;
        return $tokenok;
    }



    /**
     * This function deletes the token for user changes
     *
     * @param string $userToken The users token for validation.
     *
     * @return mixed true if token deleted . Webtemplate Error if error encountered
     * @access public
     */
    public function deleteToken(string $userToken)
    {
        $tokendeleted = true;

        $searchData = array('token' => $userToken);
        $result = $this->db->dbdelete('tokens', $searchData);
        if ($result == false) {
            $tokendeleted = false;
            $errorMsg = gettext("Unable to Delete Token");
        }

        // If deleted return true. else return a\g7mzr\webtemplate\general\General::Error
        if ($tokendeleted) {
            return true;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }

    /**
     * This function returns the event data stored in the last accessed token
     *
     * @return string The event data stored in the token
     * @access public
     */
    public function getEventData()
    {
        return $this->eventdata;
    }

    /**
     * This function returns the expire time stored in the last created token
     *
     * @return string The expite time stored in the token
     * @access public
     */
    public function getExpireTime()
    {
        return $this->expiretime;
    }


    /**
     * This function deletes all expired tokens from the database
     *
     * @return boolen True if the delete command run.  General::Error otherwise
     * @access private
     */
    private function deleteStaleTokens()
    {
        $tokendeleted = true;

        $timenow = date('Y-m-d G:i:s', time());
        $searchData = array(
            'expiredate' => array('type' => '<', 'data' => $timenow)
        );
        $result = $this->db->dbdeletemultiple('tokens', $searchData);
        if ($result == false) {
            $tokendeleted = false;
            $errorMsg = gettext("Unable to Delete Token");
        }

        // If deleted return true. else return a\g7mzr\webtemplate\general\General::Error
        if ($tokendeleted) {
            return true;
        } else {
            return\g7mzr\webtemplate\general\General::raiseError($errorMsg, 1);
        }
    }
}
