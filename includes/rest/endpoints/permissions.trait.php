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

namespace webtemplate\rest\endpoints;

/**
 * Webtemplate Restful API Permissions Traits
 **/
trait TraitPermissions
{
    /**
     * Check if the current user is in a specific group.  To access the endpoint the
     * use must be in the named group or the admin group.
     *
     * @return mixed boolean true if can access the resource.  Array otherwise
     *
     * @access public
     */
    public function permissions()
    {
        if ($this->webtemplate->session()->getUserName() == '') {
            $errmsg = array('ErrorMsg' => 'Login required to use this resource');
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
