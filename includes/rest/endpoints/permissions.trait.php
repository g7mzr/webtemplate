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
 * Webtemplate Group Traits
 *
 * @category Webtemplate
 * @package  API
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
trait TraitPermissions
{
    /**
     * Check if the current user is in a specific group.  To access the endpoint the
     * use must be in the named group or the admin group.
     *
     * @return mixed Boolean true if can access the resource.  Array otherwise
     *
     * @access public
     */
    public function permissions()
    {
        if ($this->webtemplate->session()->getUserName() == '') {
            $errmsg = array('ErrorMsg'=>'Login required to use this resource');
            return array('data' => $errmsg, 'code' => 401);
        }

        if (!$this->webtemplate->usergroups()->checkGroup($this->accessgroup)) {
            $errmsg = array(
                'ErrorMsg' => 'You do not have permission to use this resource'
            );
            return array('data' => $errmsg, 'code' => 403);
        }
        return true;
    }
}
