<?php
/**
 * This file is part of Webtemplate.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package Webtemplate
 * @subpackage Example Plugin
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @copyright (c) 2023, Sandy McNeil
 * @license https://github.com/g7mzr/webtemplate/blob/master/LICENSE GNU General Public License v3.0
 *
 */

namespace g7mzr\webtemplate\plugins\Example\lib;
/**
 * This class contains all the functions required for the Example Plugin to work
 */
class Functions
{
    /**
     * Property: app
     *
     * @var \g7mzr\webtemplate\application\Application
     * @access protected
     */
    protected $app = null;

    /**
     * __construct()
     *
     * @param \g7mzr\webtemplate\application\Application $app Pointer to application class.
     *
     * @access public
     */
    public function __construct(\g7mzr\webtemplate\application\Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get number of users.
     *
     * This function returns the number of registered users for the application
     *
     * @return mixed Int containing number of users or webtemplate error.
     *
     * @access public
     */
    public function noOfUsers()
    {
        $fieldNames = array(
            'user_id',
            'user_name',
            'user_realname',
            'user_email',
            'user_enabled',
            'user_disable_mail',
            'date(last_seen_date)',
            'date(passwd_changed) as passwd_changed'
        );
        $result = $this->app->db()->dbselectmultiple(
            'users',
            $fieldNames,
            array(),
            'user_id'
        );
        if (!\g7mzr\db\common\Common::isError($result)) {
            return $this->app->db()->rowCount();
        } else {
            return result;
        }
    }
}
