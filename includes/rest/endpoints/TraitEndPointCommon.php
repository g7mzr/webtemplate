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
 * Webtemplate RestFul API Traits
 **/
trait TraitEndPointCommon
{
    /**
     * Property: webtemplate
     *
     * @var   \g7mzr\webtemplate\application\Application
     * @access protected
     */
    protected $webtemplate = null;

    /**
     * Property: args
     *
     * @var    array
     * @access protected
     */
    protected $args;

    /**
     * Property: requestdata
     *
     * @var    array
     * @access protected
     */
    protected $requestdata;

    /**
     * This function processes the endpoint by calling the appropriate method which
     * is stored in the endpoint file as a function based on the method name
     *
     * @param string $method      HTTP method such as GET, DELETE, POST.
     * @param array  $args        Additional URI components.
     * @param array  $requestdata Contents of the GET or POST request.
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access public
     */
    public function endpoint(string $method, array $args, array $requestdata)
    {

        // Save the $args to the class variable
        $this->args = $args;

        // Process and save the $requestdata to the Class Variable
        // Remove the endpoint from the requestdata if it is there
        $path = explode('\\', __CLASS__);
        $endpoint = strtolower(array_pop($path));
        if (key_exists('request', $requestdata)) {
            if ($requestdata['request'] == $endpoint) {
                $localendpoint = array_shift($requestdata);
            }
        }
        $this->requestdata = $requestdata;

        // Convert the HTTP method to lower case to match the function name
        $lowercasemethod = strtolower($method);

        // Check if the function exists in the class
        if (method_exists($this, $lowercasemethod)) {
            // If it does call the function and return the results
            return $this->lowercasemethod();
        }

        // If the HTTP method does not exist return an invalid command
        $dataarr = array(
            'ErrorMsg' => "Method not implemented",
            'method' => $method,
            'args' => $args
        );
        $optionstr = $this->getoptions();
        return array('data' => $dataarr, 'options' => $optionstr, 'code' => 405);
    }

    /**
     * This function implements OPTIONS command.
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function options()
    {
        $optionstr = $this->getoptions();
        return array('options' => $optionstr, 'code' => 200);
    }

    /**
     * This function implements the head command
     *
     * @return array Array containing the results of the command excluding any data
     *
     * @access protected
     */
    protected function head()
    {
        if (method_exists($this, 'get')  === false) {
            $dataarr = array(
                'ErrorMsg' => "Method not implemented",
                'method' => "HEAD",
                'args' => $this->args
            );
            $optionstr = $this->getoptions();
            return array('data' => $dataarr, 'options' => $optionstr, 'code' => 405);
        }

        $result = $this->get();

        if ($result['code'] == 200) {
            $encodeddata = json_encode($result['data']);
            $datalength = strlen($encodeddata);
            return array('head' => $datalength, 'code' => 200);
        } else {
            return $result;
        }
    }

    /**
     * This function returns the list of HTTP Options that the endpoint support.  It
     * is not a valid method.
     *
     * @return string List of endpoints
     *
     * @access protected
     */
    protected function getoptions()
    {
        $optionstring = '';
        if (method_exists($this, 'get')) {
            $optionstring .= "GET, ";
        }
        if (method_exists($this, 'post')) {
            $optionstring .= "POST, ";
        }
        if (method_exists($this, 'put')) {
            $optionstring .= "PUT, ";
        }
        if (method_exists($this, 'patch')) {
            $optionstring .= "PATCH, ";
        }
        if (method_exists($this, 'delete')) {
            $optionstring .= "DELETE, ";
        }
        if ((method_exists($this, 'head')) and (method_exists($this, 'get'))) {
            $optionstring .= "HEAD, ";
        }
        if (method_exists($this, 'options')) {
            $optionstring .= "OPTIONS";
        }
        return $optionstring;
    }
}
