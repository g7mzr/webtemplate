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
     * @param array $this->args        Additional URI components
     * @param array $this->requestdata Contents of the GET request
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function get()
    {

        if ((count($this->args) > 0) or (count($this->requestdata) > 0 )) {
            $dataarr = array(
                'ErrorMsg'=>'example: GET does not take any arguments',
                'args' => $this->args,
                "requestdata" => $this->requestdata
            );
            $code = 400;
            return array('data'=>$dataarr, 'code'=>$code);
        }

        $path = explode('\\', __CLASS__);
        $endpoint = strtolower(array_pop($path));
        $dataarr = array();
        $dataarr['endpoint'] = $endpoint;
        $dataarr['method'] = strtoupper(__FUNCTION__);
        $dataarr['args'] =$this->args;
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
        $dataarr['args'] =$this->args;
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
        $dataarr['args'] =$this->args;
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
        $dataarr['args'] =$this->args;
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
        $dataarr['args'] =$this->args;
        $dataarr['requestdata'] = $this->requestdata;
        $arr = array('data' => $dataarr, 'code' => 200);
        return $arr;
    }
}
