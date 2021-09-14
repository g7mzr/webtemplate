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
 *  Webtemplate RestFul API groups endpoint class
 *
 **/
class Groups
{
    /**
     * Traits to be used by this Class
     */
    use TraitEndPointCommon;
    use TraitPermissions;

    /**
     * Property: inputdata
     *
     * @var    array
     * @access protected
     */
    protected $inputdata = array();

    /**
     * Property: accessgroup
     * This is the group a user must be a member of to access this resource
     *
     * @var    string
     * @access protected
     */
    protected $accessgroup = "editgroups";

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
        if (\count($this->args) > 1) {
            $errmsg = array('ErrorMsg' => 'groups: Invalid number of arguments');
            return array('data' => $errmsg, 'code' => 400);
        }

        if (\count($this->args) == 0) {
            $groups = $this->webtemplate->editgroups()->getGroupList();
            if (\g7mzr\webtemplate\general\General::isError($groups)) {
                $errmsg = array('ErrorMsg' => $groups->getMessage());
                return array('data' => $errmsg, 'code' => 400);
            }
        }
        if (\count($this->args) == 1) {
            if (is_numeric($this->args[0])) {
                $guid = $this->args[0];
            } else {
                $result = $this->webtemplate->editgroups()->getGroupid(
                    $this->args[0]
                );
                if (!\g7mzr\webtemplate\general\General::isError($result)) {
                    $guid = $result;
                } else {
                    $errmsg = array('ErrorMsg' => $result->getMessage());
                    return array('data' => $errmsg, 'code' => 400);
                }
            }
            $groups = $this->webtemplate->editgroups()->getSingleGroup($guid);
            if (\g7mzr\webtemplate\general\General::isError($groups)) {
                $errmsg = array('ErrorMsg' => $groups->getMessage());
                return array('data' => $errmsg, 'code' => 400);
            }
        }
        $arr = array('data' => $groups, 'code' => 200);
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
        $this->requestdata['groupid'] = 0;
        $fieldspresent = $this->groupFieldsPresent();
        if ($fieldspresent !== true) {
            $data = array('ErrorMsg' => $fieldspresent);
            $code = 400;
            return array('data' => $data, 'code' => $code);
        }

        $resultArray = $this->webtemplate->editgroups()->validateGroupData(
            $this->inputdata
        );

        //if ($resultArray[0]['msg'] != '') {
        //    $data = array('ErrorMsg'=>$resultArray[0]['msg']);
        //    return array('data'=>$data, 'code'=>400);
        //}

        //Check if the groupname aready exists
        $groupname =  $resultArray[0]['groupname'];
        if ($this->webtemplate->editgroups()->checkGroupExists($groupname)) {
            $data = array('ErrorMsg' => 'Group ' . $groupname . ' already exists.');
            return array('data' => $data, 'code' => 409);
        }

        $result = $this->webtemplate->editgroups()->saveGroup(
            $resultArray[0]['groupid'],
            $resultArray[0]['groupname'],
            $resultArray[0]['description'],
            $resultArray[0]['useforproduct'],
            $resultArray[0]['autogroup']
        );

        if (\g7mzr\webtemplate\general\General::isError($result)) {
            $data = array('ErrorMsg' => $result->getMessage());
            return array('data' => $data, 'code' => 500);
        }

        $data = $this->webtemplate->editgroups()->getSingleGroup(
            $resultArray[0]['groupid']
        );
        if (\g7mzr\webtemplate\general\General::isError($data)) {
            // Return error message
            $errmsg = array('Errormsg' => $data->getMessage());
            return array('data' => $errmsg, 'code' => 404);
        }
        return array('data' => $data, 'code' => 201);
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
        if (\count($this->args) != 1) {
            $errmsg = array('ErrorMsg' => 'groups: Invalid number put of arguments');
            return array('data' => $errmsg, 'code' => 400);
        }


        if (is_numeric($this->args[0])) {
            $gid = $this->args[0];
        } else {
            $result = $this->webtemplate->editgroups()->getGroupid($this->args[0]);
            if (!\g7mzr\webtemplate\general\General::isError($result)) {
                $gid = $result;
            } else {
                $errmsg = array('ErrorMsg' => $result->getMessage());
                return array('data' => $errmsg, 'code' => 400);
            }
        }

