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
 *  Webtemplate RestFull API logout endpoint class
 **/
class Logout
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
     * This function implements POST command to log a user out
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function post()
    {
        if (\count($this->args) > 0) {
            $errmsg = array('ErrorMsg' => 'logout: Invalid number of arguments');
            return array('data' => $errmsg, 'code' => 400);
        }

        $this->webtemplate->session()->destroy();

        $dataarr = array('Msg' => "Logged out");
        $code = 200;
        return array('data' => $dataarr, 'code' => $code);
    }
}
