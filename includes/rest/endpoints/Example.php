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
 *  Webtemplate RestFul API example endpoint class
 **/
class Example
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
                'ErrorMsg' => 'example: GET does not take any arguments',
                'args' => $this->args,
                "requestdata" => $this->requestdata
            );
            $code = 400;
            return array('data' => $dataarr, 'code' => $code);
        }

        $path = explode('\\', __CLASS__);
        $endpoint = strtolower(array_pop($path));
        $dataarr = array();
        $dataarr['endpoint'] = $endpoint;
        $dataarr['method'] = strtoupper(__FUNCTION__);
        $dataarr['args'] = $this->args;
        $dataarr['requestdata'] = $this->requestdata;
        $arr = array('data' => $dataarr, 'code' => 200);
        return $arr;
    }

    /**
     * This function implements post command.
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function post()
    {
        $path = explode('\\', __CLASS__);
        $endpoint = strtolower(array_pop($path));
        $dataarr = array();
        $dataarr['endpoint'] = $endpoint;
        $dataarr['method'] = strtoupper(__FUNCTION__);
        $dataarr['args'] = $this->args;
        $dataarr['requestdata'] = $this->requestdata;
        $arr = array('data' => $dataarr, 'code' => 200);
        return $arr;
    }

    /**
     * This function implements put command.
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function put()
    {
        $path = explode('\\', __CLASS__);
        $endpoint = strtolower(array_pop($path));
        $dataarr = array();
        $dataarr['endpoint'] = $endpoint;
        $dataarr['method'] = strtoupper(__FUNCTION__);
        $dataarr['args'] = $this->args;
        $dataarr['requestdata'] = $this->requestdata;
        $arr = array('data' => $dataarr, 'code' => 200);
        return $arr;
    }

    /**
     * This function implements patch command.
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function patch()
    {
        $path = explode('\\', __CLASS__);
        $endpoint = strtolower(array_pop($path));
        $dataarr = array();
        $dataarr['endpoint'] = $endpoint;
        $dataarr['method'] = strtoupper(__FUNCTION__);
        $dataarr['args'] = $this->args;
        $dataarr['requestdata'] = $this->requestdata;
        $arr = array('data' => $dataarr, 'code' => 200);
        return $arr;
    }

    /**
     * This function implements GET command.
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function delete()
    {
        $path = explode('\\', __CLASS__);
        $endpoint = strtolower(array_pop($path));
        $dataarr = array();
        $dataarr['endpoint'] = $endpoint;
        $dataarr['method'] = strtoupper(__FUNCTION__);
        $dataarr['args'] = $this->args;
        $dataarr['requestdata'] = $this->requestdata;
        $arr = array('data' => $dataarr, 'code' => 200);
        return $arr;
    }
}
