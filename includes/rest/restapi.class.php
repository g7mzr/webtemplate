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

namespace webtemplate\rest;

use webtemplate\application\exceptions\AppException;

/**
 *  Webtemplate REST API
 **/
class RESTapi
{
    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     *
     * @var    string
     * @access protected
     */
    protected $method = '';

    /**
     * Property: endpoint
     * The Model requested in the URI. e.g.: /files
     *
     * @var    string
     * @access protected
     */
    protected $endpoint = '';

    /**
     * Property: args
     * Any additional URI components after the endpoint and verb have been removed,
     * in our case, an integer ID for the resource.
     * e.g.: /<endpoint>/<verb>/<arg0>/<arg1> or /<endpoint>/<arg0>
     *
     * @var    array
     * @access protected
     */
    protected $args = array();

    /**
     * Property: file
     * Stores the input of the PUT request
     *
     * @var    string
     * @access protected
     */
    protected $file = null;

    /**
     * Property: requestdata
     * The data the end point needs to act on in an array format.  It can either
     * be for the Request Parameters or a JSOn file
     *
     * @var    array
     * @access protected
     */
    protected $requestdata = array();

    /**
     * Property: webtemplate
     * A Webtemplate Object
     *
     * @var    \webtemplate\general\Application
     * @access protected
     */
    protected $webtemplate = null;

    /**
     * Property: acceptjson
     * If this is true the client cann accept JSON encoded data.
     *
     * @var    boolean
     * @access protected
     */
    protected $acceptjson = false;

    /**
     * Constructor
     *
     * @param string                               $args        Client request string /<endpoint>/<arg0>/<arg1>.
     * @param string                               $method      The HTTP method used to call the api.
     * @param array                                $post        The contents of the $_POST super global array.
     * @param array                                $get         The contents of the $_GET super global array.
     * @param string                               $file        The contents of php://input.
     * @param string                               $contenttype HTTP body content type i.e. application/json.
     * @param string                               $accepttype  HTTP body content type that client can accept.
     * @param \webtemplate\application\Application $webtemplate Webtemplate object containing all app classes.
     *
     * @throws AppException If an invalid HTTP Method is called.
     * @throws AppException If an invalid HTTP Content Type is received.
     * @throws AppException If an invalid HTTP Accept header is received.
     *
     * @access public
     */
    public function __construct(
        string $args,
        string $method,
        array $post,
        array $get,
        string $file,
        string $contenttype,
        string $accepttype,
        \webtemplate\application\Application  &$webtemplate
    ) {

        $this->args = explode('/', rtrim($args, '/'));
        $this->endpoint = array_shift($this->args);
        $this->method = $method;

        switch ($this->method) {
            case 'POST':
                $request = $this->cleanInputs($post);

                // Convert $file from JSON to an PHP array
                $filearray = json_decode($file, true);
                break;
            case 'GET':
            case 'DELETE':
            case 'HEAD':
            case 'OPTIONS':
                $request = $this->args;
                break;
            case 'PATCH':
            case 'PUT':
                $request = $this->cleanInputs($post);

                // Convert $file from JSON to an PHP array
                $filearray = json_decode($file, true);
                break;
            default:
                throw new AppException(
                    'Method Not Allowed: ' . $this->method,
                    405
                );
        }

        // If there is no data in file make it an empty array
        if ($file == null) {
            $filearray = array();
        }

        // Test that the data sent to the application is in a recognised format
        // Move the request data into the $requestdata property
        if ($contenttype == 'application/json') {
            $this->requestdata = $filearray;
        } elseif (($contenttype == 'application/x-www-form-urlencoded')) {
            $this->requestdata = $request;
        } elseif (($contenttype == 'text/plain; charset=ISO-8859-1')) {
            $this->requestdata = $request;
        } elseif ($contenttype == '') {
            $this->requestdata = $request;
        } else {
            throw new AppException(
                "Invalid header Content-Type: " . $contenttype,
                400
            );
        }

        // Test that the client will accept the data in a format that the server
        // can provide
        if (strpos($accepttype, 'application/json') !== false) {
            $this->acceptjson = true;
        } else {
            throw new AppException("Invalid header Accept: " . $accepttype, 400);
        }

        $this->webtemplate = $webtemplate;
    }

    /**
     * This function process the request by calling the selected endpoint.  If no
     * endpoint is found an error is returned.
     *
     * @return string The JSON encoded result of the API request.
     *
     * @access public
     */
    public function processAPI()
    {
        $classFile = __DIR__ . "/endpoints/" . $this->endpoint . ".class.php";
        if (file_exists($classFile)) {
            include $classFile;
            $classname = 'webtemplate\\rest\\endpoints\\' . ucfirst($this->endpoint);
            if (class_exists('webtemplate\\rest\\endpoints\\' . $this->endpoint)) {
                $endpointclass = new $classname($this->webtemplate);
                $permissionsresult = $endpointclass->permissions();
                if ($permissionsresult === true) {
                    $result = $endpointclass->endpoint(
                        $this->method,
                        $this->args,
                        $this->requestdata
                    );
                    return $this->response($result);
                } else {
                    return $this->response($permissionsresult);
                }
            }
        }

        // The endpoint does not exist.
        $err['data'] = array('Errormsg' => "No Endpoint: $this->endpoint");
        $err['code'] = 501;
        return $this->response($err);
    }

    /**
     * This function sends the HTTP response and encodes the data in JSON
     *
     * @param array $data The data to be json encoded and returned to the user.
     *
     * @return string The JSON encoded result of the API request.
     *
     * @access private
     */
    private function response(array $data)
    {
        $status = $data['code'];
        $result = array();
        $result['header'] = "HTTP/1.1 " . $status . " ";
        $result['header'] .= $this->requestStatus($status);
        if ((key_exists('data', $data)) and $this->acceptjson == true) {
            $result['data'] = json_encode($data['data']);
        }
        if (key_exists('options', $data)) {
            $result['options'] = "Allow: " . $data['options'];
        }
        if (key_exists('head', $data)) {
            $result['head'] = $data['head'];
        }
        return $result;
    }

    /**
     * This function processes the input data and returns it in an array
     *
     * @param mixed $data The data to be processed.
     *
     * @return array An Array containing the request data from the client
     *
     * @access private
     */
    private function cleanInputs($data)
    {
        $clean_input = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    /**
     * This function translates error codes to error strings.
     *
     * @param integer $code The http status code of the request.
     *
     * @return string containing the text version of the error code
     *
     * @access private
     */
    private function requestStatus(int $code)
    {
        $status = array(
            200 => 'OK',
            201 => 'Created',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorised',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            500 => 'Internal Server Error',
            501 => 'Not Implemented'
        );
        return ($status[$code]) ? $status[$code] : $status[500];
    }
}