        $this->requestdata['groupid'] = $gid;
        $fieldspresent = $this->groupFieldsPresent();
        if ($fieldspresent !== true) {
            $data = array('ErrorMsg' => $fieldspresent);
            $code = 400;
            return array('data' => $data, 'code' => $code);
        }

        $resultArray = $this->webtemplate->editgroups()->validateGroupData(
            $this->inputdata
        );

        //if ($resultArray[0]['msg'] != '') {
        //    $data = array('ErrorMsg'=>$resultArray[0]['msg']);
        //    return array('data'=>$data, 'code'=>400);
        //}

        $datachanged = $this->webtemplate->editgroups()->groupDataChanged(
            $resultArray[0]['groupid'],
            $resultArray[0]['groupname'],
            $resultArray[0]['description'],
            $resultArray[0]['useforproduct'],
            $resultArray[0]['autogroup']
        );

        if (\g7mzr\webtemplate\general\General::isError($datachanged)) {
            $data = array('ErrorMsg' => $datachanged->getMessage());
            return array('data' => $data, 'code' => 500);
        }

        if ($datachanged == false) {
            $data = array('ErrorMsg' => "You have not made any changes");
            return array('data' => $data, 'code' => 400);
        }


        $result = $this->webtemplate->editgroups()->saveGroup(
            $resultArray[0]['groupid'],
            $resultArray[0]['groupname'],
            $resultArray[0]['description'],
            $resultArray[0]['useforproduct'],
            $resultArray[0]['autogroup']
        );

        if (\g7mzr\webtemplate\general\General::isError($result)) {
            $data = array('ErrorMsg' => $result->getMessage());
            return array('data' => $data, 'code' => 500);
        }

