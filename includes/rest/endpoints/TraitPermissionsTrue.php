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
 * Webtemplate Restful API PermissionsTrue Traits
 **/
trait TraitPermissionsTrue
{
    /**
     * Check if the current user is in a specific group.
     *
     * @return boolean Returns True at all times
     *
     * @access public
     */
    public function permissions()
    {
        return true;
    }
}
