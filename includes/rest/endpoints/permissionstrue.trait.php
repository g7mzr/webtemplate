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
trait TraitPermissionsTrue
{
    /**
     * Check if the current user is in a specific group.
     *
     * @return Boolean Returns True at all times
     *
     * @access public
     */
    public function permissions()
    {
        return true;
    }
}