        $data = $this->webtemplate->editgroups()->getSingleGroup(
            $resultArray[0]['groupid']
        );
        if (\g7mzr\webtemplate\general\General::isError($data)) {
            // Return error message
            $errmsg = array('Errormsg' => $data->getMessage());
            return array('data' => $errmsg, 'code' => 404);
        }
        return array('data' => $data, 'code' => 201);
    }

    /**
     * This function implements DELETE command.
     *
     * @return array The result of the action undertaken by the API end point.
     *
     * @access protected
     */
    protected function delete()
    {

        if (\count($this->args) != 1) {
            $errmsg = array(
                'ErrorMsg' => 'groups (delete): Invalid number of arguments'
            );
            return array('data' => $errmsg, 'code' => 400);
        }
        if (is_numeric($this->args[0])) {
            $gid = $this->args[0];
        } else {
            $result = $this->webtemplate->editgroups()->getGroupid(
                $this->args[0]
            );
            if (!\g7mzr\webtemplate\general\General::isError($result)) {
                $gid = $result;
            } else {
                if ($result->getCode() == DB_ERROR_NOT_FOUND) {
                    return array('code' => 204);
                } else {
                    $errmsg = array('ErrorMsg' => $result->getMessage());
                    return array('data' => $errmsg, 'code' => 500);
                }
            }
        }
        $groupdetails = $this->webtemplate->editgroups()->getSingleGroup($gid);
        if (\g7mzr\webtemplate\general\General::isError($groupdetails)) {
            if ($groupdetails->getCode() == DB_ERROR_NOT_FOUND) {
                return array('code' => 204);
            } else {
                $errmsg = array('ErrorMsg' => $groupdetails->getMessage());
                return array('data' => $errmsg, 'code' => 500);
            }
        }
        if ($groupdetails[0]['editable'] == 'N') {
            $errmsg = array("ErrorMsg" => "System groups cannot be deleted");
            return array('data' => $errmsg, 'code' => 400);
        }
        $deleteresult = $this->webtemplate->editgroups()->deleteGroup($gid);
        if (\g7mzr\webtemplate\general\General::isError($deleteresult)) {
            $errmsg = array('ErrorMsg' => "Error deleteing group: " . $this->args[0]);
            return array('data' => $errmsg, 'code' => 500);
        } else {
            return array('code' => 204);
        }
    }

    /**
     * This function checks to see that all the group database fields are present in
     * the input data from the client.
     *
     * The function checks if the mandatory fields are present.  They are
     * groupid, groupname, description, userforproduct and autogroup.
     * userforproduct and autogroup are check boxes in the web interface
     *
     * @return mixed TRUE if all fields are present.  Error Message otherwise.
     *
     * @access private
     */
    private function groupFieldsPresent()
    {
        //Check if all the input fields have been included in the JSON Input
        $fieldspresent = true;
        $errMsg = 'The following mandatory fields are missing: ';

        // Check if the groupname is present
        if (!array_key_exists('groupname', $this->requestdata)) {
            $fieldspresent = false;
            $errMsg .= "groupname ";
        }

        // Check if the description is present
        if (!array_key_exists('description', $this->requestdata)) {
            $fieldspresent = false;
            $errMsg .= "description ";
        }

        // Check if the useforproduct is present
        if (!array_key_exists('useforproduct', $this->requestdata)) {
            $fieldspresent = false;
            $errMsg .= "useforproduct ";
        }

        // Check if the autogroup is present
        if (!array_key_exists('autogroup', $this->requestdata)) {
            $fieldspresent = false;
            $errMsg .= "autogroup ";
        }

        // If at least one maditory field is missing return the error message
        if ($fieldspresent === false) {
            return $errMsg;
        }

        // Check if the input data is valid and transfer it to the working array
        $datafieldsvalid = true;
        $errMsg = "The following fields are invalid: ";

        // Chek the the userid is valid.
        $groupidoptions = array(
            'options' => array(
                //'default' => 3, // value to return if the filter fails
                // other options here
                'min_range' => 0,
                'max_range' => 999999
            ),
            'flags' => FILTER_FLAG_ALLOW_OCTAL,
        );

        $groupid = filter_var(
            $this->requestdata['groupid'],
            FILTER_VALIDATE_INT,
            $groupidoptions
        );
        if ($groupid !== false) {
            $this->inputdata['groupid'] = $groupid;
        } else {
            $datafieldsvalid = false;
            $errMsg .= "groupid ";
        }

       // Check the groupname is valid.
        $groupnameoptions = array(
            'options' => array(
                'regexp' => "/^([a-zA-Z]{5,25})$/"
            )
        );
        $groupname = filter_var(
            $this->requestdata['groupname'],
            FILTER_VALIDATE_REGEXP,
            $groupnameoptions
        );
        if ($groupname !== false) {
            $this->inputdata['groupname'] = $groupname;
        } else {
            $datafieldsvalid = false;
            $errMsg .= "groupname ";
        }

       // Check the groupdescription is valid.
        $groupdescriptionoptions = array(
            'options' => array(
                'regexp' => "/^([a-zA-Z\s\.]{5,225})$/"
            )
        );
        $groupdescription = filter_var(
            $this->requestdata['description'],
            FILTER_VALIDATE_REGEXP,
            $groupdescriptionoptions
        );
        if ($groupname !== false) {
            $this->inputdata['description'] = $groupdescription;
        } else {
            $datafieldsvalid = false;
            $errMsg .= "groupdescription ";
        }


        // Options for all check boxes
        $checkboxoptions = array(
            'options' => array(
                'regexp' => "/^(Y)|(N)$/"
            )
        );

        // Check if group is to be used for products
        $useforproduct = filter_var(
            $this->requestdata['useforproduct'],
            FILTER_VALIDATE_REGEXP,
            $checkboxoptions
        );
        if ($useforproduct !== false) {
            if ($useforproduct == 'Y') {
                $this->inputdata['useforproduct'] = 'Y';
            }
        } else {
            $datafieldsvalid = false;
            $errMsg .= "useforproduct ";
        }

        // Check if group is to be used for products
        $autogroup = filter_var(
            $this->requestdata['autogroup'],
            FILTER_VALIDATE_REGEXP,
            $checkboxoptions
        );
        if ($autogroup !== false) {
            if ($autogroup == 'Y') {
                $this->inputdata['autogroup'] = 'Y';
            }
        } else {
            $datafieldsvalid = false;
            $errMsg .= "autogroup ";
        }

        if ($datafieldsvalid === false) {
            return $errMsg;
        }

        return true;
    }
}
