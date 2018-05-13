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
     * @param pointer $webtemplate Pointer to Webtemplate Application Class
     *
     * @access public
     */
    public function __construct(&$webtemplate)
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
                'ErrorMsg'=>'version: Invalid number of arguments',
                "args" => $this->args,
                "requestdata" => $this->requestdata
            );
            $code = 400;
        } else {
            $dataarr = array(
                "name"=>$this->webtemplate->appname(),
                "Version"=>$this->webtemplate->appversion(),
                "API"=>$this->webtemplate->apiversion()
            );

            if ($this->webtemplate->usergroups()->getAdminAccess()) {
                // Get the Version of Database being used
                $databaseversion = gettext("Error Getting Database Version");
                $result = $this->webtemplate->db()->getDBVersion();
                if (!\webtemplate\general\General::isError($result)) {
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
