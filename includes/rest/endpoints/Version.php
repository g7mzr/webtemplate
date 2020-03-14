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
 *  Webtemplate RestFul API version endpoint class
 *
 **/
class Version
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
    public function __construct(\g7mzr\webtemplate\application\Application  &$webtemplate)
    {
        $this->webtemplate = $webtemplate;
    }

    /**
     * This function implements GET command.
     *
    * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function get()
    {


        if ((count($this->args) > 0) or (count($this->requestdata) > 0 )) {
            $dataarr = array(
                'ErrorMsg' => 'version: Invalid number of arguments',
                "args" => $this->args,
                "requestdata" => $this->requestdata
            );
            $code = 400;
        } else {
            $dataarr = array(
                "name" => $this->webtemplate->appname(),
                "Version" => $this->webtemplate->appversion(),
                "API" => $this->webtemplate->apiversion()
            );

            if ($this->webtemplate->usergroups()->getAdminAccess()) {
                // Get the Version of Database being used
                $databaseversion = gettext("Error Getting Database Version");
                $result = $this->webtemplate->db()->getDBVersion();
                if (!\g7mzr\webtemplate\general\General::isError($result)) {
                    $databaseversion = $result;
                }

                $dataarr['phpversion'] = phpversion();
                $dataarr['servername'] = filter_input(
                    INPUT_SERVER,
                    'SERVER_NAME',
                    \FILTER_SANITIZE_URL
                );
                $dataarr['serversoftware'] = filter_input(
                    INPUT_SERVER,
                    'SERVER_SOFTWARE',
                    \FILTER_SANITIZE_STRING
                );
                $dataarr['serveradmin'] = filter_input(
                    INPUT_SERVER,
                    'SERVER_ADMIN',
                    \FILTER_VALIDATE_EMAIL
                );
                $dataarr['databaseversion'] = $databaseversion;
            }

            $code = 200;
        }
        return array('data' => $dataarr, 'code' => $code);
    }
}
