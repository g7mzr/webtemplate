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
     * @param pointer $webtemplate Pointer to Webtemplate Application Class
     *
     * @access public
     */
    public function __construct(&$webtemplate)
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
            $errmsg = array('ErrorMsg'=>'logout: Invalid number of arguments');
            return array('data' => $errmsg, 'code' => 400);
        }

        $this->webtemplate->session()->destroy();

        $dataarr = array('Msg' =>"Logged out");
        $code = 200;
        return array('data' => $dataarr, 'code' => $code);
    }
}
