<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate\rest\endpoints;

/**
 *  WebtemplateAPI example endpoint class
 *
 * @category Webtemplate
 * @package  API
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code

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
     * @param pointer $webtemplate Pointer to Webtemplate Application Class
     *
     * @access public
     */
    public function __construct(&$webtemplate)
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
            $errmsg = array('ErrorMsg'=>'login: Invalid number of arguments');
            return array('data' => $errmsg, 'code' => 400);
        }
        $validusername = false;
        $validpasswd = false;
        if (key_exists('username', $this->requestdata)) {
            $validusername = \webtemplate\general\LocalValidate::username(
                $this->requestdata['username'],
                $this->webtemplate->config()->read('param.users.regexp')
            );
        }
        if (key_exists('password', $this->requestdata)) {
            $validpasswd = \webtemplate\general\LocalValidate::password(
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
            if (!\webtemplate\general\General::isError($userloggedin)) {
                $this->webtemplate->session()->createSession(
                    $this->requestdata['username'],
                    $this->webtemplate->user()->getUserId(),
                    false
                );
                $dataarr = array(
                    'Msg' =>"Logged in",
                    'user'=> $this->webtemplate->user()->getUserName(),
                    'realname' => $this->webtemplate->user()->getRealName(),
                    'email' => $this->webtemplate->user()->getUserEmail(),
                    'lastlogin'=> $this->webtemplate->user()->getLastSeenDate(),
                    'passwdagemsg' => $this->webtemplate->user()->getPasswdAgeMsg(),
                    'displayrows' => $this->webtemplate->user()->getDisplayRows()
                );
                $code = 200;
            } else {
                $dataarr = array('ErrorMsg' =>"Invalid username and password");
                $code = 403;
            }
        } else {
            $dataarr = array('ErrorMsg' =>"Invalid username and password");
            $code = 403;
        }

        return array('data' => $dataarr, 'code' => $code);
    }
}
