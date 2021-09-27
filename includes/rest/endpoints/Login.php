<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage RestFul Interface
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2019, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\rest\endpoints;

/**
 *  Webtemplate RestFul API Login endpoint class
 *
 **/
class Login
{
    /**
     * Traits to be used by this Class
     */
    use TraitEndPointCommon;
    use TraitPermissionsTrue;

    /**
     * Property: accessgroup
     * This is the group a user must be a member of to access this resource
     *
     * @var    string
     * @access protected
     */
    protected $accessgroup = "groupname";

    /**
     * Constructor
     *
     * @param \g7mzr\webtemplate\application\Application $webtemplate Webtemplate Application Class Object.
     *
     * @access public
     */
    public function __construct(\g7mzr\webtemplate\application\Application &$webtemplate)
    {
        $this->webtemplate = $webtemplate;
    }

    /**
     * This function implements Post command to log a user in
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function post()
    {
        if (\count($this->args) > 0) {
            $errmsg = array('ErrorMsg' => 'login: Invalid number of arguments');
            return array('data' => $errmsg, 'code' => 400);
        }
        $validusername = false;
        $validpasswd = false;
        if (key_exists('username', $this->requestdata)) {
            $validusername = \g7mzr\webtemplate\general\LocalValidate::username(
                $this->requestdata['username'],
                $this->webtemplate->config()->read('param.users.regexp')
            );
        }
        if (key_exists('password', $this->requestdata)) {
            $validpasswd = \g7mzr\webtemplate\general\LocalValidate::password(
                $this->requestdata['password'],
                $this->webtemplate->config()->read('param.users.passwdstrength')
            );
        }
        if ($validusername and $validpasswd) {
            $userloggedin = $this->webtemplate->user()->login(
                $this->requestdata['username'],
                $this->requestdata['password'],
                $this->webtemplate->config()->read('param.users.passwdage'),
                $this->webtemplate->config()->read('pref')
            );
            if (!\g7mzr\webtemplate\general\General::isError($userloggedin)) {
                $this->webtemplate->session()->createSession(
                    $this->requestdata['username'],
                    $this->webtemplate->user()->getUserId(),
                    false
                );
                $dataarr = array(
                    'Msg' => "Logged in",
                    'user' => $this->webtemplate->user()->getUserName(),
                    'realname' => $this->webtemplate->user()->getRealName(),
                    'email' => $this->webtemplate->user()->getUserEmail(),
                    'lastlogin' => $this->webtemplate->user()->getLastSeenDate(),
                    'passwdagemsg' => $this->webtemplate->user()->getPasswdAgeMsg(),
                    'displayrows' => $this->webtemplate->user()->getDisplayRows()
                );
                $code = 200;
            } else {
                $dataarr = array('ErrorMsg' => "Invalid username and password");
                $code = 403;
            }
        } else {
            $dataarr = array('ErrorMsg' => "Invalid username and password");
            $code = 403;
        }

        return array('data' => $dataarr, 'code' => $code);
    }
}
